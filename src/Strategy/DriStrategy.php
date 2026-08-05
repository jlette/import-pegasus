<?php

namespace App\Strategy;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\DriDictionary;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;

class DriStrategy implements ImportStrategyInterface
{
    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        foreach (DriDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
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

        $dateNaissanceBrute = $mappedRow[DriDictionary::COL_DATE_NAISSANCE] ?? '';

        if (is_numeric($dateNaissanceBrute)) {
            $dateNaissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateNaissanceBrute);
        } else {
            $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
            if (!$dateNaissance) {
                $dateNaissance = DateTime::createFromFormat('Y-m-d', $dateNaissanceBrute);
            }
        }

        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', (string) $dateNaissanceBrute);
        }

        // 1. Produit Programme commun
        $produitProgramme = 'ANECHINTER';

        // 2. Différenciation du statut étudiant (Erasmus vs Pensionnaire)
        $programmeBrut = mb_strtolower(trim($mappedRow[DriDictionary::COL_PROGRAMME] ?? ''), 'UTF-8');

        if (str_contains($programmeBrut, 'erasmus')) {
            $statutEtudiant = 'ENS-DRI ECH ERASMUS';
        } else {
            $statutEtudiant = 'ENS-DRI PENS ETRG';
        }

        $nomOriginal = trim($mappedRow[DriDictionary::COL_NOM] ?? '');
        $prenomOriginal = trim($mappedRow[DriDictionary::COL_PRENOM] ?? '');

        // On enlève les accents pour PEGASUS sur les champs principaux
        $nomSansAccents = $this->removeAccents($nomOriginal);
        $prenomSansAccents = $this->removeAccents($prenomOriginal);

        $genreBrut = mb_strtoupper(trim($mappedRow[DriDictionary::COL_GENRE] ?? ''));
        $sexe = str_starts_with($genreBrut, 'F') ? StudentDictionary::SEXE_F : StudentDictionary::SEXE_M;
        $genre = str_starts_with($genreBrut, 'F') ? StudentDictionary::GENRE_FEMININ : StudentDictionary::GENRE_MASCULIN;

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[DriDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[DriDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => '', // A vérifier si la DRI utilise un code concours spécifique
            'NOM_ETAT_CIVIL'    => $nomOriginal, // On garde les accents étrangers ici !
            'PRENOM_ETAT_CIVIL' => $prenomOriginal,
            'NUMERO_INE'        => '', // Pas d'INE pour les internationaux entrants
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => NormalienDictionary::FINANCEMENT_BOURSE_ENS, // A valider avec le métier
        ];

        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, 1, $statutEtudiant)
            ->setIdentite($nomSansAccents, $prenomSansAccents, $genre, $sexe)
            ->setConnaissance($connaissances);

        return $builder->buildNormalienStudent(
            $fopIns,
            '',
            '', // Ville de naissance
            $dateNaissance,
            strtoupper(trim($mappedRow[DriDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale,
            '',
            trim($mappedRow[DriDictionary::COL_ADRESSE] ?? ''),
            '',
            trim($mappedRow[DriDictionary::COL_CODE_POSTAL] ?? ''),
            strtoupper(trim($mappedRow[DriDictionary::COL_VILLE] ?? '')),
            strtoupper(trim($mappedRow[DriDictionary::COL_PAYS] ?? '')),
            '' // Téléphone
        );
    }

    /**
     * Supprime les accents et caractères spéciaux pour PEGASUS.
     */
    private function removeAccents(string $string): string
    {
        $search = [
            'À',
            'Á',
            'Â',
            'Ã',
            'Ä',
            'Å',
            'Ç',
            'È',
            'É',
            'Ê',
            'Ë',
            'Ì',
            'Í',
            'Î',
            'Ï',
            'Ò',
            'Ó',
            'Ô',
            'Õ',
            'Ö',
            'Ù',
            'Ú',
            'Û',
            'Ü',
            'Ý',
            'à',
            'á',
            'â',
            'ã',
            'ä',
            'å',
            'ç',
            'è',
            'é',
            'ê',
            'ë',
            'ì',
            'í',
            'î',
            'ï',
            'ð',
            'ò',
            'ó',
            'ô',
            'õ',
            'ö',
            'ù',
            'ú',
            'û',
            'ü',
            'ý',
            'ÿ',
            'Ñ',
            'ñ',
            'Š',
            'š',
            'Ž',
            'ž',
            'Ć',
            'ć',
            'Č',
            'č',
            'Ł',
            'ł'
        ];
        $replace = [
            'A',
            'A',
            'A',
            'A',
            'A',
            'A',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            'a',
            'a',
            'a',
            'a',
            'a',
            'a',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'o',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'N',
            'n',
            'S',
            's',
            'Z',
            'z',
            'C',
            'c',
            'C',
            'c',
            'L',
            'l'
        ];

        return str_replace($search, $replace, $string);
    }
}
