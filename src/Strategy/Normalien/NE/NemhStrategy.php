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
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[NemhDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => NormalienDictionary::CODE_CONCOURS_NE_MH,
            'NOM_ETAT_CIVIL'    => $mappedRow[NemhDictionary::COL_NOM] ?? '',
            'PRENOM_ETAT_CIVIL' => '',
            'NUMERO_INE'        => '',
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => '',
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => '',
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => NormalienDictionary::OUI,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

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
                strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                trim($mappedRow[NemhDictionary::COL_ADRESSE_POSTALE] ?? ''),
                trim($mappedRow[NemhDictionary::COL_COMPLEMENT_ADR] ?? ''),
                trim($mappedRow[NemhDictionary::COL_CODE_POSTAL] ?? ''),
                strtoupper(trim($mappedRow[NemhDictionary::COL_VILLE] ?? '')),
                strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS] ?? '')),
                trim($mappedRow[NemhDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
