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
        };
    }

    #[DataProvider('civilitesMasculinesProvider')]
    public function testCivilitesMasculines(string $brut): void
    {
        [$sexe, $genre] = $this->strategy->exposerParseGenderAndSex($brut);

        // PEGASUS attend 'H', jamais 'M' : cf. canevas de référence 2025.
        $this->assertSame(StudentDictionary::SEXE_H, $sexe);
        $this->assertSame(StudentDictionary::GENRE_MASCULIN, $genre);
    }

    public static function civilitesMasculinesProvider(): array
    {
        return [['M'], ['M.'], ['Mr'], ['Monsieur'], ['Homme'], ['  homme  ']];
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
}
