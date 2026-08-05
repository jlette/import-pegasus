<?php

namespace App\Constant;

/**
 * Mapping des colonnes du fichier Excel "DRI - PE et Erasmus"
 * STRICTEMENT EN MAJUSCULES pour correspondre au fichier d'entrée.
 * (Cela n'affectera pas les colonnes du CSV de sortie qui sont gérées par le Builder).
 */
class DriDictionary
{
    // Identité (En-têtes exacts du fichier Excel d'entrée)
    public const COL_PROGRAMME        = 'DIPLOME_ECHANGE';
    public const COL_NOM              = 'NOM';
    public const COL_PRENOM           = 'PRENOM';
    public const COL_DATE_NAISSANCE   = 'DATE_NAISSANCE';
    public const COL_PAYS_NAISSANCE   = 'PAYS_NAISSANCE';
    public const COL_NATIONALITE      = 'NATIONALITE';
    public const COL_EMAIL            = 'COURRIEL';
    public const COL_GENRE            = 'SEXE';

    // Coordonnées (En-têtes exacts du fichier Excel d'entrée)
    public const COL_ADRESSE          = 'ADRESSE';
    public const COL_CODE_POSTAL      = 'CODE POSTAL'; // Attention, il y a un espace ici dans ton Excel
    public const COL_VILLE            = 'VILLE';
    public const COL_PAYS             = 'PAYS';

    public const CODE_CONCOURS_DRI    = 'C-DRI'; // Le code PEGASUS du concours DRI

    /**
     * Retourne la liste des champs strictement obligatoires.
     * La clé = le nom dans l'Excel. La valeur = le nom affiché dans l'erreur à l'écran.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM            => 'Nom',
            self::COL_PRENOM         => 'Prénom',
            self::COL_EMAIL          => 'Courriel',
            self::COL_PROGRAMME      => 'Diplôme d\'échange (Erasmus ou PE)'
        ];
    }
}
