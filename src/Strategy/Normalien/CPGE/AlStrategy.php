<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\AlDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\MappingNotFoundException;
use App\Model\Exception\WrongFileFormatException;

class AlStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        // 1. Validation des champs obligatoires et conformité du fichier
        foreach (AlDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
            // Si la colonne n'existe même pas dans l'en-tête, c'est le mauvais fichier !
            if (!array_key_exists($cleExcel, $mappedRow)) {
                throw new WrongFileFormatException($cleExcel);
            }

            if (empty(trim($mappedRow[$cleExcel] ?? ''))) {
                throw new MissingMandatoryFieldException($nomLisible);
            }
        }

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        // 2. Traitement de la date de naissance
        $dateNaissanceBrute = $mappedRow[AlDictionary::COL_DATE_NAISSANCE] ?? '';
        $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', $dateNaissanceBrute);
        }

        // 3. Récupération dynamique du code concours
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

        // 4. Variables métiers : Détection Fonctionnaire vs Étudiant (Double vérification nationalité)
        $nationalite1 = mb_strtoupper(trim($mappedRow[AlDictionary::COL_NATIONALITE] ?? ''));
        $nationalite2 = mb_strtoupper(trim($mappedRow[AlDictionary::COL_NATIONALITE_2] ?? $mappedRow['NATIONALITE2'] ?? $mappedRow['Nationalité 2'] ?? ''));

        $estFonctionnaire = false;
        // Appel au dictionnaire pour nettoyer la nationalité en PAYS
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationalite1);

        // On teste les deux nationalités, en donnant la priorité à celle de l'UE
        foreach ([$nationalite1, $nationalite2] as $nat) {
            if (empty($nat)) {
                continue;
            }

            $isFrance = $nat === 'FRANCE' || str_contains($nat, 'FRANC') || str_contains($nat, 'FRANÇ');
            $isUe = in_array($nat, NormalienDictionary::NATIONALITES_UE) || in_array($nat, NormalienDictionary::PAYS_UE);

            if ($isFrance || $isUe) {
                $estFonctionnaire = true;
                $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nat); // Écrase l'autre !
                break;
            }
        }

        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;
        $sexe = trim($mappedRow[AlDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;
        $genre = trim($mappedRow[AlDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;
        $ouiOunon = $estFonctionnaire ? NormalienDictionary::OUI : NormalienDictionary::NON;

        // 5. Matrices dynamiques
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

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => '',
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => '',
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => '',
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => $estFonctionnaire ? NormalienDictionary::FINANCEMENT_TRAITEMENT : NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        // 6. Assemblage
        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[AlDictionary::COL_NOM] ?? '', $mappedRow[AlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        // 7. Verrouillage
        return $builder->buildNormalienStudent(
            $fopIns,
            '',
            strtoupper(trim($mappedRow[AlDictionary::COL_VILLE_NAISSANCE] ?? '')),
            $dateNaissance,
            strtoupper(trim($mappedRow[AlDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale, // NATIONALITÉ BIEN FORMATÉE ICI
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
