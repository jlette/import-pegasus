<?php

namespace App\Controller;

use App\Core\Controller;
use App\Service\ExcelReaderService;
use App\Service\FileUploadService;
use Exception;

class ImportController extends Controller
{
    public function handleUpload(): void
    {
        // 1. Sécurité : Vérifier la requête et la présence des données
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(['error' => 'Méthode non autorisée.'], 405);
        }

        if (!isset($_FILES['admis_file'])) {
            $this->sendJson(['error' => 'Aucun fichier reçu.'], 400);
        }

        $typeEtudiant = $_POST['type_etudiant'] ?? null;
        $cursus = $_POST['cursus'] ?? null;

        // L'année de campagne ne peut pas être déduite de l'horloge : les
        // imports DRI de décembre portent sur la rentrée de janvier suivante.
        $anneeCampagne = $this->lireAnneeCampagne($_POST['annee'] ?? null);

        if ($anneeCampagne === null) {
            $this->sendJson(['error' => 'L\'année de campagne est invalide.'], 400);
        }

        if (!$typeEtudiant) {
            $this->sendJson(['error' => 'Le type d\'étudiant est obligatoire.'], 400);
        }

        if ($typeEtudiant !== 'agreg' && empty($cursus)) {
            $this->sendJson(['error' => 'Le cursus est obligatoire pour ce type d\'étudiant.'], 400);
        }

        try {
            // Délégation : L'upload
            $fileUploadService = new FileUploadService();
            $destination = $fileUploadService->uploadExcelFile($_FILES['admis_file']);

            // OPTIMISATION : On charge la connexion PDO au point d'entrée (Le Contrôleur)
            $db = require dirname(__DIR__, 2) . '/config/db.php';

            // Délégation : Logique métier (On lui passe $db en paramètre)
            $excelService = new ExcelReaderService();
            $resultat = $excelService->traiterAdmissions(
                $destination,
                $typeEtudiant,
                $cursus,
                $db,
                $anneeCampagne
            );

            // Nettoyage : Suppression du fichier Excel temporaire
            if (file_exists($destination)) {
                unlink($destination);
            }

            // 5. Réponses JSON selon le résultat métier
            if (!empty($resultat['erreurs'])) {
                $this->sendJson([
                    'success' => false,
                    'message' => 'Le fichier contient des données invalides. Veuillez corriger les lignes indiquées.',
                    'erreurs' => $resultat['erreurs']
                ], 422);
            }

            $this->sendJson([
                'success' => true,
                'message' => 'Fichier importé avec succès',
                'type_traite' => $typeEtudiant,
                'cursus_traite' => $cursus,
                'filename' => $resultat['output_filename'],
                'erreurs' => [],
                'nb_importes' => isset($resultat['succes']) ? count($resultat['succes']) : 0,
                'nb_ecartes' => $resultat['ecartes'] ?? 0
            ], 200);
        } catch (Exception $e) {
            // Interception des erreurs de l'upload ou du système
            $code = $e->getCode();
            $httpCode = ($code >= 400 && $code < 600) ? $code : 500;

            // Sécurité : on tente de supprimer le fichier s'il a planté en cours de route
            if (isset($destination) && file_exists($destination)) {
                unlink($destination);
            }

            $this->sendJson(['error' => $e->getMessage()], $httpCode);
        }
    }

    /**
     * Valide l'année de campagne transmise par le formulaire.
     *
     * Une fenêtre volontairement étroite autour de l'année courante : une
     * erreur de saisie sur cette valeur fausserait l'année d'inscription et la
     * promotion de toute la population importée.
     *
     * @return int|null L'année validée, ou null si la saisie est invalide
     */
    private function lireAnneeCampagne(mixed $saisie): ?int
    {
        $anneeCourante = (int) date('Y');

        if ($saisie === null || $saisie === '') {
            return $anneeCourante;
        }

        if (!is_numeric($saisie)) {
            return null;
        }

        $annee = (int) $saisie;

        return ($annee >= $anneeCourante - 1 && $annee <= $anneeCourante + 1) ? $annee : null;
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

        // Le chemin absolu vers le dossier temporaire
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

        readfile($filePath);

        // Nettoyage
        unlink($filePath);
        exit;
    }
}