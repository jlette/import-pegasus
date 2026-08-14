<?php

namespace Tests\Strategy\Normalien\SI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\SI\SiLettreStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\SiLettreDictionary;
use App\Constant\NormalienDictionary;

#[CoversClass(SiLettreStrategy::class)]
class SiLettreStrategyTest extends TestCase
{
    private SiLettreStrategy $strategy;

    protected function setUp(): void
    {
        $concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new SiLettreStrategy($concoursServiceMock);
    }

    public function testCreateStudentMapsSociologieProfileToDss(): void
    {
        $mappedRow = [
            SiLettreDictionary::COL_NOM => 'GARCIA',
            SiLettreDictionary::COL_PRENOM => 'Maria',
            SiLettreDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            SiLettreDictionary::COL_EMAIL_PERSO => 'test@test.com',
            SiLettreDictionary::COL_CIVILITE => 'F',
            SiLettreDictionary::COL_NATIONALITE => 'CHILI', // Ajout obligatoire
            SiLettreDictionary::COL_PROFIL => 'Étudiante en sociologie',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        // Vérifie que le code programme est bien DSS
        $this->assertSame(NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_DSS, $student->produit_programme);
        // Toujours non fonctionnaire
        $this->assertSame(NormalienDictionary::NON, $student->connaissance['ENS_FONCTIONNAIRE']);
    }
}