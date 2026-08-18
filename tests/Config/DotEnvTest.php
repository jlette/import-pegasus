<?php

namespace Tests\Config;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Config\DotEnv;
use RuntimeException;

#[CoversClass(DotEnv::class)]
class DotEnvTest extends TestCase
{
    private string $fichier;

    /** @var list<string> */
    private array $clesDefinies = [];

    protected function setUp(): void
    {
        $this->fichier = sys_get_temp_dir() . '/pegasus-env-' . uniqid();
    }

    protected function tearDown(): void
    {
        foreach ($this->clesDefinies as $cle) {
            putenv($cle);
            unset($_ENV[$cle], $_SERVER[$cle]);
        }

        if (is_file($this->fichier)) {
            unlink($this->fichier);
        }
    }

    public function testLesVariablesSontChargees(): void
    {
        $this->ecrire("PEGASUS_TEST_HOTE=oracle1.cri.ulm\nPEGASUS_TEST_PORT=1521\n");
        $this->suivre('PEGASUS_TEST_HOTE', 'PEGASUS_TEST_PORT');

        DotEnv::charger($this->fichier);

        $this->assertSame('oracle1.cri.ulm', getenv('PEGASUS_TEST_HOTE'));
        $this->assertSame('1521', getenv('PEGASUS_TEST_PORT'));
    }

    public function testLesCommentairesEtLignesVidesSontIgnores(): void
    {
        $this->ecrire("# Un commentaire\n\nPEGASUS_TEST_CLE=valeur\n   # indenté\n");
        $this->suivre('PEGASUS_TEST_CLE');

        DotEnv::charger($this->fichier);

        $this->assertSame('valeur', getenv('PEGASUS_TEST_CLE'));
    }

    public function testLesGuillemetsEncadrantsSontRetires(): void
    {
        $this->ecrire("PEGASUS_TEST_A=\"mot de passe\"\nPEGASUS_TEST_B='autre'\n");
        $this->suivre('PEGASUS_TEST_A', 'PEGASUS_TEST_B');

        DotEnv::charger($this->fichier);

        $this->assertSame('mot de passe', getenv('PEGASUS_TEST_A'));
        $this->assertSame('autre', getenv('PEGASUS_TEST_B'));
    }

    /**
     * Un mot de passe peut contenir un signe égal : seule la première
     * occurrence sépare la clé de la valeur.
     */
    public function testLaValeurPeutContenirUnSigneEgal(): void
    {
        $this->ecrire("PEGASUS_TEST_MDP=a=b=c\n");
        $this->suivre('PEGASUS_TEST_MDP');

        DotEnv::charger($this->fichier);

        $this->assertSame('a=b=c', getenv('PEGASUS_TEST_MDP'));
    }

    /**
     * L'environnement réel fait autorité : un .env oublié sur le serveur ne
     * doit jamais prendre le pas sur la configuration du vhost.
     */
    public function testUneVariableDejaDefinieNEstPasEcrasee(): void
    {
        $this->suivre('PEGASUS_TEST_PRIORITE');
        putenv('PEGASUS_TEST_PRIORITE=valeur-environnement');

        $this->ecrire("PEGASUS_TEST_PRIORITE=valeur-fichier\n");
        DotEnv::charger($this->fichier);

        $this->assertSame('valeur-environnement', getenv('PEGASUS_TEST_PRIORITE'));
    }

    /**
     * Sur un serveur correctement configuré, les variables viennent de
     * l'environnement : l'absence de fichier est normale.
     */
    public function testUnFichierAbsentNEstPasUneErreur(): void
    {
        DotEnv::charger($this->fichier . '-inexistant');

        $this->expectNotToPerformAssertions();
    }

    public function testUneLigneMalformeeEstSignalee(): void
    {
        $this->ecrire("CETTE LIGNE N'A PAS DE SIGNE EGAL\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Ligne 1/');

        DotEnv::charger($this->fichier);
    }

    private function ecrire(string $contenu): void
    {
        file_put_contents($this->fichier, $contenu);
    }

    private function suivre(string ...$cles): void
    {
        foreach ($cles as $cle) {
            $this->clesDefinies[] = $cle;
        }
    }
}
