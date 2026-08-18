# Mode d'emploi — Normalisateur des admissions PEGASUS

**À l'attention des gestionnaires du CoST et de la DRI** · ENS – PSL

---

## À quoi sert cet outil ?

Chaque rentrée, vous devez créer dans PEGASUS les dossiers des nouveaux
étudiants à partir des listes d'admis fournies par les plateformes de
candidature. PEGASUS n'accepte ces listes que sous la forme d'un fichier CSV
au format très strict — le « canevas d'import » — dont la fabrication à la main
est longue et risquée.

**Le normalisateur fait cette fabrication pour vous.** Vous déposez le fichier
tel que vous l'avez reçu de la plateforme, vous indiquez de quelle population
il s'agit, et l'outil vous rend un canevas prêt à importer.

> **L'outil n'écrit jamais dans PEGASUS.** Il prépare un fichier ; c'est vous
> qui décidez de l'importer, après vérification. Vous gardez la main.

---

## Ce dont vous avez besoin

- Le fichier des admis, au format Excel (`.xls` ou `.xlsx`), **sans y avoir
  touché** — l'outil est conçu pour lire les exports bruts.
- De savoir de quelle population il s'agit (concours, cursus).
- Un accès à PEGASUS pour l'import final.

---

## Les quatre étapes

### Étape 1 — Déposer le fichier

Sur la page d'accueil, deux possibilités :

- **faire glisser** le fichier Excel depuis votre explorateur vers la zone en pointillés ;
- **cliquer** sur la zone ou sur le bouton « Sélectionnez un fichier », puis
  choisir le fichier.

> Vous pouvez aussi atteindre le bouton au clavier avec la touche `Tab`, puis
> l'activer avec `Entrée` ou la barre d'espace.

**Si un message « Format non supporté » apparaît**, c'est que le fichier n'est
pas un classeur Excel. Un `.csv`, un `.ods` ou un `.pdf` ne conviennent pas :
ouvrez le fichier dans Excel et enregistrez-le en `.xlsx`.

### Étape 2 — Indiquer la population

Une fenêtre s'ouvre en affichant le nom du fichier déposé.

**Choisissez d'abord le type d'étudiant :**

| Choix | Pour qui |
|---|---|
| **DENS** | Tous les normaliens : concours CPGE, Sélection Internationale, normaliens étudiants |
| **DRI** | Étudiants en échange international entrants (Erasmus, pensionnaires étrangers) |
| **Agrégation** | Admis à la préparation à l'agrégation |

**Si vous avez choisi DENS**, un second menu apparaît. Choisissez le cursus :

| Groupe | Cursus | Fichier attendu |
|---|---|---|
| CPGE | **SCEI** | Export « Données intégrants » du site SCEI |
| CPGE | **A/L (Lettres)** | Liste des admis EPONA, « report au format SCEI » |
| CPGE | **B/L** | Fichier issu de la fusion classés + inscrits SCEI |
| Sélection Internationale | **Lettres** / **Sciences** | Extraction des admis DEMATEC |
| Normalien étudiant | **NEMH** / **NEMS** | Export OnePSL30 |

> Certains cursus apparaissent en grisé avec la mention « À venir » : ils ne sont
> pas encore disponibles. Pour ces populations, la préparation reste manuelle.

Vérifiez enfin **l'année de campagne** proposée. Elle correspond à l'année
courante — corrigez-la si votre import concerne une autre rentrée, notamment
pour les échanges DRI de décembre qui portent sur la rentrée de janvier.

### Étape 3 — Lancer la normalisation

Cliquez sur **« Démarrer »**. Le traitement prend quelques secondes.

Trois issues possibles :

#### ✅ Tout s'est bien passé

L'outil affiche « Terminé ! », le nom du canevas produit, le nombre d'étudiants
retenus et, le cas échéant, le nombre de lignes écartées.

> **Vérifiez ces nombres avant de télécharger.** Les exports de plateforme
> contiennent souvent des non-admis, des listes complémentaires et des
> désistements : l'outil les écarte, mais c'est à vous de confirmer que le
> total correspond bien au nombre d'admis attendu.

Cliquez sur **« Télécharger »** pour récupérer le fichier.

> Le fichier est supprimé du serveur dès que vous l'avez téléchargé.
> **Enregistrez-le immédiatement** dans votre espace de travail habituel.

#### ⚠️ Le fichier ne correspond pas au cursus choisi

Un message vous indique quelle colonne attendue est introuvable, et vous demande
de vérifier votre sélection.

**Que faire :**
1. Vérifiez que le fichier déposé est bien celui de la population choisie
   — c'est l'erreur la plus fréquente.
2. Si le fichier est le bon, il se peut que la plateforme ait modifié ses
   en-têtes cette année : signalez-le au CRI en précisant le cursus, le nom du
   fichier et la colonne mentionnée dans le message.

#### ⚠️ Le fichier contient des données incomplètes ou invalides

L'outil liste **toutes** les lignes en anomalie, avec leur numéro de ligne dans
le fichier Excel. **Aucun canevas n'est produit** : un fichier partiellement
correct ne serait pas exploitable.

**Que faire :**
1. Cliquez sur **« Rapport TXT »** pour conserver la liste des anomalies.
2. Ouvrez votre fichier Excel et rendez-vous aux lignes indiquées — les numéros
   correspondent exactement à ceux affichés par Excel.
3. Corrigez, enregistrez.
4. Revenez sur l'outil et cliquez sur **« Réessayer »**, ou sur
   **« Remplacer le fichier »** pour déposer la version corrigée.

### Étape 4 — Importer dans PEGASUS

**Avant d'importer, vérifiez que :**

- les produits Année des formations existent dans PEGASUS pour l'année concernée ;
- les paramétrages de tarification ont été effectués ;
- les codes utilisés existent bien (concours, formations, phases professionnelles).

**Puis, dans PEGASUS :**

1. Menu `Inscription` → `Gérer l'inscription administrative`.
2. `Interface apprenant` → `Importation classique Étudiant par fichier`.
3. **Vérifiez que l'indice cible porte sur la bonne année.**
4. Sélectionnez le fichier CSV, chargez-le, puis **analysez-le**.
5. Si l'analyse est correcte, la liste des étudiants s'affiche.
6. Cliquez sur **« Mémorisation… »** pour lancer l'importation.
7. Une liste en rouge s'affiche dans l'onglet `imp_crm PROCESS` : vous pouvez la fermer.

> Le portail étudiant est créé dans la nuit qui suit l'import.

---

## Comprendre les messages d'anomalie

| Message | Signification | Ce qu'il faut faire |
|---|---|---|
| *« La colonne requise 'X' est introuvable dans l'en-tête »* | Le fichier ne correspond pas au cursus choisi, ou la plateforme a changé ses en-têtes | Vérifier la sélection, puis alerter le CRI |
| *« Le champ obligatoire 'X' n'est pas renseigné ou est vide »* | Une cellule indispensable est vide sur cette ligne | Compléter la cellule, ou retirer la ligne si le candidat ne doit pas être importé |
| *« La valeur 'X' fournie pour le champ 'Y' est invalide ou mal formatée »* | Le plus souvent une date dans un format inattendu | Mettre la cellule au format Date, ou saisir `JJ/MM/AAAA` |
| *« Aucune correspondance PEGASUS trouvée pour … »* | Une discipline ou un libellé de concours est inconnu du référentiel | Vérifier l'orthographe. Si l'intitulé est nouveau cette année, le signaler au CRI pour mise à jour du référentiel |
| *« La civilité 'X' ne permet pas de déterminer le sexe »* | La colonne `Genre` vaut `Autre`, est vide, ou contient une valeur inattendue | Voir la section ci-dessous |

### Cas particulier : la civilité ne permet pas de conclure

Les dossiers de candidature OnePSL30 acceptent la valeur **`Autre`** dans le
champ `Genre`. PEGASUS, lui, n'attend aujourd'hui que `Monsieur`/`Madame` et
`M`/`F` — il n'existe pas de valeur correspondante.

**L'outil ne devine pas.** Il termine le balayage du fichier, puis vous
restitue **toutes** les lignes concernées — vous les corrigez donc en une seule
fois, sans avoir à relancer après chaque correction. Aucun canevas n'est produit
tant qu'il en reste une.

C'est délibéré. Attribuer une valeur au hasard ferait entrer une information
d'état civil erronée dans PEGASUS, puis, par synchronisation, dans le SI de
l'École et jusqu'au service des ressources humaines.

**Marche à suivre :**

1. Téléchargez le rapport, ou notez les lignes signalées.
2. Pour chacune, **consultez le dossier de candidature** de la personne : le
   sexe à l'état civil y figure, et c'est cette information administrative qui
   est attendue par PEGASUS — indépendamment du genre déclaré par la personne.
3. Renseignez la valeur correspondante dans le fichier source.
4. Relancez la normalisation.

> Il s'agit d'un contournement, pas d'une cible : la limite vient de PEGASUS,
> qui n'offre pas de valeur pour un genre non binaire. Si cette valeur venait à
> être ouverte, l'outil sera adapté pour la reprendre automatiquement. Signalez
> au CRI les cas que vous rencontrez, cela documente le besoin.

---

## Bonnes pratiques

**À faire :**

- Déposer l'export **brut** de la plateforme, sans retouche préalable.
- Vérifier le nombre d'étudiants annoncé avant de télécharger.
- Ouvrir le canevas produit et **le parcourir rapidement** avant l'import.
- Conserver le fichier source et le canevas produit dans votre espace de travail.
- Signaler au CRI toute anomalie récurrente : c'est ainsi que l'outil s'améliore.

**À ne pas faire :**

- ❌ **Modifier le canevas produit dans Excel avant l'import.** Excel reformate
  silencieusement les dates, supprime les zéros initiaux des codes postaux et
  change l'encodage. Si une correction est nécessaire, corrigez le fichier
  **source** et relancez la normalisation.
- ❌ Réimporter deux fois le même canevas le même jour : la date du lot serait
  identique et **les données seraient écrasées** dans PEGASUS.
- ❌ Renommer le canevas en y ajoutant des accents : PEGASUS refuserait le fichier.
- ❌ Déposer un fichier contenant plusieurs feuilles utiles : seule la première
  est lue.
- ❌ Importer un fichier contenant des candidats non admis ou désistés :
  vérifiez toujours le nombre de lignes retenues.

---

## Questions fréquentes

**Le fichier contient des candidats non admis ou sur liste complémentaire.
Que se passe-t-il ?**
L'outil les écarte automatiquement, en s'appuyant sur les colonnes d'état
d'admission, de confirmation et de désistement propres à chaque cursus. Le
nombre de lignes écartées vous est indiqué. Comparez-le tout de même au nombre
d'admis attendu : en cas d'écart inexpliqué, n'importez pas et signalez-le.

**Mon fichier est refusé pour dépassement de capacité.**
Au-delà de 2 000 lignes de données, le fichier est refusé dans son ensemble
plutôt que traité partiellement. Scindez-le, puis importez les morceaux
successivement — **en changeant la date de lot entre deux imports**, faute de
quoi le second écraserait le premier dans PEGASUS.

**Un étudiant a une double nationalité. Comment est-il traité ?**
Si l'une de ses nationalités ouvre droit au statut de fonctionnaire — française,
d'un État membre de l'Union européenne, suisse, andorrane ou monégasque — il est
enregistré comme fonctionnaire, et c'est cette nationalité qui figure au dossier.

**Un étudiant a un nom comportant des caractères étrangers.**
Pour les échanges internationaux, les caractères non latins sont transposés,
PEGASUS les gérant mal. Le nom d'origine reste conservé dans le dossier.
**Vérifiez systématiquement l'orthographe des noms étrangers** dans le canevas
produit avant l'import.

**Puis-je traiter plusieurs concours dans un même fichier ?**
Non. Un fichier correspond à un cursus. Traitez-les séparément.

**J'ai fermé la fenêtre avant de télécharger. Le fichier est-il perdu ?**
Oui — les fichiers ne sont pas conservés sur le serveur, pour des raisons de
protection des données. Recommencez l'opération : le résultat sera identique.

**L'outil affiche « Erreur critique de communication avec le serveur ».**
Le serveur n'a pas répondu. Réessayez ; si le problème persiste, contactez le CRI.

**Le fichier d'admis ne comporte pas de colonne « nationalité ».**
C'est la nationalité qui détermine le statut de fonctionnaire, et donc le tarif
d'inscription. **N'importez pas sans elle** : demandez au pôle Concours de
compléter le fichier, en indiquant la nationalité française pour les
binationaux appelés à être fonctionnaires.

---

## En cas de problème

Contactez le **Centre de Ressources Informatiques — Pôle applications et web**,
en précisant :

- la population et le cursus choisis ;
- le nom du fichier déposé ;
- le message d'erreur affiché, ou le rapport TXT s'il a pu être téléchargé ;
- la date et l'heure de la tentative.

---

## Rappel des principales règles automatisées

Ce que l'outil déduit seul, et que vous n'avez donc pas à préparer :

| Information | Comment elle est déterminée |
|---|---|
| Statut fonctionnaire | Depuis le libellé du concours (SCEI) ou la nationalité (A/L, B/L). Jamais pour la SI et les normaliens étudiants |
| Code concours | Depuis le référentiel de l'annuaire de l'École |
| Produit programme | `ANDENS1` pour les CPGE, NEMH et NEMS ; selon la discipline pour la Sélection Internationale ; `ANECHINTER` pour la DRI |
| Phase professionnelle | Déduite du statut et de la population |
| Mode pédagogique | `EN SCOLARITE` pour la nouvelle promotion. Les étalements restent à corriger à la main |
| Genre et sexe | Déduits de la civilité du fichier source. **Sauf si celle-ci ne permet pas de conclure** : les lignes sont alors signalées et vous déterminez le sexe depuis le dossier de candidature |
| Mise en forme des noms | Nom en majuscules, prénom avec initiale majuscule |
| Numérotation des lots | Séquence sans rupture, gérée automatiquement |
| Encodage et format du fichier | Séparateur, encodage et fins de ligne conformes aux exigences de PEGASUS |
