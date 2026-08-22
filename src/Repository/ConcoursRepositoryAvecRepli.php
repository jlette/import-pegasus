<?php

namespace App\Repository;

use App\Constant\ConcoursDeSecours;
use App\Interface\CodeRepositoryInterface;
use App\Interface\SourceDeRepliInterface;
use App\Model\Exception\AnnuaireIndisponibleException;

/**
 * Décorateur qui rend l'import possible sans l'annuaire.
 *
 * L'annuaire Oracle reste la source interrogée en premier. S'il est
 * injoignable — panne du serveur, écouteur arrêté, compte verrouillé — le
 * décorateur sert les correspondances embarquées dans {@see ConcoursDeSecours}
 * pour les plateformes couvertes, au lieu de laisser l'import échouer.
 *
 * Le décorateur n'invente rien : il ne se substitue à l'annuaire que sur une
 * **indisponibilité** ({@see AnnuaireIndisponibleException}). Un annuaire
 * joignable qui répond « ce concours n'existe pas » a raison, et sa réponse est
 * respectée : le repli ne sert pas à contourner un référentiel à jour.
 *
 * Deux conditions encadrent le repli :
 *
 * - une plateforme non couverte laisse remonter la panne intacte ;
 * - l'usage du repli est mémorisé et exposé, afin d'être remonté au
 *   gestionnaire et journalisé. Cette classe implémente
 *   {@see SourceDeRepliInterface} à cette seule fin.
 */
final class ConcoursRepositoryAvecRepli implements CodeRepositoryInterface, SourceDeRepliInterface
{
    private ?AnnuaireIndisponibleException $panne = null;

    public function __construct(private CodeRepositoryInterface $annuaire) {}

    /**
     * @return list<array{ANNUAIRE_CONC_CODE: string, CONC_CODE: string}>
     *
     * @throws AnnuaireIndisponibleException Si la plateforme n'est pas couverte par le repli
     */
    public function findByPlatforme(string $platforme): array
    {
        // L'annuaire est déjà tombé pendant cet import : inutile de le
        // solliciter à nouveau, la panne ne se répare pas en cours de requête.
        if ($this->panne !== null && ConcoursDeSecours::couvre($platforme)) {
            return ConcoursDeSecours::pour($platforme);
        }

        try {
            return $this->annuaire->findByPlatforme($platforme);
        } catch (AnnuaireIndisponibleException $panne) {
            if (!ConcoursDeSecours::couvre($platforme)) {
                throw $panne;
            }

            $this->panne = $panne;

            return ConcoursDeSecours::pour($platforme);
        }
    }

    public function repliActive(): bool
    {
        return $this->panne !== null;
    }

    public function codeTechnique(): string
    {
        return $this->panne?->codeTechnique() ?? '';
    }
}
