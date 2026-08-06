<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\AlDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import spécifique au flux A/L (Concours Lettres).
 */
class AlStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, AlDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[AlDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[AlDictionary::COL_CIVILITE] ?? '');

        // Règle métier : Résolution dynamique du code concours PEGASUS via l'annuaire
        $codes = $this->concoursService->findByPlatforme(StudentDictionary::PLATEFORME_EPONA);
        $codeConcours = null;
        $phraseConcours = 'AL';

        foreach ($codes as $code) {
            if (str_contains($phraseConcours, $code['ANNUAIRE_CONC_CODE'])) {
                $codeConcours = $code['CONC_CODE'];
                break;
            }
        }

        if ($codeConcours === null) {
            throw new MappingNotFoundException('le concours annuaire', $phraseConcours);
        }

        // Règle métier (Critique) : Détection du statut Fonctionnaire vs Étudiant.
        // Les étudiants A/L ont souvent une double nationalité. 
        // Si L'UNE des deux nationalités est Française ou UE, l'étudiant obtient le statut fonctionnaire.
        $nationalite1 = mb_strtoupper(trim($mappedRow[AlDictionary::COL_NATIONALITE] ?? ''));
        $nationalite2 = mb_strtoupper(trim($mappedRow[AlDictionary::COL_NATIONALITE_2] ?? $mappedRow['NATIONALITE2'] ?? $mappedRow['Nationalité 2'] ?? ''));

        $estFonctionnaire = false;
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationalite1);

        foreach ([$nationalite1, $nationalite2] as $nat) {
            if (empty($nat)) continue;

            $isFrance = $nat === 'FRANCE' || str_contains($nat, 'FRANC') || str_contains($nat, 'FRANÇ');
            $isUe = in_array($nat, NormalienDictionary::NATIONALITES_UE) || in_array($nat, NormalienDictionary::PAYS_UE);

            if ($isFrance || $isUe) {
                $estFonctionnaire = true;
                // La nationalité ouvrant droit au statut prime et écrase l'autre dans le dossier PEGASUS
                $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nat);
                break;
            }
        }

        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;
        $ouiOunon = $estFonctionnaire ? NormalienDictionary::OUI : NormalienDictionary::NON;

        // Préparation des métadonnées requises spécifiquement pour l'ouverture des droits A/L
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[AlDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
            'NOM_ETAT_CIVIL'    => '',
            'PRENOM_ETAT_CIVIL' => '',
            'NUMERO_INE'        => $mappedRow[AlDictionary::COL_INE] ?? '',
        ];

        // L'assignation de la bourse dépend strictement du statut défini plus haut
        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => '',
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => '',
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => '',
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => $estFonctionnaire ? NormalienDictionary::FINANCEMENT_TRAITEMENT : NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[AlDictionary::COL_NOM] ?? '', $mappedRow[AlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                strtoupper(trim($mappedRow[AlDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                strtoupper(trim($mappedRow[AlDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                $mappedRow[AlDictionary::COL_ADRESSE_1] ?? '',
                $mappedRow[AlDictionary::COL_ADRESSE_2] ?? '',
                trim($mappedRow[AlDictionary::COL_CODE_POSTAL] ?? ''),
                strtoupper(trim($mappedRow[AlDictionary::COL_VILLE] ?? '')),
                strtoupper(trim($mappedRow[AlDictionary::COL_PAYS_ADRESSE] ?? '')),
                trim($mappedRow[AlDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
