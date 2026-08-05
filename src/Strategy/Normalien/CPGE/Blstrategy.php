<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\BlDictionary;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;

class BlStrategy implements ImportStrategyInterface
{
    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        // 1. Validation des champs obligatoires et conformité du fichier
        foreach (BlDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
            // Vérification stricte de l'existence de la colonne
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
        $dateNaissanceBrute = $mappedRow[BlDictionary::COL_DATE_NAISSANCE] ?? '';
        $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', $dateNaissanceBrute);
        }

        // 3. Règle Métier : Détection Fonctionnaire vs Étudiant (Une seule nationalité pour B/L)
        $nationaliteBrute = mb_strtoupper(trim($mappedRow[BlDictionary::COL_NATIONALITE] ?? ''));

        $estFonctionnaire = false;

        $isFrance = $nationaliteBrute === 'FRANCE' || str_contains($nationaliteBrute, 'FRANC') || str_contains($nationaliteBrute, 'FRANÇ');
        $isUe = in_array($nationaliteBrute, NormalienDictionary::NATIONALITES_UE) || in_array($nationaliteBrute, NormalienDictionary::PAYS_UE);

        if ($isFrance || $isUe) {
            $estFonctionnaire = true;
        }

        // Appel au dictionnaire pour nettoyer la nationalité en PAYS
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // 4. Variables métiers (B/L)
        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;
        $sexe = trim($mappedRow[BlDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;
        $genre = trim($mappedRow[BlDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;
        $ouiOunon = $estFonctionnaire ? NormalienDictionary::OUI : NormalienDictionary::NON;

        // Le code concours pour le flux B/L est fixe selon la doc PEGASUS
        $codeConcours = NormalienDictionary::CODE_CONCOURS_CPGE_BL;

        // 5. Matrices dynamiques (Uniquement les colonnes utiles)
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[BlDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE' => '', // Vide  Sera renseigné par synchro ENS
            'NUMERO_ETU_PSLR' => '', // Vide  Sera renseigné lors création portail
            'ENS_NO_INDIVIDU' => '', //Vide si nouvel étudiant ou Sera renseigné par synchro ENS
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
            'NUMERO_INE'        => $mappedRow[BlDictionary::COL_INE] ?? '',
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => NormalienDictionary::NON,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => $estFonctionnaire ? NormalienDictionary::FINANCEMENT_TRAITEMENT : NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        // 6. Assemblage
        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[BlDictionary::COL_NOM] ?? '', $mappedRow[BlDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        // 7. Verrouillage
        return $builder->buildNormalienStudent(
            $fopIns,
            '', // Situation familiale
            strtoupper(trim($mappedRow[BlDictionary::COL_VILLE_NAISSANCE] ?? '')),
            $dateNaissance,
            strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale, // NATIONALITÉ BIEN FORMATÉE ICI EN PAYS
            '', // Code INSEE
            $mappedRow[BlDictionary::COL_ADRESSE_1] ?? '',
            $mappedRow[BlDictionary::COL_ADRESSE_2] ?? '',
            trim($mappedRow[BlDictionary::COL_CODE_POSTAL] ?? ''),
            strtoupper(trim($mappedRow[BlDictionary::COL_VILLE] ?? '')),
            strtoupper(trim($mappedRow[BlDictionary::COL_PAYS_ADRESSE] ?? '')),
            trim($mappedRow[BlDictionary::COL_TELEPHONE] ?? '')
        );
    }
}
