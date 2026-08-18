<?php

namespace Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Strategy\AbstractStrategy;
use App\Constant\StudentDictionary;
use App\Model\Exception\UndeterminedSexException;
use App\Model\Student\AbstractStudent;

#[CoversClass(AbstractStrategy::class)]
class AbstractStrategyTest extends TestCase
{
    private object $strategy;

    protected function setUp(): void
    {
        // Sous-classe minimale : seule la normalisation de la civilité est testée.
        $this->strategy = new class extends AbstractStrategy {
            public function createStudent(array $row, int $currentLot, int $currentSsl): AbstractStudent
            {
                throw new \LogicException('Non utilisé dans ce test.');
            }

            public function exposerParseGenderAndSex(string $brut): array
            {
                return $this->parseGenderAndSex($brut);
            }

            public function exposerPatronyme(string $etatCivil, string $usage): string
            {
                return $this->patronyme($etatCivil, $usage);
            }

            public function exposerConnaissancesFormation(bool $estFonctionnaire): array
            {
                return $this->connaissancesFormation($estFonctionnaire);
            }
        };
    }

    #[DataProvider('civilitesMasculinesProvider')]
    public function testCivilitesMasculines(string $brut): void
    {
        [$sexe, $genre] = $this->strategy->exposerParseGenderAndSex($brut);

        // PEGASUS n'accepte que 'M' : une civilité 'H' est convertie.
        $this->assertSame(StudentDictionary::SEXE_M, $sexe);
        $this->assertSame('M', $sexe);
        $this->assertSame(StudentDictionary::GENRE_MASCULIN, $genre);
    }

    public static function civilitesMasculinesProvider(): array
    {
        return [['M'], ['M.'], ['Mr'], ['Monsieur'], ['Homme'], ['  homme  '], ['H']];
    }

    #[DataProvider('civilitesFemininesProvider')]
    public function testCivilitesFeminines(string $brut): void
    {
        [$sexe, $genre] = $this->strategy->exposerParseGenderAndSex($brut);

        $this->assertSame(StudentDictionary::SEXE_F, $sexe);
        $this->assertSame(StudentDictionary::GENRE_FEMININ, $genre);
    }

    public static function civilitesFemininesProvider(): array
    {
        // 'Mm' est la forme abrégée utilisée par DEMATEC.
        return [['Mme'], ['Mm'], ['F'], ['Femme'], ['Madame'], ['Mrs']];
    }

    /**
     * RG-02 : une civilité non déterminante ne doit jamais recevoir de valeur
     * par défaut. Auparavant, 'Autre' basculait silencieusement en Monsieur / M.
     */
    #[DataProvider('civilitesNonDeterminantesProvider')]
    public function testCiviliteNonDeterminanteEstRejetee(string $brut): void
    {
        $this->expectException(UndeterminedSexException::class);

        $this->strategy->exposerParseGenderAndSex($brut);
    }

    public static function civilitesNonDeterminantesProvider(): array
    {
        return [
            'valeur Autre de OnePSL30' => ['Autre'],
            'cellule vide' => [''],
            'espaces seuls' => ['   '],
            'saisie libre' => ['non binaire'],
            'valeur inattendue' => ['X'],
        ];
    }

    public function testLeMessageDErreurOrienteVersLeDossierDeCandidature(): void
    {
        try {
            $this->strategy->exposerParseGenderAndSex('Autre');
            $this->fail('Une UndeterminedSexException aurait dû être levée.');
        } catch (UndeterminedSexException $e) {
            $this->assertStringContainsString('Autre', $e->getMessage());
            $this->assertStringContainsString('dossier de candidature', $e->getMessage());
        }
    }

    /**
     * RG-04 : le nom et le prénom d'état civil sont obligatoires pour les
     * formations diplômantes ; ils priment donc sur le nom d'usage, qui ne sert
     * que de repli.
     */
    #[DataProvider('patronymesProvider')]
    public function testLEtatCivilPrimeSurLUsage(string $etatCivil, string $usage, string $attendu): void
    {
        $this->assertSame($attendu, $this->strategy->exposerPatronyme($etatCivil, $usage));
    }

    public static function patronymesProvider(): array
    {
        return [
            'les deux renseignés' => ['BERNARD', 'DUBOIS', 'BERNARD'],
            'état civil seul' => ['BERNARD', '', 'BERNARD'],
            'usage seul, repli' => ['', 'DUBOIS', 'DUBOIS'],
            'état civil en espaces' => ['   ', 'DUBOIS', 'DUBOIS'],
            'aucun des deux' => ['', '', ''],
        ];
    }

    /**
     * RG-01 : un admis non fonctionnaire perçoit une bourse de l'ENS. Dériver
     * les deux informations du seul statut rend l'incohérence impossible.
     */
    public function testLeStatutFonctionnaireDetermineBourseEtFinancement(): void
    {
        $fonctionnaire = $this->strategy->exposerConnaissancesFormation(true);
        $this->assertSame('NON', $fonctionnaire['ENS_BOURSE_ENS_PSL']);
        $this->assertSame('TRAITEMENT', $fonctionnaire['ENS_FINANCEMENT']);

        $boursier = $this->strategy->exposerConnaissancesFormation(false);
        $this->assertSame('OUI', $boursier['ENS_BOURSE_ENS_PSL']);
        $this->assertSame('BOURSE ENS', $boursier['ENS_FINANCEMENT']);
    }

    /**
     * Une chaîne vide écraserait la donnée existante dans PEGASUS lors d'un
     * réimport : toutes les connaissances de formation portent une valeur.
     */
    public function testAucuneConnaissanceDeFormationNEstVide(): void
    {
        foreach ([true, false] as $estFonctionnaire) {
            foreach ($this->strategy->exposerConnaissancesFormation($estFonctionnaire) as $type => $valeur) {
                $this->assertNotSame('', $valeur, "La connaissance {$type} ne doit jamais être vide.");
            }
        }
    }
}
