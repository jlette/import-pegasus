<?php

namespace App\Service;

use App\Interface\CodeRepositoryInterface;

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
}
