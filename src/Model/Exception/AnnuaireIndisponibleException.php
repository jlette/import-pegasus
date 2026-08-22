<?php

namespace App\Model\Exception;

use App\Interface\ErreurGlobaleInterface;

/**
 * Exception levée lorsque l'annuaire Oracle est injoignable.
 *
 * Le référentiel des codes concours en dépend : sans lui, aucun canevas ne peut
 * être produit pour les cursus qui l'interrogent. L'anomalie est globale — elle
 * ne dépend d'aucune ligne — et interrompt donc le traitement.
 *
 * Le message technique d'Oracle n'est pas répercuté à l'utilisateur : il
 * comporte des chemins de compilation du pilote, sans intérêt pour lui et
 * inutilement révélateurs de l'infrastructure. Le code d'erreur est en revanche
 * conservé pour le support, via codeTechnique().
 */
class AnnuaireIndisponibleException extends AbstractImportException implements ErreurGlobaleInterface
{
    public function __construct(private string $codeTechnique = '')
    {
        parent::__construct(
            "Le référentiel des concours (annuaire) est momentanément injoignable : "
                . "le canevas ne peut pas être produit sans lui. "
                . "Aucune correction du fichier ne résoudra ce problème — signalez-le au CRI"
                . ($codeTechnique !== '' ? " en précisant le code {$codeTechnique}." : ".")
        );
    }

    /**
     * Code d'erreur Oracle, par exemple ORA-28000. Destiné au journal et au
     * support, jamais affiché tel quel.
     */
    public function codeTechnique(): string
    {
        return $this->codeTechnique;
    }
}
