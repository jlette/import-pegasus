<?php

namespace App\Log;

use App\Model\Exception\AnnuaireIndisponibleException;
use Throwable;

/**
 * Journal des opérations d'import.
 *
 * Il répond à trois besoins : le support — savoir ce qui a été tenté et quand ;
 * l'audit RGPD — prouver qui a manipulé quelles données ; et le suivi des
 * campagnes — combien d'étudiants ont été traités par population.
 *
 * **Le journal ne doit contenir aucune donnée personnelle.** C'est une
 * contrainte structurante, et non une précaution : un fichier de log est copié,
 * sauvegardé et conservé bien plus longtemps que les fichiers temporaires, sans
 * bénéficier de la même vigilance. On y consigne donc des volumétries et des
 * types d'anomalie, jamais le contenu des lignes en cause.
 *
 * En particulier, les messages d'exception ne sont **pas** journalisés tels
 * quels : `InvalidDataFormatException` reprend la valeur brute rencontrée, qui
 * peut être une date de naissance ou une adresse électronique. Seule la
 * catégorie de l'anomalie est retenue.
 *
 * Format : une ligne par événement, horodatage ISO 8601 puis paires clé=valeur.
 * Lisible à l'œil, exploitable avec grep, sans dépendance à un analyseur.
 */
final class ImportLogger
{
    public function __construct(private string $cheminFichier) {}

    /**
     * Construit le journal depuis la configuration.
     */
    public static function parDefaut(): self
    {
        $chemin = getenv('APP_LOG_FILE');

        if ($chemin === false || $chemin === '') {
            $chemin = dirname(__DIR__, 2) . '/var/log/import.log';
        }

        return new self($chemin);
    }

    /**
     * Import mené à son terme : un canevas a été produit.
     */
    public function reussite(string $agent, string $population, string $cursus, array $mesures): void
    {
        $this->ecrire('INFO', 'import.reussi', array_merge(
            ['agent' => $agent, 'population' => $population, 'cursus' => $cursus],
            $mesures
        ));
    }

    /**
     * Import refusé pour cause d'anomalies de données.
     *
     * @param array<string, int> $anomaliesParType Nombre d'anomalies par catégorie
     */
    public function anomalies(
        string $agent,
        string $population,
        string $cursus,
        int $nombre,
        array $anomaliesParType,
    ): void {
        $this->ecrire('WARNING', 'import.rejete', array_merge([
            'agent' => $agent,
            'population' => $population,
            'cursus' => $cursus,
            'anomalies' => $nombre,
        ], $anomaliesParType));
    }

    /**
     * Canevas produit en mode dégradé : l'annuaire était injoignable et les
     * codes concours viennent de la table de secours embarquée.
     *
     * Événement distinct de la réussite, et de niveau WARNING : c'est souvent
     * la seule trace qu'aura le CRI d'une panne d'annuaire, le gestionnaire
     * n'ayant aucune raison de signaler un import qui a abouti.
     */
    public function repli(string $agent, string $population, string $cursus, string $codeTechnique): void
    {
        $contexte = [
            'agent' => $agent,
            'population' => $population,
            'cursus' => $cursus,
            'source' => 'table_de_secours',
        ];

        if ($codeTechnique !== '') {
            $contexte['code'] = $codeTechnique;
        }

        $this->ecrire('WARNING', 'import.repli', $contexte);
    }

    /**
     * Échec technique : format de fichier, capacité, indisponibilité de l'annuaire.
     *
     * Seule la classe de l'exception est retenue. Son message peut reprendre une
     * valeur issue du fichier source.
     */
    public function echec(string $agent, string $population, string $cursus, Throwable $erreur): void
    {
        $contexte = [
            'agent' => $agent,
            'population' => $population,
            'cursus' => $cursus,
            'exception' => $this->nomCourt($erreur),
        ];

        // Un code d'erreur d'infrastructure n'est pas une donnée personnelle,
        // et c'est la première chose que cherchera le support.
        if ($erreur instanceof AnnuaireIndisponibleException && $erreur->codeTechnique() !== '') {
            $contexte['code'] = $erreur->codeTechnique();
        }

        $this->ecrire('ERROR', 'import.echec', $contexte);
    }

    /**
     * Écrit une ligne d'événement.
     *
     * @param array<string, scalar> $contexte
     */
    private function ecrire(string $niveau, string $evenement, array $contexte): void
    {
        $repertoire = dirname($this->cheminFichier);

        if (!is_dir($repertoire) && !@mkdir($repertoire, 0750, true) && !is_dir($repertoire)) {
            return;
        }

        $paires = [];

        foreach ($contexte as $cle => $valeur) {
            $paires[] = $cle . '=' . $this->assainir((string) $valeur);
        }

        $ligne = sprintf(
            "%s %s %s %s\n",
            date('c'),
            $niveau,
            $evenement,
            implode(' ', $paires)
        );

        // Écriture atomique : plusieurs imports peuvent se chevaucher.
        @file_put_contents($this->cheminFichier, $ligne, FILE_APPEND | LOCK_EX);
    }

    /**
     * Neutralise ce qui casserait le format d'une ligne.
     *
     * Une valeur multiligne scinderait l'événement en deux ; une valeur
     * comportant une espace rendrait le découpage en paires ambigu.
     */
    private function assainir(string $valeur): string
    {
        $valeur = str_replace(["\r", "\n", "\t"], ' ', $valeur);

        return str_contains($valeur, ' ') ? '"' . str_replace('"', "'", $valeur) . '"' : $valeur;
    }

    private function nomCourt(Throwable $erreur): string
    {
        $parties = explode('\\', $erreur::class);

        return end($parties);
    }
}
