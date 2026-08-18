<?php

namespace Tests\Strategy\Normalien\CPGE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\CPGE\AlStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\AlDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\StudentDictionary;
use App\Model\Exception\MappingNotFoundException;

#[CoversClass(AlStrategy::class)]
class AlStrategyTest extends TestCase
{
    private AlStrategy $strategy;
    private $concoursServiceMock;

    protected function setUp(): void
    {
        $this->concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new AlStrategy($this->concoursServiceMock);
    }

    public function testCreateStudentIsFonctionnaireWithSecondNationalityUE(): void
    {
        // On simule que la base de données retourne bien un code concours pour 'AL'
        $this->concoursServiceMock->method('findByPlatforme')
             ->willReturn([['ANNUAIRE_CONC_CODE' => 'AL', 'CONC_CODE' => 'CODE_AL_TEST']]);

        // La première nationalité est hors UE, mais la seconde est UE
$mappedRow = [
            AlDictionary::COL_NOM => 'MARTIN',
            AlDictionary::COL_PRENOM => 'Sophie',
            AlDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            AlDictionary::COL_EMAIL_PERSO => 'test@test.com',
            AlDictionary::COL_CIVILITE => 'F', // Ajout obligatoire
            AlDictionary::COL_CONCOURS_LIBELLE => 'AL', // Ajout obligatoire
            AlDictionary::COL_INE => '123456789EE', // Ajout obligatoire
            AlDictionary::COL_NATIONALITE => 'CANADA',
            AlDictionary::COL_NATIONALITE_2 => 'ITALIE', 
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        $this->assertInstanceOf(Normalien::class, $student);
        $this->assertSame(NormalienDictionary::OUI, $student->connaissance['ENS_FONCTIONNAIRE']);
    }

    public function testThrowsExceptionIfConcoursNotFoundInAnnuaire(): void
    {
        // On simule une base de données vide (aucun code trouvé)
        $this->concoursServiceMock->method('findByPlatforme')->willReturn([]);
        $mappedRow = [
            AlDictionary::COL_NOM => 'MARTIN',
            AlDictionary::COL_PRENOM => 'Sophie',
            AlDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            AlDictionary::COL_EMAIL_PERSO => 'test@test.com',
            AlDictionary::COL_CIVILITE => 'F', // Ajout obligatoire
            AlDictionary::COL_CONCOURS_LIBELLE => 'AL', // Ajout obligatoire
            AlDictionary::COL_INE => '123456789EE', // Ajout obligatoire
            AlDictionary::COL_NATIONALITE => 'Française', // Obligatoire : détermine le statut
        ];

        try {
            $this->strategy->createStudent($mappedRow, 1, 1);
            $this->fail("Une MappingNotFoundException aurait dû être levée.");
        } catch (MappingNotFoundException $e) {
            $this->assertStringContainsString("Aucune correspondance PEGASUS", $e->getMessage());
        }
    }
}