<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiLettreDictionary;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import pour la Sélection Internationale (Filière Lettres).
 */
class SiLettreStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, SiLettreDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        $dateNaissance = $this->parseDate($mappedRow[SiLettreDictionary::COL_DATE_NAISSANCE] ?? '');
        [$sexe, $genre] = $this->parseGenderAndSex($mappedRow[SiLettreDictionary::COL_CIVILITE] ?? '');

        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // Règle Métier : Association dynamique du département.
        // Les étudiants internationaux n'ont pas de code scolarité formel dans leur fichier source.
        // On doit analyser la colonne de texte libre "Profil" pour déduire le Produit Programme PEGASUS.
        $profilBrut = $mappedRow[SiLettreDictionary::COL_PROFIL] ?? '';
        $produitProgramme = $this->determineProduitProgramme($profilBrut);

        // Règle Métier : Les "SI" ne sont jamais fonctionnaires, ils bénéficient tous d'une Bourse ENS.
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[SiLettreDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => NormalienDictionary::NON,
            'ENS_CONCOURS'      => NormalienDictionary::CODE_CONCOURS_CPGE_SI_LETTRE,
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
            ->setIdentite($mappedRow[SiLettreDictionary::COL_NOM] ?? '', $mappedRow[SiLettreDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                '',
                '',
                '',
                strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_DOMICILE] ?? '')),
                strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_DOMICILE] ?? '')),
                ''
            );
    }

    /**
     * Dédit le code produit programme PEGASUS à partir de la chaîne de caractères "Profil".
     */
    private function determineProduitProgramme(string $profil): string
    {
        $profilNorm = mb_strtolower(trim($profil), 'UTF-8');

        return match (true) {
            str_contains($profilNorm, 'economie') || str_contains($profilNorm, 'économie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_ECO,
            // "Histoire de l'art" doit être vérifié AVANT "Histoire" pour ne pas créer de faux positifs
            str_contains($profilNorm, 'art') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_ARTS,
            str_contains($profilNorm, 'histoire') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_HIST,
            str_contains($profilNorm, 'littérature') || str_contains($profilNorm, 'litterature') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_LILA,
            str_contains($profilNorm, 'philosophie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_PHIL,
            str_contains($profilNorm, 'sociologie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_DSS,
            str_contains($profilNorm, 'géo') || str_contains($profilNorm, 'geo') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_GEOG,
            str_contains($profilNorm, 'antiquit') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_DSA,
            default => throw new MappingNotFoundException('produit programme pour le profil', $profil)
        };
    }
}
