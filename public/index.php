<?php

// chemin relatif pour accéder à la config autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Appelle du controleur pour afficher la page d'accueil
use App\Controller\HomeController;

// Récupère l'url depuis la variable globale $_GET
$url = $_GET['url'] ?? ''; // Si aucune url n'est fournie, on utilise 'home' par défaut
$url = trim($url, '/'); // Supprime les slashs en début la fin de l'url
$urlParts = explode('/', $url); // Sépare l'url en parties

//retourne le HommeController si aucun paramètre n'est fourni

$controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';

$methodName = $segments[1] ?? 'index';
// Validation des noms
if (!preg_match('/^[A-Z][a-zA-Z0-9]*Controller$/', $controllerName)) {
    require_once __DIR__ . '/../app/Views/error.php';
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $methodName)) {
    require_once __DIR__ . '/../app/Views/error.php';
    exit;
}

// Liste blanche des contrôleurs et méthodes
$allowedControllers = ['HomeController'];
$allowedMethods = ['index'];

if (!in_array($controllerName, $allowedControllers) || !in_array($methodName, $allowedMethods)) {
    require_once __DIR__ . '/../app/Views/error.php';
    exit;
}
//Namespace complet du controller
$controllerClass = "App\\Controller\\$controllerName";

//Nouvelle instance du controller
if(class_exists($controllerClass)) {
    $controller = new $controllerClass();


    if (method_exists($controller, $methodName)) {

    $params = array_slice($segments, 2); // Récupère les paramètres à partir du troisième segment de l'URL
    call_user_func([$controller, $methodName], ...$params); // Appelle la méthode du controller avec les paramètres

} else {
    require_once __DIR__ . '/../views/pages/404.php';
}
} else {
    require_once __DIR__ . '/../views/pages/404.php';
}