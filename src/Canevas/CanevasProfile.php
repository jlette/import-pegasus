<?php

namespace App\Canevas;

use App\Constant\NormalienDictionary;
use App\Constant\StudentDictionary;

/**
 * Déclaration explicite de la structure d'un canevas d'import PEGASUS.
 *
 * Le profil est la source de vérité unique de la structure du fichier produit.
 * Il ne doit jamais être déduit du premier étudiant traité : une liste
 * hétérogène produirait des colonnes désalignées, et une stratégie qui oublie
 * une connaissance produirait un canevas amputé sans que rien ne le signale.
 *
 * Les libellés doivent être reproduits au caractère près, espaces compris :
 * PEGASUS rejette le fichier au moindre écart.
 */
final readonly class CanevasProfile
{
    /**
     * @param list<string> $connaissances    Types des connaissances générales, dans l'ordre
     * @param list<string> $fopIns           Types des connaissances de formation, dans l'ordre
     * @param list<string> $colonnesFinales  Libellés des colonnes de fin, hors EOL
     */
    public function __construct(
        public array $connaissances,
        public array $fopIns,
        public array $colonnesFinales,
    ) {}

    /**
     * Canevas des normaliens : 43 colonnes.
     *
     * Base des canevas validés par le CoST pour la campagne 2025, augmentée de
     * ENS_FINANCEMENT (décision MOA H3). Identique pour les sept cursus — SCEI,
     * A/L, B/L, SI-Lettres, SI-Sciences, NEMH et NEMS : seules les valeurs
     * varient selon la population, jamais la structure.
     */
    public static function normalien(): self
    {
        return new self(
            connaissances: [
                StudentDictionary::CONNAISSANCE_TYPE_EMAIL_PERSO,
                StudentDictionary::CONNAISSANCE_TYPE_EMAIL_ECOLE,
                StudentDictionary::CONNAISSANCE_TYPE_NUMERO_ET_PSLR,
                StudentDictionary::CONNAISSANCE_TYPE_NO_INDIVIDU,
                NormalienDictionary::CONNAISSANCE_TYPE_PROMO,
                NormalienDictionary::CONNAISSANCE_TYPE_FONCTIONNAIRE,
                NormalienDictionary::CONNAISSANCE_TYPE_CONCOURS,
            ],
            fopIns: [
                NormalienDictionary::FOP_INS_TYPE_SITUATION_CST,
                NormalienDictionary::FOP_INS_TYPE_SITUATION_CSB,
                NormalienDictionary::FOP_INS_TYPE_MODE_PEDAGOGIQUE,
                NormalienDictionary::FOP_INS_TYPE_BOURSE,
                NormalienDictionary::FOP_INS_TYPE_FINANCEMENT,
            ],
            colonnesFinales: [
                'Ville de Naissance',
                'Date de Naissance',
                'Pays de Naissance',
                'Nationalité Principale',
            ],
        );
    }

    /**
     * Canevas des échanges internationaux entrants (DRI).
     *
     * PROMO, ENS_FONCTIONNAIRE et ENS_CONCOURS en sont volontairement absentes :
     * les renseigner pour une population non normalienne fausse l'annuaire de
     * l'École. Les connaissances de formation, réservées à l'inscription DENS,
     * sont absentes pour la même raison. L'adresse personnelle, en revanche, est
     * obligatoire pour cette population.
     */
    public static function echange(): self
    {
        return new self(
            connaissances: [
                StudentDictionary::CONNAISSANCE_TYPE_EMAIL_PERSO,
                StudentDictionary::CONNAISSANCE_TYPE_EMAIL_ECOLE,
                StudentDictionary::CONNAISSANCE_TYPE_NUMERO_ET_PSLR,
                StudentDictionary::CONNAISSANCE_TYPE_NO_INDIVIDU,
                StudentDictionary::TYPE_URGENCE_PERSONNE,
                StudentDictionary::TYPE_URGENCE_TEL,
                StudentDictionary::TYPE_PORTABLE,
                StudentDictionary::TYPE_DPT_RATT_ECHAN,
            ],
            fopIns: [],
            colonnesFinales: [
                'Ville de Naissance',
                'Date de Naissance',
                'Pays de Naissance',
                'Nationalité Principale',
                'Courrier Voie 1',
                'Courrier Voie 2',
                'Courrier Code Postal',
                'Courrier Ville',
                'Courrier Pays',
                'CourrierTéléphone',
            ],
        );
    }

    /**
     * Libellés d'en-tête complets du canevas, dans l'ordre exact attendu.
     *
     * @return list<string>
     */
    public function enTetes(): array
    {
        $enTetes = [
            'Date_Lot',
            'No_Lot',
            'No_Ssl',
            'Type_occ',
            'Recrutement',
            'Année',
            'Produit Programme',
            'No Année',
            'Session',
            'Statut Etudiant',
            'Genre',
            'Nom',
            'Prénom',
            'Sexe',
        ];

        // Les connaissances générales sont numérotées à partir de 2 : la
        // connaissance 1 est réservée par PEGASUS.
        foreach (array_keys($this->connaissances) as $index) {
            $numero = $index + 2;
            $enTetes[] = "Connaissance {$numero} Type";
            $enTetes[] = "Connaissance {$numero} Valeur";
        }

        foreach (array_keys($this->fopIns) as $index) {
            $numero = $index + 1;
            $enTetes[] = "Connaissance_fop_ins {$numero} Type";
            $enTetes[] = "Connaissance_fop_ins {$numero} Valeur";
        }

        foreach ($this->colonnesFinales as $libelle) {
            $enTetes[] = $libelle;
        }

        $enTetes[] = 'EOL';

        return $enTetes;
    }
}
