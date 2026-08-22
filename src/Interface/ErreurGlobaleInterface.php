<?php

namespace App\Interface;

/**
 * Marque une anomalie qui condamne l'import dans son ensemble.
 *
 * Le principe de balayage complet (RG-03) veut qu'une anomalie de **donnée**
 * n'interrompe jamais le traitement : le fichier est parcouru jusqu'au bout et
 * l'utilisateur reçoit la liste exhaustive des lignes à corriger.
 *
 * Certaines anomalies échappent à cette règle parce qu'elles ne dépendent pas
 * de la ligne en cours : une colonne absente de l'en-tête, un référentiel
 * injoignable. Toutes les lignes échoueraient pour la même raison, et
 * poursuivre ne produirait qu'un rapport de N messages identiques — illisible,
 * et sans valeur pour le gestionnaire.
 *
 * Les exceptions portant cette interface interrompent donc le balayage et
 * donnent lieu à un message unique.
 */
interface ErreurGlobaleInterface {}
