<?php

namespace App\Model\Exception;

use Exception;
use App\Interface\ImportExceptionInterface;

/**
 * Socle commun. Toutes nos exceptions métiers hériteront d'elle, 
 * et profiteront de ses méthodes (comme le numéro de ligne).
 */
abstract class AbstractImportException extends Exception implements ImportExceptionInterface
{
    protected ?int $numeroLigne = null;

    public function setNumeroLigne(int $numeroLigne): self
    {
        $this->numeroLigne = $numeroLigne;
        return $this;
    }

    public function getNumeroLigne(): ?int
    {
        return $this->numeroLigne;
    }
}
