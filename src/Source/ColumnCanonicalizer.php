<?php

namespace App\Source;

/**
 * Ramène les en-têtes d'un fichier source à leur nom canonique.
 *
 * Une même donnée porte des libellés différents selon la variante de fichier
 * qui circule : la SI-Lettres est diffusée à la fois en extraction brute
 * DEMATEC (`Nom`, `naissance_date`, `nationalite`) et en fichier retravaillé
 * par le CoST (`NOM`, `Date de naissance`, `Nationalité`). Rejeter l'une des
 * deux serait incompréhensible pour le gestionnaire, qui a sous la main un
 * fichier parfaitement valide.
 *
 * La résolution se fait en deux temps :
 *   1. correspondance exacte sur le nom canonique ;
 *   2. correspondance normalisée — casse, accents et séparateurs ignorés —
 *      sur le nom canonique puis sur chacun de ses alias déclarés.
 *
 * La normalisation seule suffit pour `NOM` ≡ `Nom` ou `CODE POSTAL` ≡
 * `CODE_POSTAL`. Les libellés réellement différents, comme `naissance_date` et
 * `Date de naissance`, exigent un alias explicite.
 */
final class ColumnCanonicalizer
{
    /**
     * @param array<string, list<string>> $aliases Nom canonique => libellés acceptés
     */
    public function __construct(private array $aliases = []) {}

    /**
     * Réindexe la ligne sur les noms canoniques.
     *
     * Les colonnes non déclarées sont conservées telles quelles : elles peuvent
     * servir à d'autres traitements, et les écarter masquerait des données.
     *
     * @param array<string, mixed> $ligne
     * @return array<string, mixed>
     */
    public function canonicaliser(array $ligne): array
    {
        if ($this->aliases === []) {
            return $ligne;
        }

        // Index des en-têtes présents, sous leur forme normalisée.
        $index = [];

        foreach (array_keys($ligne) as $enTete) {
            $index[$this->normaliser((string) $enTete)] ??= $enTete;
        }

        foreach ($this->aliases as $canonique => $variantes) {
            if (array_key_exists($canonique, $ligne)) {
                continue;
            }

            foreach ([$canonique, ...$variantes] as $candidat) {
                $enTete = $index[$this->normaliser($candidat)] ?? null;

                if ($enTete !== null && array_key_exists($enTete, $ligne)) {
                    $ligne[$canonique] = $ligne[$enTete];
                    continue 2;
                }
            }
        }

        return $ligne;
    }

    /**
     * Réduit un libellé à ses seuls caractères alphanumériques, sans accents ni
     * distinction de casse.
     */
    private function normaliser(string $libelle): string
    {
        $sansAccents = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $libelle);
        $libelle = $sansAccents !== false ? $sansAccents : $libelle;

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $libelle) ?? '');
    }
}
