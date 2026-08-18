<?php

use App\Helper\AssetHelper;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
    <?= AssetHelper::loadCssTags('base'); ?>
    <?= AssetHelper::loadCssTags('layout'); ?>
    <?= AssetHelper::loadCssTags('component'); ?>
    <?= AssetHelper::assetJsPath('main.js') ?>
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">

    <?php
    // On inclut le header depuis le dossier Partial
    require_once __DIR__ . '/Partial/_header.php';
    ?>
    <main>
        <?= $content ?? ''; ?>
    </main>

    <?php
    // On inclut le footer depuis le dossier Partial
    require_once __DIR__ . '/Partial/_footer.php';
    ?>

    <?php
    // Rendus en dernier, hors de <main> : le script rend l'arrière-plan inerte
    // pendant que la modale est ouverte, ce qui suppose qu'elle n'en fasse pas
    // partie.
    require_once __DIR__ . '/Component/toast.php';
    require_once __DIR__ . '/Component/modal.php';
    ?>

</body>

</html>