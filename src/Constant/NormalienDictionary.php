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
    public const CODE_CONCOURS_NE_MH = 'NEMH';
    public const CODE_CONCOURS_NE_MS = 'NEMS';
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
    public const CODE_PRODUIT_PROGRAMME_SCIENCE_GEO = 'ANDGEO1';

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

    // ==========================================
    // PAYS_UE
    // ==========================================
    public const PAYS_UE = [
        'ALLEMAGNE',
        'ANDORRE',
        'AUTRICHE',
        'BELGIQUE',
        'BULGARIE',
        'CHYPRE',
        'CROATIE',
        'DANEMARK',
        'ESPAGNE',
        'ESTONIE',
        'FINLANDE',
        'FRANCE',
        'GRECE',
        'GRÈCE',
        'HONGRIE',
        'IRLANDE',
        'ISLANDE',
        'ITALIE',
        'LETTONIE',
        'LIECHTENSTEIN',
        'LITUANIE',
        'LUXEMBOURG',
        'MALTE',
        'MONACO',
        'NORVEGE',
        'NORVÈGE',
        'PAYS-BAS',
        'POLOGNE',
        'PORTUGAL',
        'REPUBLIQUE TCHEQUE',
        'RÉPUBLIQUE TCHÈQUE',
        'ROUMANIE',
        'SLOVAQUIE',
        'SLOVENIE',
        'SLOVÉNIE',
        'SUEDE',
        'SUÈDE',
        'SUISSE'
    ];

    public const NATIONALITES_UE = [
        'FRANCAIS',
        'FRANCAISE',
        'FRANÇAIS',
        'FRANÇAISE',
        'ALLEMAND',
        'ALLEMANDE',
        'ANDORRAN',
        'ANDORRANE',
        'AUTRICHIEN',
        'AUTRICHIENNE',
        'BELGE',
        'BULGARE',
        'CHYPRIOTE',
        'CROATE',
        'DANOIS',
        'DANOISE',
        'ESPAGNOL',
        'ESPAGNOLE',
        'ESTONIEN',
        'ESTONIENNE',
        'FINLANDAIS',
        'FINLANDAISE',
        'GREC',
        'GRECQUE',
        'HONGROIS',
        'HONGROISE',
        'IRLANDAIS',
        'IRLANDAISE',
        'ISLANDAIS',
        'ISLANDAISE',
        'ITALIEN',
        'ITALIENNE',
        'LETTON',
        'LETTONE',
        'LIECHTENSTEINOIS',
        'LIECHTENSTEINOISE',
        'LITUANIEN',
        'LITUANIENNE',
        'LUXEMBOURGEOIS',
        'LUXEMBOURGEOISE',
        'MALTAIS',
        'MALTAISE',
        'MONEGASQUE',
        'MONÉGASQUE',
        'NEERLANDAIS',
        'NEERLANDAISE',
        'NÉERLANDAIS',
        'NÉERLANDAISE',
        'HOLLANDAIS',
        'HOLLANDAISE',
        'NORVEGIEN',
        'NORVEGIENNE',
        'NORVÉGIEN',
        'NORVÉGIENNE',
        'POLONAIS',
        'POLONAISE',
        'PORTUGAIS',
        'PORTUGAISE',
        'ROUMAIN',
        'ROUMAINE',
        'SLOVAQUE',
        'SLOVENE',
        'SLOVÈNE',
        'SUEDOIS',
        'SUEDOISE',
        'SUÉDOIS',
        'SUÉDOISE',
        'SUISSE',
        'TCHEQUE',
        'TCHÈQUE'
    ];

    /**
     * NOUVEAU : Dictionnaire global de conversion des Nationalités vers les Pays
     */
    public const MAPPING_NATIONALITE_PAYS = [
        // Union Européenne
        'ALLEMAND' => 'ALLEMAGNE',
        'ALLEMANDE' => 'ALLEMAGNE',
        'ANDORRAN' => 'ANDORRE',
        'ANDORRANE' => 'ANDORRE',
        'AUTRICHIEN' => 'AUTRICHE',
        'AUTRICHIENNE' => 'AUTRICHE',
        'BELGE' => 'BELGIQUE',
        'BULGARE' => 'BULGARIE',
        'CHYPRIOTE' => 'CHYPRE',
        'CROATE' => 'CROATIE',
        'DANOIS' => 'DANEMARK',
        'DANOISE' => 'DANEMARK',
        'ESPAGNOL' => 'ESPAGNE',
        'ESPAGNOLE' => 'ESPAGNE',
        'ESTONIEN' => 'ESTONIE',
        'ESTONIENNE' => 'ESTONIE',
        'FINLANDAIS' => 'FINLANDE',
        'FINLANDAISE' => 'FINLANDE',
        'GREC' => 'GRECE',
        'GRECQUE' => 'GRECE',
        'HONGROIS' => 'HONGRIE',
        'HONGROISE' => 'HONGRIE',
        'IRLANDAIS' => 'IRLANDE',
        'IRLANDAISE' => 'IRLANDE',
        'ISLANDAIS' => 'ISLANDE',
        'ISLANDAISE' => 'ISLANDE',
        'ITALIEN' => 'ITALIE',
        'ITALIENNE' => 'ITALIE',
        'LETTON' => 'LETTONIE',
        'LETTONE' => 'LETTONIE',
        'LIECHTENSTEINOIS' => 'LIECHTENSTEIN',
        'LIECHTENSTEINOISE' => 'LIECHTENSTEIN',
        'LITUANIEN' => 'LITUANIE',
        'LITUANIENNE' => 'LITUANIE',
        'LUXEMBOURGEOIS' => 'LUXEMBOURG',
        'LUXEMBOURGEOISE' => 'LUXEMBOURG',
        'MALTAIS' => 'MALTE',
        'MALTAISE' => 'MALTE',
        'MONEGASQUE' => 'MONACO',
        'MONÉGASQUE' => 'MONACO',
        'NEERLANDAIS' => 'PAYS-BAS',
        'NEERLANDAISE' => 'PAYS-BAS',
        'HOLLANDAIS' => 'PAYS-BAS',
        'HOLLANDAISE' => 'PAYS-BAS',
        'NORVEGIEN' => 'NORVEGE',
        'NORVEGIENNE' => 'NORVEGE',
        'NORVÉGIEN' => 'NORVEGE',
        'NORVÉGIENNE' => 'NORVEGE',
        'POLONAIS' => 'POLOGNE',
        'POLONAISE' => 'POLOGNE',
        'PORTUGAIS' => 'PORTUGAL',
        'PORTUGAISE' => 'PORTUGAL',
        'ROUMAIN' => 'ROUMANIE',
        'ROUMAINE' => 'ROUMANIE',
        'SLOVAQUE' => 'SLOVAQUIE',
        'SLOVENE' => 'SLOVENIE',
        'SLOVÈNE' => 'SLOVENIE',
        'SUEDOIS' => 'SUEDE',
        'SUEDOISE' => 'SUEDE',
        'SUÉDOIS' => 'SUEDE',
        'SUÉDOISE' => 'SUEDE',
        'SUISSE' => 'SUISSE',
        'TCHEQUE' => 'REPUBLIQUE TCHEQUE',
        'TCHÈQUE' => 'REPUBLIQUE TCHEQUE',

        // Hors UE (Les plus fréquents à l'international)
        'CANADIENNE' => 'CANADA',
        'CANADIEN' => 'CANADA',
        'AMÉRICAINE' => 'ETATS-UNIS',
        'AMERICAINE' => 'ETATS-UNIS',
        'AMERICAIN' => 'ETATS-UNIS',
        'BRITANNIQUE' => 'ROYAUME-UNI',
        'ANGLAISE' => 'ROYAUME-UNI',
        'ANGLAIS' => 'ROYAUME-UNI',
        'CHINOISE' => 'CHINE',
        'CHINOIS' => 'CHINE',
        'JAPONAISE' => 'JAPON',
        'JAPONAIS' => 'JAPON',
        'MAROCAINE' => 'MAROC',
        'MAROCAIN' => 'MAROC',
        'TUNISIENNE' => 'TUNISIE',
        'TUNISIEN' => 'TUNISIE',
        'ALGERIENNE' => 'ALGERIE',
        'ALGERIEN' => 'ALGERIE',
        'LIBANAISE' => 'LIBAN',
        'LIBANAIS' => 'LIBAN',
        'RUSSE' => 'RUSSIE',
        'BRESILIENNE' => 'BRESIL',
        'BRESILIEN' => 'BRESIL',
        'BRÉSILIENNE' => 'BRESIL',
        'BRÉSILIEN' => 'BRESIL',
        'SENEGALAISE' => 'SENEGAL',
        'SENEGALAIS' => 'SENEGAL',
        'SÉNÉGALAISE' => 'SENEGAL',
        'SÉNÉGALAIS' => 'SENEGAL',
        'IVOIRIENNE' => 'COTE D\'IVOIRE',
        'IVOIRIEN' => 'COTE D\'IVOIRE',
        'CAMEROUNAISE' => 'CAMEROUN',
        'CAMEROUNAIS' => 'CAMEROUN',
        'ÉGYPTIENNE' => 'EGYPTE',
        'ÉGYPTIEN' => 'EGYPTE'
    ];

    /**
     * Convertit une nationalité (ex: FRANCAISE, POLONAIS) en pays (FRANCE, POLOGNE)
     */
    public static function formatNationaliteToPays(string $nat): string
    {
        $nat = mb_strtoupper(trim($nat));
        if (empty($nat)) {
            return '';
        }

        // 1. Si c'est déjà un pays de l'UE (le fichier comportait déjà le mot POLOGNE ou SUISSE)
        if (in_array($nat, self::PAYS_UE)) {
            return $nat;
        }

        // 2. Gestion stricte de la France et des binationaux franco-quelque-chose
        if ($nat === 'FRANCE' || str_contains($nat, 'FRANC') || str_contains($nat, 'FRANÇ')) {
            return 'FRANCE';
        }

        // 3. Appel du dictionnaire de conversion via la constante
        return self::MAPPING_NATIONALITE_PAYS[$nat] ?? $nat;
    }
}
