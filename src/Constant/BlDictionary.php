<?php

namespace App\Constant;

class BlDictionary
{
    // Mapping exact avec les en-têtes de ton fichier Excel B/L
    public const COL_INE = 'INE';
    public const COL_CIVILITE = 'Civ _lib';
    public const COL_NOM = 'Nom';
    public const COL_PRENOM = 'Prenom';
    public const COL_EMAIL_PERSO = 'Can _mel';
    public const COL_TELEPHONE = 'Can _por'; // On privilégie le portable
    public const COL_DATE_NAISSANCE = 'ddn';
    public const COL_VILLE_NAISSANCE = 'Ville de naissance';
    public const COL_PAYS_NAISSANCE = 'Pays de naissance';
    public const COL_NATIONALITE = 'nationalité';
    public const COL_ADRESSE_1 = 'Can _ad 1';
    public const COL_ADRESSE_2 = 'Can _ad 2';
    public const COL_CODE_POSTAL = 'Can _cod _pos';
    public const COL_VILLE = 'Can _com';
    public const COL_PAYS_ADRESSE = 'Can _pay _adr';

    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM => 'Nom',
            self::COL_PRENOM => 'Prénom',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_EMAIL_PERSO => 'Email personnel',
        ];
    }
}
