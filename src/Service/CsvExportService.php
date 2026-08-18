<?php

namespace App\Service;

use App\Canevas\CanevasProfile;
use App\Model\Student\AbstractStudent;
use RuntimeException;

/**
 * Génère le canevas d'import CSV attendu par PEGASUS.
 *
 * La structure du fichier provient exclusivement du profil de canevas, jamais
 * du premier étudiant rencontré : c'est ce qui garantit qu'une liste hétérogène
 * ou une stratégie incomplète ne produit pas un fichier aux colonnes décalées.
 *
 * Contraintes de forme imposées par PEGASUS et non négociables :
 * séparateur « ; », encodage ISO-8859-1, fins de ligne CRLF, libellés de
 * colonnes reproduits au caractère près, dernière colonne EOL.
 */
class CsvExportService
{
    /**
     * @param list<AbstractStudent> $etudiants
     *
     * @return string Nom du fichier généré, ou chaîne vide si aucun étudiant
     * @throws RuntimeException Si un étudiant ne respecte pas le profil
     */
    public function generateCsv(
        array $etudiants,
        string $outputDir,
        string $cursusPrefix,
        CanevasProfile $profile,
    ): string {
        if (empty($etudiants)) {
            return '';
        }

        // Le nom de fichier ne doit comporter aucun caractère accentué.
        $filename = sprintf('import_%s_%s.csv', $cursusPrefix, date('Ymd_His'));
        $filePath = rtrim($outputDir, '/') . '/' . $filename;

        $file = fopen($filePath, 'w');

        if ($file === false) {
            throw new RuntimeException("Impossible d'écrire le canevas dans {$outputDir}.");
        }

        // Conversion à la volée vers l'encodage attendu par PEGASUS, sans
        // matérialiser une seconde copie du fichier en mémoire. //TRANSLIT
        // remplace les caractères hors ISO-8859-1 par leur équivalent le plus proche.
        stream_filter_append($file, 'convert.iconv.UTF-8/ISO-8859-1//TRANSLIT');

        try {
            $this->ecrireLigne($file, $profile->enTetes());

            foreach ($etudiants as $etudiant) {
                $this->ecrireLigne($file, $this->construireLigne($etudiant, $profile));
            }
        } finally {
            fclose($file);
        }

        return $filename;
    }

    /**
     * Assemble une ligne du canevas en suivant l'ordre déclaré par le profil.
     *
     * @return list<string>
     */
    private function construireLigne(AbstractStudent $etudiant, CanevasProfile $profile): array
    {
        $ligne = [
            $etudiant->date_lot->format('Ymd'),
            $etudiant->no_lot,
            $etudiant->no_ssl,
            $etudiant->type_occ,
            $etudiant->recrutement,
            $etudiant->annee,
            $etudiant->produit_programme,
            $etudiant->no_annee,
            $etudiant->session,
            $etudiant->status_etudiant,
            $etudiant->genre,
            $etudiant->nom,
            $etudiant->prenom,
            $etudiant->sexe,
        ];

        foreach ($profile->connaissances as $type) {
            $ligne[] = $type;
            $ligne[] = $this->valeurConnaissance($etudiant->connaissance, $type, $etudiant, 'Connaissance');
        }

        $fopIns = property_exists($etudiant, 'connaissance_fop_ins')
            ? $etudiant->connaissance_fop_ins
            : [];

        foreach ($profile->fopIns as $type) {
            $ligne[] = $type;
            $ligne[] = $this->valeurConnaissance($fopIns, $type, $etudiant, 'Connaissance_fop_ins');
        }

        $colonnesFinales = $etudiant->colonnesFinales();

        foreach ($profile->colonnesFinales as $libelle) {
            if (!array_key_exists($libelle, $colonnesFinales)) {
                throw new RuntimeException(sprintf(
                    "Le canevas attend la colonne '%s', que %s n'alimente pas.",
                    $libelle,
                    $etudiant::class
                ));
            }

            $ligne[] = $colonnesFinales[$libelle];
        }

        $ligne[] = $etudiant->eol;

        // Une valeur multiligne romprait la structure du CSV côté PEGASUS.
        return array_map(
            static fn($valeur): string => str_replace(["\r", "\n"], ' ', (string) $valeur),
            $ligne
        );
    }

    /**
     * Récupère la valeur d'une connaissance déclarée au profil.
     *
     * Une connaissance manquante est une erreur, pas une colonne vide : une
     * valeur vide écrase la donnée existante dans PEGASUS lors d'un réimport.
     */
    private function valeurConnaissance(
        array $connaissances,
        string $type,
        AbstractStudent $etudiant,
        string $contexte,
    ): string {
        if (!array_key_exists($type, $connaissances)) {
            throw new RuntimeException(sprintf(
                "Le canevas attend la connaissance %s '%s', que %s ne fournit pas.",
                $contexte,
                $type,
                $etudiant::class
            ));
        }

        return (string) $connaissances[$type];
    }

    /**
     * @param list<string> $valeurs
     */
    private function ecrireLigne($file, array $valeurs): void
    {
        // Le paramètre $eol natif de fputcsv (PHP 8.1+) évite tout bricolage de
        // fin de ligne : PEGASUS exige CRLF.
        fputcsv($file, $valeurs, ';', '"', '\\', "\r\n");
    }
}
