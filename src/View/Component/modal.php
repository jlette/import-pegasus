<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-titre"
    aria-hidden="true">
    <div class="modal__overlay"></div>
    <div class="modal__container">

        <button type="button" class="modal__close" aria-label="Fermer la fenêtre">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" fill="none">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="modal__content">
            <!-- Colonne Gauche -->
            <div class="modal__left">
                <!-- Ajout de la classe "tooltip" sur la carte -->
                <div class="file-card tooltip">
                    <div class="file-icon">
                        <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" fill="none">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <!-- Le texte qui sera coupé s'il est trop long -->
                    <span class="modal__filename">fichier.xlsx</span>
                    <!-- Le texte complet caché qui apparaît au survol (injecté par ton JS) -->
                    <span class="tooltip__text js-modal-tooltip-text">fichier.xlsx</span>
                </div>
                <button type="button" class="btn btn--ghost js-restart" style="font-size: 0.8rem; padding: 5px 10px;">
                    ⟲ Remplacer le fichier
                </button>
            </div>

            <!-- Colonne Droite -->
            <div class="modal__right">
                <h2 class="modal__title" id="modal-titre">Normalisation du fichier</h2>
                <form class="modal__form" id="import-form">

                    <div class="form-group">
                        <label for="student-select" class="form-label">Type d'étudiant</label>
                        <select id="student-select" name="type_etudiant" class="form-select">
                            <option value="">-- Sélectionnez le type --</option>
                            <option value="dens">DENS (Diplôme de l'École Normale Supérieure)</option>
                            <option value="dri">DRI (Relations Internationales)</option>
                            <option value="agreg">Agrégation</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="annee-campagne" class="form-label">Année de la campagne</label>
                        <input type="number" id="annee-campagne" name="annee" class="form-select"
                            value="<?= date('Y') ?>"
                            min="<?= date('Y') - 1 ?>" max="<?= date('Y') + 1 ?>" step="1" required
                            aria-describedby="annee-campagne-aide">
                        <p id="annee-campagne-aide" class="form-help">
                            Année de l'inscription administrative. À corriger pour un import
                            de décembre portant sur la rentrée de janvier.
                        </p>
                    </div>

                    <!-- L'injection JS vient ici -->

                    <div class="modal__submit-area">
                        <button type="button" class="btn btn--ghost js-error-close">Annuler</button>
                        <button type="button" class="btn btn--primary modal__button--start">Démarrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- États cachés -->
        <div class="modal__state modal__loader" role="status" aria-live="polite">
            <div class="spinner"></div>
            <p style="color: var(--color-text-muted); font-weight: 500;">Normalisation en cours...</p>
        </div>

        <div class="modal__state modal__success" role="status" aria-live="polite">
            <div class="state-icon state-icon--success">✓</div>
            <h3>Terminé !</h3>
            <p class="js-result-filename"
                style="margin-bottom: 0.5rem; color: var(--color-text-muted); font-family: monospace;"></p>
            <p class="js-result-count" style="margin-bottom: 1rem; color: var(--color-text-muted);"></p>
            <!-- Réserves sur un import pourtant abouti : codes concours issus du
                 repli embarqué faute d'annuaire. role="alert" plutôt que le
                 role="status" du bloc parent : c'est une information que le
                 gestionnaire ne doit pas pouvoir manquer avant de télécharger. -->
            <div class="js-result-avertissement modal__warning" role="alert" hidden></div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn--ghost js-restart">Recommencer</button>
                <button type="button" class="btn btn--primary modal__button--download">Télécharger</button>
            </div>
        </div>

        <div class="modal__state modal__error" role="alert">
            <div class="state-icon state-icon--error">✕</div>
            <h3>Erreur détectée</h3>
            <div class="js-error-detail" style="width: 100%;"></div>
            <div style="display: flex; gap: 10px; margin-top: 1rem;">
                <button type="button" class="btn btn--ghost js-error-download" style="display:none;">Rapport
                    TXT</button>
                <button type="button" class="btn btn--ghost js-restart">Annuler</button>
                <button type="button" class="btn btn--primary js-error-retry">Réessayer</button>
            </div>
        </div>

    </div>
</div>