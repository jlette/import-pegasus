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
use App\Database\LazyPdo;

class StudentFactory
{
    /**
     * Retourne la bonne stratégie d'import en fonction des choix de l'utilisateur.
     * @param string $formation Ex: 'dens', 'dri', 'agreg'
     * @param string $cursus Ex: 'scei', 'al', 'erasmus'
     * @param LazyPdo $db La connexion différée à l'annuaire
     * @param int|null $anneeCampagne Année de l'inscription visée ; année courante par défaut
     */
    public static function create(
        string $formation,
        string $cursus,
        LazyPdo $db,
        ?int $anneeCampagne = null,
    ): ImportStrategyInterface
    {

        $strategy = self::instancier($formation, $cursus, $db);

        return $anneeCampagne !== null ? $strategy->pourCampagne($anneeCampagne) : $strategy;
    }

    private static function instancier(string $formation, string $cursus, LazyPdo $db): ImportStrategyInterface
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