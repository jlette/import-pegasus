<?php

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Repository\ConcoursRepository;
use App\Database\LazyPdo;
use App\Model\Exception\AnnuaireIndisponibleException;
use App\Interface\ErreurGlobaleInterface;
use PDO;
use PDOException;

#[CoversClass(ConcoursRepository::class)]
class ConcoursRepositoryTest extends TestCase
{
    /**
     * Une panne d'annuaire ne dépend d'aucune ligne : elle doit remonter comme
     * une erreur globale, qui interrompt le balayage (RG-03), et non comme une
     * anomalie de ligne répétée autant de fois qu'il y a d'étudiants.
     */
    public function testUnePanneDAnnuaireRemonteCommeErreurGlobale(): void
    {
        $repository = new ConcoursRepository($this->connexionEnPanne('ORA-28000: Compte verrouillé.'));

        try {
            $repository->findByPlatforme('SCEI');
            $this->fail('Une AnnuaireIndisponibleException aurait dû être levée.');
        } catch (AnnuaireIndisponibleException $e) {
            $this->assertInstanceOf(ErreurGlobaleInterface::class, $e);
        }
    }

    /**
     * Le message du pilote OCI comporte des chemins de compilation
     * (C:\build\...\oci_driver.c) : sans intérêt pour le gestionnaire, et
     * inutilement révélateurs de l'infrastructure.
     */
    public function testLeMessageTechniqueDuPiloteNEstPasRepercute(): void
    {
        $brut = 'SQLSTATE[HY000]: OCISessionBegin: ORA-28000: Compte verrouillé. '
            . '(C:\build\5348c34e\oci_driver.c:833)';

        $repository = new ConcoursRepository($this->connexionEnPanne($brut));

        try {
            $repository->findByPlatforme('SCEI');
            $this->fail('Une AnnuaireIndisponibleException aurait dû être levée.');
        } catch (AnnuaireIndisponibleException $e) {
            $this->assertStringNotContainsString('oci_driver', $e->getMessage());
            $this->assertStringNotContainsString('SQLSTATE', $e->getMessage());
            $this->assertStringNotContainsString('C:\build', $e->getMessage());
            // Le gestionnaire doit comprendre que corriger son fichier n'y changera rien.
            $this->assertStringContainsString('CRI', $e->getMessage());
        }
    }

    /**
     * Le code Oracle est en revanche conservé : c'est la première chose que
     * cherchera le support, et ce n'est pas une donnée personnelle.
     */
    #[DataProvider('codesOracleProvider')]
    public function testLeCodeOracleEstConserve(string $messageBrut, string $codeAttendu): void
    {
        $repository = new ConcoursRepository($this->connexionEnPanne($messageBrut));

        try {
            $repository->findByPlatforme('SCEI');
            $this->fail('Une AnnuaireIndisponibleException aurait dû être levée.');
        } catch (AnnuaireIndisponibleException $e) {
            $this->assertSame($codeAttendu, $e->codeTechnique());
        }
    }

    public static function codesOracleProvider(): array
    {
        return [
            'compte verrouillé' => ['SQLSTATE[HY000]: OCISessionBegin: ORA-28000: Compte verrouillé.', 'ORA-28000'],
            'écouteur absent' => ['ORA-12541: TNS : pas d\'écouteur', 'ORA-12541'],
            'identifiants refusés' => ['ORA-01017: invalid username/password', 'ORA-01017'],
            'sans code identifiable' => ['Connexion impossible', ''],
        ];
    }

    private function connexionEnPanne(string $message): LazyPdo
    {
        return new LazyPdo(static function () use ($message): PDO {
            throw new PDOException($message);
        });
    }
}
