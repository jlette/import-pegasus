<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\BlDictionary;
use App\Service\ConcoursService;

/**
 * Stratégie d'import spécifique au flux B/L (Concours Lettres et Sciences Sociales).
 */
class BlStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService)
    {
        parent::__construct();
    }
    protected function dictionary(): ?string
    {
        return BlDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, BlDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = $this->anneeCampagne;

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

        $connaissances = $this->connaissancesNormalien(
            $mappedRow[BlDictionary::COL_EMAIL_PERSO] ?? '',
            $annee,
            $estFonctionnaire,
            NormalienDictionary::CODE_CONCOURS_CPGE_BL
        );

        $fopIns = $this->connaissancesFormation($estFonctionnaire);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[BlDictionary::COL_NOM] ?? '', $mappedRow[BlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                mb_strtoupper(trim($mappedRow[BlDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                $mappedRow[BlDictionary::COL_ADRESSE_1] ?? '',
                $mappedRow[BlDictionary::COL_ADRESSE_2] ?? '',
                trim($mappedRow[BlDictionary::COL_CODE_POSTAL] ?? ''),
                mb_strtoupper(trim($mappedRow[BlDictionary::COL_VILLE] ?? '')),
                mb_strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_ADRESSE] ?? '')),
                trim($mappedRow[BlDictionary::COL_TELEPHONE] ?? '')
            );
    }
}