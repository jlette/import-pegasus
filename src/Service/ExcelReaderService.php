<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Factory\StudentFactory;
use App\Model\Exception\AbstractImportException;
use App\Interface\ErreurGlobaleInterface;
use App\Model\Exception\FileTooLargeException;
use Exception;
use App\Database\LazyPdo;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;



class ExcelReaderService
{
    /**
     * Traite le fichier d'admissions brut et génère le canevas PEGASUS.
     */
    public function traiterAdmissions(
        string $filePath,
        string $formation,
        string $cursus,
        LazyPdo $db,
        ?int $anneeCampagne = null,
    ): array
    {
        // 1. Allonger le temps d'exécution
        set_time_limit(120);

        // Limitation de conservation : un canevas jamais téléchargé, ou un
        // traitement interrompu, laisse des données personnelles sur le disque.
        (new TemporaryFilePurger(dirname(__DIR__, 2) . '/tmp/uploads'))->purger();

        // 2. La Factory nous donne la bonne stratégie
        $strategy = StudentFactory::create($formation, $cursus, $db, $anneeCampagne);

        // 3. Le Service prépare la lecture
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);

        // OPTIMISATION 1 : Ne charger QUE la première feuille
        if (method_exists($reader, 'listWorksheetNames')) {
            $sheetNames = $reader->listWorksheetNames($filePath);
            if (!empty($sheetNames)) {
                $reader->setLoadSheetsOnly($sheetNames[0]);
            }
        }

        // Plafond mémoire. Le filtre lit une ligne de plus que la limite, ce
        // qui permet de détecter un dépassement et de refuser le fichier au
        // lieu de le tronquer sans le dire.
        $filtreLignes = new MaxRowsReadFilter();
        $reader->setReadFilter($filtreLignes);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Le dépassement porte sur les lignes de données, en-tête exclue.
        if (count($rows) > $filtreLignes->maxRow() + 1) {
            throw new FileTooLargeException($filtreLignes->maxRow());
        }

        $etudiants = [];
        $erreurs = [];
        // Catégories d'anomalies, pour la journalisation : le journal recense
        // des types et des volumétries, jamais le contenu des lignes en cause.
        $typesAnomalies = [];
        $currentLot = 0;
        $currentSsl = 0;
        $nbEcartes = 0;

        $filtre = $strategy->admissionFilter();
        $canonicalizer = $strategy->canonicalizer();

        $headers = [];

        if (!empty($rows)) {
            // On extrait la ligne 1 (les en-têtes)
            $rawHeaders = array_shift($rows);

            // --- NOUVEAU : PROTECTION CONTRE LES COLONNES EN DOUBLE ---
            $headerCounts = [];
            foreach ($rawHeaders as $header) {
                $cleanHeader = trim((string)$header);

                if (isset($headerCounts[$cleanHeader])) {
                    $headerCounts[$cleanHeader]++;
                    // S'il y a un 2ème "Nom", on l'appelle "Nom_2" pour ne pas écraser le vrai
                    $headers[] = $cleanHeader . '_' . $headerCounts[$cleanHeader];
                } else {
                    $headerCounts[$cleanHeader] = 1;
                    $headers[] = $cleanHeader;
                }
            }
            // -----------------------------------------------------------
        }

        // 4. Traitement des lignes
        foreach ($rows as $index => $row) {

            $isRowTotallyEmpty = empty(array_filter($row, function ($val) {
                return trim((string)$val) !== '';
            }));

            if ($isRowTotallyEmpty) {
                continue;
            }

            if (count($headers) > count($row)) {
                $row = array_pad($row, count($headers), null);
            } elseif (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }

            // Les variantes de fichier d'un meme cursus ne nomment pas les
            // colonnes de la meme facon : on les ramene aux noms canoniques du
            // dictionnaire avant tout traitement.
            $mappedRow = $canonicalizer->canonicaliser(array_combine($headers, $row));

            // Les exports de plateforme melent frequemment admis, non-admis,
            // listes complementaires et desistements : seuls les candidats a
            // importer sont retenus.
            if (!$filtre->retient($mappedRow)) {
                $nbEcartes++;
                continue;
            }

            // CORRECTION : On aligne le numéro d'erreur avec la VRAIE ligne Excel 
            // (+1 car l'index démarre à 0, +1 pour compenser la ligne d'en-tête)
            $numeroLigneExcel = $index + 2;

            try {
                $etudiant = $strategy->createStudent($mappedRow, $currentLot, $currentSsl);
                $etudiants[] = $etudiant;
                $currentLot++;
            } catch (ErreurGlobaleInterface $e) {
                // Anomalie qui ne dépend pas de la ligne en cours — colonne
                // absente de l'en-tête, annuaire injoignable. Toutes les lignes
                // échoueraient pour la même raison : un message unique vaut
                // mieux qu'un rapport de N lignes identiques (RG-03).
                $erreurs = [$e->getMessage()];
                $typesAnomalies = [$this->categorie($e) => 1];
                break;
            } catch (AbstractImportException $e) {
                $erreurs[] = "Ligne $numeroLigneExcel : " . $e->getMessage();
                $typesAnomalies[$this->categorie($e)] = ($typesAnomalies[$this->categorie($e)] ?? 0) + 1;
            } catch (Exception $e) {
                $erreurs[] = "Ligne $numeroLigneExcel (Erreur système) : " . $e->getMessage();
                $typesAnomalies[$this->categorie($e)] = ($typesAnomalies[$this->categorie($e)] ?? 0) + 1;
            }
        }

        // 5. GÉNÉRATION DU FICHIER CSV PEGASUS
        $outputFilename = null;

        if (empty($erreurs) && !empty($etudiants)) {
            $csvExport = new CsvExportService();
            $outputDir = dirname(__DIR__, 2) . '/tmp/uploads/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            $outputFilename = $csvExport->generateCsv(
                $etudiants,
                $outputDir,
                $cursus,
                $strategy->canevasProfile()
            );
        }

        return [
            'succes' => $etudiants,
            'erreurs' => $erreurs,
            'types_anomalies' => $typesAnomalies,
            'ecartes' => $nbEcartes,
            // Réserves sur un import pourtant abouti : codes concours issus du
            // repli embarqué faute d'annuaire. Sans cette remontée, le canevas
            // dégradé serait indiscernable d'un canevas normal.
            'avertissements' => $strategy->avertissements(),
            'output_filename' => $outputFilename
        ];
    }

    /**
     * Catégorie d'une anomalie, pour la journalisation.
     *
     * Seul le nom court de l'exception est retenu : son message reprend
     * souvent la valeur rencontrée dans le fichier, qui peut être une donnée
     * personnelle et n'a rien à faire dans un journal.
     */
    private function categorie(\Throwable $erreur): string
    {
        $parties = explode('\\', $erreur::class);

        return end($parties);
    }
}