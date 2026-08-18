<?php

namespace App\Filter;

/**
 * Sélectionne, dans une liste source, les seuls candidats à importer.
 *
 * Les exports de plateforme ne contiennent pas que des admis : l'extraction
 * SI-Sciences 2026 comporte 29 lignes NON-ADMIS sur 39. Importer ces lignes
 * créerait dans PEGASUS le dossier administratif de candidats refusés.
 *
 * Le filtre est déclaratif : chaque cursus décrit les colonnes qui portent
 * l'état d'admission, et les valeurs qui valent acceptation ou rejet.
 */
final readonly class AdmissionFilter
{
    /**
     * @param array<string, list<string>> $valeursRetenues Colonne => valeurs acceptées
     * @param array<string, list<string>> $valeursExclues  Colonne => valeurs rejetées
     * @param list<string> $colonnesDesistement Colonnes dont le remplissage vaut désistement
     */
    public function __construct(
        private array $valeursRetenues = [],
        private array $valeursExclues = [],
        private array $colonnesDesistement = [],
    ) {}

    /**
     * Filtre ne retenant aucune ligne : utilisé par les flux dont l'export ne
     * contient, par construction, que des admis ayant confirmé leur venue.
     */
    public static function aucun(): self
    {
        return new self();
    }

    /**
     * Indique si la ligne correspond à un candidat à importer.
     *
     * @param array<string, mixed> $ligne Ligne source indexée par en-tête
     */
    public function retient(array $ligne): bool
    {
        foreach ($this->colonnesDesistement as $colonne) {
            if ($this->valeur($ligne, $colonne) !== '') {
                return false;
            }
        }

        foreach ($this->valeursExclues as $colonne => $valeurs) {
            if ($this->correspond($this->valeur($ligne, $colonne), $valeurs)) {
                return false;
            }
        }

        foreach ($this->valeursRetenues as $colonne => $valeurs) {
            // Une colonne d'état absente du fichier ne peut pas servir de
            // critère : on laisse alors passer la ligne plutôt que de tout
            // écarter silencieusement.
            if (!array_key_exists($colonne, $ligne)) {
                continue;
            }

            if (!$this->correspond($this->valeur($ligne, $colonne), $valeurs)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Comparaison insensible à la casse, aux espaces et aux accents : les
     * intitulés d'état varient d'une extraction à l'autre (« ADMIS, LP » et
     * « ADMIS,LC » cohabitent dans le même fichier).
     */
    private function correspond(string $valeur, array $valeursAttendues): bool
    {
        $normalisee = $this->normaliser($valeur);

        foreach ($valeursAttendues as $attendue) {
            if ($normalisee === $this->normaliser($attendue)) {
                return true;
            }
        }

        return false;
    }

    private function normaliser(string $valeur): string
    {
        $sansAccents = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valeur);
        $valeur = $sansAccents !== false ? $sansAccents : $valeur;

        return preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($valeur, 'UTF-8')) ?? '';
    }

    private function valeur(array $ligne, string $colonne): string
    {
        return trim((string) ($ligne[$colonne] ?? ''));
    }
}
