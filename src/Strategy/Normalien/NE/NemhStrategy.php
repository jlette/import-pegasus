<?php

namespace App\Strategy\Normalien\NE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\NemhDictionary;
use App\Filter\AdmissionFilter;
use App\Service\ConcoursService;

/**
 * Stratégie d'import pour le flux NE-MH (Normalien Étudiant - Médecine Humanités).
 */
class NemhStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService)
    {
        parent::__construct();
    }

    protected function dictionary(): ?string
    {
        return NemhDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, NemhDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = $this->anneeCampagne;

        $dateNaissance = $this->parseDate($mappedRow[NemhDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[NemhDictionary::COL_GENRE] ?? '');

        // RG-04 : l'état civil prime, le nom d'usage n'est qu'un repli.
        $nom = $this->patronyme(
            $mappedRow[NemhDictionary::COL_NOM] ?? '',
            $mappedRow[NemhDictionary::COL_NOM_USAGE] ?? ''
        );
        $prenom = $this->patronyme(
            $mappedRow[NemhDictionary::COL_PRENOM] ?? '',
            $mappedRow[NemhDictionary::COL_PRENOM_USAGE] ?? ''
        );

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
            ->setIdentite($nom, $prenom, $genre, $sexe)
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

    /**
     * Les exports OnePSL30 portent l'état d'admission, la date de confirmation
     * et l'éventuel désistement.
     */
    public function admissionFilter(): AdmissionFilter
    {
        return new AdmissionFilter(
            valeursRetenues: [NemhDictionary::COL_ETAT => ['LP', 'LC', 'Admis', 'Admis sur LC']],
            colonnesDesistement: [NemhDictionary::COL_DESISTEMENT],
        );
    }
}
