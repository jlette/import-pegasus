<?php

namespace App\Service;

/**
 * Purge les fichiers résiduels du dossier temporaire.
 *
 * Ce dossier contient l'identité, la date de naissance, la nationalité et
 * l'adresse électronique de candidats. Le cycle nominal les supprime — le
 * fichier source à la fin du traitement, le canevas après téléchargement —
 * mais un canevas jamais téléchargé, ou un traitement interrompu, laisse des
 * données personnelles sur le disque.
 *
 * La purge applique donc le principe de limitation de conservation, sans
 * dépendre d'une tâche planifiée : elle s'exécute au début de chaque import.
 */
class TemporaryFilePurger
{
    /** Durée au-delà de laquelle un fichier résiduel est supprimé. */
    public const RETENTION_SECONDES = 3600;

    public function __construct(
        private string $repertoire,
        private int $retentionSecondes = self::RETENTION_SECONDES,
    ) {}

    /**
     * Supprime les fichiers dont la dernière modification dépasse la rétention.
     *
     * @return int Nombre de fichiers supprimés
     */
    public function purger(): int
    {
        if (!is_dir($this->repertoire)) {
            return 0;
        }

        $limite = time() - $this->retentionSecondes;
        $supprimes = 0;

        foreach (glob(rtrim($this->repertoire, '/') . '/*') ?: [] as $fichier) {
            if (!is_file($fichier)) {
                continue;
            }

            $modification = @filemtime($fichier);

            if ($modification !== false && $modification < $limite && @unlink($fichier)) {
                $supprimes++;
            }
        }

        return $supprimes;
    }
}
