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
    public function __construct(private ConcoursService $concoursService)
    {
        parent::__construct();
    }

    protected function dictionary(): ?string
    {
        return AlDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, AlDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = $this->anneeCampagne;

        $dateNaissance = $this->parseDate($mappedRow[AlDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[AlDictionary::COL_CIVILITE] ?? '');

        // Règle métier : résolution du code concours via l'annuaire. Le flux
        // A/L ne porte qu'un seul concours : le libellé est constant.
        //
        // La comparaison était auparavant inversée — elle cherchait le code
        // annuaire *dans* la constante 'AL', si bien que n'importe quel code
        // 'A' ou 'L' correspondait.
        $codeConcours = $this->resolveConcours(
            AlDictionary::LIBELLE_CONCOURS,
            $this->concoursService->findByPlatforme(StudentDictionary::PLATEFORME_EPONA)
        );

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
        $connaissances = $this->connaissancesNormalien(
            $mappedRow[AlDictionary::COL_EMAIL_PERSO] ?? '',
            $annee,
            $estFonctionnaire,
            $codeConcours
        );

        // L'assignation de la bourse dépend strictement du statut défini plus haut
        $fopIns = $this->connaissancesFormation($estFonctionnaire);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OCC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[AlDictionary::COL_NOM] ?? '', $mappedRow[AlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                mb_strtoupper(trim($mappedRow[AlDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[AlDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                $mappedRow[AlDictionary::COL_ADRESSE_1] ?? '',
                $mappedRow[AlDictionary::COL_ADRESSE_2] ?? '',
                trim($mappedRow[AlDictionary::COL_CODE_POSTAL] ?? ''),
                mb_strtoupper(trim($mappedRow[AlDictionary::COL_VILLE] ?? '')),
                mb_strtoupper(trim($mappedRow[AlDictionary::COL_PAYS_ADRESSE] ?? '')),
                trim($mappedRow[AlDictionary::COL_TELEPHONE] ?? '')
            );
    }
}
