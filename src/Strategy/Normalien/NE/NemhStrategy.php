<?php

namespace App\Strategy\Normalien\NE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\NemhDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;

class NemhStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        foreach (NemhDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
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

        $dateNaissanceBrute = $mappedRow[NemhDictionary::COL_DATE_NAISSANCE] ?? '';

        // Protection magique pour les dates formatées par Excel (comme pour les SI)
        if (is_numeric($dateNaissanceBrute)) {
            $dateNaissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateNaissanceBrute);
        } else {
            $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        }

        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', (string) $dateNaissanceBrute);
        }

        $estFonctionnaire = false;
        $statutEtudiant = NormalienDictionary::STATUT_DENS_ETUDIANT; // À adapter si c'est statut Master
        $ouiOunon = NormalienDictionary::NON;

        // Gestion du genre (Le fichier fourni écrit "F" ou "M" ou "Femme"/"Homme", on sécurise)
        $genreBrut = mb_strtoupper(trim($mappedRow[NemhDictionary::COL_GENRE] ?? ''));
        $sexe = str_starts_with($genreBrut, 'F') ? StudentDictionary::SEXE_F : StudentDictionary::SEXE_M;
        $genre = str_starts_with($genreBrut, 'F') ? StudentDictionary::GENRE_FEMININ : StudentDictionary::GENRE_MASCULIN;

        // Nettoyage de la nationalité
        $nationaliteBrute = mb_strtoupper(trim($mappedRow[NemhDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Code Concours & Produit Programme (Valeurs par défaut à vérifier avec la scolarité)
        $codeConcours = NormalienDictionary::CODE_CONCOURS_NE_MH; // À adapter si c'est un autre concours
        // Si c'est pour un master précis, remplace 'ANDNEMH' par le code produit réel.
        $produitProgramme = NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE;

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[NemhDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
            'NOM_ETAT_CIVIL'    => $mappedRow[NemhDictionary::COL_NOM] ?? '',
            'PRENOM_ETAT_CIVIL' => '',
            'NUMERO_INE'        => '', // Pas d'INE dans ce formulaire par défaut
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => '',
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => '',
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => NormalienDictionary::OUI,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => NormalienDictionary::FINANCEMENT_BOURSE_ENS, // Ou "AUTRE" selon le financement NEMH
        ];

        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[NemhDictionary::COL_NOM_USAGE] ?: ($mappedRow[NemhDictionary::COL_NOM] ?? ''), $mappedRow[NemhDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        return $builder->buildNormalienStudent(
            $fopIns,
            '', // Situation familiale
            '', // Ville de naissance (absente de l'extraction)
            $dateNaissance,
            strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale,
            '', // Code INSEE 
            trim($mappedRow[NemhDictionary::COL_ADRESSE_POSTALE] ?? ''), // Voie 1 
            trim($mappedRow[NemhDictionary::COL_COMPLEMENT_ADR] ?? ''), // Voie 2
            trim($mappedRow[NemhDictionary::COL_CODE_POSTAL] ?? ''), // Code postal 
            strtoupper(trim($mappedRow[NemhDictionary::COL_VILLE] ?? '')),
            strtoupper(trim($mappedRow[NemhDictionary::COL_PAYS] ?? '')),
            trim($mappedRow[NemhDictionary::COL_TELEPHONE] ?? '')
        );
    }
}
