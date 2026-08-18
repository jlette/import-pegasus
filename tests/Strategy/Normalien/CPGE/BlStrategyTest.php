<?php

namespace Tests\Strategy\Normalien\CPGE;

use PHPUnit\Framework\TestCase;
use App\Strategy\Normalien\CPGE\BlStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\BlDictionary;
use App\Constant\NormalienDictionary;
use App\Model\Exception\MissingMandatoryFieldException;

class BlStrategyTest extends TestCase
{
    private BlStrategy $strategy;

    protected function setUp(): void
    {
        // On mock le ConcoursService requis par le constructeur de BlStrategy
        $concoursServiceMock = $this->createMock(ConcoursService::class);
        $this->strategy = new BlStrategy($concoursServiceMock);
    }

    public function testCreateStudentIsFonctionnaireWhenFrench(): void
    {
        // 1. ARRANGE : On simule une ligne Excel valide pour un étudiant français
        $mappedRow = [
            BlDictionary::COL_NOM => 'DUPONT',
            BlDictionary::COL_PRENOM => 'Jean',
            BlDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            BlDictionary::COL_EMAIL_PERSO => 'jean.dupont@test.com',
            BlDictionary::COL_CIVILITE => 'M',
            BlDictionary::COL_NATIONALITE => 'FRANCE', // Règle métier à tester
        ];

        // 2. ACT : On exécute la méthode
        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        // 3. ASSERT : Vérifications
        $this->assertInstanceOf(Normalien::class, $student);
        
        // Comme tes propriétés sont publiques en PHP 8.2, on y accède directement !
        $this->assertSame(NormalienDictionary::OUI, $student->connaissance['ENS_FONCTIONNAIRE']);
        $this->assertSame(NormalienDictionary::STATUT_DENS_FONCTIONNAIRE, $student->status_etudiant);
    }

    public function testCreateStudentIsEtudiantWhenForeignerNonUE(): void
    {
        // 1. ARRANGE : On simule une ligne Excel pour un étudiant hors UE
        $mappedRow = [
            BlDictionary::COL_NOM => 'SMITH',
            BlDictionary::COL_PRENOM => 'John',
            BlDictionary::COL_DATE_NAISSANCE => '15/06/1999',
            BlDictionary::COL_EMAIL_PERSO => 'john.smith@test.com',
            BlDictionary::COL_CIVILITE => 'M',
            BlDictionary::COL_NATIONALITE => 'CANADA', // Règle métier : Hors UE
        ];

        // 2. ACT
        $student = $this->strategy->createStudent($mappedRow, 1, 1);

        // 3. ASSERT
        $this->assertInstanceOf(Normalien::class, $student);
        $this->assertSame(NormalienDictionary::NON, $student->connaissance['ENS_FONCTIONNAIRE']);
        $this->assertSame(NormalienDictionary::STATUT_DENS_ETUDIANT, $student->status_etudiant);
    }
public function testCreateStudentThrowsExceptionWhenColumnIsMissing(): void
    {
        // 1. ARRANGE : La clé COL_DATE_NAISSANCE ('ddn') n'existe pas du tout dans le tableau
        $mappedRow = [
            BlDictionary::COL_NOM => 'MARTIN',
            BlDictionary::COL_PRENOM => 'Alice',
            BlDictionary::COL_EMAIL_PERSO => 'alice@test.com',
            BlDictionary::COL_CIVILITE => 'M',
        ];

        try {
            // 2. ACT
            $this->strategy->createStudent($mappedRow, 1, 1);
            $this->fail("Une WrongFileFormatException aurait dû être levée.");
        } catch (\App\Model\Exception\WrongFileFormatException $e) {
            // 3. ASSERT : On s'assure que c'est l'erreur de fichier non conforme
            $this->assertStringContainsString("Fichier non conforme", $e->getMessage());
        }
    }

    public function testCreateStudentThrowsExceptionWhenMandatoryFieldValueIsEmpty(): void
    {
        // 1. ARRANGE : La colonne 'ddn' existe bien, mais la cellule est vide !
        $mappedRow = [
            BlDictionary::COL_NOM => 'MARTIN',
            BlDictionary::COL_PRENOM => 'Alice',
            BlDictionary::COL_DATE_NAISSANCE => '', // La donnée est vide
            BlDictionary::COL_EMAIL_PERSO => 'alice@test.com',
            BlDictionary::COL_CIVILITE => 'M',
        ];

        try {
            // 2. ACT
            $this->strategy->createStudent($mappedRow, 1, 1);
            $this->fail("Une MissingMandatoryFieldException aurait dû être levée.");
        } catch (\App\Model\Exception\MissingMandatoryFieldException $e) {
            // 3. ASSERT : On s'assure que c'est l'erreur de champ vide
            $this->assertStringContainsString("n'est pas renseigné ou est vide", $e->getMessage());
        }
    }

    /**
     * Régression C8 : le fichier B/L 2026 transmis par le CoST ne comportait
     * pas de colonne « nationalité ». La stratégie lisait alors une chaîne vide
     * et basculait TOUTE la promotion en non-fonctionnaire — donc au mauvais
     * tarif d'inscription et au mauvais financement — sans le moindre message.
     */
    public function testUnFichierSansColonneNationaliteEstRejete(): void
    {
        $mappedRow = [
            \App\Constant\BlDictionary::COL_NOM => 'QUILHOT',
            \App\Constant\BlDictionary::COL_PRENOM => 'Charles',
            \App\Constant\BlDictionary::COL_DATE_NAISSANCE => '31/05/2005',
            \App\Constant\BlDictionary::COL_EMAIL_PERSO => 'test@example.invalid',
            \App\Constant\BlDictionary::COL_CIVILITE => 'M.',
            // La colonne nationalité est absente, comme dans le fichier 2026.
        ];

        $this->expectException(\App\Model\Exception\WrongFileFormatException::class);

        $this->strategy->createStudent($mappedRow, 0, 0);
    }
}
