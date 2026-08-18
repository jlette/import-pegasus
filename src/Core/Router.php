<?php


namespace App\Core;

/**
 * Moteur de routage principal de l'application.
 * Son rôle est d'intercepter la requête HTTP de l'utilisateur, de nettoyer l'URL,
 * et de lancer le bon contrôleur défini dans config/routes.php.
 */
class Router
{
    /**
     * Analyse l'URL demandée et instancie dynamiquement le contrôleur correspondant.
     */
    public function handleRequest()
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 1. On nettoie le préfixe du dossier de base (Linux & XAMPP)
        $basePath = '/import-pegasus';
        if (strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }

        // 2. On nettoie le "/public" s'il est encore là (XAMPP direct)
        $publicPath = '/public';
        if (strpos($url, $publicPath) === 0) {
            $url = substr($url, strlen($publicPath));
        }

        // 3. Formatage final
        $url = rtrim($url, '/');
        if ($url === '') {
            $url = '/';
        }

        // Détermine la route à suivre (ou bascule sur la page d'erreur par défaut)
        $route = AVAILABLE_ROUTES[$url] ?? DEFAULT_ROUTE;

        $controllerName = $route['controller'];
        $methodName = $route['action'];

        // Instanciation dynamique du contrôleur et exécution de sa méthode
        $controllerInstance = new $controllerName();
        return $controllerInstance->$methodName();
    }
}
