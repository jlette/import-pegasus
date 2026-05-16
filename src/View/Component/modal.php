<div class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__body">
        <div class="modal__header">
            <button class="modal__close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <!-- VUE 1 : Formulaire -->
        <div class="modal__content">
            <div class="modal__preview">
                <div class="modal__file tooltip">
                    <i class="modal__icon fa-solid fa-file-excel"></i>
                    <span class="modal__filename"></span>
                    <span class="js-modal-tooltip-text tooltip__text"></span>
                </div>
                <div class="modal__tool">
                    <input type="file" id="file" name="file"
                        accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                        hidden>
                    <label for="file" class="modal__button modal__button--replace">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span class="modal__replace-file">Remplacer le fichier</span>
                    </label>
                </div>
            </div>
            <div class="modal__actions">
                <div class="modal__form">
                    <fieldset class="modal__field">
                        <legend>Type d'étudiant</legend>
                        <select id="student-select">
                            <option value="">-- Sélectionnez le type d'étudiant --</option>
                            <option value="dens">DENS (Diplôme de l'École Normale Supérieure)</option>
                            <option value="dri">DRI (Direction des relations internationales Echange)</option>
                            <option value="agreg">AGREG</option>
                        </select>
                    </fieldset>
                    <div class="modal__submit">
                        <button class="modal__button modal__button--cancel">Annuler</button>
                        <button class="modal__button modal__button--start">Démarrer la normalisation</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- VUE 2 : Chargement -->
        <div class="modal__loader">
            <div class="modal__spinner"></div>
            <p class="modal__loader-text"><STRONG>Normalisation en cours...</STRONG></p>
            <p class="modal__loader-sub">Veuillez patienter</p>
        </div>
        <!-- VUE 3 : Succès -->
        <div class="modal__success">
            <i class="fa-solid fa-circle-check"></i>
            <p class="modal__success--text"><Strong>Normalisation terminée !</Strong></p>
            <div class="modal__success--file js-result-filename"></div>
            <button class="modal__button modal__button--download">Télécharger</button>
        </div>
    </div>