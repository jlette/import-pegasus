<?php

namespace App\Model\Student;

use DateTime;

/**
 * Représente un étudiant inscrit en Master (M1 ou M2) à l'ENS-PSL.
 *
 * Contrairement aux Normaliens, cette population ne requiert aucune information
 * de concours ou de statut fonctionnaire dans l'import PEGASUS. L'enjeu principal 
 * ici est de tracer avec précision son parcours académique (Formation, Mention et Parcours).
 */
readonly class Masterien extends AbstractStudent
{
    /**
     * @param DateTime $date_lot
     * @param int $no_lot
     * @param int $no_ssl
     * @param string $type_occ Type d'occurrence: (Valeur attendu : 'cv') 
     * @param int $annee
     * @param string $produit_programme ANCLMdpt où dpt = BIO, CHI, DMA, ECO, GSC, INF ou PHY Ex : ANCLMECO pour la L3 économie
     * @param string $produit_programme Code d'admission PEGASUS :
     * - Format  : 'ANCLM[dpt]'
     * - [dpt]   : BIO, CHI, DMA, ECO, GSC, INF, PHY
     * - Exemple : 'ANCLMECO' (L3 économie)
     * @param int $no_annee
     * @param string $status_etudiant Valeur attendue : 'ENS-EXT'
     * @param string $genre
     * @param string $nom
     * @param string $prenom
     * @param string $sexe
     * @param array<string, string> $connaissance Tableau de clé-valeur. Règle métier critique :
     * - Clé    : 'EMAIL ECOLE'
     * - Valeur : Doit finir par '@ens.psl.eu' (Sert de clé de liaison avec le dossier normalien existant)
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
        string $eol
    ) {
        // Transfert des données communes au conteneur parent
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
     *
     * Profil minimal : le dossier administratif existe déjà, la liaison se fait
     * par l'adresse EMAIL ECOLE. Aucune colonne de fin n'est alimentée.
     */
    public function colonnesFinales(): array
    {
        return [];
    }
}
