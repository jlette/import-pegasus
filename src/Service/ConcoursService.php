<?php

namespace App\Service;

use App\Constant\ConcoursDeSecours;
use App\Interface\CodeRepositoryInterface;
use App\Interface\SourceDeRepliInterface;
use App\Model\Avertissement;

/**
 * Gère la logique métier des concours.
 * Utilise le repository pour accéder aux données et les normaliser.
 */
class ConcoursService
{
    /**
     * Initialise le service avec une instance du repository des concours.
     * 
     * @param CodeRepositoryInterface $repository Repository pour accéder aux données
     */
    public function __construct(private CodeRepositoryInterface $repository) {}

    /**
     * Récupère et retourne une liste de codes correspondants à leur platforme.
     * 
     * Délègue l'accès aux données au repository et retourne le code PEGASUS
     * correspondant à la plaftorm fourni.
     *
     * @param string $platforme Code annuaire de la plateforme (ex: "SCEI", "EPONA", "AGREG", etc.)
     * 
     * @return array liste des codes concours normalisés pour PEGASUS
     */
    public function findByPlatforme(string $platforme): array
    {
        // Récupère le code normalisé via le repository
        return $this->repository->findByPlatforme($platforme);
    }

    /**
     * Avertissements à porter à la connaissance du gestionnaire.
     *
     * Un canevas produit sans l'annuaire a l'apparence d'un canevas normal.
     * C'est précisément ce qui le rend dangereux : les codes concours viennent
     * alors d'un instantané qui a pu se périmer. Le gestionnaire doit le savoir
     * avant d'importer le fichier dans PEGASUS, et le CRI doit apprendre la
     * panne même si personne ne la signale.
     *
     * @return list<Avertissement>
     */
    public function avertissements(): array
    {
        if (!$this->repository instanceof SourceDeRepliInterface || !$this->repository->repliActive()) {
            return [];
        }

        return [
            new Avertissement(
                'Le référentiel des concours (annuaire) était injoignable : les codes '
                    . 'concours proviennent de la table de secours embarquée dans l\'outil, '
                    . 'relevée le ' . $this->releveLisible() . '. Vérifiez ces codes avant '
                    . 'd\'importer le canevas dans PEGASUS, et signalez la panne au CRI.',
                $this->repository->codeTechnique(),
            ),
        ];
    }

    /**
     * Date du relevé de la table de secours, au format attendu par un lecteur français.
     */
    private function releveLisible(): string
    {
        $date = date_create_immutable(ConcoursDeSecours::RELEVE_LE);

        return $date !== false ? $date->format('d/m/Y') : ConcoursDeSecours::RELEVE_LE;
    }
}
