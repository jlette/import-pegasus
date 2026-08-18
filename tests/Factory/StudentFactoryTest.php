<?php

namespace Tests\Factory;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Factory\StudentFactory;
use App\Strategy\Normalien\CPGE\BlStrategy;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Strategy\Normalien\CPGE\AlStrategy;
use App\Strategy\Normalien\SI\SiLettreStrategy;
use App\Strategy\Normalien\SI\SiScienceStrategy;
use App\Strategy\Normalien\NE\NemhStrategy;
use App\Strategy\Normalien\NE\NemsStrategy;
use App\Strategy\DriStrategy;
use InvalidArgumentException;
use App\Database\LazyPdo;
use PDO;

class StudentFactoryTest extends TestCase
{
    #[DataProvider('densCursusProvider')]
    public function testCreateReturnsCorrectStrategyForDens(string $cursus, string $expectedClass): void
    {
        $db = LazyPdo::fromPdo($this->createMock(PDO::class));

        $strategy = StudentFactory::create('dens', $cursus, $db);

        $this->assertInstanceOf($expectedClass, $strategy);
    }

    public function testCreateReturnsDriStrategy(): void
    {
        $db = LazyPdo::fromPdo($this->createMock(PDO::class));
        
        $strategy = StudentFactory::create('dri', 'n_importe_quoi', $db);

        $this->assertInstanceOf(DriStrategy::class, $strategy);
    }

    public function testCreateThrowsExceptionForInvalidDensCursus(): void
    {
        $db = LazyPdo::fromPdo($this->createMock(PDO::class));

        try {
            StudentFactory::create('dens', 'cursus_imaginaire', $db);
            
            // Si la Factory ne plante pas, c'est une erreur. On fait échouer le test :
            $this->fail("Une InvalidArgumentException aurait dû être levée.");
        } catch (InvalidArgumentException $e) {
            // On vérifie le message d'erreur avec une assertion standard (qui ne sera jamais dépréciée)
            $this->assertSame("Cursus non valide pour DENS : cursus_imaginaire", $e->getMessage());
        }
    }

    public function testCreateThrowsExceptionForInvalidFormation(): void
    {
        $db = LazyPdo::fromPdo($this->createMock(PDO::class));

        try {
            StudentFactory::create('bts', 'al', $db);
            
            $this->fail("Une InvalidArgumentException aurait dû être levée.");
        } catch (InvalidArgumentException $e) {
            $this->assertSame("Stratégie d'import non valide pour : bts / al", $e->getMessage());
        }
    }

    /**
     * Fournisseur de données pour tester tous les cursus DENS.
     */
    public static function densCursusProvider(): array
    {
        return [
            'Cursus SCEI' => ['scei', SceiStrategy::class],
            'Cursus A/L'  => ['al', AlStrategy::class],
            'Cursus B/L'  => ['bl', BlStrategy::class],
            'Cursus SIL'  => ['sil', SiLettreStrategy::class],
            'Cursus SIS'  => ['sis', SiScienceStrategy::class],
            'Cursus NEMH' => ['nemh', NemhStrategy::class],
            'Cursus NEMS' => ['nems', NemsStrategy::class],
        ];
    }
}