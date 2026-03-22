<?php

namespace App\Controller;

use App\Core\Controller;

/**
 * Contrôleur de gestion des erreurs globales de l'application.
 * Intervient lorsqu'un utilisateur tente d'accéder à une route non définie.
 */
class ErrorController extends Controller
{
    /**
     * Prépare et affiche la page d'erreur 404 (Ressource introuvable).
     */
    public function notFound()
    {


        $data = [
            'title' => 'Page non trouvée',
            'description' => 'La page que vous recherchez n\'existe pas.'
        ];

        return $this->render('404', $data);
    }
}
