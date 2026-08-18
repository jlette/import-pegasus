<?php

/**
 * Fabrique de connexion à l'annuaire Oracle (schéma ANNUAIRE de la base Jefyco).
 *
 * L'application n'écrit jamais dans cette base : elle y lit uniquement les tables
 * de correspondance des codes concours et des disciplines.
 *
 * La connexion est paresseuse : elle n'est établie qu'au premier appel effectif.
 * Les imports qui n'interrogent pas l'annuaire — la DRI notamment — n'ouvrent
 * donc aucune connexion.
 */

use App\Database\LazyPdo;

return new LazyPdo(static function (): PDO {
    $dsn = sprintf('oci:dbname=//%s:%s/%s;charset=UTF8', DB_HOST, DB_PORT, DB_NAME);

    // Une PDOException remonte au contrôleur, qui la traduit en réponse JSON.
    // Émettre du texte ici corromprait le corps de la réponse de l'API.
    return new PDO($dsn, env_required('PEGASUS_DB_USER'), env_required('PEGASUS_DB_PASSWORD'), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
});
