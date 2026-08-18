<?php

namespace Tests\Filter;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Filter\AdmissionFilter;
use App\Strategy\Normalien\SI\SiScienceStrategy;
use App\Strategy\Normalien\NE\NemsStrategy;
use App\Service\ConcoursService;
use App\Interface\CodeRepositoryInterface;

#[CoversClass(AdmissionFilter::class)]
class AdmissionFilterTest extends TestCase
{
    /**
     * Régression : l'extraction SI-Sciences 2026 comporte 29 lignes NON-ADMIS
     * sur 39. Sans filtrage, l'outil créait dans PEGASUS le dossier
     * administratif de candidats refusés.
     */
    public function testLesNonAdmisSontEcartesDuFluxSiSciences(): void
    {
        $filtre = $this->strategieSiSciences()->admissionFilter();

        $this->assertTrue($filtre->retient(['LP/LC' => 'ADMIS, LP']));
        $this->assertTrue($filtre->retient(['LP/LC' => 'ADMIS,LC']), 'La liste complémentaire est à importer.');
        $this->assertFalse($filtre->retient(['LP/LC' => 'NON-ADMIS']));
    }

    /**
     * Les intitulés d'état varient au sein d'un même fichier : « ADMIS, LP »
     * et « ADMIS,LC » cohabitent, avec ou sans espace.
     */
    public function testLaComparaisonIgnoreEspacesCasseEtAccents(): void
    {
        $filtre = new AdmissionFilter(valeursRetenues: ['Etat' => ['ADMIS, LP']]);

        $this->assertTrue($filtre->retient(['Etat' => 'admis,lp']));
        $this->assertTrue($filtre->retient(['Etat' => '  ADMIS ,  LP  ']));
        $this->assertFalse($filtre->retient(['Etat' => 'NON-ADMIS']));
    }

    public function testUnDesistementEcarteLaLigne(): void
    {
        $filtre = $this->strategieNems()->admissionFilter();

        $this->assertTrue($filtre->retient(['État' => 'Admis', 'Désistement' => '']));
        $this->assertFalse($filtre->retient(['État' => 'Admis', 'Désistement' => '2026-07-01']));
    }

    /**
     * Une colonne d'état absente du fichier ne peut pas servir de critère :
     * l'extraction brute SI-Lettres ne porte ni rang ni confirmation. Tout
     * écarter serait pire que ne rien filtrer.
     */
    public function testUneColonneAbsenteNEcartePasLaLigne(): void
    {
        $filtre = new AdmissionFilter(valeursRetenues: ['Rang' => ['ADMIS']]);

        $this->assertTrue($filtre->retient(['Nom' => 'DUPONT']));
    }

    public function testLeFiltreNeutreRetientTout(): void
    {
        $this->assertTrue(AdmissionFilter::aucun()->retient([]));
        $this->assertTrue(AdmissionFilter::aucun()->retient(['LP/LC' => 'NON-ADMIS']));
    }

    private function strategieSiSciences(): SiScienceStrategy
    {
        return new SiScienceStrategy($this->concoursService());
    }

    private function strategieNems(): NemsStrategy
    {
        return new NemsStrategy($this->concoursService());
    }

    private function concoursService(): ConcoursService
    {
        return new ConcoursService(new class implements CodeRepositoryInterface {
            public function findByPlatforme(string $platforme): array
            {
                return [];
            }
        });
    }
}
