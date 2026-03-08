<div class="modal">
    <div class="modal__overlay"></div>
    <div class="modal__body">
        <div class="modal__header">
            <button class="modal__close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal__content">
            <div class="modal__preview">
                <label class="modal__add" for="csv_file">
                    <span class="modal__count">5</span>
                    <i class="fa-solid fa-plus"></i>
                </label>
                <div class="modal__scroller">
                    <ul class="modal__list">
                        <li class="modal__file"><i class="modal__icon fa-solid fa-file-csv"></i><span
                                class="modal__filename">fichier1.csv</span></li>
                        <li class="modal__file"><i class="modal__icon fa-solid fa-file-csv"></i><span
                                class="modal__filename">fichier2.csv</span></li>
                        <li class="modal__file"><i class="modal__icon fa-solid fa-file-csv"></i><span
                                class="modal__filename">fichier3.csv</span></li>
                        <li class="modal__file"><i class="modal__icon fa-solid fa-file-csv"></i><span
                                class="modal__filename">fichier4.csv</span></li>
                        <li class="modal__file"><i class="modal__icon fa-solid fa-file-csv"></i><span
                                class="modal__filename">fichier5.csv</span></li>
                    </ul>
                </div>

            </div>
            <div class="modal__actions">
                <div class="modal__description">Lorem Ipsum is simply dummy text of the printing and typesetting
                    industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                    unknown printer took a galley of type and scrambled it to make a type specimen book.</div>
                <button class="modal__cancel">Annuler</button>
                <button class="modal__download">Télécharger le fichier normalisé</button>
            </div>
        </div>
    </div>
</div>
<div class="upload">
    <div class="upload__col">
        <h2 class="upload__title">Normaliser des fichiers CSV</h2>

        <p class="upload__description">Transformez vos données brutes en un canevas d'import parfait. Ce normalisateur
            vérifie les informations de vos
            admis
            (statuts, cursus, concours) et s'assure de leur totale conformité avec le système PEGASUS.</p>

        <div class="upload__form">
            <label class="upload__label" for="csv_file"><i class="fa-solid fa-upload"></i><span
                    class="upload__lable__text">Sélectionnez un fichier</span></label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" hidden>
        </div>


    </div>

    <span class="upload__icon"><i class=" fa-solid fa-file-csv"></i></span>


</div>