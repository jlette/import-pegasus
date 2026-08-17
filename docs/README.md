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

> ⚠️ **Nature des fichiers 2024 — ce ne sont pas des canevas produits.** Ce sont
> des **gabarits annotés**, construits à la main pour documenter le contenu
> attendu de chaque colonne au regard de son en-tête. Leur deuxième ligne n'est
> pas une donnée mais un **commentaire** (« *Nom des colonnes dans le fichier des
> intégrants de SCEI* », « *À déduire de `Civ _lib`* », « *`Can_nai` à remettre au
> format Date* »…), et le classeur `.xlsx` correspondant comporte un onglet
> « À lire ». Ils ne constituent donc une référence ni de structure, ni de mise en
> forme, ni de valeurs — y compris pour des détails comme une espace finale dans
> un libellé de colonne. Les seuls canevas réellement produits et importés dans
> PEGASUS sont ceux de la campagne 2025.
>
> **Conséquence pour l'implémentation** : si l'un de ces gabarits venait à être
> déposé dans l'outil, sa ligne de commentaires ne doit jamais être interprétée
> comme un étudiant.

## Décisions arbitrées

| # | Question | Décision | Autorité |
|---|---|---|---|
| **H1** | « Tous les primo arrivants sont inscrits à `ANDENS1` (NEMH, NEMS) » — la Sélection Internationale est-elle concernée ? | **Non.** Portée limitée à NEMH et NEMS. La SI conserve son produit programme déduit de la discipline | MOA, arbitrage du 17/08/2026 |
| **H3** | `ENS_FINANCEMENT` doit-il figurer dans le canevas ? | **Oui, à conserver.** Le canevas comporte donc **5 paires `Connaissance_fop_ins`** et **43 colonnes** | MOA, arbitrage du 17/08/2026 |
| **H6** | `ENS_BOURSE_ENS_PSL` pour un admis CPGE non-fonctionnaire (« BIS ») ? | **`OUI`.** Règle générale : `ENS_FONCTIONNAIRE = NON` ⟹ `ENS_BOURSE_ENS_PSL = OUI`. Formalisée en RG-01 (CDCF §5.9) et vérifiée sans exception sur les 142 lignes des canevas 2025 | MOA, arbitrage du 17/08/2026 |
| **H7** | Espace finale de l'en-tête `Connaissance_fop_ins 5 Type` ? | **Non.** L'unique indice provenait des gabarits 2024, écartés (voir l'encadré ci-dessus). Le libellé suit la forme des canevas 2025 | MOA, arbitrage du 17/08/2026 |
| **H2** | `Sexe` : `H` ou `M` pour les hommes ? | **`H`.** Les six canevas 2025 portent **67 `H` et 65 `F`, aucun `M`**. Les 7 `M` qui créaient le doute provenaient du seul gabarit 2024, écarté | Résolu par l'écartement des gabarits 2024 |

## Hypothèses ouvertes

Les points suivants n'ont pas pu être tranchés depuis les sources disponibles.
Ils sont signalés en tête des sections concernées et doivent être arbitrés par
la MOA (CRI) et le CoST.

| # | Question | Hypothèse retenue dans ces documents |
|---|---|---|
| **H4** | Quelle variante de fichier d'entrée fait foi pour la SI-Lettres : `Admis_SIL_*_Extraction` ou `COORDONNEES_ADMIS_LP_SIL_*` ? | **Les deux doivent être acceptées** (voir CDCF §5.2) |
| **H5** | `Genre = 'Autre'` : quelle valeur `Genre`/`Sexe` PEGASUS attend-il ? | **Rejet explicite de la ligne** en attendant l'arbitrage |
