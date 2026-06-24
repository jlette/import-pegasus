<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Factory\StudentFactory;
use Exception;

class ExcelReaderService
{
    /**
     * Traite le fichier d'admissions brut et génère le canevas PEGASUS.
     *
     * @param string $filePath Chemin vers le fichier uploadé temporairement
     * @param string $formation Formation spécifique (DENS, DRI, AGREG, etc.) provenant du JS
     * @param string $cursus Cursus spécifique si nécessaire
     * @return array Informations sur le fichier final généré
     */
    public function traiterAdmissions(string $filePath, string $formation, string $cursus): array
    {
        // 1. La Factory nous donne la bonne stratégie
        // ATTENTION: Assure-toi d'avoir inversé les paramètres dans StudentFactory::create()
        // pour qu'ils soient bien (string $formation, string $cursus)
        $strategy = StudentFactory::create($formation, $cursus);

        // 2. Le Service lit le fichier physique
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $headers = array_shift($rows);
        $etudiants = [];
        $currentLot = 0;
        $erreurs = [];

        // 3. On boucle sur les lignes
        foreach ($rows as $index => $row) {
            $mappedRow = array_combine($headers, $row);

            try {
                // On passe la LIGNE (array) à la stratégie
                $etudiants[] = $strategy->createStudent($mappedRow, $currentLot, $index + 1);
            } catch (\InvalidArgumentException $e) {
                // On "stack" les erreurs pour les afficher en JS plus tard
                $erreurs[] = "Ligne " . ($index + 2) . " : " . $e->getMessage();
            }
        }

        // 4. GÉNÉRATION DU FICHIER CSV PEGASUS
        $outputFilename = null;

        if (!empty($etudiants)) {
            // Instanciation de ton service d'écriture
            $csvExport = new CsvExportService();

            // Le dossier d'upload que le contrôleur a utilisé
            $outputDir = dirname(__DIR__, 2) . '/tmp/uploads/';

            // On s'assure que le dossier existe
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Appel de la méthode generateCsv() de ton ExcelWriterService
            $outputFilename = $csvExport->generateCsv($etudiants, $outputDir);
        }

        // 5. On renvoie le bon tableau attendu par le contrôleur
        return [
            'succes' => $etudiants,
            'erreurs' => $erreurs,
            'output_filename' => $outputFilename // La clé indispensable pour le JS
        ];
    }
}
