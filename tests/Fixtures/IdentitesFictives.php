<?php

namespace Tests\Fixtures;

/**
 * Identités fictives utilisées par les tests de bout en bout.
 *
 * AUCUNE de ces personnes n'existe. Les canevas réels transmis par le CoST
 * contiennent des données personnelles d'admis et ne doivent jamais être
 * versionnés.
 */
final class IdentitesFictives
{
    /**
     * Lignes d'un export SCEI fictif, au format des colonnes du fichier des
     * intégrants.
     *
     * @return list<array<string, string>>
     */
    public static function lignesScei(): array
    {
        return [
            // Fonctionnaire : le libellé du concours ne porte pas la mention
            // « NON FONCTIONNAIRE ».
            [
                'Nom' => 'Müller', 'Prenom' => 'CLARA', 'Civ _lib' => 'Mme',
                'Can _nai' => '14/03/2005', 'Con _lib' => 'ENS Paris Concours BCPST',
                'Can _mel' => 'clara.muller@example.invalid', 'Can _ine' => '000000001AA',
                'Can _com _nai' => 'Strasbourg', 'Can _pay _nai' => 'France',
                'Can _pay _nat' => 'Française',
            ],
            // Non fonctionnaire : mention explicite dans le libellé.
            [
                'Nom' => 'Nakamura', 'Prenom' => 'KENJI', 'Civ _lib' => 'M.',
                'Can _nai' => '02/11/2004', 'Con _lib' => 'ENS Paris Concours MP Non Fonctionnaire',
                'Can _mel' => 'kenji.nakamura@example.invalid', 'Can _ine' => '000000002BB',
                'Can _com _nai' => 'Osaka', 'Can _pay _nai' => 'Japon',
                'Can _pay _nat' => 'Japonaise',
            ],
            // Ressortissante UE : fonctionnarisable.
            [
                'Nom' => 'Kowalczyk', 'Prenom' => 'ŁUCJA', 'Civ _lib' => 'Mme',
                'Can _nai' => '27/07/2005', 'Con _lib' => 'ENS Paris Concours PC',
                'Can _mel' => 'lucja.kowalczyk@example.invalid', 'Can _ine' => '000000003CC',
                'Can _com _nai' => 'Cracovie', 'Can _pay _nai' => 'Pologne',
                'Can _pay _nat' => 'Polonaise',
            ],
        ];
    }

    /**
     * Lignes d'un export OnePSL30 fictif pour le NEMS.
     *
     * @return list<array<string, string>>
     */
    public static function lignesNems(): array
    {
        return [
            // Nom d'usage différent de l'état civil : RG-04 doit retenir l'état civil.
            [
                'Nom' => 'Nguyen', "Nom d'usage" => 'Durand',
                'Prénom' => 'MAI', "Prénom d'usage" => '',
                'Genre' => 'Femme', 'Date de naissance' => '05/09/2006',
                'Pays de naissance' => 'France', 'Nationalité' => 'FRANCE',
                'Adresse email' => 'mai.nguyen@example.invalid',
                'Adresse postale' => '1 rue Fictive', "Complément d'adresse" => '',
                'Code postal' => '75005', 'Ville' => 'Paris', 'Pays' => 'France',
                'Téléphone' => '0600000000',
            ],
            [
                'Nom' => 'Oyelaran', "Nom d'usage" => '',
                'Prénom' => 'Adeola', "Prénom d'usage" => '',
                'Genre' => 'Homme', 'Date de naissance' => '19/01/2005',
                'Pays de naissance' => 'Nigeria', 'Nationalité' => 'Nigeria',
                'Adresse email' => 'adeola.oyelaran@example.invalid',
                'Adresse postale' => '2 avenue Imaginaire', "Complément d'adresse" => '',
                'Code postal' => '69003', 'Ville' => 'Lyon', 'Pays' => 'France',
                'Téléphone' => '0600000001',
            ],
            // Civilité non déterminante : doit être rejetée par RG-02.
            [
                'Nom' => 'Andersen', "Nom d'usage" => '',
                'Prénom' => 'Sasha', "Prénom d'usage" => '',
                'Genre' => 'Autre', 'Date de naissance' => '30/06/2005',
                'Pays de naissance' => 'Danemark', 'Nationalité' => 'Danemark',
                'Adresse email' => 'sasha.andersen@example.invalid',
                'Adresse postale' => '3 place Inventée', "Complément d'adresse" => '',
                'Code postal' => '33000', 'Ville' => 'Bordeaux', 'Pays' => 'France',
                'Téléphone' => '0600000002',
            ],
        ];
    }
}
