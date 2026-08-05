<?php

namespace App\Constant;

/**
 * Ce dictionnaire liste toutes les correspondances avec les colonnes 
 * exactes fournies dans le fichier d'export du concours SCEI.
 */
class SceiDictionary
{
    // ==========================================
    // EN-TÊTES EXCEL SCEI
    // ==========================================

    public const COL_NOM = 'Nom';
    public const COL_PRENOM = 'Prenom';
    public const COL_CIVILITE = 'Civ _lib';
    public const COL_DATE_NAISSANCE = 'Can _nai';
    public const COL_CONCOURS_LIBELLE = 'Con _lib';
    public const COL_EMAIL_PERSO = 'Can _mel';
    public const COL_INE = 'Can _ine';

    // Champs annexes
    public const COL_VILLE_NAISSANCE = 'Can _com _nai';
    public const COL_PAYS_NAISSANCE = 'Can _pay _nai';
    public const COL_NATIONALITE = 'Can _pay _nat';
    public const COL_ADRESSE_VOIE_1 = 'Can _ad 1';
    public const COL_ADRESSE_VOIE_2 = 'Can _ad 2';
    public const COL_CODE_POSTAL = 'Can _cod _pos';
    public const COL_VILLE = 'Can _com';
    public const COL_PAYS = 'Can _pay _adr';
    public const COL_TELEPHONE = 'Can _tel _cour';

    /**
     * Retourne la liste des champs obligatoires avec leur libellé humain
     * pour l'affichage des erreurs dans la modale.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM => 'Nom',
            self::COL_PRENOM => 'Prénom',
            self::COL_CIVILITE => 'Civilité',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_CONCOURS_LIBELLE => 'Concours',
            self::COL_EMAIL_PERSO => 'Email personnel',
            self::COL_INE => 'Numéro INE'
        ];
    }
}
