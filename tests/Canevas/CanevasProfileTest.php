<?php

namespace Tests\Canevas;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Canevas\CanevasProfile;

/**
 * Verrouille la structure exacte du canevas attendu par PEGASUS.
 *
 * Les en-têtes sont écrits en dur, volontairement : ce test n'a de valeur que
 * s'il échoue quand le code change. Les canevas de référence réels ne peuvent
 * pas servir de fixture — ils contiennent l'identité, la date de naissance et
 * l'adresse électronique d'admis réels, qui n'ont rien à faire dans un dépôt.
 */
#[CoversClass(CanevasProfile::class)]
class CanevasProfileTest extends TestCase
{
    /**
     * Canevas des normaliens : base 2025 augmentée de ENS_FINANCEMENT (H3).
     */
    private const EN_TETES_NORMALIEN = [
        'Date_Lot', 'No_Lot', 'No_Ssl', 'Type_occ', 'Recrutement', 'Année',
        'Produit Programme', 'No Année', 'Session', 'Statut Etudiant',
        'Genre', 'Nom', 'Prénom', 'Sexe',
        'Connaissance 2 Type', 'Connaissance 2 Valeur',
        'Connaissance 3 Type', 'Connaissance 3 Valeur',
        'Connaissance 4 Type', 'Connaissance 4 Valeur',
        'Connaissance 5 Type', 'Connaissance 5 Valeur',
        'Connaissance 6 Type', 'Connaissance 6 Valeur',
        'Connaissance 7 Type', 'Connaissance 7 Valeur',
        'Connaissance 8 Type', 'Connaissance 8 Valeur',
        'Connaissance_fop_ins 1 Type', 'Connaissance_fop_ins 1 Valeur',
        'Connaissance_fop_ins 2 Type', 'Connaissance_fop_ins 2 Valeur',
        'Connaissance_fop_ins 3 Type', 'Connaissance_fop_ins 3 Valeur',
        'Connaissance_fop_ins 4 Type', 'Connaissance_fop_ins 4 Valeur',
        'Connaissance_fop_ins 5 Type', 'Connaissance_fop_ins 5 Valeur',
        'Ville de Naissance', 'Date de Naissance', 'Pays de Naissance',
        'Nationalité Principale',
        'EOL',
    ];

    public function testLeCanevasNormalienCompteQuaranteTroisColonnes(): void
    {
        $this->assertCount(43, CanevasProfile::normalien()->enTetes());
    }

    public function testLesEnTetesNormaliensSontConformesAuCaracterePres(): void
    {
        $this->assertSame(self::EN_TETES_NORMALIEN, CanevasProfile::normalien()->enTetes());
    }

    /**
     * Régression : le type de la connaissance promo était 'PROMO'. Les six
     * canevas de référence portent 'ENS_PROMO'. Un type erroné crée dans
     * PEGASUS une connaissance parasite que la synchronisation ignore.
     */
    public function testLaConnaissancePromoUtiliseLeTypePrefixe(): void
    {
        $this->assertContains('ENS_PROMO', CanevasProfile::normalien()->connaissances);
        $this->assertNotContains('PROMO', CanevasProfile::normalien()->connaissances);
    }

    /**
     * Régression : le libellé produit était 'Nationalité Principal'.
     */
    public function testLeLibelleNationaliteEstAuFeminin(): void
    {
        $this->assertContains('Nationalité Principale', CanevasProfile::normalien()->enTetes());
    }

    /**
     * Les connaissances réservées à l'inscription DENS ne doivent jamais
     * figurer au canevas des échanges internationaux : les renseigner pour une
     * population non normalienne fausse l'annuaire de l'École.
     */
    public function testLeCanevasEchangeExclutLesConnaissancesNormaliennes(): void
    {
        $profile = CanevasProfile::echange();

        $this->assertNotContains('ENS_PROMO', $profile->connaissances);
        $this->assertNotContains('ENS_FONCTIONNAIRE', $profile->connaissances);
        $this->assertNotContains('ENS_CONCOURS', $profile->connaissances);
        $this->assertSame([], $profile->fopIns);
    }

    public function testLeCanevasEchangeCompteQuaranteQuatreColonnes(): void
    {
        // Structure relevée sur le canevas réellement importé par la DRI pour
        // la rentrée de septembre 2025 (169 étudiants).
        $this->assertCount(44, CanevasProfile::echange()->enTetes());
    }

    public function testLeCanevasEchangePorteLesContactsDUrgence(): void
    {
        $profile = CanevasProfile::echange();

        $this->assertContains('URGENCE PERSONNE', $profile->connaissances);
        $this->assertContains('URGENCE TELEPHONE', $profile->connaissances);
        $this->assertContains('PORTABLE', $profile->connaissances);
        $this->assertContains('ENS_DPT_RATT_ETU_ECHAN', $profile->connaissances);
    }

    public function testLaDerniereColonneEstToujoursEol(): void
    {
        foreach ([CanevasProfile::normalien(), CanevasProfile::echange()] as $profile) {
            $enTetes = $profile->enTetes();
            $this->assertSame('EOL', end($enTetes));
        }
    }
}
