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

> **Version XWiki.** Une restitution condensée de ce corpus, en syntaxe
> XWiki 2.1, est disponible dans [`xwiki/`](xwiki/) — prête à être collée dans
> le wiki du CRI. Le présent corpus Markdown reste la référence : en cas de
> divergence, c'est lui qui fait foi.

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
| `RE: Concours CPGE Sciences` — CRI ↔ pôle des concours | 22/05/2026 | **Référence prioritaire** — liste exhaustive des concours scientifiques 2026 |
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

> 🔴 **Alerte de reprise — campagne normalienne 2025.** PEGASUS n'accepte que
> `M` et `F` dans le champ `Sexe`. Or les six canevas normaliens de la campagne
> 2025 portent **67 occurrences de `H`** (A/L, B/L, SI-Lettres, Médecine
> Humanités). Ces dossiers sont donc dans PEGASUS avec une valeur invalide,
> propagée depuis par synchronisation vers le SI de l'École.
>
> Le canevas DRI du 15/07/2025, qui porte 78 `M` sur 169 lignes, est en
> revanche conforme.
>
> Un contrôle puis une reprise sont à prévoir, indépendamment de la correction
> apportée à l'outil.

## Décisions arbitrées

| # | Question | Décision | Autorité |
|---|---|---|---|
| **H1** | « Tous les primo arrivants sont inscrits à `ANDENS1` (NEMH, NEMS) » — la Sélection Internationale est-elle concernée ? | **Non.** Portée limitée à NEMH et NEMS. La SI conserve son produit programme déduit de la discipline | MOA, arbitrage du 17/08/2026 |
| **H3** | `ENS_FINANCEMENT` doit-il figurer dans le canevas ? | **Oui, à conserver.** Le canevas comporte donc **5 paires `Connaissance_fop_ins`** et **43 colonnes** | MOA, arbitrage du 17/08/2026 |
| **H6** | `ENS_BOURSE_ENS_PSL` pour un admis CPGE non-fonctionnaire (« BIS ») ? | **`OUI`.** Règle générale : `ENS_FONCTIONNAIRE = NON` ⟹ `ENS_BOURSE_ENS_PSL = OUI`. Formalisée en RG-01 (CDCF §5.9) et vérifiée sans exception sur les 142 lignes des canevas 2025 | MOA, arbitrage du 17/08/2026 |
| **H7** | Espace finale de l'en-tête `Connaissance_fop_ins 5 Type` ? | **Non.** L'unique indice provenait des gabarits 2024, écartés (voir l'encadré ci-dessus). Le libellé suit la forme des canevas 2025 | MOA, arbitrage du 17/08/2026 |
| **H2** | `Sexe` : quelle valeur pour les hommes ? | **`M`, pour toutes les populations** — `M` et `F` sont les seules valeurs admises par PEGASUS. Une civilité `H` rencontrée dans un fichier source est reconnue comme masculine mais **convertie en `M`** à l'écriture. Les canevas normaliens 2025, qui portent 67 `H`, sont non conformes : voir l'alerte ci-dessus | MOA, arbitrage du 18/08/2026 |
| **H4** | Quelle variante de fichier d'entrée fait foi pour la SI-Lettres ? | **Les deux sont acceptées.** L'extraction brute DEMATEC (`Admis_SIL_*_Extraction`) comme le fichier retravaillé par le CoST (`COORDONNEES_ADMIS_LP_SIL_*`). Les dictionnaires de colonnes déclarent des **alias** et la recherche est insensible à la casse et aux accents | MOA, arbitrage du 17/08/2026 |
| **H8** | Le canevas ne portant pas `NOM_ETAT_CIVIL`, que met-on dans la colonne `Nom` ? | **Le nom d'état civil**, obligatoire pour les formations diplômantes ; le nom d'usage n'est qu'un repli. Formalisée en **RG-04** (CDCF §5.7) | MOA, arbitrage du 18/08/2026 |
| **H9** | Que faire si l'annuaire Oracle est en panne pendant une campagne ? | **Repli sur une table de codes concours embarquée**, pour SCEI et A/L uniquement. Le canevas est produit, mais le gestionnaire est averti à l'écran et l'incident journalisé. Les autres populations continuent d'échouer franchement. Formalisée en **RG-05** (CDCF §5.4) | MOA, arbitrage du 22/08/2026 |
| **H10** | Quels concours scientifiques la table de secours doit-elle porter ? | **Quatre, et quatre seulement** : `Groupe BCPST` → `C-BCPST`, `Groupe PC` → `C-PC`, `MP` → `C-MP`, `PSI` → `C-PSI`. `MPI` et `INFO` sont supprimés depuis 2025 ; aucune autre modification pour la promotion 2026 | Responsable du pôle des concours, courriel du 22/05/2026 |
| **H5** | `Genre = 'Autre'` : quelle valeur PEGASUS ? | **Aucune : la ligne est rejetée.** Le scan se poursuit jusqu'au bout et aucun canevas n'est produit. `Genre` et `Sexe` sont deux données distinctes ; le sexe administratif est déterminé par le gestionnaire depuis le dossier de candidature, puis le fichier source est corrigé et relancé. Formalisée en **RG-02** (CDCF §5.7), sous le principe de balayage complet **RG-03**. Décision **provisoire**, à revoir si Phénix ouvre une valeur pour un genre non binaire | MOA, arbitrage du 17/08/2026 |

## Hypothèses ouvertes

Aucune. L'ensemble des points laissés en suspens par le corpus source a été
arbitré par la MOA le 17/08/2026.
