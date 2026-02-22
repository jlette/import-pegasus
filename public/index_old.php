<?php
// Fichier : public/index.php (Le Front Controller)

// 1. Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Chargement de la configuration
require_once __DIR__ . '/../config/config.php';

// 3. Chargement de la liste des routes autorisées
require_once __DIR__ . '/../config/routes.php';

use App\Controllers\HomeController;

// 4. Logique du Routeur (Analyse de l'URL)
$availableRouteNames = array_keys(AVAILABLE_ROUTES);

// Si le visiteur demande une page spécifique (?page=...) et qu'elle existe
if (isset($_GET['page']) && in_array($_GET['page'], $availableRouteNames, true)) {
    $page = $_GET['page'];
} else {
    // Sinon, on charge la page par défaut (l'accueil)
    $page = 'home';
}

// 5. Appel du Contrôleur approprié selon la route
switch ($page) {
    case 'home':
        $controller = new HomeController();
        $template = $controller->index();
        break;
    // Ajoutez d'autres cas pour vos autres contrôleurs ici
    default:
        $controller = new HomeController();
        $template = $controller->index();
}

// 6. Affichage final
// On charge le gabarit principal qui va intégrer la variable $template
require_once __DIR__ . '/../views/main.php';