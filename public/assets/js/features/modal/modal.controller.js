/**
 * modal.controller.js
 * Gère l'ouverture et la fermeture de la modal.
 * Le nom du fichier est injecté dynamiquement à l'ouverture.
 */

const modal = document.querySelector(".modal");
export const modalOverlay = document.querySelector(".modal__overlay");
export const modalClose = document.querySelector(".modal__close");
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

  // Réinitialiser toutes les vues
  modal
    .querySelector(".modal__loader")
    .classList.remove("modal__loader--is-active");
  modal
    .querySelector(".modal__success")
    .classList.remove("modal__success--is-active");

  // Sécurité — rétablir la fermeture au cas où on ferme pendant le chargement
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

  success.classList.add("modal__success--is-active");
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "none";
}

export function hideSuccess() {
  const success = modal.querySelector(".modal__success");
  success.classList.remove("modal__success--is-active");
}
