<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Factory\StudentFactory;
use App\Model\Exception\AbstractImportException;
use App\Model\Exception\WrongFileFormatException;
use Exception;
use App\Database\LazyPdo;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;



class ExcelReaderService
{
    /**
     * Traite le fichier d'admissions brut et génère le canevas PEGASUS.
     */
    public function traiterAdmissions(string $filePath, string $formation, string $cursus, LazyPdo $db): array
    {
        // 1. Allonger le temps d'exécution
        set_time_limit(120);

        // 2. La Factory nous donne la bonne stratégie
        $strategy = StudentFactory::create($formation, $cursus, $db);

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

        // OPTIMISATION 2 : Ne pas lire au-delà de 2000 lignes pour préserver la RAM
        $reader->setReadFilter(new MaxRowsReadFilter(2000));

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $etudiants = [];
        $erreurs = [];
        $currentLot = 0;
        $currentSsl = 0;

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

            $mappedRow = array_combine($headers, $row);

            // CORRECTION : On aligne le numéro d'erreur avec la VRAIE ligne Excel 
            // (+1 car l'index démarre à 0, +1 pour compenser la ligne d'en-tête)
            $numeroLigneExcel = $index + 2;

            try {
                $etudiant = $strategy->createStudent($mappedRow, $currentLot, $currentSsl);
                $etudiants[] = $etudiant;
                $currentLot++;
            } catch (WrongFileFormatException $e) {
                // ERREUR FATALE : Ce n'est pas le bon fichier ! 
                // On écrase les erreurs précédentes, on met l'erreur globale, et on arrête tout.
                $erreurs = [$e->getMessage()];
                break;
            } catch (AbstractImportException $e) {
                $erreurs[] = "Ligne $numeroLigneExcel : " . $e->getMessage();
            } catch (Exception $e) {
                $erreurs[] = "Ligne $numeroLigneExcel (Erreur système) : " . $e->getMessage();
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
            'output_filename' => $outputFilename
        ];
    }
}