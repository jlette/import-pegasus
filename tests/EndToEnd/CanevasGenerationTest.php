<?php

namespace Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use App\Service\CsvExportService;
use App\Service\ConcoursService;
use App\Interface\CodeRepositoryInterface;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Strategy\Normalien\NE\NemsStrategy;
use App\Model\Exception\UndeterminedSexException;
use App\Constant\SceiDictionary;
use Tests\Fixtures\IdentitesFictives;

/**
 * Chaîne complète : lignes de fichier source -> stratégie -> canevas CSV.
 *
 * C'est le filet qui protège réellement PEGASUS : il valide la sortie telle
 * qu'elle sera importée, et non le comportement isolé d'un composant.
 * Les identités utilisées sont fictives (voir tests/Fixtures/README.md).
 */
class CanevasGenerationTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/pegasus-e2e-' . uniqid();
        mkdir($this->outputDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->outputDir . '/*') ?: [] as $fichier) {
            unlink($fichier);
        }
        rmdir($this->outputDir);
    }

    public function testUnImportSceiProduitUnCanevasConforme(): void
    {
        $lignes = $this->normaliser(new SceiStrategy($this->concoursService()), IdentitesFictives::lignesScei());
        $entete = $lignes[0];
        $donnees = array_slice($lignes, 1);

        $this->assertCount(43, $entete);
        $this->assertCount(3, $donnees);

        foreach ($donnees as $ligne) {
            $this->assertCount(43, $ligne, 'Chaque ligne doit compter autant de champs que l\'en-tête.');
            $this->assertSame('EOL', end($ligne));
            $this->assertSame('da', $ligne[3]);
            $this->assertSame('ANDENS1', $ligne[6]);
        }

        $parNom = $this->indexerParNom($entete, $donnees);

        // Fonctionnaire : le libellé du concours ne porte pas la mention.
        $this->assertSame('ENS-DENS FCTIONNAIRE', $parNom['MÜLLER']['Statut Etudiant']);
        $this->assertSame('OUI', $parNom['MÜLLER']['ENS_FONCTIONNAIRE']);
        $this->assertSame('NON', $parNom['MÜLLER']['ENS_BOURSE_ENS_PSL']);
        $this->assertSame('TRAITEMENT', $parNom['MÜLLER']['ENS_FINANCEMENT']);
        // Régression C2 : la casse multi-octets ne doit pas mutiler le nom.
        $this->assertSame('Clara', $parNom['MÜLLER']['Prénom']);
        $this->assertSame('F', $parNom['MÜLLER']['Sexe']);

        // Non fonctionnaire : mention explicite dans le libellé du concours.
        $this->assertSame('ENS-DENS ETUDIANT', $parNom['NAKAMURA']['Statut Etudiant']);
        $this->assertSame('NON', $parNom['NAKAMURA']['ENS_FONCTIONNAIRE']);
        $this->assertSame('OUI', $parNom['NAKAMURA']['ENS_BOURSE_ENS_PSL']);
        $this->assertSame('BOURSE ENS', $parNom['NAKAMURA']['ENS_FINANCEMENT']);
        $this->assertSame('H', $parNom['NAKAMURA']['Sexe']);
        $this->assertSame('JAPON', $parNom['NAKAMURA']['Nationalité Principale']);

        // Ressortissante UE : fonctionnarisable, et 'Ł' translittéré à l'écriture.
        $this->assertSame('OUI', $parNom['KOWALCZYK']['ENS_FONCTIONNAIRE']);
        $this->assertSame('POLOGNE', $parNom['KOWALCZYK']['Nationalité Principale']);
        $this->assertSame('Lucja', $parNom['KOWALCZYK']['Prénom']);
    }

    public function testUnImportNemsAppliqueLaPrioriteDeLEtatCivil(): void
    {
        $lignes = IdentitesFictives::lignesNems();
        // La troisième ligne porte une civilité « Autre » : elle est traitée à part.
        $exploitables = array_slice($lignes, 0, 2);

        $resultat = $this->normaliser(new NemsStrategy($this->concoursService()), $exploitables);
        $parNom = $this->indexerParNom($resultat[0], array_slice($resultat, 1));

        // RG-04 : l'état civil « Nguyen » prime sur le nom d'usage « Durand ».
        $this->assertArrayHasKey('NGUYEN', $parNom);
        $this->assertArrayNotHasKey('DURAND', $parNom);

        // Les NEMS ne sont jamais fonctionnaires.
        foreach ($parNom as $ligne) {
            $this->assertSame('NON', $ligne['ENS_FONCTIONNAIRE']);
            $this->assertSame('OUI', $ligne['ENS_BOURSE_ENS_PSL']);
            $this->assertSame('NEMS', $ligne['ENS_CONCOURS']);
            $this->assertSame('ANDENS1', $ligne['Produit Programme']);
        }
    }

    /**
     * RG-02 : une civilité « Autre » est rejetée, jamais devinée.
     */
    public function testUneCiviliteNonDeterminanteEstRejetee(): void
    {
        $strategy = new NemsStrategy($this->concoursService());
        $lignes = IdentitesFictives::lignesNems();

        $this->expectException(UndeterminedSexException::class);

        $strategy->createStudent($lignes[2], 0, 0);
    }

    /**
     * Le numéro de lot s'incrémente sans rupture ni saut : PEGASUS écrase des
     * données en cas d'erreur de séquence.
     */
    public function testLesNumerosDeLotSIncremententSansRupture(): void
    {
        $lignes = $this->normaliser(new SceiStrategy($this->concoursService()), IdentitesFictives::lignesScei());
        $donnees = array_slice($lignes, 1);

        foreach (array_values($donnees) as $index => $ligne) {
            $this->assertSame((string) $index, $ligne[1], 'No_Lot doit suivre la séquence.');
            $this->assertSame('0', $ligne[2], 'No_Ssl vaut 0 pour une ligne da.');
        }
    }

    /**
     * Exécute la chaîne complète et retourne le CSV décodé en UTF-8.
     *
     * @return list<list<string>>
     */
    private function normaliser(object $strategy, array $lignesSource): array
    {
        $etudiants = [];
        $lot = 0;

        foreach ($lignesSource as $ligne) {
            $etudiants[] = $strategy->createStudent($ligne, $lot, 0);
            $lot++;
        }

        $fichier = (new CsvExportService())->generateCsv(
            $etudiants,
            $this->outputDir,
            'test',
            $strategy->canevasProfile()
        );

        $contenu = mb_convert_encoding(
            file_get_contents($this->outputDir . '/' . $fichier),
            'UTF-8',
            'ISO-8859-1'
        );

        $resultat = [];

        foreach (explode("\r\n", trim($contenu)) as $ligne) {
            $resultat[] = str_getcsv($ligne, ';', '"', '\\');
        }

        return $resultat;
    }

    /**
     * Indexe les lignes par nom, en résolvant les paires Type/Valeur des
     * connaissances pour rendre les assertions lisibles.
     */
    private function indexerParNom(array $entete, array $donnees): array
    {
        $index = [];

        foreach ($donnees as $ligne) {
            $associatif = array_combine($entete, $ligne);
            $resolu = $associatif;

            foreach ($associatif as $colonne => $valeur) {
                if (preg_match('/^(Connaissance(?:_fop_ins)? \d+) Type$/', $colonne, $m)) {
                    $resolu[$valeur] = $associatif[$m[1] . ' Valeur'];
                }
            }

            $index[$associatif['Nom']] = $resolu;
        }

        return $index;
    }

    private function concoursService(): ConcoursService
    {
        $repository = new class implements CodeRepositoryInterface {
            public function findByPlatforme(string $platforme): array
            {
                return [
                    ['ANNUAIRE_CONC_CODE' => 'BCPST', 'CONC_CODE' => 'C-BCPST'],
                    ['ANNUAIRE_CONC_CODE' => 'MP', 'CONC_CODE' => 'C-MP'],
                    ['ANNUAIRE_CONC_CODE' => 'PC', 'CONC_CODE' => 'C-PC'],
                ];
            }
        };

        return new ConcoursService($repository);
    }
}
