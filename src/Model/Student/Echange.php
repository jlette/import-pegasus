<?php

namespace App\Model\Student;

use DateTime;


/**
 * Représente un étudiant international (programme d'échange ou pensionnaire étranger).
 *
 * Ce profil, géré par la DRI, possède un format d'import PEGASUS très spécifique.
 * Il exige impérativement un email personnel pour garantir la première authentification 
 * de l'étudiant, ainsi que des informations de contact d'urgence et un département d'accueil.
 */

readonly class Echange extends AbstractStudent
{
    /**
     * @param string $type_occ Action sur le dossier (Valeur attendue : 'da')
     * @param string $produit_programme Code formation PEGASUS (Valeur attendue : 'ANECHINTER')
     * @param string $status_etudiant Phase professionnelle PEGASUS :
     * - 'ENS-DRI ECH ERASMUS' : Pour un échange ERASMUS
     * - 'ENS-DRI PENS ETRG'   : Pour un pensionnaire étranger
     * @param array<string, string> $connaissance Tableau des connaissances. Règles DRI :
     * - 'EMAIL PERSONNEL' : Obligatoire (Critique : permet la première authentification)
     * - 'EMAIL ECOLE', 'NUMERO_ETU_PSLR', 'ENS_NO_INDIVIDU' : Laisser vide lors d'une création
     * - 'URGENCE PERSONNE' : Nom et prénom du contact d'urgence.
     * - 'URGENCE TELEPHONE' : N° de téléphone du contact d'urgence.
     * - 'PORTABLE': N° de téléphone portable de l'étudiant.
     * - 'ENS_DPT_RATT_ETU_ECHAN': Département d'accueil. (DOIT être en MAJUSCULE).
     * - Valeurs possible : ARTS, BIOLOGIE, CHIMIE, ECLA, ECONOMIE, ETUDES COGNITIVES, GEOSCIENCES, 
     * HISTOIRE, INFORMATIQUE, LITTERATURES ET LANGAGE, MATHEMATIQUES ET APPLICATIONS, 
     * PHILOSOPHIE, PHYSIQUE, SCIENCES DE L'ANTIQUITE, SCIENCES SOCIALES.
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
        public string $situation_familiale,
        public string $ville_de_naissance,
        public string $date_de_naissance,
        public string $pays_de_naissance,
        public string $nationalite_principal,
        public string $code_insee,
        public string $courrier_voie_un,
        public string $courrier_voie_deux,
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
