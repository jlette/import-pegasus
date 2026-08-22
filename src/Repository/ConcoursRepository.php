<?php

namespace App\Repository;

use App\Interface\CodeRepositoryInterface;
use App\Database\LazyPdo;
use App\Model\Exception\AnnuaireIndisponibleException;
use PDOException;
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
     * @param LazyPdo $db Connexion différée à l'annuaire
     */
    public function __construct(private LazyPdo $db) {}

    /**
     * Recherche et retourne le code concours normalisé.
     * 
     * Récupère le code PEGASUS correspondant à un code annuaire donné
     * via la table de correspondance CORRESP_ANNUAIRE_CONC_CODE.
     * Filtre sur les enregistrements actifs (PEGASUS = 'O').
     *
     * @param string $platforme Code annuaire de la plateforme (ex: "DENS", "DRI", "AGREG", etc.)
     * 
     * @return array Liste des codes concours normalisés (CONC_CODE)
     */
    public function findByPlatforme(string $platforme): array
    {
        try {
            // Requête paramétrée : aucune interpolation de valeur dans le SQL.
            $stmt = $this->db->prepare(
                "SELECT CONC_CODE, ANNUAIRE_CONC_CODE FROM CORRESP_ANNUAIRE_CONC_CODE "
                    . "WHERE PEGASUS = 'O' AND PLATEFORME = :platforme"
            );

            $stmt->bindValue(':platforme', $platforme, PDO::PARAM_STR);
            $stmt->execute();

            $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $erreur) {
            // L'indisponibilité de l'annuaire ne dépend d'aucune ligne : elle
            // condamne l'import entier et doit donner lieu à un message unique.
            throw new AnnuaireIndisponibleException($this->codeOracle($erreur));
        }

        // Sécurité : Si le code n'est pas trouvé dans la base Oracle
        if ($codes === false) {
            // Option 1 : Lever une exception (Préférable pour bloquer l'import d'un étudiant corrompu)
            throw new RuntimeException("Erreur : Aucun code PEGASUS trouvé pour le concours annuaire '{$platforme}'.");
            // Option 2 : Retourner une chaîne vide ou un code par défaut à corriger à la main
            // return 'A_CORRIGER'; 
        }

        return $codes;
    }

    /**
     * Extrait le code d'erreur Oracle du message du pilote.
     *
     * Le message brut comporte des chemins de compilation du pilote OCI, sans
     * intérêt pour l'utilisateur et inutilement révélateurs de l'infrastructure.
     * Seul le code — ORA-28000 pour un compte verrouillé, ORA-12541 pour un
     * écouteur absent — est conservé, pour le journal et le support.
     */
    private function codeOracle(PDOException $erreur): string
    {
        return preg_match('/\b(ORA-\d{4,5})\b/', $erreur->getMessage(), $correspondance) === 1
            ? $correspondance[1]
            : '';
    }
}
