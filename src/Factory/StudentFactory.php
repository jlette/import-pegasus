<?php

namespace App\Factory;

use App\Model\Builder\StudentBuilder;
use App\Model\Student\AbstractStudent;
use App\Interface\ImportStrategyInterface;
use App\Constant\StudentDictionary;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Repository\ConcoursRepository;
use App\Service\ConcoursService;
use InvalidArgumentException;


class StudentFactory
{

    /**
     * Retourne la bonne stratégie d'import en fonction des choix de l'utilisateur.
     * * @param string $typeEtudiant Ex: 'dens', 'dri', 'agreg'
     * @param string $cursus Ex: 'scei', 'al', 'erasmus'
     */
    public static function create(string $formation, string $cursus): ImportStrategyInterface
    {
        $db = require __DIR__ . '/../../config/db.php';

        if ($formation === 'dens') {
            switch ($cursus) {
                case 'scei':
                    $repository = new ConcoursRepository($db);
                    $concoursService = new ConcoursService($repository);
                    return new SceiStrategy($concoursService);
                    //case 'al':
                    //case 'bl':
                    // case 'sil':
                    // case 'sis':
                    //     return new SelectionInternationaleStrategy();
                    // case 'nel':
                    // case 'nes':
                    // case 'nems':
                    // case 'nemh':
                    //     return new EtudiantNormalienStrategy();
            }
        }
        throw new InvalidArgumentException("Stratégie d'import non valide");
    }
}
