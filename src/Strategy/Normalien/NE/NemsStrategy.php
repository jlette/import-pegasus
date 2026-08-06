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

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[NemsDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => NormalienDictionary::CODE_CONCOURS_NE_MS,
            'NOM_ETAT_CIVIL'    => $mappedRow[NemsDictionary::COL_NOM] ?? '',
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
            ->setIdentite($nom, $mappedRow[NemsDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                '',
                $dateNaissance,
                strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                trim($mappedRow[NemsDictionary::COL_ADRESSE_POSTALE] ?? ''),
                trim($mappedRow[NemsDictionary::COL_COMPLEMENT_ADR] ?? ''),
                trim($mappedRow[NemsDictionary::COL_CODE_POSTAL] ?? ''),
                strtoupper(trim($mappedRow[NemsDictionary::COL_VILLE] ?? '')),
                strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS] ?? '')),
                trim($mappedRow[NemsDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
