<?php

/**
 * Front Controller (Point d'entrée unique de l'application).
 * Toutes les requêtes HTTP (ex: /accueil, /contact) sont redirigées ici par le serveur (via le .htaccess).
 * Ce fichier a pour rôle d'initialiser l'environnement, de charger la configuration et de lancer le moteur de routage.
 */

// 1. Initialisation de l'Autoloader (Composer)
// Permet à PHP de trouver et de charger automatiquement toutes nos classes (App\...) 
// sans que l'on ait besoin de faire des "require" manuels dans chaque fichier.
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Chargement de la configuration globale
// Importe le dictionnaire des routes (AVAILABLE_ROUTES) pour que le routeur puisse s'y référer.
require_once __DIR__ . '/../config/constants.php';

require_once __DIR__ . '/../config/routes.php';

use App\Core\Router;

// 3. Lancement de l'application
// Le routeur prend le relais : il analyse l'URL demandée par l'utilisateur 
// et orchestre l'appel au bon contrôleur.
$router = new Router();
$router->handleRequest();