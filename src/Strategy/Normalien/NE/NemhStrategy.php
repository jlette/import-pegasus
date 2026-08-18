<?php

namespace App\Strategy\Normalien\NE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\NemhDictionary;
use App\Service\ConcoursService;

/**
 * Stratégie d'import pour le flux NE-MH (Normalien Étudiant - Médecine Humanités).
 */
class NemhStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, NemhDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[NemhDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[NemhDictionary::COL_GENRE] ?? '');

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[NemhDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Règle Métier : Les NEMH entrent toujours sous le statut non-fonctionnaire (Boursier ENS).
        // Ils utilisent le code produit générique CPGE et un code concours spécifique NEMH.
        $connaissances = $this->connaissancesNormalien(
            $mappedRow[NemhDictionary::COL_EMAIL] ?? '',
            $annee,
            false,
            NormalienDictionary::CODE_CONCOURS_NE_MH
        );

        $fopIns = $this->connaissancesFormation(false);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, NormalienDictionary::STATUT_DENS_ETUDIANT)
            ->setIdentite($mappedRow[NemhDictionary::COL_NOM_USAGE] ?: ($mappedRow[NemhDictionary::COL_NOM] ?? ''), $mappedRow[NemhDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                '',
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                trim($mappedRow[NemhDictionary::COL_ADRESSE_POSTALE] ?? ''),
                trim($mappedRow[NemhDictionary::COL_COMPLEMENT_ADR] ?? ''),
                trim($mappedRow[NemhDictionary::COL_CODE_POSTAL] ?? ''),
                mb_strtoupper(trim($mappedRow[NemhDictionary::COL_VILLE] ?? '')),
                mb_strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS] ?? '')),
                trim($mappedRow[NemhDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
