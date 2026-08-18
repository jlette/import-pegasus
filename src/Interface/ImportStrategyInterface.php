<?php

namespace App\Interface;

use App\Builder\StudentBuilder;
use App\Canevas\CanevasProfile;
use App\Filter\AdmissionFilter;
use App\Source\ColumnCanonicalizer;
use App\Model\Student\AbstractStudent;

/**
 * Contrat strict que tous les "Spécialistes" d'import doivent respecter.
 */
interface ImportStrategyInterface
{
    /**
     * Lit une ligne du xls, applique les règles métier du concours, 
     * et utilise le Builder pour retourner l'étudiant parfait.
     */
    public function createStudent(array $row, int $currentLot, int $currentSsl): AbstractStudent;

    /**
     * Déclare la structure du canevas que cette stratégie alimente.
     *
     * C'est la stratégie qui connaît sa population, donc le canevas attendu :
     * le service d'export s'y conforme sans avoir à deviner.
     */
    public function canevasProfile(): CanevasProfile;

    /**
     * Déclare comment reconnaître, dans le fichier source, les candidats à
     * importer. Les exports de plateforme contiennent fréquemment des
     * non-admis, des listes complémentaires et des désistements.
     */
    public function admissionFilter(): AdmissionFilter;

    /**
     * Ramène les en-têtes du fichier source aux noms canoniques du dictionnaire
     * du cursus, afin d'accepter les différentes variantes qui circulent.
     */
    public function canonicalizer(): ColumnCanonicalizer;

    /**
     * Fixe l'année de la campagne d'inscription visée par l'import.
     */
    public function pourCampagne(int $annee): static;
}
