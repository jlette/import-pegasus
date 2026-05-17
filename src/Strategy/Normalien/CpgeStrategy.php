<?php

namespace App\Model\Strategy\Normalien;

use App\Model\Builder\StudentBuilder;
use App\Model\Strategy\ImportStrategyInterface;
use App\Model\Student\AbstractStudent;
use DateTime;
use InvalidArgumentException;
use App\Constant\StudentDictionary;
use App\Constant\NormalienDictionary;



class CpgeStrategy implements ImportStrategyInterface
{


    public function createStudent(array $row, StudentBuilder $builder, int $currentLot, int $currentSsl): AbstractStudent
    {
        // 1. DATES (La Date_lot est générée le jour de l'import, elle n'est pas dans le fichier SCEI)
        $dateLot = new DateTime(); // "la date du jour de l'import" selon le PPT

        // Date de naissance au format JJ/MM/AAAA depuis le fichier SCEI
        $dateNaissanceBrute = $row['DATE_NAISSANCE'] ?? ''; // Ex: 01/01/2004 
        $dateNaissance = DateTime::createFromFormat('d/m/Y', $dateNaissanceBrute);
        if (!$dateNaissance) {
            throw new InvalidArgumentException("Erreur : La Date de naissance '$dateNaissanceBrute' est invalide.");
        }

        // 2. LOGIQUE MÉTIER
        $formation = 'ANDENS1'; // Règle: Nouvelle promo CPGE = ANDENS1

        // On vérifie la nationalité depuis la colonne SCEI 'CODE_PAYS_NATIONALITE' ou 'LIBELLE_PAYS' 
        $codeNationalite = strtoupper(trim($row['CODE_PAYS_NATIONALITE'] ?? '001'));
        $estFonctionnaire = in_array($codeNationalite, self::NATIONALITES_UE);

        $statutEtudiant = $estFonctionnaire ? 'ENS-DENS FCTIONNAIRE' : 'ENS-DENS ETUDIANT'; // Statuts du PPT

        $fopIns = [
            'ENS_MODE_PEDAGOGIQUE' => 'EN SCOLARITE',
            'ENS_FINANCEMENT' => $estFonctionnaire ? 'TRAITEMENT' : 'BOURSE ENS',
            'PROMO' => $row['ANNEE_BAC'] ?? date('Y'), // On prend l'année du concours comme promo
            'ENS_FONCTIONNAIRE' => $estFonctionnaire ? 'OUI' : 'NON',
            'ENS_CONCOURS' => 'C-BL' // À dynamiser selon si c'est A/L, B/L, MP etc.
        ];

        // 3. ASSEMBLAGE VIA LE BUILDER
        // On mappe les colonnes SCEI vers le Builder
        $builder
            ->setInfosPegasus(
                $dateLot,
                $currentLot,
                $currentSsl,
                StudentDictionary::TYPE_OOC_DA, // 'da' car c'est une création de dossier
                StudentDictionary::RECRUTEMENT,
                StudentDictionary::SESSION,
                StudentDictionary::EOL,
            ) // 'da' car c'est une création de dossier
            ->setScolarite(
                (int)($row['ANNEE_BAC'] ?? date('Y')), // Année de l'IA 
                $formation,
                1,
                $statutEtudiant
            )
            ->setIdentite(
                $row['NOM'] ?? '', // 
                $row['PRENOM'] ?? '', // 
                $row['CIVILITE'] === 'M' ? 'Monsieur' : 'Madame', // SCEI utilise M/F, PEGASUS veut Monsieur/Madame
                $row['CIVILITE'] === 'M' ? 'M' : 'F' // 
            )
            ->setConnaissance([
                'EMAIL PERSONNEL' => $row['EMAIL'] ?? '', // 
                'NUMERO_INE' => $row['NUMERO_INE'] ?? '' // 
            ]);

        // 4. VERROUILLAGE DU DTO NORMALIEN
        return $builder->buildNormalienStudent(
            $fopIns,
            $row['VILLE_NAISSANCE'] ?? '', // 
            $dateNaissance,
            $row['PAYS_NAISSANCE'] ?? '', // 
            $row['CODE_PAYS_NATIONALITE'] ?? '' // 
        );
    }
}