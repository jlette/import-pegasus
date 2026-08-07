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
use PDO;

class StudentFactory
{
    /**
     * Retourne la bonne stratégie d'import en fonction des choix de l'utilisateur.
     * @param string $formation Ex: 'dens', 'dri', 'agreg'
     * @param string $cursus Ex: 'scei', 'al', 'erasmus'
     * @param PDO $db La connexion à la base de données injectée
     */
    public static function create(string $formation, string $cursus, PDO $db): ImportStrategyInterface
    {

        if ($formation === 'dens') {
            // On utilise le $db reçu en paramètre
            $repository = new ConcoursRepository($db);
            $concoursService = new ConcoursService($repository);

            return match ($cursus) {
                'scei' => new SceiStrategy($concoursService),
                'al'   => new AlStrategy($concoursService),
                'bl'   => new BlStrategy($concoursService),
                'sil'  => new SiLettreStrategy($concoursService),
                'sis'  => new SiScienceStrategy($concoursService),
                'nemh' => new NemhStrategy($concoursService),
                'nems' => new NemsStrategy($concoursService),
                default => throw new InvalidArgumentException("Cursus non valide pour DENS : " . $cursus)
            };
        }

        if ($formation === 'dri') {
            return new DriStrategy();
        }

        throw new InvalidArgumentException("Stratégie d'import non valide pour : " . $formation . " / " . $cursus);
    }
}