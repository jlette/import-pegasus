<?php

namespace App\Constant;

/**
 * Ce dictionnaire centralise toutes les correspondances avec les en-têtes
 * exactes des colonnes fournies dans le fichier d'export du concours A/L.
 */
class AlDictionary
{
    // ==========================================
    // EN-TÊTES EXCEL A/L
    // ==========================================

    // Identité
    public const COL_CODE_CANDIDAT = 'Can_code';
    public const COL_CIVILITE = 'Civ_lib';
    public const COL_NOM = 'Nom';
    public const COL_PRENOM = 'Prenom';
    public const COL_AUTRES_PRENOMS = 'Can_aut_pre';

    // Naissance & Nationalité
    public const COL_VILLE_NAISSANCE = 'Can_com_nai';
    public const COL_DATE_NAISSANCE = 'Can_nai';
    public const COL_DEP_NAISSANCE = 'Dep_code_nai';
    public const COL_PAYS_NAISSANCE = 'Can_pay_nai';
    public const COL_NATIONALITE = 'Can_pay_nat';
    public const COL_NATIONALITE_2 = 'NATIONALITE2';

    // Coordonnées
    public const COL_INE = 'Can_ine';
    public const COL_ADRESSE_1 = 'Can_ad 1';
    public const COL_ADRESSE_2 = 'Can_ad 2';
    public const COL_ADRESSE_3 = 'Can_ad 3';
    public const COL_CODE_POSTAL = 'Can_cod_pos';
    public const COL_VILLE = 'Can_com';
    public const COL_PAYS_ADRESSE = 'Can_pay_adr';
    public const COL_TELEPHONE = 'Can_tel';
    public const COL_PORTABLE = 'Can_por';
    public const COL_EMAIL_PERSO = 'Can_mel';

    // Scolarité & Concours
    public const COL_CLASSE_PREPA = 'Cla_lib';
    public const COL_ETABLISSEMENT_CODE = 'Eta_cod';
    public const COL_ETABLISSEMENT_LIBELLE = 'Eta_lib';
    public const COL_ANNEE_BAC = 'Can_ann_bac';
    public const COL_CONCOURS_CODE = 'Con_cod';
    public const COL_CONCOURS_LIBELLE = 'Con_lib';

    /**
     * Retourne la liste des champs obligatoires avec leur libellé humain
     * pour un affichage propre des erreurs de validation.
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
