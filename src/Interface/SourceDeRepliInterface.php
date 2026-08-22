<?php

namespace App\Interface;

/**
 * Source de données capable de rendre une réponse dégradée lorsque son
 * référentiel de référence est injoignable.
 *
 * Un repli qui ne se voit pas est un piège : il produit un résultat d'apparence
 * normale à partir de données potentiellement périmées. Toute source qui se
 * réserve cette possibilité doit donc pouvoir dire, après coup, si elle en a
 * fait usage — c'est l'objet de ce contrat.
 */
interface SourceDeRepliInterface
{
    /**
     * Vrai si au moins une réponse a été servie depuis le repli.
     */
    public function repliActive(): bool;

    /**
     * Code technique de la panne ayant déclenché le repli (`ORA-28000`…),
     * ou chaîne vide s'il n'a pas pu être déterminé.
     */
    public function codeTechnique(): string;
}
