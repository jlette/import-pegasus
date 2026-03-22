<?php

namespace App\Constant;

class NormalienDictionary
{
    // ==========================================
    // VALEURS FIXES
    // ==========================================

    //Inscription
    public const PRODUIT_PROGRAMME          = 'ANDENS1';
    public const STATUT_DENS_ETUDIANT       = 'ENS-DENS ETUDIANT';
    public const STATUT_DENS_FONCTIONNAIRE  = 'ENS-DENS FCTIONNAIRE';

    // Booléens textes
    public const OUI    = 'OUI';
    public const NON    = 'NON';

    // Modes & Financements
    public const MODE_SCOLARITE         = 'EN SCOLARITE';
    public const FINANCEMENT_TRAITEMENT = 'TRAITEMENT';
    public const FINANCEMENT_BOURSE_ENS = 'BOURSE ENS';
    public const FINANCEMENT_NC         = 'NC.'; // Le fameux "NC." avec son point obligatoire !

    // ==========================================
    // Code concours normalien
    // ==========================================

    //CPGE Sciences (Service de Concours Ecoles d'Ingénieurs)
    public const CODE_CONCOURS_CPGE_SCIENCE_BCPST   = 'C-BCPST';
    public const CODE_CONCOURS_CPGE_SCIENCE_MP      = 'C-MP';
    public const CODE_CONCOURS_CPGE_SCIENCE_MPI     = 'C-MPI';
    public const CODE_CONCOURS_CPGE_SCIENCE_PC      = 'C-PC';
    public const CODE_CONCOURS_CPGE_PSI             = 'C-PSI';

    //CPGE A/L (Concours Lettres)
    public const CODE_CONCOURS_CPGE_AL = 'C-AL';

    //CPGE B/L (Concours lettres sciences sociales)
    public const CODE_CONCOURS_CPGE_BL = 'C-BL';

    //SI (Concours sélection international)
    public const CODE_CONCOURS_CPGE_SI_LETTRE = 'SI-L';
    public const CODE_CONCOURS_CPGE_SI_SCIENCE = 'SI-S';

    //NE (Concours normalien etudiant)

    // ==========================================
    // Code produit programme pour la nouvelle promo
    // ==========================================

    //CPGE (Classe préparatoire au grande école)
    public const CODE_PRODUIT_PROGRAMME_CPGE = 'ANDENS1';

    //LETTRE
    public const CODE_PRODUIT_PROGRAMME_LETTRE_ARTS = 'ANDART1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_ECO  = 'ANDECO1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_GEOG = 'ANDGEO1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_HIST = 'ANDHIS1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_LILA = 'ANDLIT1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_PHIL = 'ANDPHI1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_DSA  = 'ANDDSA1';
    public const CODE_PRODUIT_PROGRAMME_LETTRE_DSS  = 'ANDDSS1';

    //SCIENCE
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_BIO  = 'ANDBIO1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_CHIM = 'ANDCHI1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_GSC  = 'ANDGSC1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_INFO = 'ANDINF1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_DMA  = 'ANDDMA1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_PHYS = 'ANDPHY1';
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_DEC  = 'ANDDEC1';

    // ==========================================
    // CONNAISSANCES TYPE SPÉCIFIQUES AUX NORMALIENS
    // ==========================================
    public const CONNAISSANCE_TYPE_PROMO             = 'PROMO';
    public const CONNAISSANCE_TYPE_FONCTIONNAIRE     = 'ENS_FONCTIONNAIRE';
    public const CONNAISSANCE_TYPE_CONCOURS          = 'ENS_CONCOURS';
    public const CONNAISSANCE_TYPE_NOM_ETAT_CIVIL    = 'NOM_ETAT_CIVIL';
    public const CONNAISSANCE_TYPE_PRENOM_ETAT_CIVIL = 'PRENOM_ETAT_CIVIL';
    public const CONNAISSANCE_TYPE_NUMERO_INE        = 'NUMERO_INE';

    // ==========================================
    // CONNAISSANCES FOP INS TYPE SPÉCIFIQUES AUX NORMALIENS
    // ==========================================
    public const FOP_INS_TYPE_SITUATION_CST = 'ENS_SITUATION_CST';
    public const FOP_INS_TYPE_SITUATION_CSB = 'ENS_SITUATION_CSB';
    public const FOP_INS_TYPE_MODE_PEDAGOGIQUE = 'ENS_MODE_PEDAGOGIQUE';
    public const FOP_INS_TYPE_BOURSE = 'ENS_BOURSE_ENS_PSL';
    public const FOP_INS_TYPE_FINANCEMENT = 'ENS_FINANCEMENT';
}
