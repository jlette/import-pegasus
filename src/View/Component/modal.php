<div class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__body">
        <div class="modal__header">
            <button class="modal__close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal__content">
            <div class="modal__preview">
                <div class="modal__file">
                    <i class="modal__icon fa-solid fa-file-csv"></i>
                    <span class=" modal__filename"></span>
                    <div class="modal__tooltip"></div>
                </div>
            </div>
            <div class="modal__actions">
                <div class="modal__form">
                    <fieldset class="modal__field">
                        <legend>Type d'étudiant</legend>
                        <select id="student-select">
                            <option value="">-- Sélectionnez --</option>
                            <option value="dens">DENS (Diplôme de l'École Normale Supérieure)</option>
                            <option value="dri">DRI (Direction des relations internationales Echange)</option>
                            <option value="agreg">AGREG</option>
                        </select>
                    </fieldset>
                    <fieldset class="modal__field">
                        <legend>Choix du cursus</legend>
                        <select id="cursus-select">
                            <option value="">-- Sélectionnez --</option>
                            <optgroup label="CPGE (Classe préparatoire au grande école)">
                                <option value="scei">SCEI</option>
                                <option value="al">A/L (Lettres)</option>
                                <option value="bl">B/L (Lettres Sciences Sociales)</option>
                            </optgroup>
                            <optgroup label="SI (Sélection international)">
                                <option value="sil">Lettre</option>
                                <option value="sis">Sciences</option>
                            </optgroup>
                            <optgroup label="NE (Normalien etudiant)">
                                <option value="nel">NEL (normaliens étudiant lettre)</option>
                                <option value="nes">NES (Normaliens étudiant science)</option>
                                <option value="nemh">NEMH (Normalien étudiant médecine humanité)</option>
                                <option value="nems">NEMS (normalien étudiant médecine science)</option>
                            </optgroup>
                        </select>
                    </fieldset>
                    <button class="modal__button modal__button--start">Lancer la normalisation</button>
                </div>
            </div>
        </div>
    </div>
</div>