<?php

namespace Tests\Source;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Source\ColumnCanonicalizer;
use App\Strategy\Normalien\SI\SiLettreStrategy;
use App\Service\ConcoursService;
use App\Interface\CodeRepositoryInterface;

#[CoversClass(ColumnCanonicalizer::class)]
class ColumnCanonicalizerTest extends TestCase
{
    /**
     * La normalisation générique suffit pour les écarts de casse, d'accents et
     * de séparateurs.
     */
    public function testLaNormalisationCouvreCasseAccentsEtSeparateurs(): void
    {
        $canonicalizer = new ColumnCanonicalizer([
            'Nom' => [],
            'CODE_POSTAL' => [],
            'nationalite' => [],
        ]);

        $ligne = $canonicalizer->canonicaliser([
            'NOM' => 'COLART',
            'CODE POSTAL' => '75005',
            'Nationalité' => 'Belgique',
        ]);

        $this->assertSame('COLART', $ligne['Nom']);
        $this->assertSame('75005', $ligne['CODE_POSTAL']);
        $this->assertSame('Belgique', $ligne['nationalite']);
    }

    /**
     * Les libellés réellement différents exigent un alias explicite.
     */
    public function testLesLibellesDifferentsExigentUnAlias(): void
    {
        $canonicalizer = new ColumnCanonicalizer([
            'naissance_date' => ['Date de naissance'],
        ]);

        $ligne = $canonicalizer->canonicaliser(['Date de naissance' => '26/10/2004']);

        $this->assertSame('26/10/2004', $ligne['naissance_date']);
    }

    /**
     * Une colonne déjà au nom canonique n'est jamais écrasée par un alias.
     */
    public function testLeNomCanoniqueEstPrioritaire(): void
    {
        $canonicalizer = new ColumnCanonicalizer(['Nom' => ['Patronyme']]);

        $ligne = $canonicalizer->canonicaliser(['Nom' => 'CANONIQUE', 'Patronyme' => 'ALIAS']);

        $this->assertSame('CANONIQUE', $ligne['Nom']);
    }

    public function testLesColonnesNonDeclareesSontConservees(): void
    {
        $ligne = (new ColumnCanonicalizer(['Nom' => []]))->canonicaliser([
            'NOM' => 'COLART',
            'Colonne inconnue' => 'valeur',
        ]);

        $this->assertSame('valeur', $ligne['Colonne inconnue']);
    }

    /**
     * Décision MOA H4 : les deux variantes du fichier SI-Lettres qui circulent
     * — extraction brute DEMATEC et fichier retravaillé par le CoST — doivent
     * produire exactement le même étudiant.
     */
    public function testLesDeuxVariantesSiLettresDonnentLeMemeResultat(): void
    {
        $strategy = new SiLettreStrategy(new ConcoursService(
            new class implements CodeRepositoryInterface {
                public function findByPlatforme(string $platforme): array
                {
                    return [];
                }
            }
        ));

        $canonicalizer = $strategy->canonicalizer();

        $extraction = $canonicalizer->canonicaliser([
            'Civilité' => 'M', 'Nom' => 'Colart', 'Prénom' => 'Léopold',
            'Email' => 'l@example.invalid', 'naissance_date' => '26/10/2004',
            'naissance_ville' => 'Charleroi', 'naissance_pays' => 'Belgique',
            'nationalite' => 'Belgique', 'domicile_ville' => 'Bern',
            'domicile_pays' => 'Suisse', 'Profil' => 'Sociologie',
        ]);

        $fichierCoSt = $canonicalizer->canonicaliser([
            'Civilité' => 'M', 'NOM' => 'Colart', 'Prénom' => 'Léopold',
            'Email' => 'l@example.invalid', 'Date de naissance' => '26/10/2004',
            'naissance_ville' => 'Charleroi', 'naissance_pays' => 'Belgique',
            'Nationalité' => 'Belgique', 'domicile_ville' => 'Bern',
            'Pays du domicile' => 'Suisse', 'Profil' => 'Sociologie',
            'Rang' => 'ADMIS', 'Confirmation venue' => 'OUI',
        ]);

        $depuisExtraction = $strategy->createStudent($extraction, 0, 0);
        $depuisCoSt = $strategy->createStudent($fichierCoSt, 0, 0);

        $this->assertSame($depuisExtraction->nom, $depuisCoSt->nom);
        $this->assertSame($depuisExtraction->prenom, $depuisCoSt->prenom);
        $this->assertSame($depuisExtraction->produit_programme, $depuisCoSt->produit_programme);
        $this->assertSame($depuisExtraction->nationalite_principal, $depuisCoSt->nationalite_principal);
        $this->assertSame($depuisExtraction->colonnesFinales(), $depuisCoSt->colonnesFinales());
    }
}
