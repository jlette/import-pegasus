<?php

namespace App\Model\Exception;

/**
 * Exception lancée lorsqu'un champ obligatoire est vide ou manquant dans l'Excel.
 */
class MissingMandatoryFieldException extends AbstractImportException
{
    public function __construct(string $nomDuChamp)
    {
        $message = sprintf("Le champ obligatoire '%s' n'est pas renseigné ou est vide.", $nomDuChamp);
        parent::__construct($message);
    }
}
