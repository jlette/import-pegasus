<?php

/**
 * Icônes SVG en ligne.
 *
 * Quatre icônes seulement étant utilisées, les dessiner directement évite de
 * dépendre du kit Font Awesome — un script tiers, rattaché à un compte, chargé
 * depuis Internet à chaque affichage. L'outil manipule des données
 * personnelles et doit rester fonctionnel sans accès sortant.
 *
 * Les tracés sont des formes géométriques simples, au trait, cohérentes avec le
 * SVG déjà utilisé par le bouton de fermeture de la modale. Elles n'empruntent
 * à aucune bibliothèque : pas de question de licence ni d'attribution.
 *
 * Les icônes sont décoratives : elles portent aria-hidden, le sens étant porté
 * par le texte adjacent.
 */

if (!function_exists('icone')) {
    /**
     * @param string $nom     Identifiant de l'icône
     * @param string $classes Classes CSS appliquées au <svg>
     */
    function icone(string $nom, string $classes = ''): string
    {
        $traces = [
            // Flèche vers le haut sortant d'un bac : dépôt de fichier.
            'upload' => '<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/>'
                . '<path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>',

            // Feuillet avec coin replié : le canevas produit.
            'fichier' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>'
                . '<path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>',

            // Triangle d'alerte.
            'alerte' => '<path d="M10.3 4.3 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0z"/>'
                . '<path d="M12 9v4"/><path d="M12 17h.01"/>',

            // Croix de fermeture.
            'fermer' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        ];

        if (!isset($traces[$nom])) {
            return '';
        }

        return sprintf(
            '<svg class="icon %s" viewBox="0 0 24 24" width="1em" height="1em" fill="none" '
                . 'stroke="currentColor" stroke-width="2" stroke-linecap="round" '
                . 'stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
            htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'),
            $traces[$nom]
        );
    }
}
