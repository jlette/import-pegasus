<?php

namespace App\Database;

use Closure;
use PDO;
use PDOStatement;
use Throwable;

/**
 * Connexion PDO différée.
 *
 * Expose la même surface que PDO pour les usages de l'application, mais
 * n'établit la connexion qu'au premier appel réel. Cela évite d'ouvrir une
 * session Oracle pour les imports qui n'interrogent pas l'annuaire.
 *
 * **L'échec est mémorisé au même titre que le succès.** Sans cela, une
 * connexion ratée serait retentée à chaque appel : le référentiel des concours
 * étant interrogé une fois par ligne, un mot de passe erroné produisait autant
 * de tentatives d'authentification qu'il y a d'étudiants dans le fichier. Le
 * profil Oracle par défaut verrouillant le compte au bout de dix échecs, un
 * seul import suffisait à le condamner (ORA-28000).
 *
 * La règle est donc : **une tentative de connexion par requête HTTP**, quoi
 * qu'il arrive.
 */
class LazyPdo
{
    private ?PDO $pdo = null;

    /** Échec de la première tentative, rejoué tel quel pour les suivantes. */
    private ?Throwable $echec = null;

    /** @param Closure(): PDO $factory */
    public function __construct(private Closure $factory) {}

    /**
     * Enveloppe une connexion déjà établie. Utile en test.
     */
    public static function fromPdo(PDO $pdo): self
    {
        return new self(static fn(): PDO => $pdo);
    }

    /**
     * Établit la connexion si nécessaire, puis la retourne.
     *
     * @throws Throwable L'échec initial, rejoué à l'identique
     */
    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        // Une connexion ayant déjà échoué n'est jamais retentée : réessayer
        // ne ferait qu'accumuler les échecs d'authentification côté serveur.
        if ($this->echec !== null) {
            throw $this->echec;
        }

        try {
            return $this->pdo = ($this->factory)();
        } catch (Throwable $erreur) {
            $this->echec = $erreur;

            throw $erreur;
        }
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return $this->pdo()->prepare($query, $options);
    }

    public function query(string $query, ?int $fetchMode = null): PDOStatement|false
    {
        return $fetchMode === null
            ? $this->pdo()->query($query)
            : $this->pdo()->query($query, $fetchMode);
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        return $this->pdo()->quote($string, $type);
    }
}
