/**
 * modal.controller.js
 * Gère l'ouverture et la fermeture de la modal.
 * Le nom du fichier est injecté dynamiquement à l'ouverture.
 */

const modal = document.querySelector(".modal");
const modalOverlay = document.querySelector(".modal__overlay");
const modalClose = document.querySelector(".modal__close");
const modalCancel = document.querySelector(".modal__cancel");
const modalFilename = document.querySelector(".modal__filename");
const modalTooltip = document.querySelector(".modal__tooltip");
const modalFile = document.querySelector(".modal__file");

export function initModal() {
  // Fermeture via le bouton croix
  modalClose.addEventListener("click", closeModal);

  // Fermeture via le bouton annuler
  modalCancel.addEventListener("click", closeModal);

  // Fermeture en cliquant sur l'overlay
  modalOverlay.addEventListener("click", closeModal);

  // Réafficher la tooltip au survol du fichier
  modalFile.addEventListener("mouseenter", () => {
    modalTooltip.classList.remove("modal__tooltip--hidden");
  });

  // Masquer la tooltip quand on appuie sur Échap
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      modalTooltip.classList.add("modal__tooltip--hidden");
    }
  });
}

/**
 * Ouvre la modal et injecte le nom du fichier.
 * @param {string} filename - Le nom du fichier déposé
 */
export function openModal(filename) {
  modalFilename.textContent = filename;
  modalTooltip.textContent = filename; // Tooltip natif du navigateur
  modal.classList.add("modal--is-active");
}

export function closeModal() {
  modal.classList.remove("modal--is-active");
  // Reset l'input pour permettre de resélectionner le même fichier
  const input = document.querySelector(".upload input[type='file']");
  input.value = "";
}
