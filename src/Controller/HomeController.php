<?php

namespace App\Controller;

use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        // Données à transmettre à la vue
        $data = [
            'title' => 'Import Pegasus - Accueil',
            'description' => 'Bienvenue sur notre site.'
        ];
        // Retourne le chemin de la vue avec les données
        return $this->render('homes', $data);
    }
}