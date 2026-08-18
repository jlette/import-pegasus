<?php

namespace App\Constant;

/**
 * Dictionnaire centralisant le vocabulaire strict attendu par PEGASUS.
 * Toute modification de nomenclature par PSL/l'ENS doit se faire uniquement ici.
 */
class StudentDictionary
{

    // ==========================================
    // TYPES SPÉCIFIQUES DRI (Échanges)
    // ==========================================
    public const TYPE_URGENCE_PERSONNE  = 'URGENCE PERSONNE';
    public const TYPE_URGENCE_TEL       = 'URGENCE TELEPHONE';
    public const TYPE_PORTABLE          = 'PORTABLE';
    public const TYPE_DPT_RATT_ECHAN    = 'ENS_DPT_RATT_ETU_ECHAN';

    // ==========================================
    // TYPES DE CONNAISSANCES FOP_INS (Formation)
    // ==========================================
    public const TYPE_MODE_PEDAGOGIQUE  = 'ENS_MODE_PEDAGOGIQUE';
    public const TYPE_FINANCEMENT       = 'ENS_FINANCEMENT';
    public const TYPE_BOURSE            = 'ENS_BOURSE_ENS_PSL';
    public const TYPE_SITUATION_CST     = 'ENS_SITUATION_CST';
    public const TYPE_SITUATION_CSB     = 'ENS_SITUATION_CSB';

    // ==========================================
    // VALEURS FIXES COMMUNES A TOUS LES ETUDIANTS ATTENDUES (Vocabulaire métier)
    // ==========================================

    //Inscription administrative
    public const TYPE_OOC_DA = 'da';
    public const TYPE_OOC_CV = 'cv';
    public const SESSION = 1;
    public const RECRUTEMENT = '';

    // Identité.
    // PEGASUS n'accepte que 'M' et 'F' dans le champ Sexe. Une valeur 'H'
    // rencontrée dans un fichier source est une civilité valide en entrée —
    // elle est reconnue comme masculine — mais ne doit jamais être écrite
    // telle quelle dans le canevas.
    public const SEXE_M = 'M';
    public const SEXE_F = 'F';
    public const GENRE_MASCULIN = 'Monsieur';
    public const GENRE_FEMININ = 'Madame';

    // Modes & Financements
    public const VAL_MODE_SCOLARITE     = 'EN SCOLARITE';
    public const VAL_TRAITEMENT         = 'TRAITEMENT';
    public const VAL_BOURSE_ENS         = 'BOURSE ENS';
    public const VAL_NON_CONCERNE       = 'NC.'; // Le fameux "NC." avec son point obligatoire !

    // ==========================================
    // TYPES DE CONNAISSANCES
    // ==========================================
    public const CONNAISSANCE_TYPE_EMAIL_PERSO       = 'EMAIL PERSONNEL';
    public const CONNAISSANCE_TYPE_EMAIL_ECOLE       = 'EMAIL ECOLE';
    public const CONNAISSANCE_TYPE_NUMERO_ET_PSLR    = 'NUMERO_ETU_PSLR';
    public const CONNAISSANCE_TYPE_NO_INDIVIDU       = 'ENS_NO_INDIVIDU';

    // ==========================================
    // FIN DE FICHIER
    // ==========================================
    public const EOL = 'EOL';

    // ==========================================
    // PLATEFORME
    // ==========================================
    public const PLATEFORME_SCEI = 'SCEI';
    public const PLATEFORME_SCEIENS = 'SCEI+ENS';
    public const PLATEFORME_EPONA = 'EPONA';
    public const PLATFORME_DEMATEC = 'DEMATEC';
    public const PLATFORME_DEC = 'DEC';
}
