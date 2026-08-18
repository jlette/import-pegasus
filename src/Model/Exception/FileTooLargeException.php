<?php

namespace App\Model\Exception;

/**
 * Exception levée lorsque le fichier source dépasse la capacité de traitement.
 *
 * Le fichier est refusé dans son ensemble : produire un canevas amputé des
 * lignes excédentaires ferait disparaître des étudiants sans aucun signal.
 */
class FileTooLargeException extends AbstractImportException
{
    public function __construct(int $maxLignes)
    {
        parent::__construct(sprintf(
            "Le fichier dépasse la capacité de traitement de %d lignes de données. "
                . "Scindez-le en plusieurs fichiers, puis importez-les successivement "
                . "en veillant à changer la date de lot entre deux imports.",
            $maxLignes
        ));
    }
}
