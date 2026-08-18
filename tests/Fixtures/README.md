# Jeux d'essai

Les identités contenues dans ce dossier sont **entièrement fictives**. Elles ont
été fabriquées pour la recette et ne correspondent à aucune personne réelle.

Les canevas de référence transmis par le CoST ne peuvent pas servir de jeu
d'essai : ils portent l'identité, la date de naissance, la nationalité et
l'adresse électronique d'admis réels. Les verser dans le dépôt Git les rendrait
consultables par tout porteur d'un accès en lecture, et les conserverait
indéfiniment dans l'historique.

Les cas retenus couvrent délibérément les pièges rencontrés en production :

| Cas | Ce qu'il vérifie |
|---|---|
| Nom avec tréma, prénom entièrement capitalisé | Normalisation multi-octets de la casse |
| Caractères hors ISO-8859-1 (`Ł`) | Translittération à l'écriture du CSV |
| Nationalité française, UE, hors UE | Détermination du statut de fonctionnaire |
| Binational | Priorité de la nationalité ouvrant droit au statut |
| Nom d'usage différent de l'état civil | RG-04 |
| Civilité `Autre` | RG-02 |
