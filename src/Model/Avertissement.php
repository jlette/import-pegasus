<?php

namespace App\Model;

/**
 * Réserve émise sur un import qui a pourtant abouti.
 *
 * Distincte d'une erreur : le canevas existe et reste exploitable, mais il a
 * été produit dans des conditions dont le gestionnaire doit être informé —
 * aujourd'hui, des codes concours issus de la table de secours embarquée faute
 * d'annuaire joignable.
 *
 * Deux destinataires, deux champs : `message` s'adresse au gestionnaire, en
 * clair et sans jargon ; `code` s'adresse au support, pour le journal et le
 * diagnostic. Les mélanger conduirait soit à afficher `ORA-28000` à un agent du
 * CoST, soit à priver le CRI de la seule information qui l'intéresse.
 */
final readonly class Avertissement
{
    public function __construct(
        public string $message,
        public string $code = '',
    ) {}
}
