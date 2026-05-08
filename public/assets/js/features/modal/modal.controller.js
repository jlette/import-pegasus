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
  document.body.classList.remove("no-scroll"); // Permet le scroll du body
  // Reset l'input pour permettre de resélectionner le même fichier
  const input = document.querySelector(".upload input[type='file']");
  input.value = "";
}
