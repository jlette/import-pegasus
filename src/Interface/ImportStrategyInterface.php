<?php

namespace App\Interface;

use App\Builder\StudentBuilder;
use App\Model\Student\AbstractStudent;

/**
 * Contrat strict que tous les "Spécialistes" d'import doivent respecter.
 */
interface ImportStrategyInterface
{
    /**
     * Lit une ligne du xls, applique les règles métier du concours, 
     * et utilise le Builder pour retourner l'étudiant parfait.
     */
    public function createStudent(array $row, int $currentLot, int $currentSsl): AbstractStudent;
}
