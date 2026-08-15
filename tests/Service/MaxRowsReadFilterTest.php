<?php

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Service\MaxRowsReadFilter;

#[CoversClass(MaxRowsReadFilter::class)]
class MaxRowsReadFilterTest extends TestCase
{
    public function testReadCellAllowsRowsUnderLimit(): void
    {
        // ARRANGE : Limite fixée à 5 lignes
        $filter = new MaxRowsReadFilter(5);

        // ACT & ASSERT
        $this->assertTrue($filter->readCell('A', 1));
        $this->assertTrue($filter->readCell('Z', 5));
    }

    public function testReadCellBlocksRowsOverLimit(): void
    {
        $filter = new MaxRowsReadFilter(2000);

        // ACT & ASSERT : La ligne 2001 doit être rejetée pour protéger la RAM
        $this->assertFalse($filter->readCell('A', 2001));
        $this->assertFalse($filter->readCell('ZZ', 9999));
    }
}