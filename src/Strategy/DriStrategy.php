<?php

namespace App\Strategy;

use App\Builder\StudentBuilder;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\DriDictionary;

/**
 * Stratégie d'import pour les étudiants internationaux (Direction des Relations Internationales).
 * Cette population n'est pas rattachée à un concours classique et possède des règles d'état civil strictes.
 */
class DriStrategy extends AbstractStrategy
{
    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, DriDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[DriDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[DriDictionary::COL_GENRE] ?? '');

        // Règle Métier : Différenciation du statut PEGASUS.
        // L'étudiant est soit en programme Erasmus, soit considéré comme "Pensionnaire étranger".
        $produitProgramme = 'ANECHINTER';
        $programmeBrut = mb_strtolower(trim($mappedRow[DriDictionary::COL_PROGRAMME] ?? ''), 'UTF-8');
        $statutEtudiant = str_contains($programmeBrut, 'erasmus') ? 'ENS-DRI ECH ERASMUS' : 'ENS-DRI PENS ETRG';

        // Règle Métier (État Civil) : PEGASUS gère mal les caractères spéciaux internationaux (cyrillique, accents multiples).
        // L'administration exige un nettoyage complet des noms/prénoms pour cette population.
        $nomOriginal = trim($mappedRow[DriDictionary::COL_NOM] ?? '');
        $prenomOriginal = trim($mappedRow[DriDictionary::COL_PRENOM] ?? '');
        $nomSansAccents = $this->removeAccents($nomOriginal);
        $prenomSansAccents = $this->removeAccents($prenomOriginal);

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[DriDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Pas d'INE pour les internationaux entrants lors de la création
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[DriDictionary::COL_EMAIL] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => '',
            'NOM_ETAT_CIVIL'    => $nomOriginal, // Le nom original est conservé uniquement en "Connaissance"
            'PRENOM_ETAT_CIVIL' => $prenomOriginal,
            'NUMERO_INE'        => '',
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, $statutEtudiant)
            ->setIdentite($nomSansAccents, $prenomSansAccents, $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                '',
                $dateNaissance,
                strtoupper(trim($mappedRow[DriDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                trim($mappedRow[DriDictionary::COL_ADRESSE] ?? ''),
                '',
                trim($mappedRow[DriDictionary::COL_CODE_POSTAL] ?? ''),
                strtoupper(trim($mappedRow[DriDictionary::COL_VILLE] ?? '')),
                strtoupper(trim($mappedRow[DriDictionary::COL_PAYS] ?? '')),
                ''
            );
    }

    /**
     * Aplatit les caractères non latins vers l'ASCII.
     *
     * PEGASUS gère mal les caractères spéciaux internationaux : l'administration
     * exige un nom et un prénom translittérés pour cette population. Le nom
     * d'origine reste conservé dans les connaissances NOM_ETAT_CIVIL et
     * PRENOM_ETAT_CIVIL.
     *
     * La table de correspondance manuelle qui figurait ici comptait 64 caractères
     * source pour 63 remplacements : tout ce qui suivait 'ð' était décalé d'un
     * cran, si bien que MÜLLER devenait MYLLER et MUÑOZ devenait MUSOZ.
     * Transliterator couvre en outre le cyrillique, explicitement visé par le
     * besoin, ce qu'iconv ne fait pas.
     */
    private function removeAccents(string $string): string
    {
        static $transliterator = null;

        if ($transliterator === null && class_exists(\Transliterator::class)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        }

        if ($transliterator !== null) {
            $translitere = $transliterator->transliterate($string);
            if ($translitere !== false) {
                return $translitere;
            }
        }

        // Repli si l'extension intl est absente du serveur.
        $translitere = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        return $translitere !== false ? $translitere : $string;
    }
}
