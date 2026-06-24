<?php

namespace App\Controller;

use App\Core\Controller;
use App\Service\ExcelReaderService;

class ImportController extends Controller
{
    public function handleUpload(): void
    {
        // 1. Sécurité : Vérifier que c'est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(['error' => 'Méthode non autorisée.'], 405);
        }

        // 2. Vérifier si le fichier et les données du formulaire JS sont bien là
        if (!isset($_FILES['admis_file'])) {
            $this->sendJson(['error' => 'Aucun fichier reçu.'], 400);
        }

        $file = $_FILES['admis_file'];

        // Récupération des données métiers
        $typeEtudiant = $_POST['type_etudiant'] ?? null;
        $cursus = $_POST['cursus'] ?? null;

        // 1. On vérifie d'abord que le type d'étudiant est bien là (obligatoire pour tous)
        if (!$typeEtudiant) {
            $this->sendJson(['error' => 'Le type d\'étudiant est obligatoire.'], 400);
        }

        // 2. LA RÈGLE MÉTIER : Si ce n'est pas un agreg, le cursus devient obligatoire
        if ($typeEtudiant !== 'agreg' && empty($cursus)) {
            $this->sendJson(['error' => 'Le cursus est obligatoire pour ce type d\'étudiant.'], 400);
        }

        // 3. Vérifications de sécurité du fichier (MIME Type et Erreurs)
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->sendJson(['error' => 'Erreur lors du transfert du fichier.'], 400);
        }

        $allowedMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        $fileMimeType = mime_content_type($file['tmp_name']);
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            $this->sendJson(['error' => 'Format non autorisé. Seuls les fichiers Excel sont acceptés.'], 415);
        }

        // 4. Déplacer le fichier vers le dossier sécurisé
        $uploadDir = dirname(__DIR__, 2) . '/tmp/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('import_') . '.' . $extension;
        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->sendJson(['error' => 'Impossible de sauvegarder le fichier sur le serveur.'], 500);
        }

        // 5. Appeler ton futur Service Métier pour lire l'Excel
        try {
            $excelService = new ExcelReaderService();
            $resultat = $excelService->traiterAdmissions($destination, $typeEtudiant, $cursus);

            // Nettoyage de sécurité du fichier uploadé
            unlink($destination);

            // 6. GESTION DES ERREURS MÉTIER (Lignes invalides -> Code 422)
            if (!empty($resultat['erreurs'])) {
                $this->sendJson([
                    'success' => false,
                    'message' => 'Le fichier contient des données invalides. Veuillez corriger les lignes indiquées.',
                    'erreurs' => $resultat['erreurs']
                ], 422); // <-- Le code HTTP 422 est envoyé ici !
            }

            // 7. SUCCÈS TOTAL (Fichier généré -> Code 200)
            $this->sendJson([
                'success' => true,
                'message' => 'Fichier importé avec succès',
                'type_traite' => $typeEtudiant,
                'cursus_traite' => $cursus,
                'filename' => $resultat['output_filename'],
                'erreurs' => [],
                'nb_importes' => isset($resultat['succes']) ? count($resultat['succes']) : 0
            ], 200);
        } catch (\Exception $e) {
            if (file_exists($destination)) {
                unlink($destination);
            }
            $this->sendJson(['error' => 'Erreur système : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gère le téléchargement du fichier CSV généré
     */
    public function download(): void
    {
        $filename = $_GET['filename'] ?? null;

        // Sécurité de base : empêcher la navigation dans les dossiers (Path Traversal)
        if (!$filename || str_contains($filename, '/') || str_contains($filename, '\\')) {
            http_response_code(400);
            echo "Nom de fichier invalide ou manquant.";
            exit;
        }

        // Le chemin absolu vers ton dossier temporaire
        $filePath = dirname(__DIR__, 2) . '/tmp/uploads/' . basename($filename);

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "Le fichier demandé n'existe pas sur le serveur.";
            exit;
        }

        // Entêtes HTTP indispensables pour forcer le téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // On lit le fichier et on l'envoie au navigateur
        readfile($filePath);

        // Nettoyage : on supprime le CSV une fois téléchargé pour garder un serveur propre !
        unlink($filePath);
        exit;
    }
}
