<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Service\ConcoursService;

// Importation de nos nouvelles exceptions avec le BON namespace
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\MappingNotFoundException;

class SceiStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        // 1. VÉRIFICATION DES CHAMPS OBLIGATOIRES AVANT TRAITEMENT
        $champsObligatoires = [
            'Nom' => 'Nom',
            'Prenom' => 'Prénom',
            'Civ _lib' => 'Civilité',
            'Can _nai' => 'Date de naissance',
            'Con _lib' => 'Concours',
            'Can _mel' => 'Email personnel',
            'Can _ine' => 'Numéro INE'
        ];

        foreach ($champsObligatoires as $cleExcel => $nomLisible) {
            if (empty(trim($mappedRow[$cleExcel] ?? ''))) {
                throw new MissingMandatoryFieldException($nomLisible);
            }
        }

        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee =  (int) $dateActuelle->format('Y'); // Par défaut, on prend l'année en cours comme année de l'IA

        // 1. DATES (La Date_lot est générée le jour de l'import, elle n'est pas dans le fichier SCEI)

        // Date de naissance au format JJ/MM/AAAA depuis le fichier SCEI
        $dateNaissanceBrute = $mappedRow['Can _nai'] ?? ''; // Ex: 01/01/2004 
        $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', $dateNaissanceBrute);
        }

        // 1. On récupère la phrase entière, on nettoie les espaces et on met TOUT en majuscules
        // Ex: "ENS Paris Concours  MP Non Fonctionnaire" devient "ENS PARIS CONCOURS  MP NON FONCTIONNAIRE"
        $phraseConcours = strtoupper(trim($mappedRow['Con _lib'] ?? ''));

        // 2. On vérifie si l'étudiant est fonctionnaire.
        // str_contains cherche "NON FONCTIONNAIRE". Le "!" inverse le résultat :
        // S'il trouve le mot -> false (pas fonctionnaire). S'il ne le trouve pas -> true (fonctionnaire).
        $estFonctionnaire = !str_contains($phraseConcours, 'NON FONCTIONNAIRE');

        // 3. On attribue le bon statut issu de ton dictionnaire métier (PPT)
        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;

        $sexe = $mappedRow['Civ _lib'] === 'M.' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;

        $genre = $mappedRow['Civ _lib'] === 'M.' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;

        $ouiOunon = $estFonctionnaire ?  NormalienDictionary::OUI : NormalienDictionary::NON;

        $codes = $this->concoursService->findByPlatforme(StudentDictionary::PLATEFORME_SCEI);

        $codeConcours = null;
        foreach ($codes as $code) {
            if (str_contains($phraseConcours, $code['ANNUAIRE_CONC_CODE'])) {
                $codeConcours = $code['CONC_CODE'];
                break;
            }
        }

        if ($codeConcours === null) {
            throw new MappingNotFoundException('le concours annuaire', $phraseConcours);
        }

        $connaissances = [
            'EMAIL PERSONNEL' => $mappedRow['Can _mel'] ?? '', // Obligatoire, sert à la première authentification
            'EMAIL ECOLE' => '', // Vide  Sera renseigné par synchro ENS
            'NUMERO_ETU_PSLR' => '', // Vide  Sera renseigné lors création portail
            'ENS_NO_INDIVIDU' => '', //Vide si nouvel étudiant ou Sera renseigné par synchro ENS
            'PROMO' => $annee, // Format AAAA
            'ENS_FONCTIONNAIRE' => $ouiOunon, // En majuscules pour correspondre au vocabulaire métier de PEGASUS
            'ENS_CONCOURS' => $codeConcours, // À dynamiser selon si c'est A/L, B/L, MP etc.
            'NOM_ETAT_CIVIL' =>  '',
            'PRENOM_ETAT_CIVIL' =>  '',
            'NUMERO_INE' => $mappedRow['Can _ine'] ?? '', // Obligatoire pour les étudiants SCEI, sert à la première authentification
        ];

        $fopIns = [
            'ENS_FINANCEMENT' => $estFonctionnaire ? 'TRAITEMENT' : 'BOURSE ENS',
        ];
        $situationFamiliale = '';
        $villeDeNaissance = strtoupper(trim($mappedRow['Can _com _nai'] ?? ''));
        $dateDeNaissance = DateTime::createFromFormat('d/m/Y', $mappedRow['Can _nai'] ?? '');
        $paysDeNaissance = strtoupper(trim($mappedRow['Can _pay _nai'] ?? ''));
        $nationalitePrincipal = strtoupper(trim($mappedRow['Can _pay _nat'] ?? ''));
        $codeInsee = '';
        $courrierVoie1 = $mappedRow['Can _ad 1'] ?? '';
        $courrierVoie2 = $mappedRow['Can _ad 2'] ?? '';
        $courrierCodePostal = trim($mappedRow['Can _cod _pos'] ?? '');
        $courrierVille = strtoupper(trim($mappedRow['Can _com'] ?? ''));
        $courrierPays = strtoupper(trim($mappedRow['Can _pay'] ?? ''));
        $courrierTelephone = trim($mappedRow['Can _tel _cour'] ?? '');

        // 3. ASSEMBLAGE VIA LE BUILDER
        // On mappe les colonnes SCEI vers le Builder
        $builder
            ->setInfosPegasus(
                $dateActuelle,
                $currentLot,
                $currentSsl, // no_ssl EST POUR L'INSTANT TOUJOURS A ZERO POUR LES ETUDIANTS SCEI
                StudentDictionary::TYPE_OOC_DA, // 'da' car c'est une création de dossier
                StudentDictionary::RECRUTEMENT,
                StudentDictionary::SESSION,
                StudentDictionary::EOL,
            ) // 'da' car c'est une création de dossier
            ->setScolarite(
                $annee, // Année de l'IA 
                NormalienDictionary::CODE_PRODUIT_PROGRAMME_CPGE,
                $annee, // No Année de scolarité (1ère année de CPGE)
                $statutEtudiant
            )
            ->setIdentite(
                $mappedRow['Nom'] ?? '', // 
                $mappedRow['Prenom'] ?? '', // 
                $genre, // SCEI utilise M/F, PEGASUS veut Monsieur/Madame
                $sexe // 
            )
            ->setConnaissance($connaissances);

        // 4. VERROUILLAGE DU DTO NORMALIEN
        return $builder->buildNormalienStudent(
            $fopIns,
            $situationFamiliale,
            $villeDeNaissance,
            $dateDeNaissance,
            $paysDeNaissance,
            $nationalitePrincipal,
            $codeInsee,
            $courrierCodePostal,
            $courrierVoie1,
            $courrierVoie2,
            $courrierVille,
            $courrierPays,
            $courrierTelephone
        );
    }
}
