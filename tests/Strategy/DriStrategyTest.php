<?php

namespace Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\DriStrategy;
use App\Model\Student\Normalien;
use App\Constant\DriDictionary;

#[CoversClass(DriStrategy::class)]
class DriStrategyTest extends TestCase
{
    private DriStrategy $strategy;

    protected function setUp(): void
    {
        // Pas de ConcoursService pour la DRI !
        $this->strategy = new DriStrategy();
    }

    public function testCreateStudentIsErasmusAndAccentsAreRemoved(): void
    {
        $mappedRow = [
            DriDictionary::COL_NOM => 'DÉBORD', // Avec accent
            DriDictionary::COL_PRENOM => 'Émilie', // Avec accent
            DriDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            DriDictionary::COL_EMAIL => 'test@test.com',
            DriDictionary::COL_GENRE => 'F',
            DriDictionary::COL_PROGRAMME => 'Programme ERASMUS entrant', 
        ];

        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        // Vérifie qu'il s'agit bien d'un étudiant en Echange
        $this->assertInstanceOf(Normalien::class, $student);

        // Vérifie que le statut Erasmus a bien été détecté
        $this->assertSame('ENS-DRI ECH ERASMUS', $student->status_etudiant);

        // Vérifie que les accents ont été retirés pour l'identité PEGASUS
        $this->assertSame('DEBORD', $student->nom);
        $this->assertSame('Emilie', $student->prenom);

        // Vérifie que l'identité originale avec accents est bien conservée dans les connaissances
        $this->assertSame('DÉBORD', $student->connaissance['NOM_ETAT_CIVIL']);
    }
}