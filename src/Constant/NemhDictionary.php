<?php

namespace App\Constant;

/**
 * Mapping des colonnes du fichier Excel "NEMH"
 */
class NemhDictionary
{
    // Identité
    public const COL_ETAT             = 'État';
    public const COL_GENRE            = 'Genre';
    public const COL_NOM              = 'Nom';
    public const COL_NOM_USAGE        = 'Nom d\'usage';
    public const COL_PRENOM           = 'Prénom';

    // Naissance & Nationalité
    public const COL_DATE_NAISSANCE   = 'Date de naissance';
    public const COL_PAYS_NAISSANCE   = 'Pays de naissance';
    public const COL_NATIONALITE      = 'Nationalité';

    // Coordonnées
    public const COL_ADRESSE_POSTALE  = 'Adresse postale';
    public const COL_COMPLEMENT_ADR   = 'Complément d\'adresse';
    public const COL_CODE_POSTAL      = 'Code postal';
    public const COL_VILLE            = 'Ville';
    public const COL_PAYS             = 'Pays';
    public const COL_TELEPHONE        = 'Téléphone';
    public const COL_EMAIL            = 'Adresse email';

    // Champs spécifiques au dossier (optionnel pour PEGASUS mais utile pour le métier)
    public const COL_CONFIRMATION     = 'CONFIRMATION';
    public const COL_DESISTEMENT      = 'DESISTEMENT';

    /**
     * Retourne la liste des champs strictement obligatoires pour la création d'un étudiant NEMH.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM            => 'Nom de famille',
            self::COL_PRENOM         => 'Prénom',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_NATIONALITE    => 'Nationalité',
            self::COL_EMAIL          => 'Adresse email',
        ];
    }
}
