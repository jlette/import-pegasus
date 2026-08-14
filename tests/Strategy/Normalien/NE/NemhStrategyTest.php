<?php

namespace Tests\Strategy\Normalien\NE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\NE\NemhStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\NemhDictionary;
use App\Constant\NormalienDictionary;

#[CoversClass(NemhStrategy::class)]
class NemhStrategyTest extends TestCase
{
    private NemhStrategy $strategy;

    protected function setUp(): void
    {
        $concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new NemhStrategy($concoursServiceMock);
    }

    public function testCreateStudentUsesNomUsageIfPresent(): void
    {
        $mappedRow = [
            NemhDictionary::COL_NOM => 'BERNARD',
            NemhDictionary::COL_NOM_USAGE => 'DUBOIS', // Ici on teste AVEC le nom d'usage
            NemhDictionary::COL_PRENOM => 'Alice',
            NemhDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            NemhDictionary::COL_GENRE => 'F',
            NemhDictionary::COL_EMAIL => 'test@test.com',
            NemhDictionary::COL_NATIONALITE => 'FRANCE',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertInstanceOf(Normalien::class, $student);
        $this->assertSame('DUBOIS', $student->nom);
        $this->assertSame('BERNARD', $student->connaissance['NOM_ETAT_CIVIL']);
    }

    public function testCreateStudentIsAlwaysNonFonctionnaireWithCorrectConcoursCode(): void
    {
        $mappedRow = [
            NemhDictionary::COL_NOM => 'BERNARD',
            NemhDictionary::COL_NOM_USAGE => '', // Ici on teste SANS le nom d'usage (la chaîne doit être vide)
            NemhDictionary::COL_PRENOM => 'Alice',
            NemhDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            NemhDictionary::COL_GENRE => 'F',
            NemhDictionary::COL_EMAIL => 'test@test.com',
            NemhDictionary::COL_NATIONALITE => 'FRANCE',
        ];
        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertSame('BERNARD', $student->nom);
        $this->assertSame(NormalienDictionary::NON, $student->connaissance['ENS_FONCTIONNAIRE']);
        $this->assertSame(NormalienDictionary::CODE_CONCOURS_NE_MH, $student->connaissance['ENS_CONCOURS']);
    }
}