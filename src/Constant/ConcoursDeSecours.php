<?php

namespace App\Constant;

/**
 * Table de correspondance des concours embarquée dans le code, servant de
 * repli lorsque l'annuaire Oracle est injoignable.
 *
 * ## Pourquoi
 *
 * L'annuaire Jefyco est la source de vérité des codes concours, et il le
 * reste : une panne ne doit pas empêcher de produire un canevas en pleine
 * campagne d'admission, où la fenêtre de saisie se compte en jours. Les codes
 * CPGE sont stables d'une année sur l'autre — ils changent lors d'une réforme
 * de filière, pas au fil de l'eau —, ce qui rend le repli acceptable pour ces
 * deux plateformes.
 *
 * ## Portée volontairement étroite
 *
 * Seules **SCEI** (CPGE sciences) et **EPONA** (A/L) sont couvertes. Les
 * plateformes non listées continuent d'échouer franchement : mieux vaut un
 * import refusé qu'un canevas rempli de codes devinés.
 *
 * ## Nature exacte de cette table
 *
 * C'est un **instantané daté**, pas une source de vérité. Il dérive dès que
 * l'annuaire est modifié, sans que rien ne le signale. Deux garde-fous :
 *
 * 1. l'outil avertit systématiquement le gestionnaire quand un canevas a été
 *    produit depuis cette table plutôt que depuis l'annuaire ;
 * 2. l'événement est journalisé, pour que le CRI sache que l'annuaire est
 *    tombé même si personne ne le signale.
 *
 * ## Mise à jour
 *
 * À reprendre après toute évolution de `CORRESP_ANNUAIRE_CONC_CODE`, en
 * rejouant la requête de production :
 *
 * ```sql
 * SELECT PLATEFORME, ANNUAIRE_CONC_CODE, CONC_CODE
 * FROM   CORRESP_ANNUAIRE_CONC_CODE
 * WHERE  PEGASUS = 'O'
 *   AND  PLATEFORME IN ('SCEI', 'EPONA')
 * ORDER  BY PLATEFORME, ANNUAIRE_CONC_CODE;
 * ```
 *
 * `C-MPI` et `INFO`, supprimés en 2025, sont volontairement absents : les
 * réintroduire ferait resurgir des codes que PEGASUS n'accepte plus.
 */
final class ConcoursDeSecours
{
    /** Date du dernier relevé de l'annuaire ayant servi à établir cette table. */
    public const RELEVE_LE = '2026-08-22';

    /**
     * Correspondances par plateforme, dans la forme exacte des lignes rendues
     * par l'annuaire — la résolution du libellé s'applique à l'identique.
     *
     * @var array<string, list<array{ANNUAIRE_CONC_CODE: string, CONC_CODE: string}>>
     */
    private const TABLE = [
        StudentDictionary::PLATEFORME_SCEI => [
            ['ANNUAIRE_CONC_CODE' => 'BCPST', 'CONC_CODE' => NormalienDictionary::CODE_CONCOURS_CPGE_SCIENCE_BCPST],
            ['ANNUAIRE_CONC_CODE' => 'MP',    'CONC_CODE' => NormalienDictionary::CODE_CONCOURS_CPGE_SCIENCE_MP],
            ['ANNUAIRE_CONC_CODE' => 'PC',    'CONC_CODE' => NormalienDictionary::CODE_CONCOURS_CPGE_SCIENCE_PC],
            ['ANNUAIRE_CONC_CODE' => 'PSI',   'CONC_CODE' => NormalienDictionary::CODE_CONCOURS_CPGE_PSI],
        ],
        StudentDictionary::PLATEFORME_EPONA => [
            ['ANNUAIRE_CONC_CODE' => AlDictionary::LIBELLE_CONCOURS, 'CONC_CODE' => NormalienDictionary::CODE_CONCOURS_CPGE_AL],
        ],
    ];

    /**
     * Indique si le repli sait répondre pour cette plateforme.
     *
     * Une plateforme non couverte doit laisser remonter la panne : produire un
     * canevas depuis une table vide reviendrait à rejeter chaque ligne pour
     * concours introuvable, en masquant la cause réelle.
     */
    public static function couvre(string $plateforme): bool
    {
        return isset(self::TABLE[$plateforme]);
    }

    /**
     * Correspondances embarquées pour une plateforme.
     *
     * @return list<array{ANNUAIRE_CONC_CODE: string, CONC_CODE: string}>
     */
    public static function pour(string $plateforme): array
    {
        return self::TABLE[$plateforme] ?? [];
    }

    /**
     * Plateformes couvertes, pour la documentation et les tests.
     *
     * @return list<string>
     */
    public static function plateformes(): array
    {
        return array_keys(self::TABLE);
    }
}
