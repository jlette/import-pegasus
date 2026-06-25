/**
 * modal.controller.js
 * Gère l'ouverture et la fermeture de la modal.
 * Le nom du fichier est injecté dynamiquement à l'ouverture.
 */

const modal = document.querySelector(".modal");
export const modalOverlay = document.querySelector(".modal__overlay");
export const modalClose = document.querySelector(".modal__close");
export const modalDownloadBtn = modal.querySelector(".modal__button--download");
const modalFilename = document.querySelector(".modal__filename");
const modalTooltipContent = modal.querySelector(".js-modal-tooltip-text");

export function initModal() {
  // Fermeture via le bouton croix
  modalClose.addEventListener("click", closeModal);

  // Fermeture en cliquant sur l'overlay
  modalOverlay.addEventListener("click", closeModal);

  /*   // Réafficher la tooltip au survol du fichier
  modalFile.addEventListener("mouseenter", () => {
    modalTooltip.classList.remove("modal__tooltip--hidden");
  });

  // Masquer la tooltip quand on appuie sur Échap
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      modalTooltip.classList.add("modal__tooltip--hidden");
    }
  }); */
}

/**
 * Ouvre la modal et injecte le nom du fichier.
 * @param {string} filename - Le nom du fichier déposé
 */
export function openModal(filename) {
  modalFilename.textContent = filename;
  modalTooltipContent.textContent = filename; // Tooltip personnalisée
  modal.classList.add("modal--is-active");
  document.body.classList.add("no-scroll"); // Empêche le scroll du body
}

export function closeModal() {
  modal.classList.remove("modal--is-active");
  document.body.classList.remove("no-scroll");

  // 🧹 Réinitialiser TOUTES les vues sans exception
  const loader = modal.querySelector(".modal__loader");
  if (loader) loader.classList.remove("modal__loader--is-active");

  const success = modal.querySelector(".modal__success");
  if (success) success.classList.remove("modal__success--is-active");

  // 👉 LA CORRECTION EST ICI : On force la désactivation de l'erreur
  const error = modal.querySelector(".modal__error");
  if (error) error.classList.remove("modal__error--is-active");

  // Sécurité — rétablir la fermeture au cas où
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";

  // Reset l'input
  const input = document.querySelector(".upload input[type='file']");
  if (input) input.value = "";
}

export function showLoading() {
  const loader = modal.querySelector(".modal__loader");
  loader.classList.add("modal__loader--is-active");

  modalClose.style.visibility = "hidden"; // croix cachée
  modalOverlay.style.pointerEvents = "none"; // overlay bloqué
}
export function hideLoading() {
  const loader = modal.querySelector(".modal__loader");
  loader.classList.remove("modal__loader--is-active");

  // Rétablir la fermeture
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";
}

export function showSuccess(filename) {
  const success = modal.querySelector(".modal__success");
  const nameEl = modal.querySelector(".js-result-filename");

  if (nameEl && filename) nameEl.textContent = filename;

  // Croix fermeture
  const closeBtn = success.querySelector(".modal__close");
  closeBtn.addEventListener("click", closeModal);

  // Bouton recommencer
  const restartBtn = success.querySelector(".js-restart");
  restartBtn.addEventListener("click", closeModal);

  // Overlay — déjà branché dans initModal(), rien à faire

  success.classList.add("modal__success--is-active");
  modalClose.style.visibility = "hidden"; // cache la croix du header
  modalOverlay.style.pointerEvents = "none"; // désactive l'overlay
}

export function hideSuccess() {
  const success = modal.querySelector(".modal__success");
  success.classList.remove("modal__success--is-active");
}

// On ajoute le paramètre rawErrors (qui sera notre tableau d'erreurs PHP)
export function showError(message, domElement = null, rawErrors = null) {
  const error = modal.querySelector(".modal__error");
  const detailEl = modal.querySelector(".js-error-detail");

  if (detailEl) {
    detailEl.replaceChildren();
    if (message) {
      const p = document.createElement("p");
      p.textContent = message;
      detailEl.appendChild(p);
    }
    if (domElement instanceof HTMLElement) {
      detailEl.appendChild(domElement);
    }
  }

  const modalLoader = modal.querySelector(".modal__loader");
  if (modalLoader) modalLoader.classList.remove("modal__loader--is-active");

  const closeBtn = error.querySelector(".modal__close");
  const closeCancelBtn = error.querySelector(".js-error-close");
  const retryBtn = error.querySelector(".js-error-retry");

  if (closeBtn) closeBtn.onclick = closeModal;
  if (closeCancelBtn) closeCancelBtn.onclick = closeModal;
  if (retryBtn) {
    retryBtn.onclick = () => {
      hideError();
    };
  }

  // --- NOUVEAU : Gestion du téléchargement du fichier TXT ---
  const downloadBtn = error.querySelector(".js-error-download");
  if (downloadBtn) {
    // S'il y a des erreurs dans le tableau, on active le bouton
    if (rawErrors && rawErrors.length > 0) {
      downloadBtn.style.display = "inline-block";
      downloadBtn.onclick = () => generateErrorFile(rawErrors);
    } else {
      downloadBtn.style.display = "none";
    }
  }
  // ----------------------------------------------------------

  error.classList.add("modal__error--is-active");
  modalClose.style.visibility = "hidden";
  modalOverlay.style.pointerEvents = "none";
}

/**
 * Fonction utilitaire qui génère le fichier .txt à la volée
 */
function generateErrorFile(errors) {
  const date = new Date().toLocaleString("fr-FR");

  // 1. On prépare le texte du fichier
  let textContent = `Rapport d'erreurs d'importation PEGASUS\n`;
  textContent += `Généré le : ${date}\n`;
  textContent += `=======================================\n\n`;

  errors.forEach((err) => {
    textContent += `- ${err}\n`;
  });

  // 2. On crée un "fichier virtuel" en mémoire (Blob)
  const blob = new Blob([textContent], { type: "text/plain;charset=utf-8" });
  const url = URL.createObjectURL(blob);

  // 3. On simule un clic sur un lien invisible pour forcer le téléchargement
  const a = document.createElement("a");
  a.href = url;
  a.download = "rapport_erreurs_pegasus.txt";
  document.body.appendChild(a);
  a.click();

  // 4. On nettoie le navigateur
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

export function hideError() {
  const error = modal.querySelector(".modal__error");
  error.classList.remove("modal__error--is-active");
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";
}
