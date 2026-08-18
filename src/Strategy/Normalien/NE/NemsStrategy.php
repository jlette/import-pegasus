<?php

namespace App\Strategy\Normalien\NE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\NemsDictionary;
use App\Service\ConcoursService;

/**
 * Stratégie d'import pour le flux NE-MS (Normalien Étudiant - Master Sciences).
 */
class NemsStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, NemsDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[NemsDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[NemsDictionary::COL_GENRE] ?? '');

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[NemsDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Règle métier : On privilégie le nom d'usage s'il existe, sinon on bascule sur le nom de naissance.
        $nom = $mappedRow[NemsDictionary::COL_NOM_USAGE] ?: ($mappedRow[NemsDictionary::COL_NOM]);

        $connaissances = $this->connaissancesNormalien(
            $mappedRow[NemsDictionary::COL_EMAIL] ?? '',
            $annee,
            false,
            NormalienDictionary::CODE_CONCOURS_NE_MS
        );

        $fopIns = $this->connaissancesFormation(false);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, NormalienDictionary::STATUT_DENS_ETUDIANT)
            ->setIdentite($nom, $mappedRow[NemsDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                '',
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                trim($mappedRow[NemsDictionary::COL_ADRESSE_POSTALE] ?? ''),
                trim($mappedRow[NemsDictionary::COL_COMPLEMENT_ADR] ?? ''),
                trim($mappedRow[NemsDictionary::COL_CODE_POSTAL] ?? ''),
                mb_strtoupper(trim($mappedRow[NemsDictionary::COL_VILLE] ?? '')),
                mb_strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS] ?? '')),
                trim($mappedRow[NemsDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
