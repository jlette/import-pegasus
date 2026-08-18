<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SceiDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import pour la plateforme SCEI (Filière Sciences).
 */
class SceiStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, SceiDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee =  (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[SceiDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[SceiDictionary::COL_CIVILITE] ?? '');

        // Règle métier : Détection du statut via le libellé brut du concours.
        // Contrairement à l'A/L basé sur la nationalité, le fichier SCEI indique explicitement
        // la mention "NON FONCTIONNAIRE" dans le titre du concours si l'étudiant est boursier.
        $phraseConcours = mb_strtoupper(trim($mappedRow[SceiDictionary::COL_CONCOURS_LIBELLE] ?? ''));
        $estFonctionnaire = !str_contains($phraseConcours, 'NON FONCTIONNAIRE');

        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;
        $ouiOunon = $estFonctionnaire ?  NormalienDictionary::OUI : NormalienDictionary::NON;

        $codes = $this->concoursService->findByPlatforme(StudentDictionary::PLATEFORME_SCEI);
        $codeConcours = null;

        foreach ($codes as $code) {
            if (str_contains($phraseConcours, $code['ANNUAIRE_CONC_CODE'])) {
                $codeConcours = $code['CONC_CODE'];
                break;
            }
        }

        if ($codeConcours === null) {
            throw new MappingNotFoundException('le concours annuaire', $phraseConcours);
        }

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SceiDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipal = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        $connaissances = $this->connaissancesNormalien(
            $mappedRow[SceiDictionary::COL_EMAIL_PERSO] ?? '',
            $annee,
            $estFonctionnaire,
            $codeConcours
        );

        $fopIns = $this->connaissancesFormation($estFonctionnaire);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[SceiDictionary::COL_NOM] ?? '', $mappedRow[SceiDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                mb_strtoupper(trim($mappedRow[SceiDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[SceiDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipal,
                '',
                $mappedRow[SceiDictionary::COL_ADRESSE_VOIE_1] ?? '',
                $mappedRow[SceiDictionary::COL_ADRESSE_VOIE_2] ?? '',
                trim($mappedRow[SceiDictionary::COL_CODE_POSTAL] ?? ''),
                mb_strtoupper(trim($mappedRow[SceiDictionary::COL_VILLE] ?? '')),
                mb_strtoupper(trim($mappedRow[SceiDictionary::COL_PAYS] ?? '')),
                trim($mappedRow[SceiDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
