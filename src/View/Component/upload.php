<?php require_once __DIR__ . '/_icon.php'; ?>
<!-- La zone globale n'a plus d'attributs clavier -->
<div class="upload" id="dropzone">
    <div class="upload__col">
        <h2 class="upload__title">Normaliser les fichiers d'admissions</h2>

        <p class="upload__description">Transformez vos données brutes en un canevas d'import parfait. Ce normalisateur
            vérifie les informations de vos admis (statuts, cursus, concours) et s'assure de leur totale conformité avec
            le système PEGASUS.</p>

        <div class="upload__form">
            <!-- C'est le bouton qui capte le focus clavier -->
            <span class="upload__label" tabindex="0" role="button" aria-label="Sélectionner un fichier">
                <?= icone('upload') ?>
                <span class="upload__lable__text">Sélectionnez un fichier</span>
            </span>
            <input type="file" id="file" name="file"
                accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                hidden>
        </div>
    </div>
    <span class="upload__icon"><?= icone('fichier') ?></span>
</div>