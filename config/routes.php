<?php

/**
 * Fichier de configuration des routes.
 * Ce fichier centralise toutes les correspondances entre les URL demandées par l'utilisateur
 * et les contrôleurs chargés de les traiter. Cela permet d'avoir une vue d'ensemble de l'application.
 */

use App\Controller\IndexController;
use App\Controller\ErrorController;

// Liste des routes valides de l'application
const AVAILABLE_ROUTES = [

    '/' => [
        'controller' => IndexController::class,
        'action' => 'index'
    ],

];

// Route de repli utilisée si l'URL demandée ne correspond à aucune route existante (Erreur 404)
const DEFAULT_ROUTE = [
    'controller' => ErrorController::class,
    'action' => 'notFound'
];
