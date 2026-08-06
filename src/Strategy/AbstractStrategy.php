<?php

namespace App\Strategy;

use App\Interface\ImportStrategyInterface;
use App\Model\Exception\MissingMandatoryFieldException;
use App\Model\Exception\InvalidDataFormatException;
use App\Model\Exception\WrongFileFormatException;
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
     * Normalise les civilités hétérogènes des différents concours vers la nomenclature PEGASUS.
     * 
     * @param string $genreBrut La civilité brute (ex: "F.", "Mm", "M", etc.)
     * @return array Retourne un tuple structuré : [Sexe_PEGASUS, Genre_PEGASUS]
     */
    protected function parseGenderAndSex(string $genreBrut): array
    {
        $genreBrut = mb_strtoupper(trim($genreBrut));

        // Regroupe toutes les variations possibles identifiées dans les fichiers sources
        if (str_starts_with($genreBrut, 'F') || $genreBrut === 'MM' || $genreBrut === 'MADAME' || $genreBrut === 'MRS' || $genreBrut === 'MS' || $genreBrut === 'F.' || $genreBrut === 'FEMME' || $genreBrut === 'FEMININ' || $genreBrut === 'F.' || $genreBrut === 'MME') {
            return [StudentDictionary::SEXE_F, StudentDictionary::GENRE_FEMININ];
        }

        return [StudentDictionary::SEXE_M, StudentDictionary::GENRE_MASCULIN];
    }
}
