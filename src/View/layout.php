<?php

use App\Helper\AssetHelper;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $description ?>">
    <script src=" https://kit.fontawesome.com/19c4efe90e.js" crossorigin="anonymous">
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap"
        rel="stylesheet">

    <?= AssetHelper::loadCssTags('base'); ?>
    <?= AssetHelper::loadCssTags('layout'); ?>
    <?= AssetHelper::loadCssTags('component'); ?>
    <?= AssetHelper::assetJsPath('main.js') ?>
    <title><?= $title ?>
    </title>
</head>

<body>

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

</body>

</html>