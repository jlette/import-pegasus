<?php

namespace App\Database;

use Closure;
use PDO;
use PDOStatement;

/**
 * Connexion PDO différée.
 *
 * Expose la même surface que PDO pour les usages de l'application, mais
 * n'établit la connexion qu'au premier appel réel. Cela évite d'ouvrir une
 * session Oracle pour les imports qui n'interrogent pas l'annuaire.
 */
class LazyPdo
{
    private ?PDO $pdo = null;

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
     */
    public function pdo(): PDO
    {
        return $this->pdo ??= ($this->factory)();
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
