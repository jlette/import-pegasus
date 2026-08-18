<?php

namespace App\Model\Student;

use DateTime;

/**
 * Modèle de base pour l'export PEGASUS.
 *
 * Cette classe agit comme un conteneur de données immuable (readonly). 
 * Elle rassemble les informations obligatoires communes à toutes les 
 * populations d'étudiants (Normaliens, Mastériens, etc.) exigées par l'ENS.
 *
 * @package App\Model\Student
 */
readonly abstract class AbstractStudent
{
    /**
     * Initialise un étudiant avec ses données pré-formatées.
     *
     * Attention : La classe étant en lecture seule, aucune modification
     * de texte (strtoupper, ucfirst) ne peut se faire ici. C'est le Builder
     * qui doit garantir le formatage strict des chaînes avant l'instanciation.
     *
     * @param DateTime $date_lot Date de création du lot d'import (format attendu AAAAMMJJ)
     * @param int $no_lot Identifiant séquentiel du lot D du lot. S'incrémente pour chaque 'da'. Reste identique pour les 'cv' liés.
     * @param int $no_ssl Identifiant séquentiel alternatif. Vaut 0 pour un 'da', s'incrémente pour chaque 'cv' du même lot.
     * @param string $type_occ Type d'occurrence, Action sur le dossier PEGASUS :
     * - 'da' : Création du dossier administratif initial (Ex: Normalien)
     * - 'cv' : Ajout d'une 2ème IA (Ex: Cursus LM). Le portail étudiant sera généré la nuit suivante.
     * @param int $annee Année universitaire de référence (format attendu: AAAA)
     * @param string $produit_programme Code de la formation (ex: ANM1APE)
     * @param int $no_annee Année d'étude dans le programme (format attendu: AAAA)
     * @param string $status_etudiant Phase professionnelle PEGASUS (ex: ENS-EXT)
     * @param string $genre Civilité (Monsieur / Madame)
     * @param string $nom Nom de famille (Préalablement formaté en MAJUSCULES)
     * @param string $prenom Prénom (Préalablement formaté avec la première lettre en majuscule)
     * @param string $sexe Sexe (M / F)
     * @param array<string, string> $connaissance Tableau des couples Type/Valeur (Extrait indépendamment des n° de colonnes du CSV).
     */
    public function __construct(
        public DateTime $date_lot,
        public int $no_lot,
        public int $no_ssl,
        public string $type_occ,
        public string $recrutement,
        public int $annee,
        public string $produit_programme,
        public int $no_annee,
        public int $session,
        public string $status_etudiant,
        public string $genre,
        public string $nom,
        public string $prenom,
        public string $sexe,
        public array $connaissance,
        public string $eol
    ) {}

    /**
     * Projette l'étudiant sur les colonnes de fin du canevas.
     *
     * Chaque population sait quelles colonnes elle alimente : le service
     * d'export n'a donc pas à inspecter la forme des objets qu'il reçoit.
     *
     * @return array<string, string> Valeurs indexées par le libellé exact de colonne
     */
    abstract public function colonnesFinales(): array;
}
