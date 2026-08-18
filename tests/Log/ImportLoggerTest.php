<?php

namespace Tests\Log;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Log\ImportLogger;
use App\Model\Exception\UndeterminedSexException;
use RuntimeException;

#[CoversClass(ImportLogger::class)]
class ImportLoggerTest extends TestCase
{
    private string $fichier;

    protected function setUp(): void
    {
        $this->fichier = sys_get_temp_dir() . '/pegasus-log-' . uniqid() . '/import.log';
    }

    protected function tearDown(): void
    {
        if (is_file($this->fichier)) {
            unlink($this->fichier);
            rmdir(dirname($this->fichier));
        }
    }

    public function testUneReussiteEstConsignee(): void
    {
        $this->journal()->reussite('10.0.0.5', 'dens', 'bl', [
            'annee' => 2026,
            'retenus' => 25,
            'ecartes' => 3,
        ]);

        $ligne = $this->derniereLigne();

        $this->assertStringContainsString('INFO import.reussi', $ligne);
        $this->assertStringContainsString('agent=10.0.0.5', $ligne);
        $this->assertStringContainsString('population=dens', $ligne);
        $this->assertStringContainsString('cursus=bl', $ligne);
        $this->assertStringContainsString('retenus=25', $ligne);
        $this->assertStringContainsString('ecartes=3', $ligne);
    }

    public function testLesAnomaliesSontRecenseesParCategorie(): void
    {
        $this->journal()->anomalies('10.0.0.5', 'dens', 'nems', 4, [
            'UndeterminedSexException' => 1,
            'MissingMandatoryFieldException' => 3,
        ]);

        $ligne = $this->derniereLigne();

        $this->assertStringContainsString('WARNING import.rejete', $ligne);
        $this->assertStringContainsString('anomalies=4', $ligne);
        $this->assertStringContainsString('UndeterminedSexException=1', $ligne);
        $this->assertStringContainsString('MissingMandatoryFieldException=3', $ligne);
    }

    /**
     * Contrainte structurante : un journal est copié, sauvegardé et conservé
     * bien plus longtemps que les fichiers temporaires. Le message d'exception
     * reprend la valeur rencontrée dans le fichier source — ici une civilité,
     * ailleurs une date de naissance — et n'a rien à y faire.
     */
    public function testLeMessageDExceptionNEstPasConsigne(): void
    {
        $this->journal()->echec('10.0.0.5', 'dens', 'nems', new UndeterminedSexException('Autre'));

        $ligne = $this->derniereLigne();

        $this->assertStringContainsString('ERROR import.echec', $ligne);
        $this->assertStringContainsString('exception=UndeterminedSexException', $ligne);
        $this->assertStringNotContainsString('Autre', $ligne);
        $this->assertStringNotContainsString('dossier de candidature', $ligne);
    }

    /**
     * Une valeur multiligne scinderait l'événement en deux entrées.
     */
    public function testLesValeursMultilignesNeCassentPasLeFormat(): void
    {
        $this->journal()->reussite("10.0.0.5\nINFO faux.evenement", 'dens', 'bl', ['retenus' => 1]);

        $this->assertCount(1, file($this->fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    public function testLesValeursAvecEspaceSontEncadrees(): void
    {
        $this->journal()->echec('poste des concours', 'dens', 'bl', new RuntimeException('peu importe'));

        $this->assertStringContainsString('agent="poste des concours"', $this->derniereLigne());
    }

    public function testChaqueEvenementEstHorodateEnIso8601(): void
    {
        $this->journal()->reussite('10.0.0.5', 'dri', '', ['retenus' => 34]);

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2} /',
            $this->derniereLigne()
        );
    }

    public function testLesEvenementsSAjoutentSansEcraser(): void
    {
        $journal = $this->journal();
        $journal->reussite('10.0.0.5', 'dens', 'bl', ['retenus' => 1]);
        $journal->reussite('10.0.0.6', 'dens', 'al', ['retenus' => 2]);

        $this->assertCount(2, file($this->fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    private function journal(): ImportLogger
    {
        return new ImportLogger($this->fichier);
    }

    private function derniereLigne(): string
    {
        $lignes = file($this->fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return end($lignes);
    }
}
