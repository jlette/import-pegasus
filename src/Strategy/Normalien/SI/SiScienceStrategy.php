<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiScienceDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\MappingNotFoundException;
use App\Model\Exception\WrongFileFormatException;

class SiScienceStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        foreach (SiScienceDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
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

        $dateNaissanceBrute = $mappedRow[SiScienceDictionary::COL_DATE_NAISSANCE] ?? '';

        if (is_numeric($dateNaissanceBrute)) {
            $dateNaissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateNaissanceBrute);
        } else {
            // On tente d'abord le format français classique (01/07/2006)
            $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);

            // Si ça échoue, on tente le format international avec tirets (2006-07-01)
            if (!$dateNaissance) {
                $dateNaissance = DateTime::createFromFormat('Y-m-d', $dateNaissanceBrute);
            }
        }

        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', (string) $dateNaissanceBrute);
        }

        $estFonctionnaire = false;
        $statutEtudiant = NormalienDictionary::STATUT_DENS_ETUDIANT;
        $ouiOunon = NormalienDictionary::NON;

        // Le fichier utilise "M" et "Mm"
        $sexe = trim($mappedRow[SiScienceDictionary::COL_CIVILITE] ?? '') === 'M' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;
        $genre = trim($mappedRow[SiScienceDictionary::COL_CIVILITE] ?? '') === 'M' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;

        // Concaténation de l'indicatif et du numéro
        $indicatif = trim($mappedRow[SiScienceDictionary::COL_INDICATIF] ?? '');
        $numero = trim($mappedRow[SiScienceDictionary::COL_TELEPHONE] ?? '');
        // $telephoneComplet = !empty($indicatif) ? '+' . $indicatif . ' ' . $numero : $numero;
        $telephoneComplet = "+" . $indicatif . " " . $numero;
        // Nettoyage de la nationalité
        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Code Concours (Sélection Internationale Sciences)
        $codeConcours = NormalienDictionary::CODE_CONCOURS_CPGE_SI_SCIENCE;

        // Déduction du produit programme via le profil (en anglais)
        $profilBrut = $mappedRow[SiScienceDictionary::COL_PROFIL] ?? '';
        $produitProgramme = $this->determineProduitProgramme($profilBrut);

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[SiScienceDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
            'NOM_ETAT_CIVIL'    => '',
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
            ->setIdentite($mappedRow[SiScienceDictionary::COL_NOM] ?? '', $mappedRow[SiScienceDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        return $builder->buildNormalienStudent(
            $fopIns,
            '',
            strtoupper(trim($mappedRow[SiScienceDictionary::COL_VILLE_NAISSANCE] ?? '')),
            $dateNaissance,
            strtoupper(trim($mappedRow[SiScienceDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale,
            '',
            '',
            '',
            '',
            strtoupper(trim($mappedRow[SiScienceDictionary::COL_VILLE_DOMICILE] ?? '')),
            strtoupper(trim($mappedRow[SiScienceDictionary::COL_PAYS_DOMICILE] ?? '')),
            ''
        );
    }

    /**
     * STREAMING_CHUNK:Traduction des profils anglophones en codes PEGASUS
     */
    private function determineProduitProgramme(string $profil): string
    {
        $profilNorm = mb_strtolower(trim($profil), 'UTF-8');

        // Note : Vérifie que ces codes correspondent bien à tes constantes NormalienDictionary
        // S'ils n'existent pas encore dans NormalienDictionary, il faudra les rajouter (ex: ANDMAT1, ANDPHY1...)
        return match (true) {
            str_contains($profilNorm, 'math') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_DMA, // Mathématiques
            str_contains($profilNorm, 'physic') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_PHYS, // Physique
            str_contains($profilNorm, 'chemist') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_CHIM, // Chimie
            str_contains($profilNorm, 'earth') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_GSC, // Géosciences
            str_contains($profilNorm, 'cognitiv') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_DEC, // Sciences Cognitives (DEC)
            str_contains($profilNorm, 'biolog') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_BIO, // Biologie
            str_contains($profilNorm, 'comput') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_INFO, // Informatique

            default => throw new MappingNotFoundException('produit programme pour le profil', $profil)
        };
    }
}
