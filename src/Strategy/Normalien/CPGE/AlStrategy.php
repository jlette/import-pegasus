<?php

namespace App\Strategy\Normalien\CPGE;

use App\Builder\StudentBuilder;
use App\Interface\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;
use App\Constant\AlDictionary; // Importation du dictionnaire AL
use App\Service\ConcoursService;

use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\MappingNotFoundException;

class AlStrategy implements ImportStrategyInterface
{
    public function __construct(private ConcoursService $concoursService) {}

    public function createStudent(array $mappedRow, int $currentLot, int $currentSsl): AbstractStudent
    {
        $builder = new StudentBuilder();
        $dateActuelle = new DateTime();
        $annee = (int) $dateActuelle->format('Y');

        // TODO : 1. Définir les champs obligatoires spécifiques au fichier A/L
        // Date de naissance au format JJ/MM/AAAA depuis le fichier SCEI
        $dateNaissanceBrute = $mappedRow['Can_nai'] ?? ''; // Ex: 01/01/2004 
        $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        if (!$dateNaissance) {
            throw new InvalidDataFormatException('Date de naissance', $dateNaissanceBrute);
        }

        // On récupère la phrase entière, on nettoie les espaces et on met TOUT en majuscules
        // Ex: "ENS Paris Concours  MP Non Fonctionnaire" devient "ENS PARIS CONCOURS  MP NON FONCTIONNAIRE"
        $phraseConcours = strtoupper(trim($mappedRow['Con_lib'] ?? ''));
        // TODO : 2. Extraire et valider les données (Date de naissance, etc.)

        // TODO : 3. Déterminer le statut Fonctionnaire / Étudiant
        $estFonctionnaire = in_array(strtoupper(trim($mappedRow['Can_pay_nat'] ?? '')), AlDictionary::ARRAY_NATIONALITE);
        $statutEtudiant = $estFonctionnaire ? NormalienDictionary::STATUT_DENS_FONCTIONNAIRE : NormalienDictionary::STATUT_DENS_ETUDIANT;
        $sexe = $mappedRow['Civ_lib'] === 'M.' ? StudentDictionary::SEXE_M : StudentDictionary::SEXE_F;
        $genre = $mappedRow['Civ_lib'] === 'M.' ? StudentDictionary::GENRE_MASCULIN : StudentDictionary::GENRE_FEMININ;
        $ouiOunon = $estFonctionnaire ?  NormalienDictionary::OUI : NormalienDictionary::NON;


        // TODO : 4. Trouver le bon code concours PEGASUS
        $codes = $this->concoursService->findByPlatforme(StudentDictionary::PLATEFORME_EPONA);

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
            'EMAIL PERSONNEL' => $mappedRow['Can_mel'] ?? '', // Obligatoire, sert à la première authentification
            'EMAIL ECOLE' => '', // Vide  Sera renseigné par synchro ENS
            'NUMERO_ETU_PSLR' => '', // Vide  Sera renseigné lors création portail
            'ENS_NO_INDIVIDU' => '', //Vide si nouvel étudiant ou Sera renseigné par synchro ENS
            'PROMO' => $annee, // Format AAAA
            'ENS_FONCTIONNAIRE' => $ouiOunon, // En majuscules pour correspondre au vocabulaire métier de PEGASUS
            'ENS_CONCOURS' => $codeConcours, // À dynamiser selon si c'est A/L, B/L, MP etc.
            'NOM_ETAT_CIVIL' =>  '',
            'PRENOM_ETAT_CIVIL' =>  '',
            'NUMERO_INE' => $mappedRow['Can_ine'] ?? '', // Obligatoire pour les étudiants sert à la première authentification
        ];
        $fopIns = [
            'ENS_FINANCEMENT' => $estFonctionnaire ? 'TRAITEMENT' : 'BOURSE ENS',
        ];

        $situationFamiliale = '';
        $villeDeNaissance = strtoupper(trim($mappedRow['Can_com_nai'] ?? ''));
        // Utilisation de l'objet DateTime déjà validé plus haut pour éviter un double parsing
        $dateDeNaissance = $dateNaissance;
        $paysDeNaissance = strtoupper(trim($mappedRow['Can_pay_nai'] ?? ''));
        $nationalitePrincipal = strtoupper(trim($mappedRow['Can_pay_nat'] ?? ''));
        $codeInsee = '';
        $courrierVoie1 = $mappedRow['Can_ad 1'] ?? '';
        $courrierVoie2 = $mappedRow['Can_ad 2'] ?? '';
        $courrierCodePostal = trim($mappedRow['Can_cod_pos'] ?? '');
        $courrierVille = strtoupper(trim($mappedRow['Can_com'] ?? ''));
        $courrierPays = strtoupper(trim($mappedRow['Can_pay'] ?? ''));
        $courrierTelephone = trim($mappedRow['Can_por'] ?? '');
        // TODO : 5. Construire l'étudiant via le Builder

        $builder
            ->setInfosPegasus(
                $dateActuelle,
                $currentLot,
                $currentSsl, // no_ssl EST POUR L'INSTANT TOUJOURS A ZERO 
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
