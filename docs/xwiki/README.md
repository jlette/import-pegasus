# Documentation au format XWiki

Version condensée du corpus `docs/`, écrite en **syntaxe XWiki 2.1** pour être
copiée-collée directement dans le wiki du CRI.

## Contenu

| Fichier | Page XWiki cible |
|---|---|
| `00-Accueil.xwiki` | `PEGASUS` (page parente) |
| `01-Expression-de-besoin.xwiki` | `PEGASUS / Expression de besoin` |
| `02-Cahier-des-charges-fonctionnel.xwiki` | `PEGASUS / Cahier des charges fonctionnel` |
| `03-Cahier-des-charges-technique.xwiki` | `PEGASUS / Cahier des charges technique` |
| `04-Mode-d-emploi.xwiki` | `PEGASUS / Mode d'emploi` |

## Marche à suivre

1. Créer la page parente **`PEGASUS`**, puis les quatre pages enfants avec les
   titres exacts du tableau ci-dessus.
2. Sur chaque page : **Modifier → Wiki** (l'éditeur source, pas l'éditeur
   WYSIWYG — celui-ci réinterpréterait le collage).
3. Vérifier que la syntaxe de la page est bien **XWiki 2.1**
   (*Modifier → Informations de la page → Syntaxe*).
4. Coller le contenu du fichier, puis enregistrer.

L'ordre importe peu, sauf pour les liens : ils ne deviennent actifs qu'une fois
les pages cibles créées.

## Si votre arborescence diffère

Les liens inter-pages sont écrits en références **absolues** préfixées par
`PEGASUS.` :

```
[[Expression de besoin>>doc:PEGASUS.Expression de besoin.WebHome]]
```

Si la page parente porte un autre nom, ou si elle est elle-même imbriquée sous
un espace existant (`Projets.PEGASUS`, par exemple), remplacer le préfixe
partout — cinq occurrences réparties sur trois fichiers :

```bash
grep -rn '>>doc:PEGASUS' docs/xwiki/
```

## Éléments de syntaxe employés

Utile pour relire ou modifier le contenu directement dans le wiki.

| Élément | Syntaxe |
|---|---|
| Titres | `= Niveau 1 =`, `== Niveau 2 ==`, `=== Niveau 3 ===` |
| Gras / italique / code | `**gras**`, `//italique//`, `##code##` |
| Tableau | `\|=En-tête\|=En-tête` puis `\|cellule\|cellule` |
| Encadrés | `{{info}}`, `{{warning}}`, `{{error}}`, `{{quote}}` |
| Bloc de code | `{{code language="php"}} … {{/code}}` |
| Verbatim en ligne | `{{{texte brut}}}` |
| Sommaire | `{{toc/}}` |
| Liste de définitions | `; terme` puis `: définition` |

Deux pièges à connaître si vous éditez ces pages :

- **`~` est le caractère d'échappement.** `~194` masquerait le `1`. Écrire
  `~~194`, ou préférer `≈ 194`.
- **`//` ouvre une italique.** Les chaînes comme `//TRANSLIT` ou
  `UTF-8/ISO-8859-1//TRANSLIT` doivent être placées dans un bloc verbatim
  `{{{ }}}`.

## Rapport avec `docs/`

Le corpus Markdown de `docs/` reste la **référence versionnée avec le code** :
c'est lui qu'on met à jour en même temps qu'une évolution fonctionnelle. Ces
pages XWiki en sont une **restitution condensée** à destination des lecteurs du
wiki.

En cas de divergence, `docs/` fait foi. Après une évolution notable, régénérer
la page XWiki concernée plutôt que de la corriger des deux côtés.
