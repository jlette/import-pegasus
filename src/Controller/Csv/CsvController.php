<?php

namespace App\Controller\Csv;

use App\Core\Controller;

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
            'title' => 'Import Pegasus - Accueil',
            'description' => 'Bienvenue sur notre site.'
        ];

        return $this->render('Csv/index', $data);
    }
}