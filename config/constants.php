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
define('BASE_URL', 'http://localhost/import-pegasus/public');
define('APP_ROOT', __DIR__ . '/..');

// Configuration de la base de données (à remplir)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'import_pegasus');