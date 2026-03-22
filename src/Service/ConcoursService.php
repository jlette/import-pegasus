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
     * Récupère et retourne le code concours normalisé.
     * 
     * Délègue l'accès aux données au repository et retourne le code PEGASUS
     * correspondant au code annuaire fourni.
     *
     * @param string $codeConcours Code annuaire du concours
     * 
     * @return string Code concours normalisé (CONC_CODE)
     */
    public function getCode(string $codeConcours): string
    {
        // Récupère le code normalisé via le repository
        return $this->repository->findCode($codeConcours);
    }
}