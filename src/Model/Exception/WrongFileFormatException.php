<?php

namespace App\Model\Exception;

class WrongFileFormatException extends AbstractImportException
{
    public function __construct(string $colonneManquante)
    {
        parent::__construct(
            "Fichier non conforme : La colonne requise '$colonneManquante' est introuvable dans l'en-tête. " .
                "Êtes-vous sûr d'avoir sélectionné le bon fichier Excel pour ce cursus / type d'étudiant ?"
        );
    }
}
