<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\MaxRowsReadFilter;

#[CoversClass(MaxRowsReadFilter::class)]
class MaxRowsReadFilterTest extends TestCase
{
    public function testLesLignesSousLePlafondSontLues(): void
    {
        $filtre = new MaxRowsReadFilter(5);

        $this->assertTrue($filtre->readCell('A', 1), "L'en-tête est toujours lu.");
        $this->assertTrue($filtre->readCell('Z', 6), 'Cinquième ligne de données.');
    }

    /**
     * Le filtre lit délibérément une ligne de plus que le plafond : c'est ce
     * dépassement qui permet à ExcelReaderService de refuser le fichier au lieu
     * de le tronquer en silence.
     */
    public function testUneLigneSupplementaireEstLuePourDetecterLeDepassement(): void
    {
        $filtre = new MaxRowsReadFilter(5);

        $this->assertTrue($filtre->readCell('A', 7), 'Ligne témoin du dépassement.');
        $this->assertFalse($filtre->readCell('A', 8), 'Au-delà, la mémoire est préservée.');
    }

    public function testLaMemoireResteProtegeeSurUnTresGrosFichier(): void
    {
        $filtre = new MaxRowsReadFilter();

        $this->assertFalse($filtre->readCell('ZZ', 99999));
    }

    public function testLePlafondEstExpose(): void
    {
        $this->assertSame(2000, (new MaxRowsReadFilter())->maxRow());
        $this->assertSame(50, (new MaxRowsReadFilter(50))->maxRow());
    }
}
