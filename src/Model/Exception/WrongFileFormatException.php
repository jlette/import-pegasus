<?php

namespace App\Model\Exception;

use App\Interface\ErreurGlobaleInterface;

class WrongFileFormatException extends AbstractImportException implements ErreurGlobaleInterface
{
    public function __construct(string $colonneManquante)
    {
        parent::__construct(
            "Fichier non conforme : La colonne requise '$colonneManquante' est introuvable dans l'en-tête. " .
                "Êtes-vous sûr d'avoir sélectionné le bon fichier Excel pour ce cursus / type d'étudiant ?"
        );
    }
}
