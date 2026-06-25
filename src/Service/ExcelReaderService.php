<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Factory\StudentFactory;
use App\Model\Exception\AbstractImportException; // <-- Le bon namespace est ici
use Exception;

class ExcelReaderService
{
    /**
     * Traite le fichier d'admissions brut et génère le canevas PEGASUS.
     */
    public function traiterAdmissions(string $filePath, string $formation, string $cursus): array
    {
        // 1. La Factory nous donne la bonne stratégie
        $strategy = StudentFactory::create($formation, $cursus);

        // 2. Le Service lit le fichier physique
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $headers = array_shift($rows);
        $etudiants = [];
        $erreurs = [];

        // 3. Initialisation des compteurs PEGASUS
        $currentLot = 1; // S'incrémente à chaque nouvel étudiant (chaque occurrence 'da')
        $currentSsl = 0; // Reste TOUJOURS à 0 pour une occurrence 'da'

        // 4. On boucle sur les lignes
        foreach ($rows as $index => $row) {
            $mappedRow = array_combine($headers, $row);

            // Sécurité : On ignore les lignes complètement vides à la fin du fichier Excel
            if (empty(trim($mappedRow['Nom'] ?? ''))) {
                continue;
            }

            $numeroLigneExcel = $index + 2; // +2 pour compenser l'index 0 et l'en-tête retirée

            try {
                // On passe la LIGNE (array) et les compteurs actuels à la stratégie
                $etudiant = $strategy->createStudent($mappedRow, $currentLot, $currentSsl);

                $etudiants[] = $etudiant;

                // RÈGLE PEGASUS : Le dossier a été créé avec succès, on prépare le lot suivant
                $currentLot++;
            } catch (AbstractImportException $e) {
                // GESTION SOLID : On attrape uniquement nos exceptions métier pour la modale (422)
                $erreurs[] = "Ligne $numeroLigneExcel : " . $e->getMessage();
            } catch (Exception $e) {
                // Exception inattendue (ex: problème serveur)
                $erreurs[] = "Ligne $numeroLigneExcel (Erreur système) : " . $e->getMessage();
            }
        }

        // 5. GÉNÉRATION DU FICHIER CSV PEGASUS
        $outputFilename = null;

        // On ne génère le fichier QUE s'il n'y a eu AUCUNE erreur dans les lignes
        if (empty($erreurs) && !empty($etudiants)) {
            $csvExport = new CsvExportService();

            $outputDir = dirname(__DIR__, 2) . '/tmp/uploads/';

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFilename = $csvExport->generateCsv($etudiants, $outputDir);
        }

        // 6. On renvoie le résultat au contrôleur
        return [
            'succes' => $etudiants,
            'erreurs' => $erreurs,
            'output_filename' => $outputFilename
        ];
    }
}
