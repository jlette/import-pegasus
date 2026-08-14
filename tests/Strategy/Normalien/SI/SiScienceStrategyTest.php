<?php

namespace Tests\Strategy\Normalien\SI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\SI\SiScienceStrategy;
use App\Service\ConcoursService;
use App\Constant\SiScienceDictionary;
use App\Constant\NormalienDictionary;

#[CoversClass(SiScienceStrategy::class)]
class SiScienceStrategyTest extends TestCase
{
    private SiScienceStrategy $strategy;

    protected function setUp(): void
    {
        $concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new SiScienceStrategy($concoursServiceMock);
    }

    public function testCreateStudentMapsComputProfileToInfo(): void
    {
        $mappedRow = [
            SiScienceDictionary::COL_NOM => 'WANG',
            SiScienceDictionary::COL_PRENOM => 'Wei',
            SiScienceDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            SiScienceDictionary::COL_EMAIL_PERSO => 'test@test.com',
            SiScienceDictionary::COL_CIVILITE => 'M',
            SiScienceDictionary::COL_NATIONALITE => 'CHINE', // Ajout obligatoire
            SiScienceDictionary::COL_PROFIL => 'Computer Science',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertSame(NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_INFO, $student->produit_programme);
    }
}