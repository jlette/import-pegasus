<?php

namespace App\Constant;

/**
 * Correspondance des colonnes du fichier d'échanges internationaux de la DRI.
 *
 * La source est l'export MoveOn, reformaté par la DRI avant transmission. Les
 * en-têtes de ce fichier reformaté sont en majuscules et séparés par des
 * soulignés ; les alias déclarés par la stratégie couvrent les variantes.
 */
class DriDictionary
{
    // Identité
    public const COL_PROGRAMME        = 'DIPLOME_ECHANGE';
    public const COL_NOM              = 'NOM';
    public const COL_PRENOM           = 'PRENOM';
    public const COL_GENRE            = 'SEXE';
    public const COL_DATE_NAISSANCE   = 'DATE_NAISSANCE';
    public const COL_VILLE_NAISSANCE  = 'VILLE_NAISSANCE';
    public const COL_DPT_NAISSANCE    = 'DPT_NAISSANCE';
    public const COL_PAYS_NAISSANCE   = 'PAYS_NAISSANCE';
    public const COL_NATIONALITE      = 'NATIONALITE';
    public const COL_EMAIL            = 'COURRIEL';

    // Coordonnées personnelles — obligatoires pour cette population
    public const COL_ADRESSE          = 'ADRESSE';
    public const COL_COMPLEMENT_ADR   = 'COMPLEMENT_ADRESSE';
    public const COL_CODE_POSTAL      = 'CODE_POSTAL';
    public const COL_VILLE            = 'VILLE';
    public const COL_PAYS             = 'PAYS';
    public const COL_INDICATIF        = 'INDICATIF';
    public const COL_TELEPHONE        = 'TELEPHONE';

    // Contact d'urgence et rattachement — spécifiques aux échanges
    public const COL_DPT_ENS          = 'DPT_ENS';
    public const COL_URGENCE_NOM      = 'URGENCE_NOM';
    public const COL_URGENCE_CONTACT  = 'URGENCE_CONTACT';
    public const COL_URGENCE_INDICATIF = 'URGENCE_INDICATIF';
    public const COL_URGENCE_TELEPHONE = 'URGENCE_TELEPHONE';

    // Valeurs PEGASUS propres aux échanges internationaux
    public const PRODUIT_PROGRAMME    = 'ANECHINTER';
    public const STATUT_ERASMUS       = 'ENS-DRI ECH ERASMUS';
    public const STATUT_PENSIONNAIRE  = 'ENS-DRI PENS ETRG';

    /**
     * Champs sans lesquels le dossier ne peut pas être créé.
     *
     * L'adresse électronique personnelle est critique : c'est elle qui permet
     * la première authentification de l'étudiant sur le portail.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM       => 'Nom',
            self::COL_PRENOM    => 'Prénom',
            self::COL_EMAIL     => 'Courriel',
            self::COL_PROGRAMME => 'Diplôme d\'échange (Erasmus ou PE)',
            self::COL_DPT_ENS   => 'Département de rattachement à l\'ENS',
        ];
    }
}
