<?php

namespace App\Helper;

/**
 * Gestionnaire des ressources statiques (CSS, JS, Images).
 * * Ce helper a deux objectifs principaux :
 * 1. Centraliser les chemins vers le dossier "public" pour éviter de casser les liens
 * si le projet est déplacé (ex: du dossier local XAMPP vers un serveur de prod).
 * 2. Gérer le "Cache Busting" automatiquement : on force le navigateur à télécharger
 * la nouvelle version du fichier à chaque modification, évitant les problèmes d'affichage.
 */
class AssetHelper
{
// ------------------------------------------------ GESTION CSS -----------------------------------------------------------

    /**
     * Génère une balise <link> CSS avec un versioning automatique (Cache-Busting).
     *
     * @param string $relativePath Chemin relatif du fichier (ex: 'base/_reset.css').
     * @return string Balise HTML complète.
     */
    public static function generateCssTag(string $relativePath): string
    {
        $url = \BASE_URL . '/assets/css/' . $relativePath;

        // TODO (Dev) : time() force le rafraîchissement continu du cache. 
        // À remplacer par filemtime() en production pour de meilleures performances.
        $version = time();

        return '<link rel="stylesheet" href="' . $url . '?v=' . $version . '"/>';
    }

    /**
     * Construit la liste des balises CSS d'un dossier.
     *
     * @param string $directory Le dossier cible (ex: 'base', 'layout').
     * @return string Le bloc HTML indenté, prêt pour l'affichage.
     */
    public static function loadCssTags(string $directory): string
    {
        $dir =  __DIR__ . '/../../public/assets/css/' . $directory;
        $scanned_directory = array_diff(scandir($dir), array('..', '.'));

        $tags = [];

        foreach ($scanned_directory as $file) {
            $tags[] = self::generateCssTag($directory . '/' . $file);
        }

        return implode("\n    ", $tags) . "\n";
    }

    // ------------------------------------------------ GESTION JS -----------------------------------------------------------

    /**
     * Génère la balise HTML pour inclure un script JavaScript.
     * Inclut l'attribut "defer" par défaut pour ne pas bloquer le chargement de la page HTML.
     *
     * @param string $path Le chemin relatif depuis le dossier "public/assets/js/"
     * @return string La balise <script> complète
     */
    public static function assetJsPath(string $path): string
    {
        $url = BASE_URL . '/assets/js/' . $path;

        // HACK : Utilisation de time() pour le développement.
        // Cela génère un numéro unique à chaque seconde pour forcer le navigateur
        // à ne jamais garder le fichier en cache.
        // Note pour la mise en prod : remplacer time() par filemtime() pour plus de performance.
        $version = time();

        return '<script src="' . $url . '?v=' . $version . '" defer></script>';
    }

    // ------------------------------------------------ GESTION IMG -----------------------------------------------------------

    /**
     * Génère l'URL sécurisée d'une image avec son numéro de version (Cache-Busting).
     *
     * @param string $relativePath Chemin de l'image (ex: 'logo.svg').
     * @return string L'URL complète prête à être injectée dans un attribut src ou srcset.
     */
    public static function generateImgUrl(string $relativePath): string
    {
        $url = \BASE_URL . '/assets/img/' . $relativePath;

        // TODO (Dev) : À remplacer par filemtime() en production
        $version = time();

        return $url . '?v=' . $version;
    }
}