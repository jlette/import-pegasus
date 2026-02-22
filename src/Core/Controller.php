<?php

// Définit l'espace de noms pour organiser les contrôleurs.
namespace App\Core;

// La classe Controller gère les requêtes liées à la page d'accueil.
class Controller
{
    /**
     * Prépare et rend une vue.
     * @param string $view Le nom du fichier de la vue (sans l'extension .php).
     * @param array $data Les données à transmettre à la vue.
     * @return string Le contenu HTML de la vue.
     * @throws \Exception Si le fichier de la vue n'est pas trouvé.
     */
    public function render($view, $data = [])
    {
        // Construit le chemin complet vers le fichier de la vue.
        $viewPath = __DIR__ . '/../../views/pages/' . $view . '.php';

        // Extrait les données du tableau associatif en variables individuelles.
        // Ces variables deviennent accessibles directement dans le fichier de la vue.
        extract($data);

        // Vérifie si le fichier de la vue existe.
        if(file_exists($viewPath)) {
            // Si la vue existe, appelle la méthode qui en capture le contenu.
            return $this->renderView($viewPath);
        } else {
            // Si la vue n'existe pas, lance une exception pour signaler l'erreur.
            throw new \Exception("La vue $view n'existe pas.");
        }
    }

    /**
     * Capture le contenu d'un fichier de vue en utilisant la mise en mémoire tampon.
     * @param string $viewPath Le chemin complet vers le fichier de la vue.
     * @return string Le contenu HTML capturé.
     */
    private function renderView($viewPath)
    {
        // Démarre la mise en mémoire tampon de la sortie.
        ob_start();
        // Inclut le fichier de la vue. Son contenu est capturé dans le tampon.
        include $viewPath;
        // Récupère le contenu du tampon, puis l'efface et arrête la temporisation.
        return ob_get_clean();
    }
}