<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Plafonne le nombre de lignes chargées en mémoire.
 *
 * Le filtre lit délibérément **une ligne de plus** que la limite : c'est ce
 * dépassement qui permet à l'appelant de détecter que le fichier excède la
 * capacité de traitement, et de le refuser explicitement plutôt que de le
 * tronquer en silence.
 *
 * Une troncature muette serait le pire des comportements pour un outil dont la
 * promesse est la fiabilité : les étudiants au-delà du plafond disparaîtraient
 * du canevas sans que rien ne le signale.
 */
class MaxRowsReadFilter implements IReadFilter
{
    public const DEFAUT_MAX_LIGNES = 2000;

    public function __construct(private int $maxRow = self::DEFAUT_MAX_LIGNES) {}

    public function maxRow(): int
    {
        return $this->maxRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // La ligne 1 porte les en-têtes : le plafond s'entend en lignes de
        // données. On lit une ligne supplémentaire pour détecter le dépassement.
        return $row <= $this->maxRow + 2;
    }
}
