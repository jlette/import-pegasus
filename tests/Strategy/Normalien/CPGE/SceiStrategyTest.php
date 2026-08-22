<?php

namespace Tests\Strategy\Normalien\CPGE;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Service\ConcoursService;
use App\Model\Student\Normalien;
use App\Constant\SceiDictionary;
use App\Constant\NormalienDictionary;
use App\Interface\CodeRepositoryInterface;
use App\Model\Exception\AnnuaireIndisponibleException;
use App\Repository\ConcoursRepositoryAvecRepli;

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

    /**
     * Chaîne réelle, annuaire compris : une panne pendant la fenêtre
     * d'admission ne doit pas empêcher de produire un dossier, et le
     * gestionnaire doit repartir avec une réserve explicite.
     */
    public function testUnDossierEstProduitMalgreUnAnnuaireInjoignable(): void
    {
        $annuaire = new class implements CodeRepositoryInterface {
            public function findByPlatforme(string $platforme): array
            {
                throw new AnnuaireIndisponibleException('ORA-28000');
            }
        };

        $strategy = new SceiStrategy(
            new ConcoursService(new ConcoursRepositoryAvecRepli($annuaire))
        );

        $student = $strategy->createStudent([
            SceiDictionary::COL_NOM => 'DURAND',
            SceiDictionary::COL_PRENOM => 'Paul',
            SceiDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            SceiDictionary::COL_EMAIL_PERSO => 'test@test.com',
            SceiDictionary::COL_CIVILITE => 'M',
            SceiDictionary::COL_INE => '123456789EE',
            SceiDictionary::COL_CONCOURS_LIBELLE => 'CONCOURS PSI',
        ], 1, 1);

        // Le code vient de la table embarquée, et c'est bien le plus spécifique.
        $this->assertSame(
            NormalienDictionary::CODE_CONCOURS_CPGE_PSI,
            $student->connaissance['ENS_CONCOURS']
        );

        $this->assertNotEmpty($strategy->avertissements());
    }
}