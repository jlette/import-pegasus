<?php

namespace App\Strategy;

use App\Builder\StudentBuilder;
use App\Canevas\CanevasProfile;
use App\Constant\DriDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\StudentDictionary;
use App\Model\Student\AbstractStudent;
use DateTime;

/**
 * Stratégie d'import des étudiants en échange international entrants (DRI).
 *
 * Cette population n'est pas rattachée à un concours : les connaissances
 * ENS_PROMO, ENS_FONCTIONNAIRE et ENS_CONCOURS ne doivent jamais être
 * renseignées, sous peine de fausser l'annuaire de l'École. Elle exige en
 * revanche un contact d'urgence, un département de rattachement et une adresse
 * personnelle complète.
 *
 * Les imports ont lieu deux fois par an : à l'été pour la rentrée de septembre,
 * en décembre pour celle de janvier.
 */
class DriStrategy extends AbstractStrategy
{
    public function canevasProfile(): CanevasProfile
    {
        return CanevasProfile::echange();
    }

    /**
     * Le canevas DRI porte 'M' pour les hommes, là où les canevas normaliens
     * portent 'H'. Les deux conventions sont attestées sur des fichiers 2025
     * réellement importés : 78 occurrences de 'M' côté DRI, 67 de 'H' côté
     * normaliens. Chaque population conserve donc la sienne.
     */
    protected function sexeMasculin(): string
    {
        return 'M';
    }

    /**
     * L'export MoveOn reformaté par la DRI a connu plusieurs conventions de
     * nommage : les variantes rencontrées sont acceptées.
     */
    protected function columnAliases(): array
    {
        return [
            DriDictionary::COL_CODE_POSTAL      => ['CODE POSTAL'],
            DriDictionary::COL_COMPLEMENT_ADR   => ['COMPLEMENT ADRESSE'],
            DriDictionary::COL_DATE_NAISSANCE   => ['DATE NAISSANCE'],
            DriDictionary::COL_URGENCE_TELEPHONE => ['URGENCE TELEPHONE'],
            DriDictionary::COL_DPT_ENS          => ['Sous-établissement'],
            DriDictionary::COL_PROGRAMME        => ['Offre de séjour'],
        ];
    }

    protected function dictionary(): ?string
    {
        return DriDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, DriDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[DriDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[DriDictionary::COL_GENRE] ?? '');

        // Règle métier : l'étudiant relève soit d'un échange Erasmus, soit du
        // statut de pensionnaire étranger (accord bilatéral).
        $programme = mb_strtolower(trim($mappedRow[DriDictionary::COL_PROGRAMME] ?? ''), 'UTF-8');
        $statutEtudiant = str_contains($programme, 'erasmus')
            ? DriDictionary::STATUT_ERASMUS
            : DriDictionary::STATUT_PENSIONNAIRE;

        // Règle métier (état civil) : PEGASUS gère mal les caractères non
        // latins. L'administration exige une translittération du nom et du
        // prénom pour cette population.
        $nom = $this->removeAccents(trim($mappedRow[DriDictionary::COL_NOM] ?? ''));
        $prenom = $this->removeAccents(trim($mappedRow[DriDictionary::COL_PRENOM] ?? ''));

        $nationalite = NormalienDictionary::formatNationaliteToPays(
            mb_strtoupper(trim($mappedRow[DriDictionary::COL_NATIONALITE] ?? ''), 'UTF-8')
        );

        $connaissances = [
            StudentDictionary::CONNAISSANCE_TYPE_EMAIL_PERSO    => trim($mappedRow[DriDictionary::COL_EMAIL] ?? ''),
            StudentDictionary::CONNAISSANCE_TYPE_EMAIL_ECOLE    => '',
            StudentDictionary::CONNAISSANCE_TYPE_NUMERO_ET_PSLR => '',
            StudentDictionary::CONNAISSANCE_TYPE_NO_INDIVIDU    => '',
            StudentDictionary::TYPE_URGENCE_PERSONNE            => $this->contactUrgence($mappedRow),
            StudentDictionary::TYPE_URGENCE_TEL                 => $this->telephone(
                $mappedRow[DriDictionary::COL_URGENCE_INDICATIF] ?? '',
                $mappedRow[DriDictionary::COL_URGENCE_TELEPHONE] ?? ''
            ),
            StudentDictionary::TYPE_PORTABLE                    => $this->telephone(
                $mappedRow[DriDictionary::COL_INDICATIF] ?? '',
                $mappedRow[DriDictionary::COL_TELEPHONE] ?? ''
            ),
            // Le département de rattachement doit être en majuscules.
            StudentDictionary::TYPE_DPT_RATT_ECHAN              => mb_strtoupper(
                trim($mappedRow[DriDictionary::COL_DPT_ENS] ?? ''),
                'UTF-8'
            ),
        ];

        return $builder
            ->setInfosPegasus(
                $dateActuelle,
                $currentLot,
                $currentSsl,
                StudentDictionary::TYPE_OOC_DA,
                StudentDictionary::RECRUTEMENT,
                StudentDictionary::SESSION,
                StudentDictionary::EOL
            )
            ->setScolarite($annee, DriDictionary::PRODUIT_PROGRAMME, $annee, $statutEtudiant)
            ->setIdentite($nom, $prenom, $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildEchangeStudent(
                '',
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_VILLE_NAISSANCE] ?? ''), 'UTF-8'),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_PAYS_NAISSANCE] ?? ''), 'UTF-8'),
                $nationalite,
                '',
                trim((string) ($mappedRow[DriDictionary::COL_DPT_NAISSANCE] ?? '')),
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_ADRESSE] ?? ''), 'UTF-8'),
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_COMPLEMENT_ADR] ?? ''), 'UTF-8'),
                trim((string) ($mappedRow[DriDictionary::COL_CODE_POSTAL] ?? '')),
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_VILLE] ?? ''), 'UTF-8'),
                mb_strtoupper(trim($mappedRow[DriDictionary::COL_PAYS] ?? ''), 'UTF-8'),
                $this->telephone(
                    $mappedRow[DriDictionary::COL_INDICATIF] ?? '',
                    $mappedRow[DriDictionary::COL_TELEPHONE] ?? ''
                )
            );
    }

    /**
     * Nom de la personne à joindre en cas d'urgence.
     *
     * L'export de la DRI porte deux colonnes : le nom de la personne et la
     * nature du lien. Seul le nom est attendu par PEGASUS.
     */
    private function contactUrgence(array $mappedRow): string
    {
        $nom = trim($mappedRow[DriDictionary::COL_URGENCE_NOM] ?? '');

        return $nom !== ''
            ? mb_strtoupper($nom, 'UTF-8')
            : mb_strtoupper(trim($mappedRow[DriDictionary::COL_URGENCE_CONTACT] ?? ''), 'UTF-8');
    }

    /**
     * Recompose un numéro à partir de l'indicatif et du numéro national.
     *
     * Le canevas de référence porte des numéros au format international
     * accolé, par exemple 0033763726678.
     */
    private function telephone(mixed $indicatif, mixed $numero): string
    {
        $numero = preg_replace('/\D/', '', (string) $numero) ?? '';

        if ($numero === '') {
            return '';
        }

        $indicatif = preg_replace('/\D/', '', (string) $indicatif) ?? '';

        if ($indicatif === '') {
            return $numero;
        }

        return '00' . $indicatif . ltrim($numero, '0');
    }

    /**
     * Aplatit les caractères non latins vers l'ASCII.
     *
     * PEGASUS gère mal les caractères spéciaux internationaux : l'administration
     * exige un nom et un prénom translittérés pour cette population.
     *
     * La table de correspondance manuelle qui figurait ici comptait 64
     * caractères source pour 63 remplacements : tout ce qui suivait 'ð' était
     * décalé d'un cran, si bien que MÜLLER devenait MYLLER et MUÑOZ devenait
     * MUSOZ. Transliterator couvre en outre le cyrillique, explicitement visé
     * par le besoin, ce qu'iconv ne fait pas.
     */
    private function removeAccents(string $chaine): string
    {
        static $transliterator = null;

        if ($transliterator === null && class_exists(\Transliterator::class)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        }

        if ($transliterator !== null) {
            $translitere = $transliterator->transliterate($chaine);
            if ($translitere !== false) {
                return $translitere;
            }
        }

        // Repli si l'extension intl est absente du serveur.
        $translitere = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chaine);

        return $translitere !== false ? $translitere : $chaine;
    }
}
