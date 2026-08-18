<?php require_once __DIR__ . '/_icon.php'; ?>
<div class="upload" id="dropzone">
    <div class="upload__col">
        <h2 class="upload__title">Normaliser les fichiers d'admissions</h2>

        <p class="upload__description">Transformez vos données brutes en un canevas d'import parfait. Ce normalisateur
            vérifie les informations de vos admis (statuts, cursus, concours) et s'assure de leur totale conformité avec
            le système PEGASUS.</p>

        <div class="upload__form">
            <!-- Un <label> natif : le clic et l'activation au clavier sont
                 pris en charge par le navigateur, sans JavaScript. -->
            <label class="upload__label" for="file">
                <?= icone('upload') ?>
                <span class="upload__lable__text">Sélectionnez un fichier</span>
            </label>
            <!-- L'input reste focalisable — masqué visuellement, pas retiré de
                 l'ordre de tabulation comme le faisait l'attribut hidden. -->
            <input type="file" id="file" name="file" class="visually-hidden"
                accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
    </div>
    <span class="upload__icon"><?= icone('fichier') ?></span>
</div>