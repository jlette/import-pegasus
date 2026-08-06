<?php

namespace App\Service;

use Exception;

/**
 * Service dédié à la gestion sécurisée de l'upload des fichiers Excel.
 * Isole la logique de manipulation des fichiers du système d'exploitation.
 */
class FileUploadService
{
    /**
     * Valide et sauvegarde le fichier uploadé dans le dossier temporaire.
     *
     * @param array $file Le tableau $_FILES['nom_du_champ']
     * @return string Le chemin absolu vers le fichier sauvegardé
     * @throws Exception Si le fichier est invalide, non autorisé ou impossible à sauvegarder
     */
    public function uploadExcelFile(array $file): string
    {
        // 1. Vérification des erreurs de transfert
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors du transfert du fichier.', 400);
        }

        // 2. Vérification stricte du type MIME (Sécurité)
        $allowedMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        $fileMimeType = mime_content_type($file['tmp_name']);
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            throw new Exception('Format non autorisé. Seuls les fichiers Excel sont acceptés.', 415);
        }

        // 3. Préparation du dossier de destination
        $uploadDir = dirname(__DIR__, 2) . '/tmp/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 4. Génération d'un nom de fichier unique et sécurisé
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('import_') . '.' . $extension;
        $destination = $uploadDir . $newFileName;

        // 5. Déplacement du fichier
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Impossible de sauvegarder le fichier sur le serveur.', 500);
        }

        return $destination;
    }
}
