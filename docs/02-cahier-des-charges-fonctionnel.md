# Cahier des charges fonctionnel (CDCF)

**Projet PEGASUS — Normalisateur des admissions** · ENS – PSL · CRI

> **Référentiel appliqué** : documents et fichiers **2026** > fichiers **2025** ;
> fichiers 2024 écartés comme obsolètes. Voir [README](README.md).
>
> **Décisions arbitrées par la MOA le 17/08/2026 :**
> - **H1** — « tous les primo arrivants suite aux concours sont inscrits au
>   produit d'année `ANDENS1` (NEMH, NEMS) » s'entend en **portée restreinte** :
>   NEMH et NEMS passent à `ANDENS1`, **la Sélection Internationale n'est pas
>   concernée** et conserve son produit programme déduit de la discipline.
> - **H3** — **`ENS_FINANCEMENT` est conservé** dans le canevas. Celui-ci
>   comporte donc **5 paires `Connaissance_fop_ins`** et **43 colonnes**.
> - **H6** — `ENS_FONCTIONNAIRE = NON` implique `ENS_BOURSE_ENS_PSL = OUI`.
>   Règle formalisée en **RG-01** (§5.9).
> - **H2 / H7** — résolues par l'écartement des gabarits 2024 : `Sexe` vaut `H`
>   pour les hommes, et l'en-tête `Connaissance_fop_ins 5 Type` ne porte pas
>   d'espace finale.
>
> ⚠️ Les fichiers `*_2024.csv` / `.xlsx` sont des **gabarits annotés** — leur
> deuxième ligne est un commentaire décrivant le contenu attendu de chaque
> colonne, pas une donnée. Ils ne font référence sur aucun point. Voir
> [README](README.md).

---

## 1. Acteurs

| Acteur | Rôle | Droits attendus |
|---|---|---|
| **Gestionnaire scolarité (CoST)** | Utilisateur principal. Récupère les listes d'admis, produit le canevas, l'importe dans PEGASUS | Dépôt, normalisation, téléchargement |
| **Gestionnaire concours (CoST)** | Fournit les listes d'admis et les intitulés de disciplines de l'année | — (hors outil) |
| **Gestionnaire DRI** | Traite les échanges internationaux entrants, deux fois par an | Dépôt, normalisation, téléchargement |
| **MOA scolarité et admissions (CRI)** | Détient les règles métier, arbitre les évolutions annuelles | Consultation, recette |
| **Développeur / exploitant (CRI)** | Maintient l'outil, met à jour les dictionnaires | Accès code et configuration |

## 2. Vue d'ensemble du processus

```
┌─ Plateforme de candidature ─┐
│ SCEI · EPONA · OnePSL30     │   export .xls / .xlsx
│ DEMATEC · MoveOn · DEC      │──────────────┐
└─────────────────────────────┘              │
                                             ▼
                            ┌────────────────────────────────┐
                            │  NORMALISATEUR (périmètre)     │
                            │  1. Dépôt du fichier           │
                            │  2. Choix population + cursus  │
                            │  3. Contrôle et déduction      │
                            │  4. Génération du canevas CSV  │
                            └────────────────┬───────────────┘
                                             │ canevas .csv conforme
                                             ▼
                            ┌────────────────────────────────┐
                            │  PEGASUS / Phénix (hors péri.) │
                            │  Import manuel par le CoST     │
                            └────────────────┬───────────────┘
                                             ▼
                     Portail étudiant (nuit) → SI ENS → Annuaire → SRH
```

---

## 3. Fonctionnalités

### F1 — Dépôt du fichier source

| | |
|---|---|
| **Description** | L'utilisateur dépose un export brut de plateforme, sans retouche préalable |
| **Modalités** | Glisser-déposer sur la zone dédiée, ou sélection via l'explorateur de fichiers |
| **Formats acceptés** | `.xls` (Excel 97-2003) et `.xlsx` (Excel 2007+) |
| **Contrôles** | Extension et type MIME côté navigateur ; type MIME et taille côté serveur |
| **Règles** | Seule la première feuille est exploitée. La première ligne porte les en-têtes |
| **Rejets** | Format non supporté → notification non bloquante, le fichier n'est pas transmis |

**Exigences complémentaires :**

- **F1.1** — Une taille maximale doit être appliquée et annoncée à l'utilisateur.
- **F1.2** — Les colonnes portant un libellé identique doivent être désambiguïsées
  automatiquement plutôt que provoquer un écrasement silencieux.
- **F1.3** — Un fichier dépassant la capacité de traitement doit être **refusé
  explicitement**, jamais tronqué en silence.

### F2 — Sélection de la population et du cursus

| | |
|---|---|
| **Description** | L'utilisateur déclare la population, ce qui détermine le jeu de règles appliqué |
| **Niveau 1** | Type d'étudiant : `DENS`, `DRI`, `Agrégation` |
| **Niveau 2** | Cursus, affiché dynamiquement et uniquement pour `DENS` |
| **Règles** | `DRI` et `Agrégation` ne comportent pas de sous-cursus. Les cursus non encore livrés sont visibles mais désactivés, avec la mention « À venir » |

**Arborescence des cursus DENS :**

| Groupe | Cursus | Code | État |
|---|---|---|---|
| CPGE | SCEI (Sciences) | `scei` | ✅ Livré |
| CPGE | A/L (Lettres) | `al` | ✅ Livré |
| CPGE | B/L (Lettres et sciences sociales) | `bl` | ✅ Livré |
| Sélection Internationale | Lettres | `sil` | ✅ Livré |
| Sélection Internationale | Sciences | `sis` | ✅ Livré |
| Normalien étudiant | NEMH (Médecine – Humanités) | `nemh` | ✅ Livré |
| Normalien étudiant | NEMS (Médecine – Sciences) | `nems` | ✅ Livré |
| Normalien étudiant | NEL (Lettres) | `nel` | ⏳ À venir |
| Normalien étudiant | NES (Sciences) | `nes` | ⏳ À venir |
| FrontCog | Frontiers of cognition and neuroscience | `frontcog` | ⏳ À venir |
| Olympiades | Bourses Olympiques | `olympiades` | ⏳ À venir |

### F3 — Saisie de l'année de campagne

| | |
|---|---|
| **Description** | L'utilisateur confirme ou corrige l'année de la campagne d'inscription |
| **Valeur par défaut** | Année civile courante |
| **Justification** | Les imports DRI de décembre concernent la rentrée de janvier de l'année suivante. Les lauréats des Bourses Olympiques d'une année antérieure intègrent le DENS avec la promo de l'année d'entrée effective |
| **Portée** | Alimente `Année`, `No Année` et la connaissance `ENS_PROMO` |

> Cette fonctionnalité n'est pas implémentée à ce jour : l'année est déduite de
> l'horloge système. Voir §10.

### F4 — Filtrage des candidats à importer

| | |
|---|---|
| **Description** | Seuls les candidats effectivement admis **et** ayant confirmé leur venue sont retenus |
| **Criticité** | 🔴 **Majeure** — le fichier `Coordonnées_Admis_LP_SIS_2026.xlsx` comporte **29 lignes `NON-ADMIS` sur 39** |

**Colonnes de filtrage par cursus (fichiers 2026) :**

| Cursus | Colonne | Valeurs à conserver | Valeurs à exclure |
|---|---|---|---|
| SI-Sciences | `LP/LC` | `ADMIS, LP`, `ADMIS,LC` | `NON-ADMIS` |
| SI-Lettres | `Rang` / `Confirmation venue` | `ADMIS` / `OUI` | tout autre |
| NEMH | `État`, `CONFIRMATION`, `DESISTEMENT` | `LP` + confirmation renseignée | désistement renseigné |
| NEMS | `État`, `Confirmation`, `Désistement` | `Admis`, `Admis sur LC` + confirmation `1` | désistement renseigné |
| SCEI | *(implicite)* | Le fichier des intégrants ne contient que les admis ayant confirmé | — |

**Règles complémentaires :**

- **F4.1** — Pour la SI-Sciences, la liste complémentaire est à importer :
  « pour le SI-S les 10 sont à importer, donc même ceux sur liste complémentaire ».
- **F4.2** — Le nombre de lignes retenues et le nombre de lignes écartées doivent
  être restitués à l'utilisateur avant génération.

> Cette fonctionnalité n'est pas implémentée à ce jour. Voir §10.

### F5 — Contrôle de cohérence du fichier

| Contrôle | Déclencheur | Portée | Comportement |
|---|---|---|---|
| **Fichier inadapté au cursus** | Colonne attendue absente de l'en-tête | Globale | Arrêt immédiat, message unique invitant à vérifier le couple fichier/cursus |
| **Champ obligatoire vide** | Cellule requise vide | Ligne | Cumul des erreurs, numéro de ligne Excel réel |
| **Format de date invalide** | Date non interprétable | Ligne | Cumul, avec la valeur brute reçue |
| **Correspondance introuvable** | Profil ou concours sans équivalent PEGASUS | Ligne | Cumul, avec la valeur cherchée |
| **Civilité non reconnue** | Valeur hors nomenclature (`Autre`, vide, saisie libre) | Ligne | Cumul — **ne jamais appliquer de valeur par défaut** |

**Règle d'or : aucun canevas n'est produit tant qu'une anomalie subsiste.**
Un fichier partiellement correct n'est pas exploitable — il conduirait à un
import incomplet dont la reprise est plus coûteuse que la correction en amont.

**Champs obligatoires par cursus :**

| Cursus | Champs obligatoires |
|---|---|
| SCEI | Nom, Prénom, Civilité, Date de naissance, Libellé du concours, Email personnel, INE |
| A/L | Nom, Prénom, Civilité, Date de naissance, Email personnel |
| B/L | Nom, Prénom, Date de naissance, Email personnel |
| SI-L / SI-S | Nom, Prénom, Date de naissance, Nationalité, Email |
| NEMH / NEMS | Nom, Prénom, Date de naissance, Nationalité, Adresse email |
| DRI | Nom, Prénom, Courriel, Diplôme d'échange |

### F6 — Normalisation et déduction métier

Cœur fonctionnel de l'outil. Détaillé au §5.

### F7 — Restitution des anomalies

| | |
|---|---|
| **Description** | Liste exhaustive des lignes en anomalie, exploitable pour corriger le fichier source |
| **Format** | Une entrée par anomalie : `Ligne <n> : <message>` |
| **Numérotation** | Numéro de ligne **du fichier Excel** tel que vu par l'utilisateur, en-tête comprise |
| **Export** | Rapport texte téléchargeable, horodaté |
| **Ergonomie** | Possibilité de corriger le fichier puis de relancer sans repartir de zéro |

### F8 — Génération du canevas

| | |
|---|---|
| **Description** | Production du fichier CSV conforme aux règles PEGASUS |
| **Nommage** | `import_<cursus>_<AAAAMMJJ>_<HHMMSS>.csv` — sans caractère accentué |
| **Structure** | Conforme au canevas de référence de la campagne (§6) |
| **Déclenchement** | Uniquement si aucune anomalie n'a été détectée et qu'au moins une ligne est retenue |

### F9 — Téléchargement et purge

| | |
|---|---|
| **Description** | Récupération du canevas par l'utilisateur |
| **Sécurité** | Le fichier ne doit être accessible qu'à l'utilisateur qui l'a produit |
| **Purge** | Suppression après téléchargement, et purge automatique des fichiers résiduels au-delà d'une heure |
| **Justification** | Le fichier contient des données personnelles de candidats |

---

## 4. Cas d'usage

### UC-01 — Normaliser une liste d'admis CPGE Sciences (SCEI)

- **Acteur** : gestionnaire scolarité CoST
- **Fréquence** : une fois par an, en août
- **Préconditions** : export « Données intégrants » récupéré sur `gestion.scei-concours.fr` ; produits Année créés dans PEGASUS pour l'année visée ; paramétrages de tarification effectués

| # | Acteur | Système |
|---|---|---|
| 1 | Dépose l'export SCEI | Contrôle le format, ouvre la fenêtre de normalisation |
| 2 | Choisit `DENS` puis `SCEI` | Affiche le sélecteur de cursus |
| 3 | Confirme l'année de campagne | Pré-remplit l'année courante |
| 4 | Lance la normalisation | Contrôle les colonnes, déduit statut, concours, financement |
| 5 | — | Produit le canevas et propose le téléchargement |
| 6 | Télécharge, vérifie, importe dans PEGASUS | Purge le fichier temporaire |

**Alternatives :**
- *3a. Fichier d'un autre cursus* → arrêt immédiat, message d'orientation.
- *4a. Lignes incomplètes* → rapport d'anomalies, aucun canevas produit.
- *4b. Libellé de concours inconnu* → anomalie mentionnant le libellé exact, à faire arbitrer par le pôle Concours.

### UC-02 — Normaliser une liste d'admis B/L

Identique à UC-01, avec deux spécificités :

- Le fichier résulte d'une fusion opérée par le CoST entre le fichier des
  classés et le fichier des inscrits SCEI, seule source des informations de naissance.
- **Le fichier B/L 2026 ne comporte pas de colonne `nationalité`.** Or c'est la
  nationalité qui détermine le statut de fonctionnaire. Le système **doit
  refuser le fichier** avec un message explicite plutôt que de basculer
  l'ensemble de la promotion en non-fonctionnaire (§10, [C8]).

### UC-03 — Normaliser une liste de Sélection Internationale

- **Fréquence** : une fois par an, en juillet
- **Spécificités** :
  - le produit programme se déduit de la colonne `Profil` (§5.5) ;
  - deux variantes de fichier circulent — extraction brute et fichier retravaillé
    par le CoST — dont les en-têtes diffèrent (§5.2) ;
  - la liste complémentaire est à importer pour la SI-Sciences (F4.1) ;
  - tous les admis SI sont non-fonctionnaires et boursiers ENS.

### UC-04 — Normaliser une liste NEMH / NEMS

- **Spécificités** :
  - fichiers OnePSL30, très larges (jusqu'à 99 colonnes) dont la grande majorité
    ne concerne pas l'import ;
  - nom d'usage prioritaire sur le nom d'état civil, ce dernier étant conservé
    en connaissance s'il diffère ;
  - produit programme `ANDENS1` (décision H1) ;
  - **la valeur `Autre` existe dans la colonne `Genre`** et doit être traitée
    comme une anomalie tant que la valeur PEGASUS attendue n'est pas arbitrée.

### UC-05 — Normaliser une liste d'échanges internationaux (DRI)

- **Acteur** : gestionnaire DRI
- **Fréquence** : deux fois par an — été pour septembre, décembre pour janvier
- **Spécificités** :
  - source : export MoveOn ;
  - produit programme constant `ANECHINTER` ;
  - statut selon le type d'échange (§5.7) ;
  - **connaissances spécifiques obligatoires** : contact d'urgence, téléphone
    d'urgence, portable de l'étudiant, département de rattachement ;
  - **`ENS_PROMO`, `ENS_FONCTIONNAIRE` et `ENS_CONCOURS` ne doivent pas être
    renseignées** — les renseigner pour une population non normalienne fausse l'annuaire ;
  - adresse personnelle obligatoire ;
  - translittération des caractères non latins, PEGASUS gérant mal les accents étrangers.

### UC-06 — Corriger un fichier rejeté

| # | Acteur | Système |
|---|---|---|
| 1 | Lance la normalisation | Détecte des anomalies, les liste |
| 2 | Télécharge le rapport | Produit un fichier texte horodaté |
| 3 | Corrige le fichier source | — |
| 4 | Relance | Retraite le fichier corrigé |

---

## 5. Règles de gestion

### 5.1 Nomenclature des populations

| Terme | Signification |
|---|---|
| **IA** | Inscription administrative |
| **IP** | Inscription pédagogique |
| **Normalien** | Étudiant inscrit au DENS |
| **DENS** | Diplôme de l'École normale supérieure |
| **Élève** | Normalien issu d'un concours CPGE — scolarité de 4 ans |
| **BIS** | Admis CPGE non fonctionnaire, boursier ENS, classé en rang *bis* |
| **LP / LC** | Liste principale / liste complémentaire |
| **`ANDxxx1`** | Code produit : `A`nnée · e`N`s · `D`ENS · `xxx` département · `1` première année |
| **`ANDENS1`** | Formation fictive d'attente, avant choix du département |

### 5.2 Correspondance des colonnes d'entrée (fichiers 2026)

| Donnée | SCEI / B/L | SI-L / SI-S (extraction) | SI (coordonnées CoST) | NEMH / NEMS |
|---|---|---|---|---|
| Civilité | `Civ _lib` | `Civilité` | `Civilité` | `Genre` |
| Nom | `Nom` | `Nom` | `NOM` ⚠️ | `Nom` |
| Nom d'usage | — | — | — | `Nom d'usage` |
| Prénom | `Prenom` | `Prénom` | `Prénom` | `Prénom` |
| Date de naissance | `Can _nai` / `ddn` | `naissance_date` | `Date de naissance` ⚠️ | `Date de naissance` |
| Ville de naissance | `Can _com _nai` / `Ville de naissance` | `naissance_ville` | `naissance_ville` | — |
| Pays de naissance | `Can _pay _nai` / `Pays de naissance` | `naissance_pays` | `naissance_pays` | `Pays de naissance` |
| Nationalité | `Can _pay _nat` / *absente en B/L 2026* ⚠️ | `nationalite` | `Nationalité` ⚠️ | `Nationalité` |
| Email personnel | `Can _mel` | `Email` | `Email` | `Adresse email` |
| Concours | `Con _lib` | — | — | `Concours` |
| Discipline | — | `Profil` | `Profil` | — |
| INE | `Can _ine` / `INE` | — | — | — |

⚠️ **Écarts d'en-têtes entre variantes d'un même cursus.** L'outil doit accepter
les deux variantes de fichier SI-Lettres qui circulent, par tolérance sur la
casse et par gestion d'alias de colonnes.

### 5.3 Détermination du statut « fonctionnaire »

Règle transverse : le statut détermine à la fois le tarif d'inscription, la
phase professionnelle et le mode de financement.

| Cursus | Règle | Source |
|---|---|---|
| **SCEI** | Le libellé `Con _lib` porte la mention `NON FONCTIONNAIRE` → non-fonctionnaire. Sinon fonctionnaire | Document 2026 |
| **A/L** | Deux colonnes de nationalité (`can_pay_nat`, `can_pay_nat_2`). Si **l'une des deux** est française ou d'un pays fonctionnarisable → fonctionnaire | Mail 22/05/2026 |
| **B/L** | Colonne `FRANÇAIS` (1 = français, 0 = étranger) ; si 0, examen de la nationalité | Mail 22/05/2026 |
| **SI-L / SI-S** | Jamais fonctionnaires — boursiers ENS | Mail 17/07/2026 |
| **NEMH / NEMS** | Jamais fonctionnaires — boursiers ENS | Code + canevas 2025 |
| **DRI** | Sans objet — la connaissance ne doit pas être renseignée | Présentation, slide 28 |

**Pays ouvrant droit au statut de fonctionnaire** : France, États membres de
l'Union européenne, **Suisse, Andorre, Monaco**.

**Cas des binationaux** — arbitrage du 17/07/2026 :

> « Tous les élèves fonctionnaires sont de nationalité française ; pour les
> étudiants binationaux également, nous devons indiquer leur nationalité
> française afin que leur statut de fonctionnaire soit bien pris en compte. »

La nationalité ouvrant droit au statut prime donc et devient la nationalité
principale portée au dossier.

### 5.4 Codes concours

Résolus dynamiquement depuis l'annuaire Oracle Jefyco (§CDCT), et non codés en dur.

| Famille | Codes |
|---|---|
| CPGE | `C-AL`, `C-BL`, `C-BCPST`, `C-MP`, `C-PC`, `C-PSI` |
| Sélection Internationale | `SI-L`, `SI-S` |
| Normaliens étudiants | `NEL`, `NES`, `NEMH`, `NEMS` |
| FrontCog | `FrontCog` — **seule valeur dont la casse mixte est volontaire** |

**Supprimés en 2025, ne doivent plus apparaître** : `C-MPI`, `INFO`.

**Règle de résolution** : la correspondance doit se faire par **mot entier** et,
en cas de préfixes communs, retenir le code le plus long. `MP` étant une
sous-chaîne de `MPI`, et `SI` de `PSI`, une comparaison par simple inclusion
produit des affectations erronées.

### 5.5 Produit programme

| Population | Produit programme | Source |
|---|---|---|
| CPGE (SCEI, A/L, B/L) | `ANDENS1` | Le département est choisi après la rentrée |
| **NEMH** | `ANDENS1` | **Document 2026** — annule « département indiqué par le responsable de concours » |
| **NEMS** | `ANDENS1` | **Document 2026** — annule `ANDBIO1` du CR 2025 |
| SI-Lettres, SI-Sciences | `ANDxxx1` selon la discipline (décision H1) | Canevas 2025 |
| FrontCog | `ANDDEC1` | Mail 17/03/2026 |
| Bourses Olympiques | `ANDDMA1` (DENS Mathématiques) | Présentation, slide 5 |
| DRI | `ANECHINTER` | Présentation, slide 27 |

**Correspondance discipline → produit, SI-Lettres (valeurs 2026) :**

| Profil | Produit | Remarque |
|---|---|---|
| `Economie` | `ANDECO1` | |
| `Histoire de l'Art` | `ANDART1` | **doit être testé avant `Histoire`** |
| `Histoire` | `ANDHIS1` | |
| `Littératures` | `ANDLIT1` | |
| `Philosophie` | `ANDPHI1` | |
| `Sociologie` | `ANDDSS1` | |
| Linguistique / sciences des langages | `ANDDEC1` | Mail 17/03/2026 |
| Études classiques, archéologie, sciences de l'Antiquité | `ANDDSA1` | |
| Histoire et philosophie du droit | `ANDDSS1` | Mail 17/03/2026 |

**Correspondance discipline → produit, SI-Sciences (valeurs 2026, en anglais) :**

| Profil | Produit |
|---|---|
| `Mathematics` | `ANDDMA1` |
| `Physics` | `ANDPHY1` |
| `Chemistry` | `ANDCHI1` |
| `Earth sciences` | `ANDGSC1` |
| `Cognitive sciences` | `ANDDEC1` |
| `Biology` | `ANDBIO1` |
| `Computing sciences` | `ANDINF1` |

**Règles de comparaison** : sans tenir compte des accents ni de la casse — les
intitulés varient d'une année sur l'autre et l'accentuation est inconstante.
Une discipline non reconnue doit produire une anomalie explicite, jamais un
code par défaut.

### 5.6 Phase professionnelle (`Statut Etudiant`)

| Population | Valeur |
|---|---|
| Normalien fonctionnaire | `ENS-DENS FCTIONNAIRE` |
| Normalien non fonctionnaire | `ENS-DENS ETUDIANT` |
| Échange Erasmus | `ENS-DRI ECH ERASMUS` |
| Pensionnaire étranger | `ENS-DRI PENS ETRG` |
| Cursus L/M | `ENS-EXT` |

Seules les phases commençant par `ENS-` sont utilisables.

### 5.7 Normalisation de l'identité

| Champ | Règle | Précision |
|---|---|---|
| `Nom` | Majuscules | Traitement **multi-octets obligatoire** : `Müller` → `MULLER`, jamais `MüLLER` |
| `Prénom` | Initiale majuscule, reste en minuscules | `JOSÉ` → `José`. Un seul prénom, ou un seul prénom composé |
| `Genre` | `Monsieur` ou `Madame` | |
| `Sexe` | `H` ou `F` (décision H2) | |
| Nom d'usage | Prioritaire sur le nom d'état civil | Le nom d'état civil est porté en connaissance `NOM_ETAT_CIVIL` s'il diffère |
| Caractères non latins | Translittérés | Applicable à la DRI ; l'original est conservé en connaissance |
| Villes, pays, nationalités | Majuscules si renseignés | |

**Civilités rencontrées et normalisation attendue :**

| Valeur source | Cursus | → Genre / Sexe |
|---|---|---|
| `M`, `M.`, `Homme` | SCEI, B/L, SI, NEMH, NEMS | `Monsieur` / `H` |
| `Mme`, `Mm`, `Femme` | SCEI, B/L, SI, NEMH, NEMS | `Madame` / `F` |
| **`Autre`** | NEMS 2026 | ⚠️ **Anomalie** — valeur PEGASUS à arbitrer (H5) |
| vide ou non reconnue | — | ⚠️ **Anomalie** — jamais de valeur par défaut |

### 5.8 Nationalités

- Les fichiers 2026 portent des **noms de pays** (`Allemagne`, `Chine`,
  `Fédération Russe`), non des adjectifs.
- Les fichiers plus anciens et certains flux portent des adjectifs
  (`égyptienne`) : les deux formes doivent être acceptées et converties en pays.
- Variantes à normaliser : `Les Etats Unis d'Amérique` → `ETATS-UNIS`,
  `Fédération Russe` → `RUSSIE`.
- Toute valeur contenant `franco` désigne la France.
- Champ vide en SCEI : à interpréter comme France.

### 5.9 Financement et connaissances de formation

| Connaissance | Valeur attendue | Portée |
|---|---|---|
| `ENS_SITUATION_CST` (congé sans traitement) | `OUI` / `NON` | Normaliens uniquement, vide sinon |
| `ENS_SITUATION_CSB` (congé sans bourse) | `OUI` / `NON` | Normaliens uniquement, vide sinon |
| `ENS_MODE_PEDAGOGIQUE` | `EN SCOLARITE` pour la nouvelle promotion | Réservé DENS. Les étalements sont corrigés manuellement par le CoST |
| `ENS_BOURSE_ENS_PSL` | `OUI` / `NON` | Normaliens uniquement, vide sinon |
| `ENS_FINANCEMENT` | `TRAITEMENT`, `BOURSE ENS` ou `NC.` | **Conservé** (décision H3). Réservé à l'inscription DENS. Le point de `NC.` est obligatoire |

**Valeurs attendues par population** — toutes constatées dans les canevas de
référence 2025, sauf mention contraire :

| Population | `CST` | `CSB` | `MODE_PEDAGOGIQUE` | `BOURSE_ENS_PSL` | `FINANCEMENT` |
|---|---|---|---|---|---|
| CPGE fonctionnaire (SCEI, A/L, B/L) | `NON` | `NON` | `EN SCOLARITE` | `NON` | `TRAITEMENT` |
| CPGE non fonctionnaire (« BIS ») | `NON` | `NON` | `EN SCOLARITE` | `OUI` | `BOURSE ENS` |
| SI-Lettres, SI-Sciences | `NON` | `NON` | `EN SCOLARITE` | `OUI` | `BOURSE ENS` |
| NEMH, NEMS | `NON` | `NON` | `EN SCOLARITE` | `OUI` | `BOURSE ENS` |
| DRI | *(vide)* | *(vide)* | *(vide)* | *(vide)* | *(vide)* — réservé DENS |

> ⚠️ **`NON` et non une chaîne vide.** Les quatre premières connaissances doivent
> porter la valeur `NON` explicite pour un normalien. Une valeur vide **écrase la
> donnée existante** dans PEGASUS lors d'un réimport.

**RG-01 — Invariant de cohérence statut / financement.** Le statut de
fonctionnaire détermine entièrement les deux connaissances de financement :

```
ENS_FONCTIONNAIRE = OUI  ⟹  ENS_BOURSE_ENS_PSL = NON  ∧  ENS_FINANCEMENT = TRAITEMENT
ENS_FONCTIONNAIRE = NON  ⟹  ENS_BOURSE_ENS_PSL = OUI  ∧  ENS_FINANCEMENT = BOURSE ENS
```

Un admis non fonctionnaire perçoit une bourse de l'ENS : les deux informations
ne peuvent donc jamais diverger. Cet invariant a été vérifié sur les
**142 lignes** des six canevas de référence 2025 — 97 fonctionnaires et
45 boursiers, **aucune violation**.

Il doit être implémenté comme un **contrôle de cohérence bloquant** en sortie :
toute ligne qui le viole révèle un défaut de la chaîne de déduction et ne doit
pas être écrite dans le canevas.

### 5.10 Numérotation des lots

| Champ | Règle |
|---|---|
| `Date_Lot` | Date du jour de l'import, `AAAAMMJJ`. Un nouvel import impose une nouvelle date, faute de quoi les données sont écrasées |
| `No_Lot` | Incrément à partir de 0, **sans rupture ni saut**, pour chaque ligne `da` |
| `No_Ssl` | `0` pour une ligne `da`, puis incrément pour chaque ligne `cv` du même lot |
| `Type_occ` | `da` = création du dossier et de l'IA ; `cv` = IA supplémentaire sur un dossier existant |
| `Session` | Toujours `1` |
| `Recrutement` | Toujours vide |
| `EOL` | Toujours `EOL` |

Dans le périmètre actuel, toutes les populations sont en `da` : `No_Ssl` vaut
donc systématiquement `0`.

---

## 6. Structure du canevas de sortie

**Référence : canevas 2025 augmenté de `ENS_FINANCEMENT` (décision H3) — 43 colonnes.**
Les canevas 2024 sont obsolètes et ne doivent plus servir de modèle : ils
comptaient certes 43 colonnes, mais réparties différemment — une seule paire
`Connaissance_fop_ins` (`ENS_FINANCEMENT`), et un bloc `Situation familiale` /
`Code INSEE` / `Courrier *` qui a depuis disparu.

| # | Colonne | Contenu |
|---|---|---|
| 1 | `Date_Lot` | `AAAAMMJJ` |
| 2 | `No_Lot` | Incrément à partir de 0 |
| 3 | `No_Ssl` | `0` |
| 4 | `Type_occ` | `da` |
| 5 | `Recrutement` | *(vide)* |
| 6 | `Année` | `AAAA` |
| 7 | `Produit Programme` | Code produit année |
| 8 | `No Année` | `AAAA` |
| 9 | `Session` | `1` |
| 10 | `Statut Etudiant` | Phase professionnelle |
| 11 | `Genre` | `Monsieur` / `Madame` |
| 12 | `Nom` | Majuscules |
| 13 | `Prénom` | Initiale majuscule |
| 14 | `Sexe` | `H` / `F` |
| 15-16 | `Connaissance 2 Type` / `Valeur` | `EMAIL PERSONNEL` — obligatoire, sert à la première authentification |
| 17-18 | `Connaissance 3` | `EMAIL ECOLE` — vide en création, alimenté par synchronisation |
| 19-20 | `Connaissance 4` | `NUMERO_ETU_PSLR` — vide en création, obligatoire pour créer le portail |
| 21-22 | `Connaissance 5` | `ENS_NO_INDIVIDU` — vide en création |
| 23-24 | `Connaissance 6` | `ENS_PROMO` — année d'admission, `AAAA` |
| 25-26 | `Connaissance 7` | `ENS_FONCTIONNAIRE` — `OUI` / `NON` |
| 27-28 | `Connaissance 8` | `ENS_CONCOURS` — code concours |
| 29-30 | `Connaissance_fop_ins 1` | `ENS_SITUATION_CST` |
| 31-32 | `Connaissance_fop_ins 2` | `ENS_SITUATION_CSB` |
| 33-34 | `Connaissance_fop_ins 3` | `ENS_MODE_PEDAGOGIQUE` |
| 35-36 | `Connaissance_fop_ins 4` | `ENS_BOURSE_ENS_PSL` |
| 37-38 | `Connaissance_fop_ins 5` | `ENS_FINANCEMENT` — `TRAITEMENT`, `BOURSE ENS` ou `NC.` |
| 39 | `Ville de Naissance` | Majuscules — obligatoire pour les fonctionnaires |
| 40 | `Date de Naissance` | `JJ/MM/AAAA` |
| 41 | `Pays de Naissance` | Majuscules |
| 42 | `Nationalité Principale` | Majuscules — **libellé exact, `Principale` et non `Principal`** |
| 43 | `EOL` | `EOL` |

**Règles de forme impératives :**

- séparateur `;` ; encodage ISO-8859-1 ; fin de ligne `CRLF` ;
- libellés de colonnes non modifiables, **espaces compris**, casse respectée ;
- ordre des colonnes non modifiable ;
- aucune ligne vide en fin de fichier ;
- nom de fichier sans caractère accentué ;
- une colonne **présente mais vide écrase la donnée existante** dans PEGASUS :
  ne produire que les colonnes effectivement renseignées.

**Colonnes supplémentaires pour la DRI** — connaissances `URGENCE PERSONNE`,
`URGENCE TELEPHONE`, `PORTABLE`, `ENS_DPT_RATT_ETU_ECHAN`, plus l'adresse
personnelle complète. `ENS_PROMO`, `ENS_FONCTIONNAIRE` et `ENS_CONCOURS` sont
en revanche **à supprimer**.

**Départements de rattachement DRI** (`ENS_DPT_RATT_ETU_ECHAN`, en majuscules) :
ARTS, BIOLOGIE, CHIMIE, ECLA, ECONOMIE, ETUDES COGNITIVES, GEOSCIENCES,
HISTOIRE, INFORMATIQUE, LITTERATURES ET LANGAGE, MATHEMATIQUES ET APPLICATIONS,
PHILOSOPHIE, PHYSIQUE, SCIENCES DE L'ANTIQUITE, SCIENCES SOCIALES.

---

## 7. Parcours utilisateur

```
   Page d'accueil
        │
        ├─ Dépôt fichier (glisser-déposer / sélection)
        │        │
        │        ├─ format invalide ──► notification, retour au dépôt
        │        ▼
   Fenêtre de normalisation
        │
        ├─ Type d'étudiant  ──► si DENS : sélecteur de cursus
        ├─ Année de campagne (pré-remplie)
        │
        ├─ « Démarrer »
        │        │
        │        ├─ sélection incomplète ──► notification, pas d'envoi
        │        ▼
        │   Traitement (indicateur de progression)
        │        │
        │        ├─ mauvais fichier ──► message unique d'orientation
        │        │                         └─► « Réessayer » / « Annuler »
        │        │
        │        ├─ anomalies ──► liste ligne à ligne
        │        │                  ├─► rapport texte téléchargeable
        │        │                  └─► correction hors outil puis relance
        │        ▼
        │   Succès : nom du canevas + nombre de lignes retenues / écartées
        │        │
        │        └─► Téléchargement ──► purge serveur
        ▼
   Import manuel dans PEGASUS (hors périmètre)
```

---

## 8. Exigences non fonctionnelles

| Domaine | Exigence |
|---|---|
| **Performance** | Traitement d'un fichier de 400 lignes en moins de 10 secondes |
| **Capacité** | Refus explicite au-delà de la capacité de traitement, jamais de troncature |
| **Disponibilité** | Usage saisonnier ; aucune exigence de haute disponibilité |
| **Sécurité** | Authentification obligatoire ; accès restreint aux agents CoST et DRI |
| **Confidentialité** | Un canevas n'est accessible qu'à son producteur ; purge automatique |
| **Traçabilité** | Journalisation de chaque import : agent, population, volumétrie, horodatage |
| **Accessibilité** | Conformité RGAA visée : navigation clavier complète, restitution des messages aux lecteurs d'écran, contrastes suffisants |
| **Compatibilité** | Navigateurs du parc ENS, versions courantes |
| **Autonomie** | Aucune dépendance à un service externe : polices et icônes hébergées localement |
| **Langue** | Interface et messages intégralement en français |

---

## 9. Évolutions prévues

| Évolution | Échéance | Impact |
|---|---|---|
| Cursus NEL et NES | Prochaine campagne | Format d'export identique aux NEMH/NEMS (même plateforme OnePSL30) |
| FrontCog | À planifier | Liste minimale fournie par le DEC ; produit `ANDDEC1` |
| Bourses Olympiques | À planifier | Produit `ANDDMA1` ; promo = année d'entrée effective, pas année de réussite |
| Préparation à l'agrégation | À planifier | Source DEMATEC ; distinguer normaliens et non-normaliens pour le tarif |
| Migration SI vers OnePSL30 | Promo 2027 | Nouveau format d'entrée pour SI-L et SI-S |
| Réinscriptions au DENS | Non arbitré | Passage d'année ou export/réimport, selon décision CoST/PSL |

---

## 10. Écarts entre le CDCF et l'implémentation

Constats issus de la revue de code, à traiter avant la prochaine campagne.

| Réf. | Écart | Gravité |
|---|---|---|
| **C1** | Le canevas produit comporte **53 colonnes au lieu de 43** : connaissance `PROMO` au lieu de `ENS_PROMO` ; libellé `Nationalité Principal` au lieu de `Principale` ; paire `NUMERO_INE` (et, selon les cursus, `NOM_ETAT_CIVIL` / `PRENOM_ETAT_CIVIL`) en trop ; bloc `Situation familiale` / `Code INSEE` / `Courrier *` en trop. *`ENS_FINANCEMENT` est en revanche correct depuis l'arbitrage H3* | 🔴 |
| **C1b** | `SceiStrategy` ne produit **qu'une seule** paire `Connaissance_fop_ins` (`ENS_FINANCEMENT`) : `ENS_SITUATION_CST`, `ENS_SITUATION_CSB`, `ENS_MODE_PEDAGOGIQUE` et `ENS_BOURSE_ENS_PSL` sont absentes du canevas SCEI | 🔴 |
| **C1c** | `AlStrategy`, `SiLettreStrategy` et `SiScienceStrategy` émettent une **chaîne vide** pour `ENS_SITUATION_CST` et `ENS_SITUATION_CSB` (et `ENS_BOURSE_ENS_PSL` pour l'A/L) là où les canevas de référence portent `NON`. Une valeur vide écrase la donnée existante lors d'un réimport | 🔴 |
| **C1d** | `DriStrategy` renseigne `ENS_MODE_PEDAGOGIQUE` et `ENS_FINANCEMENT`, réservés à l'inscription DENS et qui doivent rester vides pour cette population | 🟠 |
| **C2** | Normalisation de casse non multi-octets : `Müller` → `MüLLER`, `JOSÉ` → `JosÉ` | 🔴 |
| **C3** | Table de translittération DRI désalignée : `MÜLLER` → `MYLLER`, `MUÑOZ` → `MUSOZ` | 🔴 |
| **C4** | Fichier `Blstrategy.php` incompatible PSR-4 : erreur fatale sur tout import B/L en environnement Linux | 🔴 |
| **C5** | Identifiants Oracle en clair dans le dépôt Git | 🔴 |
| **C6** | Aucun filtrage des non-admis (F4) : 29 `NON-ADMIS` sur 39 lignes dans le fichier SI-S 2026 | 🔴 |
| **C7** | `Genre = 'Autre'` basculé silencieusement en `Monsieur` / `M` | 🔴 |
| **C8** | Colonne `nationalité` absente du fichier B/L 2026 : toute la promotion bascule en non-fonctionnaire sans alerte | 🔴 |
| **M1** | Règles DRI non appliquées : `ENS_PROMO` et `ENS_FONCTIONNAIRE` renseignées à tort, connaissances d'urgence absentes | 🟠 |
| **M2** | Résolution du code concours par inclusion de chaîne : `MP` peut être retenu pour `MPI` | 🟠 |
| **M3** | Année déduite de l'horloge système au lieu d'être saisie (F3) | 🟠 |
| **M4** | Troncature silencieuse au-delà de 2 000 lignes (F1.3) | 🟠 |
| **M5** | Ni authentification, ni protection CSRF, ni purge des fichiers temporaires | 🟠 |
| **M6** | Variante `COORDONNEES_ADMIS_LP_SIL 2026.xlsx` rejetée faute de tolérance sur les en-têtes (§5.2) | 🟠 |
| **M7** | `Sexe` produit à `M` alors que les six canevas de référence 2025 portent `H` sans exception (décision H2) | 🟠 |
| **M8** | Civilité non reconnue basculée par défaut en `Monsieur` (§5.7) | 🟠 |
