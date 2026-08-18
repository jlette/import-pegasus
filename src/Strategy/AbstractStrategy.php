<?php

namespace App\Strategy;

use App\Interface\ImportStrategyInterface;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;
use App\Model\Exception\UndeterminedSexException;
use App\Constant\StudentDictionary;
use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Socle commun pour toutes les stratégies d'import (Pattern Template Method).
 * 
 * Cette classe abstraite centralise les opérations de nettoyage et de validation 
 * communes à tous les flux (dates, genre, vérification des colonnes). 
 * Elle permet aux classes enfants de se concentrer exclusivement sur les règles métiers.
 */
abstract class AbstractStrategy implements ImportStrategyInterface
{
    /**
     * Vérifie l'intégrité de la ligne Excel lue.
     * 
     * Garantit que le fichier uploadé contient bien les bonnes colonnes (évite les imports croisés)
     * et qu'aucune donnée critique exigée par PEGASUS n'est vide.
     *
     * @param array $mappedRow La ligne Excel transformée en tableau associatif
     * @param array $mandatoryFields Dictionnaire des champs requis ['Clé_Excel' => 'Nom Lisible']
     * 
     * @throws WrongFileFormatException Si une colonne attendue est introuvable
     * @throws MissingMandatoryFieldException Si une cellule obligatoire est vide
     */
    protected function validateMandatoryFields(array $mappedRow, array $mandatoryFields): void
    {
        foreach ($mandatoryFields as $cleExcel => $nomLisible) {
            if (!array_key_exists($cleExcel, $mappedRow)) {
                throw new WrongFileFormatException($cleExcel);
            }
            if (empty(trim((string)($mappedRow[$cleExcel] ?? '')))) {
                throw new MissingMandatoryFieldException($nomLisible);
            }
        }
    }

    /**
     * Homogénéise le format des dates extraites d'Excel.
     * 
     * Gère la dualité d'Excel qui peut renvoyer soit un numéro de série (ex: 45290), 
     * soit une chaîne de texte selon le formatage de la cellule source.
     *
     * @param mixed $dateBrute La valeur brute lue dans la cellule
     * @param string $nomChamp Nom du champ pour contextualiser l'erreur éventuelle
     * 
     * @return DateTime Objet date sécurisé pour le Builder
     * @throws InvalidDataFormatException Si la date est irrécupérable
     */
    protected function parseDate(mixed $dateBrute, string $nomChamp = 'Date de naissance'): DateTime
    {
        if (is_numeric($dateBrute)) {
            $date = Date::excelToDateTimeObject($dateBrute);
            if ($date) return $date;
        } else {
            $date = DateTime::createFromFormat('d/m/Y', (string) $dateBrute);
            if (!$date) {
                $date = DateTime::createFromFormat('Y-m-d', (string) $dateBrute);
            }
            if ($date) return $date;
        }

        throw new InvalidDataFormatException($nomChamp, (string) $dateBrute);
    }

    /**
     * Normalise les civilités hétérogènes des différents concours vers la
     * nomenclature PEGASUS.
     *
     * RG-02 : le genre déclaré et le sexe à l'état civil sont deux données
     * distinctes. Lorsque la civilité ne permet pas de conclure — valeur
     * « Autre » des dossiers OnePSL30, cellule vide, saisie libre — l'outil ne
     * choisit pas : il lève une exception. Le scan se poursuit néanmoins jusqu'à
     * la fin du fichier (RG-03), afin que le gestionnaire obtienne la liste
     * complète des lignes à corriger en un seul passage.
     *
     * @param string $genreBrut La civilité brute (ex: "M.", "Mme", "Femme")
     *
     * @return array{0: string, 1: string} Tuple [Sexe_PEGASUS, Genre_PEGASUS]
     * @throws UndeterminedSexException Si la civilité ne permet pas de conclure
     */
    protected function parseGenderAndSex(string $genreBrut): array
    {
        $normalise = mb_strtoupper(trim($genreBrut), 'UTF-8');
        $normalise = rtrim($normalise, '.');

        if (in_array($normalise, self::CIVILITES_FEMININES, true)) {
            return [StudentDictionary::SEXE_F, StudentDictionary::GENRE_FEMININ];
        }

        if (in_array($normalise, self::CIVILITES_MASCULINES, true)) {
            return [StudentDictionary::SEXE_H, StudentDictionary::GENRE_MASCULIN];
        }

        throw new UndeterminedSexException(trim($genreBrut));
    }

    /**
     * Civilités féminines rencontrées dans les fichiers sources.
     * 'MM' est la forme abrégée de « Mme » utilisée par DEMATEC.
     */
    private const CIVILITES_FEMININES = [
        'F', 'MME', 'MM', 'MADAME', 'MRS', 'MS', 'MISS', 'FEMME', 'FEMININ', 'FÉMININ',
    ];

    /**
     * Civilités masculines rencontrées dans les fichiers sources.
     */
    private const CIVILITES_MASCULINES = [
        'M', 'H', 'MR', 'MONSIEUR', 'HOMME', 'MASCULIN',
    ];
}
