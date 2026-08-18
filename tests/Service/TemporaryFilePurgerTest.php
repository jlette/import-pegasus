<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\TemporaryFilePurger;

#[CoversClass(TemporaryFilePurger::class)]
class TemporaryFilePurgerTest extends TestCase
{
    private string $repertoire;

    protected function setUp(): void
    {
        $this->repertoire = sys_get_temp_dir() . '/pegasus-purge-' . uniqid();
        mkdir($this->repertoire);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->repertoire . '/*') ?: [] as $fichier) {
            unlink($fichier);
        }
        rmdir($this->repertoire);
    }

    public function testLesFichiersPerimesSontSupprimes(): void
    {
        $ancien = $this->creer('ancien.csv', ageSecondes: 7200);
        $recent = $this->creer('recent.csv', ageSecondes: 60);

        $supprimes = (new TemporaryFilePurger($this->repertoire))->purger();

        $this->assertSame(1, $supprimes);
        $this->assertFileDoesNotExist($ancien);
        $this->assertFileExists($recent, "Un import en cours ne doit pas voir ses fichiers disparaître.");
    }

    public function testUnRepertoireAbsentNEstPasUneErreur(): void
    {
        $this->assertSame(0, (new TemporaryFilePurger($this->repertoire . '-inexistant'))->purger());
    }

    public function testLaRetentionEstConfigurable(): void
    {
        $this->creer('fichier.csv', ageSecondes: 120);

        $this->assertSame(1, (new TemporaryFilePurger($this->repertoire, 60))->purger());
    }

    private function creer(string $nom, int $ageSecondes): string
    {
        $chemin = $this->repertoire . '/' . $nom;
        file_put_contents($chemin, 'contenu');
        touch($chemin, time() - $ageSecondes);

        return $chemin;
    }
}
