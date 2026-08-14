<?php

namespace Tests\Strategy\Normalien\CPGE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\SceiDictionary;
use App\Constant\NormalienDictionary;

#[CoversClass(SceiStrategy::class)]
class SceiStrategyTest extends TestCase
{
    private SceiStrategy $strategy;
    private $concoursServiceMock;

    protected function setUp(): void
    {
        $this->concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new SceiStrategy($this->concoursServiceMock);
    }

    public function testCreateStudentIsBoursierWhenConcoursIndicatesNonFonctionnaire(): void
    {
        $this->concoursServiceMock->method('findByPlatforme')
             ->willReturn([['ANNUAIRE_CONC_CODE' => 'MP', 'CONC_CODE' => 'CODE_MP_TEST']]);

        // Le libellé contient "NON FONCTIONNAIRE"
        $mappedRow = [
            SceiDictionary::COL_NOM => 'DURAND',
            SceiDictionary::COL_PRENOM => 'Paul',
            SceiDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            SceiDictionary::COL_EMAIL_PERSO => 'test@test.com',
            SceiDictionary::COL_CIVILITE => 'M',
            SceiDictionary::COL_INE => '123456789EE', // Ajout obligatoire
            SceiDictionary::COL_CONCOURS_LIBELLE => 'CONCOURS MP NON FONCTIONNAIRE',
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertSame(NormalienDictionary::NON, $student->connaissance['ENS_FONCTIONNAIRE']);
        $this->assertSame(NormalienDictionary::STATUT_DENS_ETUDIANT, $student->status_etudiant);
    }
}