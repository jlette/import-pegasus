<?php

namespace App\Strategy\Normalien\NE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\NemsDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;

class NemsStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        foreach (NemsDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
            if (!array_key_exists($cleExcel, $mappedRow)) {
                // Si ça plante ici, c'est probablement que la colonne s'appelle "Date de naissa" dans l'Excel brut
                throw new WrongFileFormatException($cleExcel);
            }

            if (empty(trim($mappedRow[$cleExcel] ?? ''))) {
                throw new MissingMandatoryFieldException($nomLisible);
            }
        }

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissanceBrute = $mappedRow[NemsDictionary::COL_DATE_NAISSANCE] ?? '';

        if (is_numeric($dateNaissanceBrute)) {
            $dateNaissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateNaissanceBrute);
        } else {
            $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        }

        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', (string) $dateNaissanceBrute);
        }

        $estFonctionnaire = false;
        $statutEtudiant = NormalienDictionary::STATUT_DENS_ETUDIANT;
        $ouiOunon = NormalienDictionary::NON;

        $genreBrut = mb_strtoupper(trim($mappedRow[NemsDictionary::COL_GENRE] ?? ''));
        $sexe = str_starts_with($genreBrut, 'F') ? StudentDictionary::SEXE_F : StudentDictionary::SEXE_M;
        $genre = str_starts_with($genreBrut, 'F') ? StudentDictionary::GENRE_FEMININ : StudentDictionary::GENRE_MASCULIN;

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[NemsDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // On peut récupérer le nom exact du concours depuis la colonne si besoin
        // $nomConcoursExcel = trim($mappedRow[NemsDictionary::COL_CONCOURS] ?? '');

        // Code Concours & Produit Programme par défaut pour NEMS

        $codeConcours = NormalienDictionary::CODE_CONCOURS_NE_MS; // À adapter si c'est un autre concours

        $produitProgramme = NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE;

        $nom = $mappedRow[NemsDictionary::COL_NOM_USAGE] ?: ($mappedRow[NemsDictionary::COL_NOM]);


        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[NemsDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
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

        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, $statutEtudiant)
            ->setIdentite($nom, $mappedRow[NemsDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        return $builder->buildNormalienStudent(
            $fopIns,
            '', // Situation familiale
            '', // Ville de naissance (souvent absente de ce type d'extraction)
            $dateNaissance,
            strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale,
            '', // Code INSEE 
            trim($mappedRow[NemsDictionary::COL_ADRESSE_POSTALE] ?? ''),
            trim($mappedRow[NemsDictionary::COL_COMPLEMENT_ADR] ?? ''),
            trim($mappedRow[NemsDictionary::COL_CODE_POSTAL] ?? ''),
            strtoupper(trim($mappedRow[NemsDictionary::COL_VILLE] ?? '')),
            strtoupper(trim($mappedRow[NemsDictionary::COL_PAYS] ?? '')),
            trim($mappedRow[NemsDictionary::COL_TELEPHONE] ?? '')
        );
    }
}
