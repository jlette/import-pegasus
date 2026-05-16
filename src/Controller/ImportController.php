<?php

namespace App\Controller;

use App\Core\Controller;

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

        // Récupération des données métiers envoyées par le FormData de ton JS !
        $typeEtudiant = $_POST['type_etudiant'] ?? null;
        $cursus = $_POST['cursus'] ?? null;

        if (!$typeEtudiant || !$cursus) {
            $this->sendJson(['error' => 'Le type d\'étudiant et le cursus sont obligatoires.'], 400);
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

        // 4. Déplacer le fichier vers le dossier sécurisé (à la racine, hors de src/)
        $uploadDir = __DIR__ . '/../../../tmp/uploads/'; // Ajuste ce chemin selon l'emplacement exact de ton contrôleur

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
            // Exemple : 
            // $excelService = new \App\Service\ExcelReaderService();
            // $resultat = $excelService->traiterAdmissions($destination, $typeEtudiant, $cursus);

            // Nettoyage de sécurité
            unlink($destination);

            // 6. Renvoyer un succès à ton Javascript
            $this->sendJson([
                'success' => true,
                'message' => 'Fichier importé avec succès',
                'type_traite' => $typeEtudiant, // Juste pour tester que PHP a bien compris le JS
                'cursus_traite' => $cursus // Juste pour tester que PHP a bien compris le JS
            ], 200);
        } catch (\Exception $e) {
            if (file_exists($destination)) {
                unlink($destination);
            }
            $this->sendJson(['error' => 'Erreur métier : ' . $e->getMessage()], 500);
        }
    }
}
