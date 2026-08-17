# Documentation du projet PEGASUS — Normalisateur des admissions

> Outil interne du CRI / CoST de l'École normale supérieure – PSL.
> Transforme les exports bruts des plateformes de candidature en canevas CSV
> conformes aux règles d'import de PEGASUS/Helisia (back-office *Phénix*).

## Corpus documentaire

| Document | Objet | Public |
|---|---|---|
| [01 — Expression de besoin](01-expression-de-besoin.md) | Contexte, problèmes, objectifs, ROI attendu | Direction, MOA, comité de pilotage |
| [02 — Cahier des charges fonctionnel (CDCF)](02-cahier-des-charges-fonctionnel.md) | Fonctionnalités, cas d'usage, parcours, règles de gestion | MOA, CoST, DRI, recette |
| [03 — Cahier des charges technique (CDCT)](03-cahier-des-charges-technique.md) | Architecture, stack, modèle de données, sécurité, déploiement | Développeurs, exploitants CRI |
| [04 — Mode d'emploi](04-mode-emploi.md) | Prise en main pas à pas | Gestionnaires CoST, DRI |

## Référentiel documentaire source et règle de priorité

Ces documents ont été reconstitués par rétro-ingénierie à partir d'un corpus
hétérogène (comptes rendus, présentation, échanges de courriels, fichiers
d'exemple d'entrée et de sortie), en l'absence de tout cahier des charges initial.

**Règle de priorité temporelle appliquée**, en cas de contradiction entre sources :

```
Documents et fichiers 2026  >  Fichiers 2025  >  ✗ 2024 (obsolètes, écartés)
```

| Source | Date | Statut |
|---|---|---|
| `type de population import pegasus 2026.docx` | 2026 | **Référence prioritaire** |
| Fichiers d'entrée `*_2026.xlsx` / `.xls` (BL, SIL, SIS, MH, MS, Bourses Olympiques) | 2026 | **Référence prioritaire** (formats d'entrée) |
| Échanges de courriels CRI ↔ CoST ↔ DRI | 03 → 07/2026 | Référence (règles de gestion) |
| `Exemple_SI_Lettres_2025.csv`, `AL/BL/SIL/MH_import_primo_2025` | 2025 | **Référence de repli** — seul format de sortie valide connu |
| `PEGASUS_Intégrer_les_admis` (présentation, 30 slides) | 19/05/2026 | Référence (procédure d'import côté PEGASUS) |
| `CR_Modélisation_listes_des_admis_pour_Pegasus` | 02/12/2025 | Référence de repli |
| `exemple_cpge_sciences_2024.*`, `exemple_SI_Lettres_2024.*` | 2024 | ⛔ **Obsolètes — ne pas utiliser** |

## Hypothèses ouvertes

Les points suivants n'ont pas pu être tranchés depuis les sources disponibles.
Ils sont signalés en tête des sections concernées et doivent être arbitrés par
la MOA (CRI) et le CoST.

| # | Question | Hypothèse retenue dans ces documents |
|---|---|---|
| **H1** | « Tous les primo arrivants sont inscrits à `ANDENS1` (NEMH, NEMS) » — la Sélection Internationale est-elle concernée ? | **Non** : portée limitée à NEMH et NEMS. La SI conserve son produit programme par discipline |
| **H2** | `Sexe` : `H` ou `M` pour les hommes ? | **`H`** — 141 occurrences sur 148 dans les canevas de référence 2025 |
| **H3** | `ENS_FINANCEMENT` doit-il figurer dans le canevas 2026 ? | **Non** — absent des canevas 2025, et « laissé vide » selon le document 2026 |
| **H4** | Quelle variante de fichier d'entrée fait foi pour la SI-Lettres : `Admis_SIL_*_Extraction` ou `COORDONNEES_ADMIS_LP_SIL_*` ? | **Les deux doivent être acceptées** (voir CDCF §5.2) |
| **H5** | `Genre = 'Autre'` : quelle valeur `Genre`/`Sexe` PEGASUS attend-il ? | **Rejet explicite de la ligne** en attendant l'arbitrage |
