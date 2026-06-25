<?php

namespace App\Model\Exception;


/**
 * Exception lancée lorsque le format d'une donnée lue est incorrect.
 */
class InvalidDataFormatException extends AbstractImportException
{
    /**
     * @param string $champ Nom du champ en faute (ex: 'Date de naissance')
     * @param string $valeurRecue La valeur brute lue dans l'Excel (ex: '')
     */
    public function __construct(string $champ, string $valeurRecue)
    {
        $message = sprintf(
            "La valeur '%s' fournie pour le champ '%s' est invalide ou mal formatée.",
            $valeurRecue,
            $champ
        );

        parent::__construct($message);
    }
}
