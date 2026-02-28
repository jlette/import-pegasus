<?php

namespace App\Controller\Csv;

use App\Core\Controller;
use App\Helper\AssetHelper;

/**
 * Contrôleur dédié à la gestion des imports de fichiers CSV.
 * C'est le point d'entrée principal de l'application (Page d'accueil).
 */
class CsvController extends Controller
{
    /**
     * Prépare les métadonnées et affiche la page d'accueil contenant le formulaire d'import.
     */
    public function index()
    {

        $data = [
            'title' => 'Normalisateur des admissions pour les intégrer dans PEGASUS | ENS PSL',
            'description' => 'Outil interne du CoST (ENS PSL) pour contrôler, formater et valider les canevas CSV des apprenants avant leur import dans PEGASUS.',
        ];

        return $this->render('Csv/index', $data);
    }
}