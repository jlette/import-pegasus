<?php

namespace Tests\Strategy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Strategy\DriStrategy;
use App\Model\Student\Echange;
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
        $student = $this->strategy->createStudent($this->ligneSource(), 1, 1);

        // La DRI produit un Echange, pas un Normalien : les deux populations
        // n'ont pas le même canevas.
        $this->assertInstanceOf(Echange::class, $student);

        $this->assertSame('ENS-DRI ECH ERASMUS', $student->status_etudiant);
        $this->assertSame('ANECHINTER', $student->produit_programme);

        // Les caractères non latins sont translittérés pour PEGASUS.
        $this->assertSame('DEBORD', $student->nom);
        $this->assertSame('Emilie', $student->prenom);
    }

    /**
     * Slide 28 : « Les connaissances PROMO, FONCTIONNAIRE et CONCOURS ne
     * doivent pas être renseignées ». Les renseigner pour une population non
     * normalienne fausse l'annuaire de l'École.
     */
    public function testAucuneConnaissanceNormalienneNEstProduite(): void
    {
        $student = $this->strategy->createStudent($this->ligneSource(), 0, 0);

        $this->assertArrayNotHasKey('ENS_PROMO', $student->connaissance);
        $this->assertArrayNotHasKey('ENS_FONCTIONNAIRE', $student->connaissance);
        $this->assertArrayNotHasKey('ENS_CONCOURS', $student->connaissance);
        $this->assertSame([], $this->strategy->canevasProfile()->fopIns);
    }

    /**
     * Le contact d'urgence, le portable et le département de rattachement sont
     * obligatoires pour cette population.
     */
    public function testLesConnaissancesSpecifiquesAuxEchangesSontRenseignees(): void
    {
        $student = $this->strategy->createStudent($this->ligneSource(), 0, 0);

        $this->assertSame('WOOLWARD KEITHLEY', $student->connaissance['URGENCE PERSONNE']);
        $this->assertSame('0033763726678', $student->connaissance['URGENCE TELEPHONE']);
        $this->assertSame('0033143209203', $student->connaissance['PORTABLE']);
        $this->assertSame('LITTÉRATURES ET LANGAGE', $student->connaissance['ENS_DPT_RATT_ETU_ECHAN']);
    }

    /**
     * PEGASUS n'accepte que 'H' et 'F', toutes populations confondues.
     *
     * Le canevas DRI de juillet 2025 portait 78 'M' sur 169 lignes : ces
     * lignes ont été importées avec une valeur invalide. La convention est
     * donc unifiée sur celle des canevas normaliens.
     */
    public function testLeSexeMasculinVautToujoursH(): void
    {
        $ligne = $this->ligneSource();
        $ligne[DriDictionary::COL_GENRE] = 'M';

        $student = $this->strategy->createStudent($ligne, 0, 0);

        $this->assertSame('H', $student->sexe);
        $this->assertSame('Monsieur', $student->genre);
    }

    /**
     * L'export MoveOn nomme le département « Sous-établissement » et le
     * programme « Offre de séjour ».
     */
    public function testLesEnTetesMoveOnSontAcceptes(): void
    {
        $ligne = $this->strategy->canonicalizer()->canonicaliser([
            'NOM' => 'ADAMS', 'PRENOM' => 'Jane', 'COURRIEL' => 'jane@example.invalid',
            'SEXE' => 'F', 'DATE_NAISSANCE' => '21/04/1998',
            'Offre de séjour' => 'ENS-DRI ECH ERASMUS',
            'Sous-établissement' => 'Histoire',
        ]);

        $student = $this->strategy->createStudent($ligne, 0, 0);

        $this->assertSame('ENS-DRI ECH ERASMUS', $student->status_etudiant);
        $this->assertSame('HISTOIRE', $student->connaissance['ENS_DPT_RATT_ETU_ECHAN']);
    }

    /**
     * @return array<string, string>
     */
    private function ligneSource(): array
    {
        return [
            DriDictionary::COL_NOM => 'DÉBORD',
            DriDictionary::COL_PRENOM => 'Émilie',
            DriDictionary::COL_DATE_NAISSANCE => '01/01/2000',
            DriDictionary::COL_EMAIL => 'test@example.invalid',
            DriDictionary::COL_GENRE => 'F',
            DriDictionary::COL_PROGRAMME => 'Programme ERASMUS entrant',
            DriDictionary::COL_DPT_ENS => 'Littératures et langage',
            DriDictionary::COL_URGENCE_NOM => 'Woolward Keithley',
            DriDictionary::COL_URGENCE_INDICATIF => '33',
            DriDictionary::COL_URGENCE_TELEPHONE => '0763726678',
            DriDictionary::COL_INDICATIF => '33',
            DriDictionary::COL_TELEPHONE => '0143209203',
        ];
    }

    /**
     * Régression : la table de translittération manuelle comptait 64 caractères
     * source pour 63 remplacements. Tout ce qui suivait 'ð' était décalé d'un
     * cran — MÜLLER devenait MYLLER, MUÑOZ devenait MUSOZ, et 'ł' disparaissait.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('nomsInternationauxProvider')]
    public function testTranslitterationDesNomsInternationaux(string $source, string $attendu): void
    {
        $methode = new \ReflectionMethod(\App\Strategy\DriStrategy::class, 'removeAccents');
        $methode->setAccessible(true);

        $this->assertSame($attendu, $methode->invoke(new \App\Strategy\DriStrategy(), $source));
    }

    public static function nomsInternationauxProvider(): array
    {
        return [
            'tréma majuscule' => ['MÜLLER', 'MULLER'],
            'tilde espagnol' => ['MUÑOZ', 'MUNOZ'],
            'l barré polonais' => ['Łukasz', 'Lukasz'],
            'caron tchèque' => ['Šimon', 'Simon'],
            'y tréma' => ['ÿvette', 'yvette'],
            'accents portugais' => ['Ólafsdóttir', 'Olafsdottir'],
            'cyrillique' => ['Дмитрий', 'Dmitrij'],
            'nom déjà ASCII' => ['SMITH', 'SMITH'],
        ];
    }
}
