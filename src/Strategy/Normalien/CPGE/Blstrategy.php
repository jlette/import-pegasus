<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\BlDictionary;

/**
 * Stratégie d'import spécifique au flux B/L (Concours Lettres et Sciences Sociales).
 */
class BlStrategy extends AbstractStrategy
{
    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, BlDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[BlDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[BlDictionary::COL_CIVILITE] ?? '');

        // Règle Métier : Détection du statut Fonctionnaire vs Étudiant.
        // Contrairement au flux A/L, le fichier B/L ne gère qu'une seule nationalité déclarée.
        $nationaliteBrute = mb_strtoupper(trim($mappedRow[BlDictionary::COL_NATIONALITE] ?? ''));

        $isFrance = $nationaliteBrute === 'FRANCE' || str_contains($nationaliteBrute, 'FRANC') || str_contains($nationaliteBrute, 'FRANÇ');
        $isUe = in_array($nationaliteBrute, NormalienDictionary::NATIONALITES_UE) || in_array($nationaliteBrute, NormalienDictionary::PAYS_UE);

        $estFonctionnaire = $isFrance || $isUe;

        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);
        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[BlDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $estFonctionnaire ? NormalienDictionary::OUI : NormalienDictionary::NON,
            'ENS_CONCOURS'      => NormalienDictionary::CODE_CONCOURS_CPGE_BL, // Le code est fixe pour toute la population B/L
            'NUMERO_INE'        => $mappedRow[BlDictionary::COL_INE] ?? '',
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => $estFonctionnaire ? NormalienDictionary::FINANCEMENT_TRAITEMENT : NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[BlDictionary::COL_NOM] ?? '', $mappedRow[BlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                strtoupper(trim($mappedRow[BlDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                $mappedRow[BlDictionary::COL_ADRESSE_1] ?? '',
                $mappedRow[BlDictionary::COL_ADRESSE_2] ?? '',
                trim($mappedRow[BlDictionary::COL_CODE_POSTAL] ?? ''),
                strtoupper(trim($mappedRow[BlDictionary::COL_VILLE] ?? '')),
                strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_ADRESSE] ?? '')),
                trim($mappedRow[BlDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
