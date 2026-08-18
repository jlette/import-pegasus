<?php

namespace App\Model\Exception;

/**
 * Exception levée lorsque la civilité du fichier source ne permet pas de
 * déterminer le sexe attendu par PEGASUS (RG-02).
 *
 * Les dossiers de candidature OnePSL30 admettent la valeur « Autre », pour
 * laquelle PEGASUS n'offre aujourd'hui aucune correspondance. L'outil ne
 * choisit jamais à la place du gestionnaire : une valeur inventée entrerait
 * dans PEGASUS puis, par synchronisation, dans le SI de l'École et jusqu'au SRH.
 */
class UndeterminedSexException extends AbstractImportException
{
    public function __construct(string $valeurRecue)
    {
        $message = $valeurRecue === ''
            ? "La civilité est absente : impossible de déterminer le sexe. "
                . "Renseignez-la d'après le dossier de candidature."
            : sprintf(
                "La civilité '%s' ne permet pas de déterminer le sexe. "
                    . "Déterminez-le d'après le dossier de candidature, "
                    . "indépendamment du genre déclaré, puis corrigez le fichier source.",
                $valeurRecue
            );

        parent::__construct($message);
    }
}
