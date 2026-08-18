<?php

namespace App\Constant;

/**
 * Mapping des colonnes du fichier Excel "Sélection Internationale Sciences"
 */
class SiScienceDictionary
{
    // Noms exacts des colonnes dans le fichier Excel fourni
    public const COL_NUM_CANDIDAT   = 'N° candidat';
    public const COL_CIVILITE       = 'Civilité';
    public const COL_NOM            = 'Nom';
    public const COL_PRENOM         = 'Prénom';
    public const COL_EMAIL_PERSO    = 'Email';
    public const COL_INDICATIF      = 'Indicatif';
    public const COL_TELEPHONE      = 'Téléphone';
    public const COL_DATE_NAISSANCE = 'naissance_date';
    public const COL_VILLE_NAISSANCE = 'naissance_ville';
    public const COL_PAYS_NAISSANCE = 'naissance_pays';
    public const COL_NATIONALITE    = 'nationalite';
    public const COL_UNIVERSITY     = 'university';
    public const COL_VILLE_DOMICILE = 'domicile_ville';
    public const COL_PAYS_DOMICILE  = 'domicile_pays';
    public const COL_ETAT_ADMISSION = 'LP/LC';
    public const COL_PROFIL         = 'Profil';
    public const COL_RANG           = 'Rang';

    /**
     * Retourne la liste des champs strictement obligatoires pour la création d'un étudiant SI Sciences.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM            => 'Nom',
            self::COL_PRENOM         => 'Prénom',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_NATIONALITE    => 'Nationalité',
            self::COL_EMAIL_PERSO    => 'Email',
        ];
    }
}
