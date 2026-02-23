<?php


namespace App\Core;

/**
 * Contrôleur parent (Moteur de rendu).
 * Tous les contrôleurs de l'application doivent hériter de cette classe.
 * Elle centralise la logique d'affichage pour éviter de dupliquer le code de chargement des vues.
 */
abstract class Controller
{

    /**
     * Génère une vue spécifique, lui injecte des données, et l'intègre dans le layout global.
     * Utilise la mémoire tampon (ob_start) pour construire la page en silence avant de l'afficher.
     */
    protected function render(string $viewPath, array $data = []): void
    {
        ob_start();
        extract($data);

         // Construction du chemin ciblant spécifiquement le dossier des pages
        $viewFile = __DIR__ . '/../View/Page/' . $viewPath . '.php';

        // Sécurité : on empêche le script de planter silencieusement si le fichier physique manque
        if(file_exists($viewFile)) {
           
            require $viewFile;
        } else {
            
            throw new \Exception("La vue $viewFile n'existe pas.");
        }

        $content = ob_get_clean();

        // Chargement du squelette HTML principal (Layout)
        $layoutFile = __DIR__ . '/../View/layout.php';


        if(file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            throw new \Exception("Le layout $layoutFile n'existe pas.");
        }
    }
}