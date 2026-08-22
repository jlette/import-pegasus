<?php

namespace Tests\Repository;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Repository\ConcoursRepositoryAvecRepli;
use App\Interface\CodeRepositoryInterface;
use App\Constant\ConcoursDeSecours;
use App\Constant\StudentDictionary;
use App\Model\Exception\AnnuaireIndisponibleException;
use RuntimeException;

#[CoversClass(ConcoursRepositoryAvecRepli::class)]
class ConcoursRepositoryAvecRepliTest extends TestCase
{
    /**
     * Nominal : l'annuaire reste la source de vérité. Tant qu'il répond, sa
     * réponse est rendue telle quelle et le repli demeure invisible.
     */
    public function testUnAnnuaireJoignableEstServiSansRepli(): void
    {
        $attendu = [['ANNUAIRE_CONC_CODE' => 'MP', 'CONC_CODE' => 'C-MP']];
        $repository = new ConcoursRepositoryAvecRepli($this->annuaireQuiRepond($attendu));

        $this->assertSame($attendu, $repository->findByPlatforme(StudentDictionary::PLATEFORME_SCEI));
        $this->assertFalse($repository->repliActive());
        $this->assertSame('', $repository->codeTechnique());
    }

    /**
     * Raison d'être du dispositif : une panne d'annuaire en pleine fenêtre
     * d'admission ne doit pas condamner la campagne. Les codes CPGE étant
     * stables, la table embarquée prend le relais.
     */
    public function testUnAnnuaireInjoignableBasculeSurLaTableEmbarquee(): void
    {
        $repository = new ConcoursRepositoryAvecRepli($this->annuaireEnPanne('ORA-12541'));

        $this->assertSame(
            ConcoursDeSecours::pour(StudentDictionary::PLATEFORME_SCEI),
            $repository->findByPlatforme(StudentDictionary::PLATEFORME_SCEI)
        );
    }

    /**
     * Un repli silencieux serait pire que la panne : le canevas produit
     * ressemblerait en tout point à un canevas normal. Son usage doit être
     * observable, pour l'affichage comme pour le journal.
     */
    public function testLUsageDuRepliEstObservable(): void
    {
        $repository = new ConcoursRepositoryAvecRepli($this->annuaireEnPanne('ORA-28000'));
        $repository->findByPlatforme(StudentDictionary::PLATEFORME_EPONA);

        $this->assertTrue($repository->repliActive());
        $this->assertSame('ORA-28000', $repository->codeTechnique());
    }

    /**
     * Une plateforme absente de la table embarquée doit laisser remonter la
     * panne intacte. Servir une table vide ferait échouer chaque ligne pour
     * « concours introuvable » et masquerait la cause réelle.
     */
    public function testUnePlateformeNonCouverteLaisseRemonterLaPanne(): void
    {
        $repository = new ConcoursRepositoryAvecRepli($this->annuaireEnPanne('ORA-28000'));

        $this->expectException(AnnuaireIndisponibleException::class);
        $repository->findByPlatforme(StudentDictionary::PLATEFORME_DEMATEC);
    }

    /**
     * Le repli ne s'active que sur une indisponibilité. Un annuaire joignable
     * qui échoue pour une autre raison a le dernier mot : le repli n'est pas un
     * moyen de contourner un référentiel qui répond.
     */
    public function testUneErreurAutreQuUnePanneNEstPasRattrapee(): void
    {
        $annuaire = new class implements CodeRepositoryInterface {
            public function findByPlatforme(string $platforme): array
            {
                throw new RuntimeException('Aucun code PEGASUS trouvé.');
            }
        };

        $repository = new ConcoursRepositoryAvecRepli($annuaire);

        $this->expectException(RuntimeException::class);
        $repository->findByPlatforme(StudentDictionary::PLATEFORME_SCEI);
    }

    /**
     * Une fois la panne constatée, l'annuaire n'est plus sollicité : il ne se
     * répare pas au milieu d'une requête, et le solliciter à chaque ligne est
     * exactement ce qui a verrouillé le compte de service en août 2026.
     */
    public function testLAnnuaireNEstPlusSolliciteApresUnePanne(): void
    {
        $annuaire = new class implements CodeRepositoryInterface {
            public int $appels = 0;

            public function findByPlatforme(string $platforme): array
            {
                $this->appels++;

                throw new AnnuaireIndisponibleException('ORA-28000');
            }
        };

        $repository = new ConcoursRepositoryAvecRepli($annuaire);

        // Un fichier de 40 admis interroge le référentiel une fois par ligne.
        for ($ligne = 0; $ligne < 40; $ligne++) {
            $repository->findByPlatforme(StudentDictionary::PLATEFORME_SCEI);
        }

        $this->assertSame(1, $annuaire->appels);
    }

    private function annuaireQuiRepond(array $reponse): CodeRepositoryInterface
    {
        return new class($reponse) implements CodeRepositoryInterface {
            public function __construct(private array $reponse) {}

            public function findByPlatforme(string $platforme): array
            {
                return $this->reponse;
            }
        };
    }

    private function annuaireEnPanne(string $codeOracle): CodeRepositoryInterface
    {
        return new class($codeOracle) implements CodeRepositoryInterface {
            public function __construct(private string $codeOracle) {}

            public function findByPlatforme(string $platforme): array
            {
                throw new AnnuaireIndisponibleException($this->codeOracle);
            }
        };
    }
}
