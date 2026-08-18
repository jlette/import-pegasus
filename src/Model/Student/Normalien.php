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
     * @param string $status_etudiant Valeur attendue : 'ENS-DENS ETUDIANT' ou 'ENS-DENS FCTIONNAIRE'
     * @param string $genre
     * @param string $nom
     * @param string $prenom
     * @param string $sexe
     * @param array<string, string> $connaissance
     * @param array<string, string> $connaissance_fop_ins Tableau clé-valeur des colonnes de connaissances_fos_ins
     * @param string $ville_de_naissance
     * @param string $situation_familiale
     * @param DateTime $date_de_naissance (format attendu: JJ/MM/AAAA)
     * @param string $pays_de_naissance 
     * @param string $nationalite_principal
     * @param string $code_insee Code INSEE de la commune de naissance (5 caractères, ex: "75056" pour Paris)
     * @param string $courrier_voie_1
     * @param string $courrier_voie_2
     * @param string $courrier_code_postal
     * @param string $courrier_ville
     * @param string $courrier_pays
     * @param string $courrier_telephone
     */
    public function __construct(
        DateTime $date_lot,
        int $no_lot,
        int $no_ssl,
        string $type_occ,
        string $recrutement,
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
        public string $situation_familiale,
        public string $ville_de_naissance,
        public DateTime $date_de_naissance,
        public string $pays_de_naissance,
        public string $nationalite_principal,
        public string $code_insee,
        public string $courrier_voie_1,
        public string $courrier_voie_2,
        public string $courrier_code_postal,
        public string $courrier_ville,
        public string $courrier_pays,
        public string $courrier_telephone
    ) {
        parent::__construct(
            $date_lot,
            $no_lot,
            $no_ssl,
            $type_occ,
            $recrutement,
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

    /**
     * @inheritDoc
     */
    public function colonnesFinales(): array
    {
        return [
            'Ville de Naissance'     => $this->ville_de_naissance,
            'Date de Naissance'      => $this->date_de_naissance->format('d/m/Y'),
            'Pays de Naissance'      => $this->pays_de_naissance,
            'Nationalité Principale' => $this->nationalite_principal,
        ];
    }
}
