<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Strategy\AbstractStrategy;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiLettreDictionary;
use App\Filter\AdmissionFilter;
use App\Service\ConcoursService;
use App\Model\Exception\MappingNotFoundException;

/**
 * Stratégie d'import pour la Sélection Internationale (Filière Lettres).
 */
class SiLettreStrategy extends AbstractStrategy
{
    public function __construct(private ConcoursService $concoursService)
    {
        parent::__construct();
    }

    protected function dictionary(): ?string
    {
        return SiLettreDictionary::class;
    }

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $this->validateMandatoryFields($mappedRow, SiLettreDictionary::getMandatoryFields());

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = $this->anneeCampagne;

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
        $connaissances = $this->connaissancesNormalien(
            $mappedRow[SiLettreDictionary::COL_EMAIL_PERSO] ?? '',
            $annee,
            false,
            NormalienDictionary::CODE_CONCOURS_CPGE_SI_LETTRE
        );

        $fopIns = $this->connaissancesFormation(false);

        return $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, NormalienDictionary::STATUT_DENS_ETUDIANT)
            ->setIdentite($mappedRow[SiLettreDictionary::COL_NOM] ?? '', $mappedRow[SiLettreDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances)
            ->buildNormalienStudent(
                $fopIns,
                '',
                mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_NAISSANCE] ?? '')),
                $dateNaissance,
                mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_NAISSANCE] ?? '')),
                $nationalitePrincipale,
                '',
                '',
                '',
                '',
                mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_DOMICILE] ?? '')),
                mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_DOMICILE] ?? '')),
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

    /**
     * Deux variantes du fichier SI-Lettres circulent : l'extraction brute
     * DEMATEC et le fichier retravaillé par le CoST. Les deux sont acceptées
     * (décision MOA H4).
     */
    protected function columnAliases(): array
    {
        return [
            SiLettreDictionary::COL_DATE_NAISSANCE => ['Date de naissance'],
            SiLettreDictionary::COL_PAYS_DOMICILE  => ['Pays du domicile'],
            SiLettreDictionary::COL_NATIONALITE    => ['Nationalité'],
            SiLettreDictionary::COL_VILLE_NAISSANCE => ['Ville de naissance'],
            SiLettreDictionary::COL_PAYS_NAISSANCE  => ['Pays de naissance'],
        ];
    }

    /**
     * Le fichier retravaillé par le CoST porte le rang d'admission et la
     * confirmation de venue ; l'extraction brute ne les contient pas, auquel
     * cas le filtre laisse passer les lignes.
     */
    public function admissionFilter(): AdmissionFilter
    {
        return new AdmissionFilter(
            valeursRetenues: [
                SiLettreDictionary::COL_RANG => ['ADMIS'],
                SiLettreDictionary::COL_CONFIRMATION_VENUE => ['OUI'],
            ],
        );
    }
}
