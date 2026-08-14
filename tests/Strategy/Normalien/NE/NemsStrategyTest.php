<?php

namespace Tests\Strategy\Normalien\NE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\NE\NemsStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\NemsDictionary;
use App\Constant\NormalienDictionary;

#[CoversClass(NemsStrategy::class)]
class NemsStrategyTest extends TestCase
{
    private NemsStrategy $strategy;

    protected function setUp(): void
    {
        // Le ConcoursService est injecté mais non utilisé dans la méthode createStudent,
        // on le mock simplement pour satisfaire le constructeur.
        $concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new NemsStrategy($concoursServiceMock);
    }

    public function testCreateStudentUsesNomUsageIfPresent(): void
    {
        $mappedRow = [
            NemsDictionary::COL_NOM => 'DUPONT',
            NemsDictionary::COL_NOM_USAGE => 'MARTIN', // Ici on teste AVEC le nom d'usage
            NemsDictionary::COL_PRENOM => 'Paul',
            NemsDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            NemsDictionary::COL_GENRE => 'M',
            NemsDictionary::COL_EMAIL => 'test@test.com',
            NemsDictionary::COL_NATIONALITE => 'FRANCE',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertInstanceOf(Normalien::class, $student);
        // On vérifie que la stratégie a bien favorisé le nom d'usage (qui est mis en majuscule par le Builder)
        $this->assertSame('MARTIN', $student->nom);
        // Et que le nom de naissance est stocké à part
        $this->assertSame('DUPONT', $student->connaissance['NOM_ETAT_CIVIL']);
    }

    public function testCreateStudentIsAlwaysNonFonctionnaireWithCorrectConcoursCode(): void
    {
        $mappedRow = [
            NemsDictionary::COL_NOM => 'DUPONT',
            NemsDictionary::COL_NOM_USAGE => '', // Ici on teste SANS le nom d'usage (la chaîne doit être vide)
            NemsDictionary::COL_PRENOM => 'Paul',
            NemsDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            NemsDictionary::COL_GENRE => 'M',
            NemsDictionary::COL_EMAIL => 'test@test.com',
            NemsDictionary::COL_NATIONALITE => 'FRANCE',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        // Vérifie le fallback sur le nom de naissance
        $this->assertSame('DUPONT', $student->nom);
        
        // Vérifie les règles inhérentes au statut NE-MS
        $this->assertSame(NormalienDictionary::NON, $student->connaissance['ENS_FONCTIONNAIRE']);
        $this->assertSame(NormalienDictionary::CODE_CONCOURS_NE_MS, $student->connaissance['ENS_CONCOURS']);
    }
}