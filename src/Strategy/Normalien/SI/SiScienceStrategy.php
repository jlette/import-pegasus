<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiScienceDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import pour la Sélection Internationale (Filière Sciences).
 */
class SiScienceStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, SiScienceDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[SiScienceDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[SiScienceDictionary::COL_CIVILITE] ?? '');

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SiScienceDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Association dynamique du département depuis les profils anglophones
        $profilBrut = $mappedRow[SiScienceDictionary::COL_PROFIL] ?? '';
        $produitProgramme = $this->determineProduitProgramme($profilBrut);

        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[SiScienceDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => NormalienDictionary::CODE_CONCOURS_CPGE_SI_SCIENCE,
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

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, NormalienDictionary::STATUT_DENS_ETUDIANT)
            ->setIdentite($mappedRow[SiScienceDictionary::COL_NOM] ?? '', $mappedRow[SiScienceDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
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
}
