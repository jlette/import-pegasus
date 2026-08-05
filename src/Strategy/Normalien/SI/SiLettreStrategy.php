<?php

namespace App\Strategy\Normalien\SI;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\SiLettreDictionary; // Ce dictionnaire sera à créer
use App\Service\ConcoursService;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\MappingNotFoundException;
use App\Model\Exception\WrongFileFormatException;

class SiLettreStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        // 1. Validation des champs obligatoires et conformité du fichier
        foreach (SiLettreDictionary::getMandatoryFields() as $cleExcel => $nomLisible) {
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

        // 2. Traitement de la date de naissance (Gère les formats Texte et Numéro de série Excel)
        $dateNaissanceBrute = $mappedRow[SiLettreDictionary::COL_DATE_NAISSANCE] ?? '';

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

        // 3. Règles Métier "Sélection Internationale"
        // Les SI ne sont pas fonctionnaires (ils ont une Bourse ENS)
        $estFonctionnaire = false;
        $statutEtudiant = NormalienDictionary::STATUT_DENS_ETUDIANT;
        $ouiOunon = NormalienDictionary::NON;

        $sexe = trim($mappedRow[SiLettreDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;
        $genre = trim($mappedRow[SiLettreDictionary::COL_CIVILITE] ?? '') === 'M.' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;

        // 4. Nettoyage de la nationalité
        $nationaliteBrute = mb_strtoupper(trim($mappedRow[SiLettreDictionary::COL_NATIONALITE] ?? ''));
        $nationalitePrincipale = NormalienDictionary::formatNationaliteToPays($nationaliteBrute);

        // 5. Récupération du Code Concours et Produit Programme
        // On force le code concours "C-SIL" (Sélection Internationale Lettres)
        $codeConcours = NormalienDictionary::CODE_CONCOURS_CPGE_SI_LETTRE;

        // NOUVEAU : On déduit le programme via le profil
        $profilBrut = $mappedRow[SiLettreDictionary::COL_PROFIL] ?? '';
        $produitProgramme = $this->determineProduitProgramme($profilBrut);

        // 6. Matrices dynamiques
        $connaissances = [
            'EMAIL PERSONNEL'   => $mappedRow[SiLettreDictionary::COL_EMAIL_PERSO] ?? '',
            'EMAIL ECOLE'       => '',
            'NUMERO_ETU_PSLR'   => '',
            'ENS_NO_INDIVIDU'   => '',
            'PROMO'             => $annee,
            'ENS_FONCTIONNAIRE' => $ouiOunon,
            'ENS_CONCOURS'      => $codeConcours,
            'NOM_ETAT_CIVIL'    => '',
            'PRENOM_ETAT_CIVIL' => '',
            'NUMERO_INE'        => '', // Vide pour les internationaux
        ];

        $fopIns = [
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CST      => '',
            NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB      => '',
            NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE   => NormalienDictionary::MODE_SCOLARITE,
            NormalienDictionary::FOP_INS_TYPE_BOURSE             => NormalienDictionary::OUI,
            NormalienDictionary::FOP_INS_TYPE_FINANCEMENT        => NormalienDictionary::FINANCEMENT_BOURSE_ENS,
        ];

        // 7. Assemblage
        $builder
            ->setInfosPegasus($dateActuelle, $currentLot, $currentSsl, StudentDictionary::TYPE_OOC_DA, StudentDictionary::RECRUTEMENT, StudentDictionary::SESSION, StudentDictionary::EOL)
            ->setScolarite($annee, $produitProgramme, $annee, $statutEtudiant)
            ->setIdentite($mappedRow[SiLettreDictionary::COL_NOM] ?? '', $mappedRow[SiLettreDictionary::COL_PRENOM] ?? '', $genre, $sexe)
            ->setConnaissance($connaissances);

        // 8. Verrouillage (On retire les champs d'adresses inexistants du fichier)
        return $builder->buildNormalienStudent(
            $fopIns,
            '', // Situation familiale
            strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_NAISSANCE] ?? '')),
            $dateNaissance,
            strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_NAISSANCE] ?? '')),
            $nationalitePrincipale,
            '', // Code INSEE 
            '', // Voie 1 non fournie dans ce fichier Excel
            '', // Voie 2
            '', // Code postal non fourni
            strtoupper(trim($mappedRow[SiLettreDictionary::COL_VILLE_DOMICILE] ?? '')), // On utilise la ville de domicile
            strtoupper(trim($mappedRow[SiLettreDictionary::COL_PAYS_DOMICILE] ?? '')),
            trim($mappedRow[SiLettreDictionary::COL_TELEPHONE] ?? '')
        );
    }

    /**
     * STREAMING_CHUNK:Méthode utilitaire pour associer un profil Excel à un code PEGASUS
     * Détermine le code produit programme PEGASUS à partir de la colonne "Profil"
     */
    private function determineProduitProgramme(string $profil): string
    {
        $profilNorm = mb_strtolower(trim($profil), 'UTF-8');

        return match (true) {
            str_contains($profilNorm, 'economie') || str_contains($profilNorm, 'économie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_ECO,

            // ATTENTION : "Histoire de l'art" doit être vérifié AVANT "Histoire" 
            // sinon le str_contains('histoire') attraperait les deux !
            str_contains($profilNorm, 'art') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_ARTS,
            str_contains($profilNorm, 'histoire') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_HIST,

            str_contains($profilNorm, 'littérature') || str_contains($profilNorm, 'litterature') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_LILA,
            str_contains($profilNorm, 'philosophie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_PHIL,
            str_contains($profilNorm, 'sociologie') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_DSS,

            // Codes conservés au cas où le fichier évolue
            str_contains($profilNorm, 'géo') || str_contains($profilNorm, 'geo') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_GEOG,
            str_contains($profilNorm, 'antiquit') => NormalienDictionary::CODE_PRODUIT_PROGRAMME_LETTRE_DSA,

            default => throw new MappingNotFoundException('produit programme pour le profil', $profil)
        };
    }
}
