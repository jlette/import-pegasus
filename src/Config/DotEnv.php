<?php

namespace App\Config;

use RuntimeException;

/**
 * Chargeur de fichier `.env`.
 *
 * Le fichier n'est jamais versionné : il porte le mot de passe du schéma
 * ANNUAIRE, dont le CRI conserve la référence dans son coffre.
 *
 * **Les variables déjà présentes dans l'environnement ne sont jamais écrasées.**
 * En production, la configuration passe donc par le vhost Apache (`SetEnv`) ou
 * l'unité systemd, et le fichier `.env` sert au poste de développement — sans
 * qu'il soit possible qu'un `.env` oublié sur le serveur prenne le pas sur la
 * configuration réelle.
 *
 * Format accepté : `CLE=valeur`, une par ligne. Les lignes vides et celles
 * commençant par `#` sont ignorées. La valeur peut être encadrée de guillemets
 * simples ou doubles, qui sont retirés. Ni interpolation, ni valeur multiligne :
 * l'outil n'en a pas l'usage, et les proscrire évite les surprises.
 */
final class DotEnv
{
    /**
     * Charge un fichier `.env` s'il existe.
     *
     * L'absence du fichier n'est pas une erreur : sur un serveur correctement
     * configuré, les variables viennent de l'environnement.
     *
     * @throws RuntimeException Si le fichier existe mais est illisible ou malformé
     */
    public static function charger(string $chemin): void
    {
        if (!is_file($chemin)) {
            return;
        }

        $lignes = @file($chemin, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lignes === false) {
            throw new RuntimeException("Le fichier de configuration {$chemin} est illisible.");
        }

        foreach ($lignes as $numero => $ligne) {
            $ligne = trim($ligne);

            if ($ligne === '' || str_starts_with($ligne, '#')) {
                continue;
            }

            if (!str_contains($ligne, '=')) {
                throw new RuntimeException(sprintf(
                    'Ligne %d de %s : format attendu CLE=valeur.',
                    $numero + 1,
                    $chemin
                ));
            }

            [$cle, $valeur] = explode('=', $ligne, 2);
            $cle = trim($cle);

            if ($cle === '') {
                throw new RuntimeException(sprintf('Ligne %d de %s : clé vide.', $numero + 1, $chemin));
            }

            // L'environnement réel fait toujours autorité.
            if (getenv($cle) !== false) {
                continue;
            }

            self::definir($cle, self::nettoyer($valeur));
        }
    }

    /**
     * Retire les guillemets encadrants éventuels.
     */
    private static function nettoyer(string $valeur): string
    {
        $valeur = trim($valeur);

        if (strlen($valeur) >= 2) {
            $premier = $valeur[0];
            $dernier = $valeur[strlen($valeur) - 1];

            if ($premier === $dernier && ($premier === '"' || $premier === "'")) {
                return substr($valeur, 1, -1);
            }
        }

        return $valeur;
    }

    private static function definir(string $cle, string $valeur): void
    {
        putenv("{$cle}={$valeur}");
        $_ENV[$cle] = $valeur;
        $_SERVER[$cle] = $valeur;
    }
}
