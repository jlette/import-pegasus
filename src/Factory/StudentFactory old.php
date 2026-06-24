<?php

namespace App\Model\Factory;

use App\Model\Builder\StudentBuilder;
use App\Model\Student\AbstractStudent;
use App\Model\Strategy\ImportStrategyInterface;
use App\Constant\StudentDictionary;



class StudentFactory
{
    private StudentBuilder $builder;
    private string $provenanceAdmin;
    private int $currentLot = 0;
    private int $currentSsl = 0;

    public function __construct(StudentBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param array $row La ligne du Xls
     * @param ImportStrategyInterface $strategy Le "Spécialiste" choisi par l'utilisateur dans le formulaire
     */
    public function createFromXlsRow(string $provenanceAdmin, array $row, ImportStrategyInterface $strategy): AbstractStudent
    {
        // 1. Gestion stricte des compteurs PEGASUS (valable pour TOUS les imports)
        $typeOcc = strtolower(trim($row['Type_occ'] ?? 'da'));

        if ($typeOcc === 'da') {
            $this->currentLot++;
            $this->currentSsl = 0;
        } elseif ($typeOcc === 'cv') {
            $this->currentSsl++;
        }

        // 2. On délègue TOUTE la complexité de lecture au Spécialiste !
        return $strategy->createStudent($row, $this->builder, $this->currentLot, $this->currentSsl);
    }
}