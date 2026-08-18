<?php

namespace App\Constant;

/**
 * Mapping des colonnes du fichier Excel "NEMS" (Normalien Étudiant Master Sciences)
 */
class NemsDictionary
{
    // Identité
    public const COL_CONCOURS         = 'Concours';
    public const COL_ETAT             = 'État';
    public const COL_GENRE            = 'Genre';
    public const COL_NOM              = 'Nom';
    public const COL_NOM_USAGE        = 'Nom d\'usage';
    public const COL_PRENOM           = 'Prénom';
    public const COL_PRENOM_USAGE     = 'Prénom d\'usage';

    // Naissance & Nationalité (Attention aux troncatures de l'export Excel)
    public const COL_DATE_NAISSANCE   = 'Date de naissance'; // Remplacer par 'Date de naissa' si vraiment tronqué dans le fichier brut
    public const COL_PAYS_NAISSANCE   = 'Pays de naissance'; // Remplacer par 'Pays de naissa' si tronqué
    public const COL_NATIONALITE      = 'Nationalité';

    // Coordonnées
    public const COL_ADRESSE_POSTALE  = 'Adresse postale'; // Remplacer par 'Adresse posta' si tronqué
    public const COL_COMPLEMENT_ADR   = 'Complément d\'adresse';
    public const COL_CODE_POSTAL      = 'Code postal';
    public const COL_VILLE            = 'Ville';
    public const COL_PAYS             = 'Pays';
    public const COL_TELEPHONE        = 'Téléphone';
    public const COL_EMAIL            = 'Adresse email';

    // Champs spécifiques
    public const COL_CONFIRMATION     = 'Confirmation';
    public const COL_DESISTEMENT      = 'Désistement';

    /**
     * Retourne la liste des champs strictement obligatoires pour la création d'un étudiant NEMS.
     */
    public static function getMandatoryFields(): array
    {
        return [
            self::COL_NOM            => 'Nom',
            self::COL_PRENOM         => 'Prénom',
            self::COL_DATE_NAISSANCE => 'Date de naissance',
            self::COL_NATIONALITE    => 'Nationalité',
            self::COL_EMAIL          => 'Adresse email',
        ];
    }
}
