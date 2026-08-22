<?php

namespace Tests\Constant;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Constant\ConcoursDeSecours;
use App\Constant\StudentDictionary;
use App\Constant\AlDictionary;
use App\Constant\NormalienDictionary;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use App\Canevas\CanevasProfile;

#[CoversClass(ConcoursDeSecours::class)]
class ConcoursDeSecoursTest extends TestCase
{
    /**
     * Portée volontairement étroite : seules les deux plateformes dont les
     * codes sont stables d'une campagne à l'autre. L'élargir sans arbitrage
     * reviendrait à produire des canevas sur des codes devinés.
     */
    public function testSeulesLesPlateformesArbitreesSontCouvertes(): void
    {
        $this->assertSame(
            [StudentDictionary::PLATEFORME_SCEI, StudentDictionary::PLATEFORME_EPONA],
            ConcoursDeSecours::plateformes()
        );

        $this->assertFalse(ConcoursDeSecours::couvre(StudentDictionary::PLATEFORME_DEMATEC));
        $this->assertSame([], ConcoursDeSecours::pour(StudentDictionary::PLATEFORME_DEMATEC));
    }

    /**
     * La table doit avoir exactement la forme des lignes rendues par Oracle :
     * c'est ce qui permet à la résolution du libellé de s'appliquer sans
     * distinguer les deux sources.
     */
    public function testLaTableALaFormeDesLignesDeLAnnuaire(): void
    {
        foreach (ConcoursDeSecours::plateformes() as $plateforme) {
            $lignes = ConcoursDeSecours::pour($plateforme);

            $this->assertNotEmpty($lignes, "Le repli de {$plateforme} est vide.");

            foreach ($lignes as $ligne) {
                $this->assertSame(['ANNUAIRE_CONC_CODE', 'CONC_CODE'], array_keys($ligne));
                $this->assertNotSame('', $ligne['ANNUAIRE_CONC_CODE']);
                $this->assertNotSame('', $ligne['CONC_CODE']);
            }
        }
    }

    /**
     * `C-MPI` et `INFO` ont été supprimés en 2025 : PEGASUS ne les accepte
     * plus. Les réintroduire par le repli reviendrait à ressusciter, en cas de
     * panne, des codes que la campagne courante rejette.
     */
    public function testLesCodesSupprimesEn2025NeReapparaissentPas(): void
    {
        $codes = [];

        foreach (ConcoursDeSecours::plateformes() as $plateforme) {
            foreach (ConcoursDeSecours::pour($plateforme) as $ligne) {
                $codes[] = $ligne['CONC_CODE'];
            }
        }

        $this->assertNotContains(NormalienDictionary::CODE_CONCOURS_CPGE_SCIENCE_MPI, $codes);
        $this->assertNotContains('INFO', $codes);
    }

    /**
     * Le repli n'a d'intérêt que si la résolution du libellé sait l'exploiter :
     * on rejoue ici, sur la table embarquée, les libellés tels qu'ils
     * apparaissent dans les fichiers SCEI et A/L.
     *
     * Le cas `Groupe MPI` est le plus instructif : `MPI` ayant été supprimé, le
     * repli ne doit pas lui répondre `C-MP` par simple inclusion de chaîne.
     */
    #[DataProvider('libellesReels')]
    public function testLaResolutionParLibelleFonctionneSurLeRepli(
        string $plateforme,
        string $libelle,
        ?string $attendu,
    ): void {
        $resolveur = $this->resolveur();
        $codes = ConcoursDeSecours::pour($plateforme);

        if ($attendu === null) {
            $this->expectException(\App\Model\Exception\MappingNotFoundException::class);
            $resolveur($libelle, $codes);

            return;
        }

        $this->assertSame($attendu, $resolveur($libelle, $codes));
    }

    public static function libellesReels(): array
    {
        return [
            'SCEI — MP fonctionnaire' => [StudentDictionary::PLATEFORME_SCEI, 'CONCOURS MP', 'C-MP'],
            'SCEI — PC non fonctionnaire' => [StudentDictionary::PLATEFORME_SCEI, 'CONCOURS PC NON FONCTIONNAIRE', 'C-PC'],
            'SCEI — PSI n_est pas résolu en SI' => [StudentDictionary::PLATEFORME_SCEI, 'CONCOURS PSI', 'C-PSI'],
            'SCEI — BCPST' => [StudentDictionary::PLATEFORME_SCEI, 'CONCOURS BCPST', 'C-BCPST'],
            'SCEI — MPI supprimé en 2025' => [StudentDictionary::PLATEFORME_SCEI, 'GROUPE MPI', null],
            'EPONA — A/L' => [StudentDictionary::PLATEFORME_EPONA, AlDictionary::LIBELLE_CONCOURS, 'C-AL'],
        ];
    }

    /**
     * Expose `resolveConcours`, protégée dans la stratégie de base, afin de la
     * confronter à la table embarquée.
     */
    private function resolveur(): callable
    {
        $strategie = new class extends AbstractStrategy {
            public function createStudent(array $row, int $currentLot, int $currentSsl): AbstractStudent
            {
                throw new \LogicException('Hors sujet pour ce test.');
            }

            public function canevasProfile(): CanevasProfile
            {
                return CanevasProfile::normalien();
            }

            public function resoudre(string $libelle, array $codes): string
            {
                return $this->resolveConcours($libelle, $codes);
            }
        };

        return static fn(string $libelle, array $codes): string => $strategie->resoudre($libelle, $codes);
    }
}
