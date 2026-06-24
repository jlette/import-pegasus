<?php

$dsn = 'oci:dbname=//' . DB_HOST . ':' . DB_PORT . '/' . DB_NAME . ';charset=UTF8';

try {
    $db = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT         => true,
    ]);
} catch (PDOException $e) {
    echo 'Echec de la connexion : ' . $e->getMessage();
    exit;
}

return $db;
