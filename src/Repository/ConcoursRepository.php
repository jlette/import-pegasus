<?php

namespace App\Repository;

use App\Interface\CodeRepositoryInterface;
use RuntimeException;
use PDO;

/**
 * Gère l'accès aux données des concours dans la base de données.
 * Implémente l'interface CodeRepositoryInterface pour la cohérence architecturale.
 */
class ConcoursRepository implements CodeRepositoryInterface
{
    /**
     * Initialise le repository avec une instance PDO.
     * 
     * @param PDO $db Connexion à la base de données
     */
    public function __construct(private PDO $db) {}

    /**
     * Recherche et retourne le code concours normalisé.
     * 
     * Récupère le code PEGASUS correspondant à un code annuaire donné
     * via la table de correspondance CORRESP_ANNUAIRE_CONC_CODE.
     * Filtre sur les enregistrements actifs (PEGASUS = 'O').
     *
     * @param string $codeConcours Code annuaire du concours
     * 
     * @return string Code concours normalisé (CONC_CODE)
     */
    public function findCode(string $codeConcours): string
    {
        // Prépare la requête paramétrée pour éviter les injections SQL
        $stmt = $this->db->prepare("SELECT CONC_CODE FROM CORRESP_ANNUAIRE_CONC_CODE WHERE PEGASUS = 'O' AND ANNUAIRE_CONC_CODE = :codeConcours");

        // Lie le paramètre avec typage strict
        $stmt->bindValue(':codeConcours', $codeConcours, PDO::PARAM_STR);

        // Exécute la requête
        $stmt->execute();

        // Récupère le code normalisé
        $code = $stmt->fetchColumn();

        // Sécurité : Si le code n'est pas trouvé dans la base Oracle
        if ($code === false) {
            // Option 1 : Lever une exception (Préférable pour bloquer l'import d'un étudiant corrompu)
            throw new RuntimeException("Erreur : Aucun code PEGASUS trouvé pour le concours annuaire '{$codeConcours}'.");
            // Option 2 : Retourner une chaîne vide ou un code par défaut à corriger à la main
            // return 'A_CORRIGER'; 
        }

        return $code;
    }
}