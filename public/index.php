<?php

/**
 * Front Controller (Point d'entrée unique de l'application).
 * Toutes les requêtes HTTP (ex: /accueil, /contact) sont redirigées ici par le serveur (via le .htaccess).
 * Ce fichier a pour rôle d'initialiser l'environnement, de charger la configuration et de lancer le moteur de routage.
 */

// 1. Initialisation de l'Autoloader (Composer)
// Permet à PHP de trouver et de charger automatiquement toutes nos classes (App\...) 
// sans que l'on ait besoin de faire des "require" manuels dans chaque fichier.
require_once '../vendor/autoload.php';

// 2. Chargement de la configuration globale
// Importe le dictionnaire des routes (AVAILABLE_ROUTES) pour que le routeur puisse s'y référer.
require_once '../config/routes.php';

use App\Core\Router;

// --- TEMPORAIRE : MODE DÉVELOPPEMENT ---
// Force l'affichage de toutes les erreurs PHP à l'écran pour faciliter le débogage.
// ⚠️ ALERTE : Ces trois lignes devront impérativement être supprimées ou commentées 
// lors du passage en production (en ligne) pour des raisons de sécurité, 
// afin de ne pas révéler l'architecture du serveur aux visiteurs.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------

// 3. Lancement de l'application
// Le routeur prend le relais : il analyse l'URL demandée par l'utilisateur 
// et orchestre l'appel au bon contrôleur.
$router = new Router();
$router->handleRequest();