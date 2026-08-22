<?php

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Database\LazyPdo;
use PDO;
use PDOException;

#[CoversClass(LazyPdo::class)]
class LazyPdoTest extends TestCase
{
    public function testLaConnexionNEstPasEtablieAvantLePremierUsage(): void
    {
        $appels = 0;
        new LazyPdo(function () use (&$appels): PDO {
            $appels++;
            return $this->createMock(PDO::class);
        });

        $this->assertSame(0, $appels, 'Un import DRI ne doit ouvrir aucune session Oracle.');
    }

    public function testLaConnexionReussieNEstEtablieQuUneFois(): void
    {
        $appels = 0;
        $db = new LazyPdo(function () use (&$appels): PDO {
            $appels++;
            return $this->createMock(PDO::class);
        });

        $db->pdo();
        $db->pdo();
        $db->pdo();

        $this->assertSame(1, $appels);
    }

    /**
     * Régression ORA-28000.
     *
     * L'échec n'était pas mémorisé : la connexion était retentée à chaque
     * appel. Le référentiel des concours étant interrogé une fois par ligne, un
     * mot de passe erroné produisait autant de tentatives d'authentification
     * qu'il y a d'étudiants dans le fichier. Le profil Oracle par défaut
     * verrouillant le compte au bout de dix échecs, un seul import suffisait à
     * le condamner.
     */
    public function testUneConnexionEchoueeNEstJamaisRetentee(): void
    {
        $tentatives = 0;
        $db = new LazyPdo(function () use (&$tentatives): PDO {
            $tentatives++;
            throw new PDOException('ORA-28000: Compte verrouillé.');
        });

        // Simule un fichier de 98 lignes interrogeant l'annuaire.
        for ($ligne = 0; $ligne < 98; $ligne++) {
            try {
                $db->pdo();
            } catch (PDOException) {
                // L'appelant traduit ensuite l'échec en erreur métier.
            }
        }

        $this->assertSame(1, $tentatives, 'Une seule tentative d\'authentification par requête.');
    }

    public function testLEchecInitialEstRejoueAlIdentique(): void
    {
        $db = new LazyPdo(static function (): PDO {
            throw new PDOException('ORA-28000: Compte verrouillé.');
        });

        $premier = null;
        try {
            $db->pdo();
        } catch (PDOException $e) {
            $premier = $e;
        }

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('ORA-28000');

        try {
            $db->pdo();
        } catch (PDOException $second) {
            $this->assertSame($premier, $second, 'La même exception doit être rejouée.');
            throw $second;
        }
    }
}
