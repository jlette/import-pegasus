<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Canevas\CanevasProfile;
use App\Service\CsvExportService;
use App\Builder\StudentBuilder;
use App\Constant\NormalienDictionary;
use App\Constant\StudentDictionary;
use DateTime;
use RuntimeException;

/**
 * Contrôle la conformité de forme du fichier produit : encodage, séparateur,
 * fins de ligne, et refus des étudiants qui ne respectent pas le profil.
 */
#[CoversClass(CsvExportService::class)]
class CsvExportServiceStructureTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/pegasus-test-' . uniqid();
        mkdir($this->outputDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->outputDir . '/*') ?: [] as $fichier) {
            unlink($fichier);
        }
        rmdir($this->outputDir);
    }

    public function testLeFichierEstEncodeEnIso88591AvecDesFinsDeLigneCrLf(): void
    {
        $contenu = $this->genererContenu();

        $this->assertStringContainsString("\r\n", $contenu, 'PEGASUS exige des fins de ligne CRLF.');

        // 'Ü' vaut 0xDC en ISO-8859-1 et 0xC3 0x9C en UTF-8.
        $this->assertStringContainsString("\xDC", $contenu, "Le 'Ü' doit être encodé sur un seul octet.");
        $this->assertStringNotContainsString("\xC3\x9C", $contenu, 'Le fichier ne doit pas rester en UTF-8.');
    }

    public function testLesCaracteresHorsIso88591SontTranslitteres(): void
    {
        // 'Ł' n'existe pas en ISO-8859-1 : //TRANSLIT doit le rabattre sur 'L'.
        $contenu = $this->genererContenu(prenom: 'Łukasz');

        $this->assertStringContainsString('Lukasz', $contenu);
    }

    public function testLaDerniereColonneDeChaqueLigneEstEol(): void
    {
        $lignes = array_filter(explode("\r\n", $this->genererContenu()));

        foreach ($lignes as $ligne) {
            $this->assertStringEndsWith('EOL', $ligne);
        }
    }

    /**
     * Une connaissance absente est une erreur, pas une colonne vide : une valeur
     * vide écrase la donnée existante dans PEGASUS lors d'un réimport.
     */
    public function testUnEtudiantIncompletEstRefuse(): void
    {
        $etudiant = $this->construireEtudiant(connaissances: ['EMAIL PERSONNEL' => 'a@b.fr']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/EMAIL ECOLE/');

        (new CsvExportService())->generateCsv(
            [$etudiant],
            $this->outputDir,
            'test',
            CanevasProfile::normalien()
        );
    }

    public function testAucunFichierNEstProduitSansEtudiant(): void
    {
        $nom = (new CsvExportService())->generateCsv([], $this->outputDir, 'test', CanevasProfile::normalien());

        $this->assertSame('', $nom);
        $this->assertSame([], glob($this->outputDir . '/*'));
    }

    private function genererContenu(string $prenom = 'JOSÉ'): string
    {
        $fichier = (new CsvExportService())->generateCsv(
            [$this->construireEtudiant(prenom: $prenom)],
            $this->outputDir,
            'test',
            CanevasProfile::normalien()
        );

        return file_get_contents($this->outputDir . '/' . $fichier);
    }

    private function construireEtudiant(string $prenom = 'JOSÉ', ?array $connaissances = null): \App\Model\Student\Normalien
    {
        $connaissances ??= [
            StudentDictionary::CONNAISSANCE_TYPE_EMAIL_PERSO     => 'a@b.fr',
            StudentDictionary::CONNAISSANCE_TYPE_EMAIL_ECOLE     => '',
            StudentDictionary::CONNAISSANCE_TYPE_NUMERO_ET_PSLR  => '',
            StudentDictionary::CONNAISSANCE_TYPE_NO_INDIVIDU     => '',
            NormalienDictionary::CONNAISSANCE_TYPE_PROMO         => '2026',
            NormalienDictionary::CONNAISSANCE_TYPE_FONCTIONNAIRE => 'OUI',
            NormalienDictionary::CONNAISSANCE_TYPE_CONCOURS      => 'C-BL',
        ];

        return (new StudentBuilder())
            ->setInfosPegasus(new DateTime(), 0, 0, 'da', '', 1, 'EOL')
            ->setScolarite(2026, 'ANDENS1', 2026, 'ENS-DENS FCTIONNAIRE')
            ->setIdentite('MÜLLER', $prenom, 'Monsieur', 'H')
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                [
                    NormalienDictionary::FOP_INS_TYPE_SITUATION_CST    => 'NON',
                    NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB    => 'NON',
                    NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE => 'EN SCOLARITE',
                    NormalienDictionary::FOP_INS_TYPE_BOURSE           => 'NON',
                    NormalienDictionary::FOP_INS_TYPE_FINANCEMENT      => 'TRAITEMENT',
                ],
                '', 'PARIS', new DateTime('2005-05-31'), 'FRANCE', 'FRANCE',
                '', '', '', '', '', '', ''
            );
    }
}
