<?php

namespace App\Factory;

use App\Interface\ImportStrategyInterface;
use App\Strategy\Normalien\CPGE\BlStrategy;
use App\Strategy\Normalien\CPGE\SceiStrategy;
use App\Strategy\Normalien\SI\SiScienceStrategy;
use App\Strategy\Normalien\NE\NemhStrategy;
use App\Strategy\Normalien\SI\SiLettreStrategy;
use App\Strategy\Normalien\NE\NemsStrategy;
use App\Strategy\DriStrategy;
use App\Strategy\Normalien\CPGE\AlStrategy; // Import ajouté
use App\Repository\ConcoursRepository;
use App\Service\ConcoursService;
use InvalidArgumentException;

class StudentFactory
{
    /**
     * Retourne la bonne stratégie d'import en fonction des choix de l'utilisateur.
     * @param string $formation Ex: 'dens', 'dri', 'agreg'
     * @param string $cursus Ex: 'scei', 'al', 'erasmus'
     */
    public static function create(string $formation, string $cursus): ImportStrategyInterface
    {
        $db = require __DIR__ . '/../../config/db.php';

        if ($formation === 'dens') {
            // Initialisation des dépendances pour les concours (CPGE, A/L, etc.)
            $repository = new ConcoursRepository($db);
            $concoursService = new ConcoursService($repository);

            switch ($cursus) {
                case 'scei':
                    return new SceiStrategy($concoursService);

                case 'al':
                    return new AlStrategy($concoursService);

                case 'bl':
                    return new BlStrategy($concoursService);
                case 'sil':
                    return new SiLettreStrategy($concoursService);
                case 'sis':
                    return new SiScienceStrategy($concoursService);
                case 'nemh':
                    return new NemhStrategy($concoursService);
                case 'nems':
                    return new NemsStrategy($concoursService);

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
        } elseif ($formation === 'dri') {
            //Si la formation est 'dri' 
            // On retourne la stratégie sans le service concours (elle n'en a pas besoin)
            return new DriStrategy();
        }


        throw new InvalidArgumentException("Stratégie d'import non valide pour : " . $formation . " / " . $cursus);
    }
}
