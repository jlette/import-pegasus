<?php

namespace App\Model\Student;

abstract class Student
{
    private Int $date_lot;
    private Int $no_lot;
    private Int $no_ssl;
    private String $type_occ;
    private String $recrutement;
    private Int $annee;
    private String $produit_programme;
    private Int $no_annee;
    private Int $session;
    private String $status_etudiant;
    private String $genre;
    private String $nom;
    private String $prenom;
    private String $sexe;
    private String $lieu_naissance;
    private array $connaissance;

    public function __construct(Int $date_lot, Int $no_lot, Int $no_ssl, String $type_occ, String $recrutement, Int $annee, String $produit_programme, Int $no_annee, Int $session, String $status_etudiant, String $genre, String $nom, String $prenom, String $sexe, String $lieu_naissance, array $connaissance)
    {
        $this->date_lot = $date_lot;
        $this->no_lot = $no_lot;
        $this->no_ssl = $no_ssl;
        $this->type_occ = $type_occ;
        $this->recrutement = $recrutement;
        $this->annee = $annee;
        $this->produit_programme = $produit_programme;
        $this->no_annee = $no_annee;
        $this->session = $session;
        $this->status_etudiant = $status_etudiant;
        $this->genre = $genre;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->sexe = $sexe;
        $this->lieu_naissance = $lieu_naissance;
        $this->connaissance = $connaissance;
    }

    // Getters et setters pour les propriétés
    public function getDateLot()
    {
        return $this->date_lot;
    }

    public function getNoLot()
    {
        return $this->no_lot;
    }

    public function getNoSsl()
    {
        return $this->no_ssl;
    }

    public function getTypeOcc()
    {
        return $this->type_occ;
    }

    public function getRecrutement()
    {
        return $this->recrutement;
    }

    public function getAnnee()
    {
        return $this->annee;
    }

    public function getProduitProgramme()
    {
        return $this->produit_programme;
    }

    public function getName()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }
}
