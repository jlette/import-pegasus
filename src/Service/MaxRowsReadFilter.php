<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Filtre pour protéger la mémoire : On bloque la lecture au-delà d'un certain nombre de lignes.
 */
class MaxRowsReadFilter implements IReadFilter
{
    private int $maxRow;

    public function __construct(int $maxRow = 2000)
    {
        $this->maxRow = $maxRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // On ne stocke en mémoire que les X premières lignes
        if ($row <= $this->maxRow) {
            return true;
        }
        return false;
    }
}