# Cahier des charges technique (CDCT)

**Projet PEGASUS — Normalisateur des admissions** · ENS – PSL · CRI

> **Référentiel appliqué** : documents et fichiers **2026** > fichiers **2025** ;
> fichiers 2024 écartés. Voir [README](README.md).
>
> Ce document décrit **l'architecture cible**. Les écarts avec l'implémentation
> actuelle sont recensés au §11.

---

## 1. Stack technique

| Couche | Technologie | Version | Justification |
|---|---|---|---|
| Langage serveur | PHP | ≥ 8.2 | Types `readonly`, promotion de propriétés, `match`, `fputcsv` avec séparateur de ligne natif |
| Serveur HTTP | Apache 2.4 + `mod_rewrite` | — | Parc existant du CRI |
| Gestion de dépendances | Composer | 2.x | Autoloading PSR-4 |
| Lecture de tableurs | `phpoffice/phpspreadsheet` | ^5.5 | Seule bibliothèque PHP couvrant `.xls` et `.xlsx` |
| Base de données | Oracle (PDO OCI) | — | Annuaire Jefyco, référentiel des codes concours |
| Tests | PHPUnit | ^13.3 | — |
| Front-end | HTML5, CSS3, JavaScript ES Modules | — | Aucun framework : périmètre restreint, maintenance simplifiée |

**Aucune dépendance externe à l'exécution.** Polices et icônes doivent être
hébergées localement : l'outil manipule des données personnelles et doit rester
fonctionnel sans accès Internet sortant.

---

## 2. Architecture logicielle

### 2.1 Principes

L'application suit une architecture **MVC en couches**, structurée autour de
trois patrons complémentaires :

| Patron | Rôle | Motivation |
|---|---|---|
| **Strategy** | Une classe par flux d'admission (SCEI, A/L, B/L, SI-L, SI-S, NEMH, NEMS, DRI) | Chaque plateforme a ses en-têtes et ses règles. Ajouter un cursus n'impose de modifier aucun cursus existant |
| **Factory** | Sélection de la stratégie depuis le couple population/cursus | Un seul point d'aiguillage |
| **Builder** | Assemblage et normalisation avant instanciation des modèles immuables | Les modèles sont `readonly` : la normalisation doit précéder la construction |

S'y ajoutent deux dispositifs structurants :

- **Dictionnaires de constantes** — un par flux d'entrée, plus un par
  vocabulaire de sortie. Ils isolent les libellés de colonnes, qui changent
  chaque année, du code métier qui, lui, est stable.
- **Profils de canevas** — une déclaration explicite, par population et par
  campagne, de la structure exacte du fichier produit.

### 2.2 Arborescence

```
import-pegasus/
├── config/
│   ├── constants.php          Constantes applicatives (sans secret)
│   ├── db.php                 Fabrique de connexion PDO
│   └── routes.php             Table de routage
├── public/                    ← unique racine web exposée
│   ├── index.php              Contrôleur frontal
│   ├── .htaccess              Réécriture vers index.php
│   └── assets/
│       ├── css/   base/ · layout/ · component/
│       ├── js/    main.js · features/{upload,modal,select,toast}/
│       └── img/
├── src/
│   ├── Core/          Router, Controller
│   ├── Controller/    Index, Import, Error
│   ├── Service/       ExcelReader, CsvExport, FileUpload, Concours, MaxRowsReadFilter
│   ├── Factory/       StudentFactory
│   ├── Builder/       StudentBuilder
│   ├── Strategy/      AbstractStrategy + une classe par flux
│   ├── Model/
│   │   ├── Student/   AbstractStudent, Normalien, Echange, Masterien
│   │   └── Exception/ Hiérarchie d'exceptions métier
│   ├── Repository/    ConcoursRepository (Oracle)
│   ├── Constant/      Dictionnaires d'entrée et de sortie
│   ├── Canevas/       Profils de canevas (cible)
│   ├── Interface/     Contrats
│   ├── Helper/        AssetHelper
│   └── View/          layout, Page/, Partial/, Component/
├── tests/             Miroir de src/
└── docs/              Le présent corpus documentaire
```

### 2.3 Espaces de noms

```json
{
  "autoload":     { "psr-4": { "App\\":   "src/"   } },
  "autoload-dev": { "psr-4": { "Tests\\": "tests/" } }
}
```

> **Contrainte impérative** : le nom de fichier doit correspondre **exactement**
> au nom de classe, casse comprise. Le serveur cible est sous Linux, dont le
> système de fichiers est sensible à la casse — un écart provoque une erreur
> fatale qui reste invisible en développement sous Windows.

### 2.4 Flux de traitement

```
Requête POST /api/import
  │
  ├─ ImportController::handleUpload()
  │    ├─ Validation de la requête (méthode, présence du fichier, paramètres)
  │    ├─ FileUploadService::uploadExcelFile()   → fichier temporaire
  │    └─ ExcelReaderService::traiterAdmissions()
  │         ├─ StudentFactory::create()          → stratégie du cursus
  │         ├─ Lecture PhpSpreadsheet            → tableau de lignes
  │         ├─ Normalisation des en-têtes        → clés associatives
  │         ├─ Filtrage des non-admis            → lignes retenues
  │         ├─ Pour chaque ligne :
  │         │    └─ Strategy::createStudent()
  │         │         ├─ validateMandatoryFields()
  │         │         ├─ parseDate() · parseGenderAndSex()
  │         │         ├─ Règles métier du cursus
  │         │         │    └─ ConcoursService → ConcoursRepository → Oracle
  │         │         └─ StudentBuilder → Modèle readonly
  │         └─ CsvExportService::generateCsv()   → canevas .csv
  │
  └─ Réponse JSON : succès + nom du fichier, ou 422 + liste d'anomalies
```

### 2.5 Contrats

```php
interface ImportStrategyInterface
{
    public function createStudent(array $row, int $currentLot, int $currentSsl): AbstractStudent;
}

interface CodeRepositoryInterface
{
    public function findByPlatforme(string $platforme): array;
}

interface ImportExceptionInterface extends \Throwable {}
```

### 2.6 Hiérarchie d'exceptions

| Exception | Portée | Comportement |
|---|---|---|
| `WrongFileFormatException` | **Globale** | Colonne attendue absente : arrêt immédiat du traitement, message unique |
| `MissingMandatoryFieldException` | Ligne | Champ obligatoire vide : cumulée |
| `InvalidDataFormatException` | Ligne | Donnée mal formée : cumulée |
| `MappingNotFoundException` | Ligne | Correspondance introuvable : cumulée |

Toutes héritent de `AbstractImportException`, qui porte le numéro de ligne, et
implémentent `ImportExceptionInterface`.

Cette distinction est structurante : une erreur de **fichier** doit arrêter le
traitement, une erreur de **donnée** doit être collectée pour restituer à
l'utilisateur la liste complète des corrections à effectuer.

---

## 3. Modèle de données

### 3.1 Modèle objet

```
AbstractStudent  (readonly, abstraite)
│   date_lot · no_lot · no_ssl · type_occ · recrutement · annee
│   produit_programme · no_annee · session · status_etudiant
│   genre · nom · prenom · sexe · connaissance[] · eol
│
├── Normalien   + connaissance_fop_ins[] · situation_familiale
│                · ville_de_naissance · date_de_naissance · pays_de_naissance
│                · nationalite_principal · code_insee · courrier_*
│
├── Echange     + contact d'urgence · département de rattachement
│                · adresse personnelle complète
│
└── Masterien   (profil minimal : liaison par EMAIL ECOLE @ens.psl.eu)
```

Les modèles sont des **objets de transfert immuables** (`readonly`) : toute
normalisation est effectuée par le `StudentBuilder` en amont de l'instanciation.

### 3.2 Base de données Oracle

L'application est **exclusivement en lecture**. Elle n'écrit dans aucune base.

| Élément | Valeur |
|---|---|
| Serveur | `oracle1.cri.ulm` |
| Base | `jefyco` |
| Schéma | `ANNUAIRE` |
| Compte | Compte dédié du schéma `ANNUAIRE`, mot de passe géré sous KeePass — **ne pas utiliser le compte `grhum`** |

**Table `CORRESP_ANNUAIRE_CONC_CODE`** — correspondance concours :

| Colonne | Usage |
|---|---|
| `PLATEFORME` | Outil d'admission d'origine (`SCEI`, `EPONA`, `DEMATEC`, `DEC`…) |
| `ANNUAIRE_CONC_CODE` | Code du concours tel qu'il apparaît dans le fichier source |
| `CONC_CODE` | Code concours attendu par PEGASUS |
| `PEGASUS` | Filtre d'activité : ne retenir que `'O'` |

```sql
SELECT CONC_CODE, ANNUAIRE_CONC_CODE
FROM   CORRESP_ANNUAIRE_CONC_CODE
WHERE  PEGASUS = 'O'
  AND  PLATEFORME = :platforme
```

**Table `CORRESP_FORMATION_DEPARTEMENT`** — correspondance discipline → produit :

| Colonne | Usage |
|---|---|
| `DISCIPLINE_ADMISSION` | Intitulés possibles, séparés par des virgules. Filtrer sur les lignes renseignées |
| `COMPOSANT_CODE` | Code de la formation PEGASUS **sans l'année** — y ajouter le chiffre `1` |

> La comparaison doit se faire **sans accents et sans distinction de casse** :
> les intitulés changent d'une année sur l'autre et l'accentuation est inconstante.

### 3.3 Stockage sur disque

| Emplacement | Contenu | Cycle de vie |
|---|---|---|
| `tmp/uploads/` | Fichier source déposé | Supprimé dès la fin du traitement, y compris en cas d'erreur |
| `tmp/uploads/` | Canevas généré | Supprimé après téléchargement ; purge automatique au-delà d'une heure |

`tmp/` est hors de la racine web et doit rester inaccessible en HTTP.

---

## 4. Format de sortie — contraintes techniques

| Contrainte | Valeur | Motif |
|---|---|---|
| Séparateur | `;` | Exigence PEGASUS |
| Encodage | ISO-8859-1 | Exigence PEGASUS |
| Fin de ligne | `CRLF` | Exigence PEGASUS |
| Caractères hors ISO-8859-1 | Translittérés | `//TRANSLIT` sur le flux de conversion |
| Sauts de ligne internes | Remplacés par une espace | Une valeur multiligne casserait la structure |
| Nom de fichier | Sans caractère accentué | Exigence PEGASUS |
| Lignes vides finales | Interdites | Provoquent un rejet à l'import |

La conversion d'encodage est réalisée par un **filtre de flux natif**
(`stream_filter_append` avec `convert.iconv.UTF-8/ISO-8859-1//TRANSLIT`), qui
évite de matérialiser une seconde copie du fichier en mémoire.

**Le profil de canevas est la source de vérité de la structure.** Il ne doit
jamais être déduit du premier objet traité : une liste hétérogène produirait
des colonnes désalignées.

```php
final readonly class CanevasProfile
{
    public function __construct(
        public array $connaissances,
        public array $fopIns,
        public array $colonnesFinales,
    ) {}

    /** Canevas normalien — base 2025 + ENS_FINANCEMENT (décision H3) : 43 colonnes. */
    public static function normalien(): self
    {
        return new self(
            connaissances: [
                'EMAIL PERSONNEL', 'EMAIL ECOLE', 'NUMERO_ETU_PSLR',
                'ENS_NO_INDIVIDU', 'ENS_PROMO', 'ENS_FONCTIONNAIRE', 'ENS_CONCOURS',
            ],
            fopIns: [
                'ENS_SITUATION_CST', 'ENS_SITUATION_CSB',
                'ENS_MODE_PEDAGOGIQUE', 'ENS_BOURSE_ENS_PSL',
                'ENS_FINANCEMENT',
            ],
            colonnesFinales: [
                'Ville de Naissance', 'Date de Naissance',
                'Pays de Naissance', 'Nationalité Principale',
            ],
        );
    }
}
```

Le profil est **identique pour les sept cursus normaliens** — SCEI, A/L, B/L,
SI-Lettres, SI-Sciences, NEMH et NEMS. Seules les **valeurs** varient selon la
population (CDCF §5.9) ; la structure, elle, ne varie pas. Une stratégie qui
omet une connaissance du profil doit provoquer une erreur, jamais une colonne
manquante.

La DRI relève d'un profil distinct : connaissances de contact d'urgence et
département de rattachement, adresse personnelle complète, et **absence** de
`ENS_PROMO`, `ENS_FONCTIONNAIRE`, `ENS_CONCOURS` et des connaissances de
formation réservées à l'inscription DENS.

---

## 5. Routage

| Route | Méthode | Contrôleur | Réponse |
|---|---|---|---|
| `/` | GET | `IndexController::index` | Page d'accueil |
| `/api/import` | POST | `ImportController::handleUpload` | JSON |
| `/api/download` | GET | `ImportController::download` | Flux CSV |
| *(défaut)* | — | `ErrorController::notFound` | Page 404 |

**Codes de réponse de `/api/import` :**

| Code | Signification | Charge utile |
|---|---|---|
| `200` | Canevas généré | `filename`, `nb_importes`, `nb_ecartes` |
| `400` | Requête incomplète | `error` |
| `405` | Méthode non autorisée | `error` |
| `415` | Format de fichier refusé | `error` |
| `422` | Anomalies métier | `message`, `erreurs[]` |
| `500` | Erreur système | `error` — **sans détail technique en production** |

Le contrôleur frontal purge tout tampon de sortie avant d'émettre une réponse
JSON : une erreur PHP affichée en amont produirait un JSON invalide, que le
front interpréterait à tort comme une panne réseau.

---

## 6. Sécurité

### 6.1 Gestion des secrets

Aucun secret ne doit figurer dans le dépôt. Les paramètres de connexion sont
lus depuis l'environnement, l'absence d'une variable étant une erreur fatale
immédiate plutôt qu'un défaut silencieux :

```php
define('DB_HOST',     getenv('PEGASUS_DB_HOST')
    ?: throw new RuntimeException('Variable PEGASUS_DB_HOST non définie'));
define('DB_USER',     getenv('PEGASUS_DB_USER')
    ?: throw new RuntimeException('Variable PEGASUS_DB_USER non définie'));
define('DB_PASSWORD', getenv('PEGASUS_DB_PASSWORD')
    ?: throw new RuntimeException('Variable PEGASUS_DB_PASSWORD non définie'));
```

Variables attendues : `PEGASUS_DB_HOST`, `PEGASUS_DB_PORT`, `PEGASUS_DB_NAME`,
`PEGASUS_DB_USER`, `PEGASUS_DB_PASSWORD`, `APP_ENV`, `APP_BASE_URL`.

### 6.2 Exigences de sécurité

| Domaine | Exigence |
|---|---|
| **Authentification** | Obligatoire, adossée au dispositif d'authentification de l'École. À défaut : restriction par adresse IP et authentification HTTP |
| **Autorisation** | Accès réservé aux agents CoST et DRI |
| **CSRF** | Jeton de session vérifié sur toute requête `POST` |
| **Accès aux canevas** | Identifiant opaque non devinable, lié à la session émettrice. Un nom dérivé d'un horodatage est énumérable |
| **Traversée de répertoire** | Nom de fichier réduit à son composant final, chemin résolu et vérifié comme appartenant au répertoire temporaire |
| **Dépôt de fichier** | Contrôle du type MIME **et** de l'extension ; taille plafonnée ; nom généré côté serveur |
| **Injection SQL** | Requêtes préparées et paramétrées exclusivement |
| **XSS** | Aucune insertion de HTML depuis une donnée serveur : les messages sont injectés en contenu textuel |
| **En-tête `Host`** | L'URL de base provient de la configuration, jamais de `$_SERVER['HTTP_HOST']` |
| **Divulgation** | `display_errors` désactivé en production, `log_errors` actif vers un fichier hors racine web |
| **Journalisation** | Agent, population, volumétrie, horodatage, résultat — sans données personnelles |

### 6.3 Conformité RGPD

| Principe | Application |
|---|---|
| **Minimisation** | Ne produire que les colonnes strictement nécessaires. Adresse, téléphone et INE ne sont pas repris du dossier de candidature ; la ville de naissance n'est requise que pour les élèves fonctionnaires |
| **Limitation de conservation** | Fichiers source et canevas supprimés dès la fin du traitement ; purge automatique des résidus |
| **Confidentialité** | Accès authentifié ; canevas accessible au seul producteur ; répertoire temporaire hors racine web |
| **Intégrité** | Contrôles bloquants en amont ; aucun import partiel |
| **Traçabilité** | Journal des opérations sans données personnelles |

---

## 7. Performance

Volumétrie réelle : environ 380 étudiants par an, fichiers de quelques dizaines
de kilo-octets. Les enjeux de performance sont faibles ; les dispositifs
suivants relèvent surtout de la robustesse.

| Dispositif | Effet |
|---|---|
| `setReadDataOnly(true)` | Ignore styles et formats à la lecture |
| `setLoadSheetsOnly()` | Ne charge que la première feuille |
| Filtre de lecture par nombre de lignes | Plafonne l'empreinte mémoire — **doit refuser explicitement au-delà du seuil, jamais tronquer** |
| `disconnectWorksheets()` | Libère la mémoire dès la lecture terminée |
| Filtre de flux pour la conversion d'encodage | Évite une seconde copie en mémoire |
| Connexion Oracle paresseuse | N'ouvre la connexion que si le cursus l'exige : la DRI n'interroge pas l'annuaire |
| `filemtime()` pour la version des ressources statiques | Permet la mise en cache navigateur, qu'un horodatage courant interdit |

---

## 8. Tests

### 8.1 Stratégie

| Niveau | Objet | Priorité |
|---|---|---|
| **Bout en bout** | Fichier d'entrée réel → canevas attendu, **comparaison à l'octet près** | 🔴 Critique |
| Unitaire — stratégies | Règles métier par cursus : statut, concours, produit programme | 🔴 Haute |
| Unitaire — export | Structure du canevas, encodage, fins de ligne, libellés de colonnes | 🔴 Haute |
| Unitaire — lecture | En-têtes dupliqués, lignes vides, lignes plus courtes que l'en-tête | 🟠 Moyenne |
| Unitaire — normalisation | Casse multi-octets, dates, civilités, nationalités | 🟠 Moyenne |
| Unitaire — aiguillage | Factory, exceptions | 🟡 Basse |

**Le test de bout en bout est le seul filet réel.** Les couples fichier
d'entrée / canevas attendu existent déjà pour plusieurs populations et
constituent un jeu de recette prêt à l'emploi. Sans lui, aucun refactoring
ne peut être considéré comme sûr.

### 8.2 Configuration

Un fichier `phpunit.xml` doit être présent à la racine :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true" cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Application">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include><directory>src</directory></include>
    </source>
</phpunit>
```

**Convention impérative** : tout fichier de test doit être suffixé `Test.php`,
faute de quoi il est ignoré sans avertissement.

---

## 9. Déploiement

### 9.1 Prérequis serveur

| Élément | Exigence |
|---|---|
| PHP | ≥ 8.2 avec `mbstring`, `iconv`, `zip`, `xml`, `gd`, `pdo_oci` |
| Client Oracle | Instant Client, avec `TNS_ADMIN` configuré si nécessaire |
| Apache | `mod_rewrite` actif, `AllowOverride All` sur le répertoire du projet |
| Écriture | Le compte du serveur web doit pouvoir écrire dans `tmp/` |

### 9.2 Installation

```bash
git clone <dépôt> import-pegasus && cd import-pegasus
composer install --no-dev --optimize-autoloader
mkdir -p tmp/uploads && chmod 750 tmp/uploads
chown -R www-data:www-data tmp
# Renseigner les variables d'environnement (vhost, systemd ou fichier hors dépôt)
```

### 9.3 Configuration Apache

La racine web doit pointer sur `public/`, et **uniquement** sur `public/`.

```apache
<VirtualHost *:443>
    ServerName   pegasus-import.ens.psl.eu
    DocumentRoot /var/www/import-pegasus/public

    SetEnv PEGASUS_DB_HOST     oracle1.cri.ulm
    SetEnv PEGASUS_DB_PORT     1521
    SetEnv PEGASUS_DB_NAME     jefyco
    SetEnv PEGASUS_DB_USER     annuaire
    SetEnv PEGASUS_DB_PASSWORD "<depuis le coffre>"
    SetEnv APP_ENV             production
    SetEnv APP_BASE_URL        https://pegasus-import.ens.psl.eu

    <Directory /var/www/import-pegasus/public>
        AllowOverride All
        Require all granted
    </Directory>

    php_admin_flag display_errors off
    php_admin_flag log_errors    on
    php_admin_value error_log    /var/log/php/import-pegasus.log
</VirtualHost>
```

> La réécriture de secours qui redirige la racine du projet vers `public/` est
> un artefact de développement local. En production, `DocumentRoot` doit viser
> `public/` directement et le `.htaccess` racine peut être supprimé.

### 9.4 Contrôles de mise en production

- [ ] Aucun secret dans le dépôt ; mot de passe Oracle renouvelé
- [ ] `display_errors` désactivé, `log_errors` actif
- [ ] `DocumentRoot` sur `public/` ; `tmp/` inaccessible en HTTP
- [ ] Authentification en place
- [ ] Ressources statiques hébergées localement, aucun appel externe
- [ ] Purge automatique de `tmp/uploads/` planifiée
- [ ] Suite de tests au vert, test de bout en bout inclus
- [ ] Correspondance exacte entre noms de fichiers et noms de classes vérifiée sous Linux
- [ ] Journalisation applicative opérationnelle
- [ ] Un import de recette validé par le CoST sur données réelles

### 9.5 Maintenance annuelle

À exécuter en avril-mai, avant le pic de campagne.

| Vérification | Source |
|---|---|
| Évolution des en-têtes des exports | Dictionnaires d'entrée |
| Intitulés de disciplines de l'année | Pôle Concours + `CORRESP_FORMATION_DEPARTEMENT` |
| Ajouts et suppressions de concours | `CORRESP_ANNUAIRE_CONC_CODE` |
| Évolution du canevas d'import | Profils de canevas |
| Évolution des pays fonctionnarisables | Dictionnaire des nationalités |
| Migration de plateforme | SI vers OnePSL30 en promo 2027 |

---

## 10. Conventions de développement

| Domaine | Convention |
|---|---|
| Style | PSR-12 |
| Autoloading | PSR-4 — nom de fichier identique au nom de classe, **casse comprise** |
| Nommage | Classes en `PascalCase`, méthodes en `camelCase`, constantes en `SCREAMING_SNAKE_CASE` |
| Langue | Code et identifiants en français lorsqu'ils portent un concept métier (`estFonctionnaire`, `produitProgramme`) ; anglais pour les termes techniques |
| Commentaires | Documenter le **pourquoi** métier, pas le **quoi** technique. Chaque règle non évidente cite sa source |
| Chaînes multi-octets | `mb_*` obligatoire pour toute transformation de casse |
| Commits | Convention *Conventional Commits* (`feat`, `fix`, `refactor`, `test`, `perf`, `docs`) |

**Bonne pratique à préserver** : les commentaires « Règle métier : … » présents
dans les stratégies expliquent des arbitrages qui ne se déduisent d'aucun
fichier. Ils constituent, avec le présent corpus, la mémoire du projet.

---

## 11. Écarts entre le CDCT et l'implémentation

| Réf. | Écart | Correction attendue |
|---|---|---|
| **C1** | Structure du canevas déduite du premier objet traité ; 53 colonnes produites au lieu de 43 | Introduire `CanevasProfile` (§4) |
| **C1b** | `SceiStrategy` ne déclare qu'une paire `Connaissance_fop_ins` sur les cinq attendues | Aligner sur le profil unique |
| **C1c** | `AlStrategy` et les deux stratégies SI émettent `''` au lieu de `NON` pour les connaissances de situation | Valeurs explicites |
| **C1d** | `DriStrategy` renseigne des connaissances réservées à l'inscription DENS | Profil DRI distinct |
| **C2** | `strtoupper` / `strtolower` / `ucfirst` non multi-octets | `mb_strtoupper`, `mb_convert_case(…, MB_CASE_TITLE, 'UTF-8')` |
| **C3** | Table de translittération de 64 entrées face à 63 remplacements | Remplacer par `iconv('UTF-8','ASCII//TRANSLIT//IGNORE')` ou `Transliterator` |
| **C4** | `Blstrategy.php` ≠ classe `BlStrategy` | Renommer le fichier |
| **C5** | Identifiants Oracle versionnés | Variables d'environnement + rotation du mot de passe |
| **C6** | Aucun filtrage des non-admis | Filtre déclaratif par cursus |
| **C7** | Civilité inconnue basculée en `Monsieur` par défaut | Lever une exception métier cumulable (RG-02) ; aucun canevas produit |
| **C8** | Colonne source absente traitée comme valeur vide | Rendre obligatoires les colonnes portant une règle de gestion |
| **M1** | `CsvExportService` interroge la forme des objets via `property_exists` (13 occurrences) | Méthode `colonnesFinales()` polymorphe sur les modèles |
| **M2** | `StudentFactory` statique, instancie ses propres dépendances | Factory injectable |
| **M3** | Le contrôleur charge la configuration de base de données par inclusion de fichier | Injection d'une connexion paresseuse |
| **M4** | `config/db.php` émet du texte puis interrompt l'exécution | Lever une `PDOException` |
| **M5** | `DriStrategy` construit un `Normalien` ; `Echange` inutilisé | Utiliser `buildEchangeStudent()` et un profil DRI |
| **M6** | Résolution du concours par inclusion de chaîne | Comparaison par mot entier, code le plus long d'abord |
| **M7** | Année déduite de l'horloge système | Paramètre d'entrée |
| **M8** | Troncature silencieuse au-delà de 2 000 lignes | Refus explicite |
| **M9** | Versionnage des ressources statiques par horodatage courant | `filemtime()` |
| **M10** | Absence de `phpunit.xml` ; un fichier de test mal suffixé n'est jamais exécuté | Ajouter la configuration, renommer le fichier |
| **M11** | Espace de noms `Pegasus\Import\` déclaré vers un répertoire inexistant | Nettoyer `composer.json` |
| **M12** | Trois fichiers vides versionnés ; dépendances injectées non utilisées dans cinq stratégies | Supprimer |
| **M13** | Constantes dupliquées entre dictionnaires de sortie | Source unique |
| **M14** | Connexion Oracle persistante ouverte même pour la DRI | Connexion paresseuse |
| **M15** | URL codées en dur dans le JavaScript | Injection depuis la configuration serveur |
| **M16** | Dépendances externes (polices, icônes) chargées depuis Internet | Hébergement local |
| **M17** | Fenêtre modale sans rôle ARIA, sans piège de focus, sans fermeture clavier | Élément `<dialog>` natif |
| **M18** | Notifications non restituées aux lecteurs d'écran | `role="status"`, `aria-live` |
| **M19** | Traces de débogage résiduelles, dont des noms de fichiers d'admis | Supprimer |
