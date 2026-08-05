<?php

namespace App\Constant;

/**
 * Mapping des colonnes du fichier Excel "Sélection Internationale Lettres"
 */
class SiLettreDictionary
{
    // Noms exacts des colonnes dans le fichier Excel fourni
    public const COL_CIVILITE       = 'Civilité';
    public const COL_NOM            = 'Nom';
    public const COL_PRENOM         = 'Prénom';
    public const COL_EMAIL_PERSO    = 'Email';
    public const COL_TELEPHONE      = 'Téléphone';
    public const COL_DATE_NAISSANCE = 'naissance_date';
    public const COL_VILLE_NAISSANCE = 'naissance_ville';
    public const COL_PAYS_NAISSANCE = 'naissance_pays';
    public const COL_NATIONALITE    = 'nationalite';
    public const COL_VILLE_DOMICILE = 'domicile_ville';
    public const COL_PAYS_DOMICILE  = 'domicile_pays';
    public const COL_PROFIL         = 'Profil';

    /**
     * Retourne la liste des champs strictement obligatoires pour la création d'un étudiant SI.
     * Le format est ['Clé_Excel' => 'Nom lisible pour l'erreur']
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM            => 'Nom de famille',
            self::COL_PRENOM         => 'Prénom',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_NATIONALITE    => 'Nationalité',
            self::COL_EMAIL_PERSO    => 'Email',
        ];
    }
}
