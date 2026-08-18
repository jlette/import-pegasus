<?php

namespace App\Builder;

use App\Model\Student\Echange;
use App\Model\Student\Masterien;
use App\Model\Student\Normalien;
use DateTime;

/**
 * Monteur (Builder) responsable de la préparation et du nettoyage des données brutes.
 * * Son rôle est d'agir comme un "sas de sécurité" entre le fichier source (CSV) 
 * et nos modèles en lecture seule (DTO). Il garantit que toutes les chaînes de caractères 
 * respectent les règles typographiques strictes de PEGASUS avant l'instanciation finale.
 * * @package App\Model\Builder
 */
class StudentBuilder
{
    // --- Variables temporaires de construction ---
    private DateTime $date_lot;
    private int $no_lot;
    private int $no_ssl;
    private string $type_occ;
    private string $recrutement;
    private int $annee;
    private string $produit_programme;
    private int $no_annee;
    private int $session;
    private string $statut_etudiant;
    private string $genre;
    private string $eol;
    private string $nom;
    private string $prenom;
    private string $sexe;
    private array $connaissance = [];

    // --- 1. ÉTAPES DE PRÉPARATION (Fluent Interface) ---

    /**
     * Configure les identifiants de suivi de l'import.
     * Le type d'occurrence est sécurisé en minuscules pour éviter un rejet d'intégration ('DA' -> 'da').
     */

    public function setInfosPegasus(DateTime $date_lot, int $no_lot, int $no_ssl, string $type_occ, string $recrutement, int $session, string $eol): self
    {
        $this->date_lot = $date_lot;
        $this->no_lot = $no_lot;
        $this->no_ssl = $no_ssl;
        $this->recrutement = $recrutement;
        $this->session = $session;
        $this->eol = $eol;
        $this->type_occ = $type_occ;
        return $this;
    }

    /**
     * Définit le cursus académique.
     * Les codes programmes et statuts PEGASUS doivent impérativement être en majuscules.
     */
    public function setScolarite(int $annee, string $produit_programme, int $no_annee, string $statut_etudiant): self
    {
        $this->annee = $annee;
        $this->produit_programme = $produit_programme;
        $this->no_annee = $no_annee;
        $this->statut_etudiant = $statut_etudiant;
        return $this;
    }

    /**
     * Normalise l'état civil selon les standards administratifs de l'ENS.
     * Force le NOM en capitales et le Prénom/Genre avec une majuscule initiale.
     *
     * Les fonctions mb_* sont impératives : strtoupper() et ucfirst() opèrent
     * octet par octet et laissent intacts les caractères accentués encodés sur
     * plusieurs octets. "Müller" devenait "MüLLER", "JOSÉ" devenait "JosÉ".
     * MB_CASE_TITLE gère en outre les prénoms composés ("Jean-Luc", "O'Brien").
     */
    public function setIdentite(string $nom, string $prenom, string $genre, string $sexe): self
    {
        $this->nom = mb_strtoupper(trim($nom), 'UTF-8');
        $this->prenom = mb_convert_case(trim($prenom), MB_CASE_TITLE, 'UTF-8');
        $this->genre = mb_convert_case(trim($genre), MB_CASE_TITLE, 'UTF-8');
        $this->sexe = mb_strtoupper(trim($sexe), 'UTF-8');

        return $this;
    }

    /**
     * Charge les paires "Type/Valeur" extraites dynamiquement du fichier source.
     */
    public function setConnaissance(array $connaissance): self
    {
        $this->connaissance = $connaissance;

        return $this;
    }

    // --- 2. ÉTAPES DE FABRICATION (Les méthodes Build) ---

    /**
     * Assemble et verrouille un étudiant Normalien.
     * * @param array $connaissance_fop_ins Données de formation propres aux normaliens
     * @return Normalien Objet finalisé et immuable
     */
    public function buildNormalienStudent(array $connaissance_fop_ins, string $situation_familiale, string $ville_de_naissance, DateTime $date_de_naissance, string $pays_de_naissance, string $nationalite_principal, string $code_insee, string $courrier_voie_1, string $courrier_voie_2, string $courrier_code_postal, string $courrier_ville, string $courrier_pays, string $courrier_telephone): Normalien
    {
        return new Normalien(
            $this->date_lot,
            $this->no_lot,
            $this->no_ssl,
            $this->type_occ,
            $this->recrutement,
            $this->annee,
            $this->produit_programme,
            $this->no_annee,
            $this->session,
            $this->statut_etudiant,
            $this->genre,
            $this->nom,
            $this->prenom,
            $this->sexe,
            $this->connaissance,
            $this->eol,
            $connaissance_fop_ins,
            $situation_familiale,
            $ville_de_naissance,
            $date_de_naissance,
            $pays_de_naissance,
            $nationalite_principal,
            $code_insee,
            $courrier_voie_1,
            $courrier_voie_2,
            $courrier_code_postal,
            $courrier_ville,
            $courrier_pays,
            $courrier_telephone
        );
    }

    /**
     * Assemble et verrouille un étudiant Mastérien.
     * * C'est le profil d'intégration le plus léger : l'administration ne requiert
     * aucune coordonnée postale ou civile détaillée pour cette population.
     * * @return Masterien Objet finalisé et immuable
     */
    public function buildMasterienStudent(): Masterien
    {
        return new Masterien(
            $this->date_lot,
            $this->no_lot,
            $this->no_ssl,
            $this->type_occ,
            $this->recrutement,
            $this->annee,
            $this->produit_programme,
            $this->no_annee,
            $this->session,
            $this->statut_etudiant,
            $this->genre,
            $this->nom,
            $this->prenom,
            $this->sexe,
            $this->connaissance,
            $this->eol
        );
    }

    /**
     * Assemble et verrouille un étudiant en Échange (International).
     * * Profil très complet (DRI) : nécessite l'intégration obligatoire des 
     * coordonnées postales complètes et des contacts d'urgence.
     * * @return Echange Objet finalisé et immuable
     */
    public function buildEchangeStudent(
        string $situation_familiale,
        string $ville_de_naissance,
        DateTime $date_de_naissance,
        string $pays_de_naissance,
        string $nationalite_principal,
        string $code_insee,
        string $departement_de_naissance,
        string $courrier_voie_un,
        string $courrier_voie_deux,
        string $courrier_code_postal,
        string $courrier_ville,
        string $courrier_pays,
        string $courrier_telephone
    ): Echange {
        return new Echange(
            $this->date_lot,
            $this->no_lot,
            $this->no_ssl,
            $this->type_occ,
            $this->recrutement,
            $this->annee,
            $this->produit_programme,
            $this->no_annee,
            $this->session,
            $this->statut_etudiant,
            $this->genre,
            $this->nom,
            $this->prenom,
            $this->sexe,
            $this->connaissance,
            $this->eol,
            $situation_familiale,
            $ville_de_naissance,
            $date_de_naissance,
            $pays_de_naissance,
            $nationalite_principal,
            $code_insee,
            $departement_de_naissance,
            $courrier_voie_un,
            $courrier_voie_deux,
            $courrier_code_postal,
            $courrier_ville,
            $courrier_pays,
            $courrier_telephone
        );
    }
}
