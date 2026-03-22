<?php

namespace App\Model\Student;

use DateTime;

/**
 * Représente un étudiant Normalien (inscription au DENS/Diplôme de l'École Normale Supérieure).
 *
 * Cette population possède des règles strictes dans PEGASUS : elle est la seule
 * à exiger des informations sur le concours d'entrée, la promotion d'origine
 * et les données de financement spécifiques à l'ENS.
 */
readonly class Normalien extends AbstractStudent
{
    /**
     * @param DateTime $date_lot format
     * @param int $no_lot
     * @param int $no_ssl
     * @param string $type_occ
     * @param int $annee
     * @param string $produit_programme Code d'admission PEGASUS :
     * - 'ANDENS1'   : CPGE et Médecine Humanités
     * - 'AND[dpt]1' : Autres (dpt = ART, BIO, CHI, DEC, DSA, DSS, ECO, GEO, GSC, HIS, INF, LIT, PHI, PHY)
     * - Exceptions  : BIO (Méd. Sciences), DEC (Linguistique), DSA (Classiques/Archéo)
     * @param int $no_annee
     * @param string $status_etudiant Valeur attendue : 'ENS-DENS ETUDIANT' ou 'ENS-DENS ETUDIANT'
     * @param string $genre
     * @param string $nom
     * @param string $prenom
     * @param string $sexe
     * @param array<string, string> $connaissance
     * @param array<string, string> $connaissance_fop_ins Tableau clé-valeur des colonnes de connaissances_fos_ins
     * @param string $ville_de_naissance
     * @param DateTime $date_de_naissance (format attendu: JJ/MM/AAAA)
     * @param string $pays_de_naissance 
     * @param string $nationalite_principal
     */
    public function __construct(
        DateTime $date_lot,
        int $no_lot,
        int $no_ssl,
        string $type_occ,
        string $recrutemment,
        int $annee,
        string $produit_programme,
        int $no_annee,
        int $session,
        string $status_etudiant,
        string $genre,
        string $nom,
        string $prenom,
        string $sexe,
        array $connaissance,
        string $eol,

        // --- Propriétés spécifiques ---
        public array $connaissance_fop_ins,
        public string $ville_de_naissance,
        public DateTime $date_de_naissance,
        public string $pays_de_naissance,
        public string $nationalite_principal
    ) {
        parent::__construct(
            $date_lot,
            $no_lot,
            $no_ssl,
            $type_occ,
            $recrutemment,
            $annee,
            $produit_programme,
            $no_annee,
            $session,
            $status_etudiant,
            $genre,
            $nom,
            $prenom,
            $sexe,
            $connaissance,
            $eol
        );
    }
}
