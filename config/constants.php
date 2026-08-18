<?php

/**
 * Configuration applicative.
 *
 * Aucun secret ne doit figurer dans ce fichier : les paramètres sensibles sont
 * lus depuis l'environnement (vhost Apache, unité systemd, ou fichier hors dépôt).
 * L'absence d'une variable requise est une erreur fatale immédiate, jamais un
 * défaut silencieux.
 */

date_default_timezone_set('Europe/Paris');

// Charge le fichier .env s'il existe. Les variables déjà définies dans
// l'environnement — vhost Apache, unité systemd — ne sont jamais écrasées :
// un .env oublié sur le serveur ne peut donc pas prendre le pas sur la
// configuration réelle.
\App\Config\DotEnv::charger(__DIR__ . '/../.env');

/**
 * Lit une variable d'environnement obligatoire.
 *
 * @throws RuntimeException si la variable n'est pas définie
 */
function env_required(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        throw new RuntimeException(
            "Variable d'environnement manquante : {$name}. "
                . "Voir docs/03-cahier-des-charges-technique.md §9."
        );
    }

    return $value;
}

/**
 * Lit une variable d'environnement optionnelle.
 */
function env_default(string $name, string $default): string
{
    $value = getenv($name);

    return ($value === false || $value === '') ? $default : $value;
}

// --- Environnement d'exécution ---------------------------------------------

define('APP_ENV', env_default('APP_ENV', 'production'));
define('IS_PRODUCTION', APP_ENV === 'production');

// En production, aucune erreur ne doit fuiter vers le navigateur : elle
// révélerait l'architecture du serveur et casserait les réponses JSON de l'API.
ini_set('display_errors', IS_PRODUCTION ? '0' : '1');
ini_set('display_startup_errors', IS_PRODUCTION ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// --- Identité de l'application ---------------------------------------------

define('BASE_NAME', 'Import Pegasus');
define('APP_ROOT', __DIR__ . '/..');

// L'URL de base provient de la configuration, jamais de l'en-tête Host qui est
// contrôlé par le client.
define('BASE_URL', rtrim(env_default('APP_BASE_URL', '/import-pegasus'), '/'));

// --- Base de données Oracle (annuaire Jefyco, lecture seule) ----------------

define('DB_HOST', env_default('PEGASUS_DB_HOST', 'oracle1.cri.ulm'));
define('DB_PORT', env_default('PEGASUS_DB_PORT', '1521'));
define('DB_NAME', env_default('PEGASUS_DB_NAME', 'jefyco'));
