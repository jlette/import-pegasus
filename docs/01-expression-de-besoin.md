# Expression de besoin — Projet PEGASUS / Normalisateur des admissions

| | |
|---|---|
| **Projet** | PEGASUS — Intégration des admis et autres populations étudiantes |
| **Maîtrise d'ouvrage** | Centre de Ressources Informatiques (CRI) — Pôle projets scolarité et admissions |
| **Maîtrise d'œuvre** | CRI — Pôle applications et web |
| **Bénéficiaires** | CoST (pôles Concours et Scolarité), DRI |
| **Établissement** | École normale supérieure – PSL |
| **Version** | 1.0 — reconstituée par rétro-ingénierie |
| **Référentiel** | Documents 2026 > fichiers 2025 (voir [README](README.md)) |

---

## 1. Contexte

### 1.1 Le système cible

L'École normale supérieure – PSL gère la scolarité de ses étudiants dans
**PEGASUS/Helisia**, dont le back-office s'appelle *Phénix*. Chaque rentrée,
l'établissement doit y créer les dossiers administratifs et les inscriptions
administratives (IA) de l'ensemble de ses nouvelles populations étudiantes.

Cette création se fait par **import d'un fichier CSV** au format dit
« canevas d'import », via le menu `Inscription | Gérer l'inscription
administrative → Interface apprenant | Importation classique Étudiant par
fichier`.

Après import, PEGASUS génère le portail étudiant dans la nuit, puis une
synchronisation alimente le Système d'Information de l'École, l'annuaire, et —
pour les élèves fonctionnaires — le Service des Ressources Humaines et
l'application RH (Mangue).

### 1.2 Les populations concernées

Environ **380 étudiants par an**, répartis en flux très hétérogènes :

| Population | Volume indicatif | Plateforme d'origine | Période d'import |
|---|---|---|---|
| Élèves normaliens CPGE Sciences (BCPST, MP, PC, PSI) | ~194 fonctionnaires + non-fonctionnaires | SCEI | Août |
| Élèves normaliens CPGE A/L | — | EPONA (ENS de Lyon) | Début juillet |
| Élèves normaliens CPGE B/L | — | SCEI | Début juillet |
| Normaliens étudiants (NEL, NES, NEMH, NEMS) | ~180 | OnePSL30 | Juin – juillet |
| Sélection Internationale Lettres et Sciences | ~20 | DEMATEC (→ OnePSL30 en promo 2027) | Juillet |
| Bourses Olympiques | 3 | DEMATEC | Variable |
| FrontCog | — | Département d'Études Cognitives | — |
| Préparation à l'agrégation | — | DEMATEC | — |
| Échanges internationaux entrants (Erasmus, pensionnaires étrangers) | ~34 | MoveOn (DRI) | Été + décembre |

---

## 2. Problèmes rencontrés

### 2.1 Un format d'import extrêmement contraint, pour un risque disproportionné

La présentation de référence est sans ambiguïté sur l'enjeu :

> « L'import des étudiants dans Phénix est soumis à des règles très strictes.
> Le formatage des données est donc une étape capitale car un mauvais format
> de celles-ci peut avoir pour conséquence : **au mieux, fausser les
> synchronisations avec le Système d'Information** ; **au pire, écraser des
> données dans PEGASUS et le rendre inexploitable.** »

Les contraintes cumulées sont nombreuses et sans tolérance :

- ordre des colonnes figé, libellés au caractère près, **espaces compris**, casse respectée ;
- séparateur `;`, encodage compatible ISO-8859-1, fins de ligne `CRLF` ;
- connaissances appairées par binôme `Type` / `Valeur` portant le même numéro ;
- codes en majuscules, à l'exception signalée (`FrontCog`) ;
- `NC.` obligatoirement suivi de son point ;
- dernière colonne toujours `EOL`, contenant `EOL` ;
- nom de fichier sans caractère accentué ;
- lignes vides en fin de fichier interdites ;
- **une colonne présente mais vide écrase la donnée existante** — d'où la règle
  « mieux vaut supprimer une colonne que ne pas la remplir ».

Une erreur sur un code concours ne provoque pas de rejet : elle **crée
silencieusement un nouveau code** que la synchronisation ne reconnaîtra pas,
polluant durablement le référentiel et l'annuaire.

### 2.2 Des sources d'admission multiples et instables

La difficulté centrale, formulée dès le compte rendu de cadrage :

> « La complexité réside dans le fait que les sources d'admission des étudiants
> sont multiples et par conséquent le format des listes d'admis en entrée. »

Concrètement, sur les fichiers 2026 :

- **six plateformes** aux exports incompatibles (SCEI, EPONA, OnePSL30, DEMATEC, MoveOn, DEC) ;
- des **libellés de colonnes différents pour la même donnée** : la date de
  naissance s'appelle `ddn` (B/L), `naissance_date` (SI), `Date de naissance` (NEMH/NEMS) ;
- des **langues mêlées** : les profils SI-Sciences sont en anglais
  (`Mathematics`, `Cognitive sciences`), ceux de SI-Lettres en français
  (`Sociologie`, `Histoire de l'Art`) ;
- des **formats de date divergents au sein d'un même lot** — signalé nommément
  en juin 2026 pour les boursiers olympiques ;
- des **intitulés de disciplines qui changent d'une année sur l'autre**, avec
  des disparités d'accentuation imposant une comparaison sans accents ;
- des **variantes concurrentes du même fichier** : pour la SI-Lettres 2026
  circulent une extraction brute et un fichier retravaillé par le CoST, aux
  en-têtes incompatibles ;
- des **colonnes qui disparaissent** : le fichier B/L 2026 ne comporte plus de
  colonne `nationalité`, alors que c'est la donnée qui détermine le statut de
  fonctionnaire.

### 2.3 Une fabrication manuelle, coûteuse et faillible

En l'absence d'outil, le CoST fabriquait chaque canevas sous Excel :

- **B/L** : fusion par RECHERCHEV entre le fichier des classés et le fichier des
  inscrits SCEI, seul moyen d'obtenir les informations de naissance indispensables ;
- **CPGE** : déduction manuelle du statut fonctionnaire depuis le libellé du concours ;
- **A/L** : arbitrage manuel sur deux colonnes de nationalité pour les binationaux ;
- **SI** : traduction manuelle du profil en code produit programme ;
- **DRI** : reformatage manuel de l'export MoveOn.

Ce travail est **saisonnier, concentré sur quelques jours**, et effectué sous
pression : les imports conditionnent l'ouverture du portail étudiant, la
campagne de communication et le site de rentrée.

### 2.4 Des incidents avérés

Le 17 juillet 2026, la responsable adjointe du pôle Scolarité écrit, après
deux tentatives d'import B/L :

> « L'import n'a pas fonctionné non plus avec ce fichier, nous rencontrons
> toujours le même message d'erreur. **Je vais tenter de préparer l'import à la
> main.** »

Le même jour, elle rappelle l'urgence :

> « Nous devons procéder à l'import aujourd'hui et suite à l'import nous avons
> encore plusieurs démarches à réaliser niveau comm et site de rentrée pour que
> les élèves puissent bien avoir accès à toutes les informations. »

### 2.5 Un enjeu de conformité RGPD

Les fichiers manipulés contiennent des données personnelles sensibles de
candidats et d'étudiants : identité, date et lieu de naissance, nationalité,
adresse postale, téléphone, adresse électronique personnelle, INE.

La Direction des affaires juridiques a déjà fait retirer la question de la
ville de naissance des dossiers de candidature OnePSL30, jugée non nécessaire
au regard du RGPD. La MOA a formalisé le principe applicable :

> « Pour la ville de naissance, ce n'est indispensable que pour les élèves
> fonctionnaires. Et en ces temps où le piratage se fait à grande échelle,
> c'est mieux de ne demander que le strict nécessaire. »

Le CoST a par ailleurs arbitré que **l'adresse postale, le téléphone et l'INE
ne doivent pas être repris de l'outil de candidature** mais saisis par
l'étudiant lui-même lors de son inscription administrative.

---

## 3. Objectifs du projet

### 3.1 Objectif général

Doter le CoST et la DRI d'un **outil web interne de normalisation** capable de
produire, à partir d'un export brut de plateforme de candidature, un canevas
d'import CSV **conforme par construction** aux règles de PEGASUS.

La formulation d'origine, adressée à la DRI en décembre 2025 :

> « Nous étudions la faisabilité d'un outil permettant de générer les fichiers
> d'import Pegasus à partir des fichiers d'export des différents outils de
> candidature et de gestion. »

### 3.2 Objectifs opérationnels

| # | Objectif | Indicateur de succès |
|---|---|---|
| **O1** | Supprimer toute manipulation manuelle du canevas | 0 RECHERCHEV, 0 retouche Excel entre l'export et l'import |
| **O2** | Garantir la conformité stricte du fichier produit | Import PEGASUS accepté du premier coup, sans message d'erreur |
| **O3** | Centraliser les règles métier dans un référentiel unique | Une règle = un seul endroit dans le code, modifiable sans refonte |
| **O4** | Détecter les anomalies **avant** l'import, pas après | Rapport d'erreurs ligne à ligne, exportable, avant génération |
| **O5** | Absorber les évolutions annuelles de format | Adaptation d'une campagne à l'autre par simple mise à jour de dictionnaires |
| **O6** | Réduire l'exposition des données personnelles | Ne produire que les colonnes strictement nécessaires ; purge des fichiers temporaires |
| **O7** | Rendre le dispositif transmissible | Documentation complète, tests automatisés, code lisible par un successeur |

### 3.3 Périmètre

**Inclus** — génération du canevas CSV pour :

- Normaliens DENS : CPGE (SCEI, A/L, B/L), Sélection Internationale (Lettres, Sciences), Normaliens étudiants (NEMH, NEMS) ;
- Échanges internationaux DRI (Erasmus, pensionnaires étrangers).

**Prévu, non encore livré** — NEL, NES, FrontCog, Bourses Olympiques, préparation à l'agrégation.

**Exclu** — l'import dans PEGASUS lui-même, qui reste une opération manuelle du
CoST ; les mastériens PSL, importés par PSL ; les réinscriptions au DENS,
traitées par passage d'année ou export/réimport ; les HDR, saisies manuellement.

### 3.4 Contraintes structurantes

| Contrainte | Origine |
|---|---|
| Ne jamais écrire directement dans PEGASUS | Le CoST garde la main sur l'acte d'import et sa vérification |
| Fonctionner sur l'infrastructure PHP/Apache du CRI | Parc existant, pas de nouvelle brique |
| Interroger l'annuaire Oracle Jefyco pour les codes concours | Source de vérité déjà en place |
| Ne pas reprendre adresse, téléphone et INE dans le canevas | Arbitrage CoST + RGPD |
| Rester utilisable par des gestionnaires non informaticiens | Public cible du CoST et de la DRI |

---

## 4. Retour sur investissement attendu

### 4.1 Gains quantifiables

| Poste | Avant | Après | Gain |
|---|---|---|---|
| Fabrication d'un canevas (par population) | 2 h à 1 j selon la complexité | < 5 min | **~90 %** du temps de préparation |
| Reprises après rejet PEGASUS | 1 à 3 itérations, parfois abandon et saisie manuelle | 0 attendue | Suppression des reprises |
| Campagne complète (≈ 9 populations) | 3 à 5 jours-homme concentrés | < 1 jour-homme | **~4 j-h par campagne** |
| Fiabilisation d'un import raté | Ressaisie manuelle sous contrainte de délai | Sans objet | Suppression du risque de saisie |

### 4.2 Gains qualitatifs

- **Réduction du risque majeur.** Le scénario d'écrasement de données dans
  PEGASUS, explicitement redouté, est neutralisé par un contrôle systématique
  en amont plutôt que par la vigilance individuelle.
- **Fin de la dépendance à la connaissance tacite.** Les règles — nationalités
  ouvrant droit au statut de fonctionnaire, correspondances discipline →
  département, codes concours — sont aujourd'hui portées par quelques personnes
  et reconstituées à chaque campagne. Elles deviennent explicites et versionnées.
- **Désaisonnalisation de la charge.** Le pic de juillet-août cesse de dépendre
  de la disponibilité d'un expert unique.
- **Qualité de l'annuaire et du SI.** L'élimination des codes concours erronés
  supprime la pollution du référentiel et les incohérences propagées par
  synchronisation jusqu'au SRH.
- **Traçabilité.** Le rapport d'anomalies documente ce qui a été corrigé et
  pourquoi, ce qu'aucun fichier Excel retouché à la main ne permettait.

### 4.3 Coût d'entretien

Le principal poste récurrent est l'**adaptation annuelle** : intitulés de
disciplines, ajouts et suppressions de concours (MPI et INFO ont disparu en
2025), migration de plateforme (la SI passe sur OnePSL30 en promo 2027).

L'architecture retenue confine ces évolutions dans des **dictionnaires de
constantes** et des **tables de correspondance Oracle** : la charge estimée est
de l'ordre de **1 à 2 jours-homme par campagne**, à comparer aux 4 jours-homme
économisés à chaque campagne sur la seule fabrication des canevas.

---

## 5. Facteurs clés de succès

1. **Un jeu de recette permanent.** Les couples fichier d'entrée / canevas
   attendu doivent être conservés et rejoués automatiquement à chaque
   évolution. C'est la seule garantie objective de non-régression.
2. **Un interlocuteur métier identifié par population.** Les règles les plus
   subtiles (nationalités fonctionnarisables, département d'affectation NEMH)
   ne se déduisent d'aucun fichier.
3. **Une revue annuelle avant campagne**, en avril-mai, pour intégrer les
   évolutions de format avant le pic d'activité.
4. **Le maintien du contrôle humain final.** L'outil prépare, le CoST vérifie
   et importe. Cette séparation est une garantie, pas une limite.
