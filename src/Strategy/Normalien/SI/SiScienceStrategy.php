<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiScienceDictionary;
use App\Filter\AdmissionFilter;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import pour la Sélection Internationale (Filière Sciences).
 */
class SiScienceStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService)
    {
        parent::__construct();
    }

    protected function dictionary(): ?string
    {
        return SiScienceDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, SiScienceDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = $this->anneeCampagne;

        $dateNaissance = $this->parseDate($mappedRow[SiScienceDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[SiScienceDictionary::COL_CIVILITE] ?? '');

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Association dynamique du département depuis les profils anglophones
        $profilBrut = $mappedRow[SiScienceDictionary::COL_PROFIL] ?? '';
        $produitProgramme = $this->determineProduitProgramme($profilBrut);

        $connaissances = $this->connaissancesNormalien(
            $mappedRow[SiScienceDictionary::COL_EMAIL_PERSO] ?? '',
            $annee,
            false,
            NormalienDictionary::CODE_CONCOURS_CPGE_SI_SCIENCE
        );

        $fopIns = $this->connaissancesFormation(false);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, NormalienDictionary::STATUT_DENS_ETUDIANT)
            ->setIdentite($mappedRow[SiScienceDictionary::COL_NOM] ?? '', $mappedRow[SiScienceDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                '',
                '',
                '',
                mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_VILLE_DOMICILE] ?? '')),
                mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_PAYS_DOMICILE] ?? '')),
                ''
            );
    }

    /**
     * Traduction des profils originellement anglophones en codes programmes PEGASUS.
     */
    private function determineProduitProgramme(string $profil): string
    {
        $profilNorm = mb_strtolower(trim($profil), 'UTF-8');

        return match (true) {
            str_contains($profilNorm, 'math') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_DMA,
            str_contains($profilNorm, 'physic') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_PHYS,
            str_contains($profilNorm, 'chemist') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_CHIM,
            str_contains($profilNorm, 'earth') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_GSC,
            str_contains($profilNorm, 'cognitiv') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_DEC,
            str_contains($profilNorm, 'biolog') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_BIO,
            str_contains($profilNorm, 'comput') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_SCIENCE_INFO,
            default => throw new MappingNotFoundException('produit programme pour le profil', $profil)
        };
    }

    /**
     * L'extraction brute et le fichier « Coordonnées » du CoST ne nomment pas
     * les colonnes de la même façon (décision MOA H4).
     */
    protected function columnAliases(): array
    {
        return [
            SiScienceDictionary::COL_TELEPHONE      => ['telephone'],
            SiScienceDictionary::COL_INDICATIF      => ['indicatif'],
            SiScienceDictionary::COL_DATE_NAISSANCE => ['Date de naissance'],
            SiScienceDictionary::COL_NATIONALITE    => ['Nationalité'],
        ];
    }

    /**
     * L'extraction SI-Sciences mêle admis et non-admis : le fichier 2026
     * comporte 29 lignes NON-ADMIS sur 39. La liste complémentaire est en
     * revanche à importer — « pour le SI-S les 10 sont à importer, donc même
     * ceux sur liste complémentaire ».
     */
    public function admissionFilter(): AdmissionFilter
    {
        return new AdmissionFilter(
            valeursRetenues: [SiScienceDictionary::COL_ETAT_ADMISSION => ['ADMIS, LP', 'ADMIS,LC']],
            valeursExclues: [SiScienceDictionary::COL_ETAT_ADMISSION => ['NON-ADMIS']],
        );
    }
}
