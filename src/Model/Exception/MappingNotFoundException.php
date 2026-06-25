<?php

namespace App\Model\Exception;


/**
 * Exception lancée lorsqu'une correspondance d'annuaire (Oracle) est introuvable.
 */
class MappingNotFoundException extends AbstractImportException
{
    /**
     * @param string $contexte Ce qu'on cherchait à mapper (ex: 'le concours')
     * @param string $valeurCherchee Le libellé brut recherché (ex: 'ENS PARIS MP')
     */
    public function __construct(string $contexte, string $valeurCherchee)
    {
        $message = sprintf(
            "Aucune correspondance PEGASUS trouvée pour %s avec la valeur '%s'.",
            $contexte,
            $valeurCherchee
        );

        parent::__construct($message);
    }
}
