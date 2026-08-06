<?php

// Configuration de l'application

// Définir la timezone
date_default_timezone_set('Europe/Paris');

// Affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Constantes de l'application
define('BASE_NAME', 'Import Pegasus');
define('BASE_URL', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/import-pegasus');
define('APP_ROOT', __DIR__ . '/..');

// Configuration de la base de données (à remplir)
define('DB_HOST', 'oracle1.cri.ulm');
define('DB_USER', 'annuaire');
define('DB_PASSWORD', 'dromautr');
define('DB_NAME', 'jefyco');
define('DB_PORT', '1521');
