import { baseUrl } from "../upload/upload.service.js";
import {
  activerPiegeDeFocus,
  libererPiegeDeFocus,
} from "./modal.a11y.js";

/**
 * modal.controller.js
 * Gère l'ouverture, la fermeture et l'état visuel de la modale.
 * Utilise la délégation d'événements pour éviter les "Ghost Events".
 */

const modal = document.querySelector(".modal");
export const modalOverlay = document.querySelector(".modal__overlay");
export const modalClose = document.querySelector(".modal__close");
const modalFilename = document.querySelector(".modal__filename");
const modalTooltipContent = modal.querySelector(".js-modal-tooltip-text");

// Stockage de l'état (State Management)
let currentSuccessFilename = null;
let currentRawErrors = null;
let gestionnaireClavier = null;

export function initModal() {
  // Délégation globale : On écoute tous les clics dans la modale
  modal.addEventListener("click", (e) => {
    // 1. Boutons de fermeture
    if (
      e.target.closest(".modal__close") ||
      e.target.closest(".js-error-close")
    ) {
      closeModal();
    }
    // 2. Boutons "Recommencer"
    else if (e.target.closest(".js-restart")) {
      restartAndPromptFile();
    }
    // 3. Bouton "Réessayer"
    else if (e.target.closest(".js-error-retry")) {
      hideError();
    }
    // 4. Bouton "Télécharger le rapport TXT"
    else if (e.target.closest(".js-error-download")) {
      if (currentRawErrors && currentRawErrors.length > 0) {
        generateErrorFile(currentRawErrors);
      }
    }
    // 5. Bouton "Télécharger le CSV"
    else if (
      e.target.closest(".modal__button--download:not(.js-error-download)")
    ) {
      if (currentSuccessFilename) {
        downloadGeneratedCsv(currentSuccessFilename);
      }
    }
  });

  // Fermeture en cliquant sur l'overlay
  modalOverlay.addEventListener("click", closeModal);
}

export function openModal(filename) {
  modalFilename.textContent = filename;
  modalTooltipContent.textContent = filename;
  modal.classList.add("modal--is-active");
  document.body.classList.add("no-scroll");

  gestionnaireClavier = activerPiegeDeFocus(modal, closeModal);
}

export function closeModal() {
  modal.classList.remove("modal--is-active");
  document.body.classList.remove("no-scroll");

  libererPiegeDeFocus(modal, gestionnaireClavier);
  gestionnaireClavier = null;

  const loader = modal.querySelector(".modal__loader");
  if (loader) loader.classList.remove("modal__loader--is-active");

  const success = modal.querySelector(".modal__success");
  if (success) success.classList.remove("modal__success--is-active");

  const error = modal.querySelector(".modal__error");
  if (error) error.classList.remove("modal__error--is-active");

  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";

  // Réinitialisation de l'état
  currentSuccessFilename = null;
  currentRawErrors = null;
  const input = document.querySelector(".upload input[type='file']");
  if (input) input.value = "";
}

export function restartAndPromptFile() {
  closeModal();
  const input = document.querySelector(".upload input[type='file']");
  if (input) input.click();
}

export function showLoading() {
  const loader = modal.querySelector(".modal__loader");
  loader.classList.add("modal__loader--is-active");
  modalClose.style.visibility = "hidden";
  modalOverlay.style.pointerEvents = "none";
}

export function hideLoading() {
  const loader = modal.querySelector(".modal__loader");
  loader.classList.remove("modal__loader--is-active");
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";
}

export function showSuccess(filename, nbImportes = null, nbEcartes = null) {
  currentSuccessFilename = filename; // Sauvegarde dans l'état global
  const success = modal.querySelector(".modal__success");
  const nameEl = modal.querySelector(".js-result-filename");

  if (nameEl && filename) nameEl.textContent = filename;

  // Le gestionnaire doit pouvoir rapprocher ces nombres de ce qu'il attend
  // avant de télécharger : les exports contiennent souvent des non-admis.
  const countEl = modal.querySelector(".js-result-count");
  if (countEl && nbImportes !== null) {
    countEl.textContent =
      nbEcartes > 0
        ? `${nbImportes} étudiant(s) retenu(s), ${nbEcartes} ligne(s) écartée(s) (non-admis ou désistements).`
        : `${nbImportes} étudiant(s) retenu(s).`;
  }

  success.classList.add("modal__success--is-active");
  modalClose.style.visibility = "hidden";
  modalOverlay.style.pointerEvents = "none";

  deplacerLeFocusVers(success);
}

export function showError(message, domElement = null, rawErrors = null) {
  currentRawErrors = rawErrors; // Sauvegarde dans l'état global
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

  const downloadBtn = error.querySelector(".js-error-download");
  if (downloadBtn) {
    downloadBtn.style.display =
      rawErrors && rawErrors.length > 0 ? "inline-block" : "none";
  }

  error.classList.add("modal__error--is-active");
  modalClose.style.visibility = "hidden";
  modalOverlay.style.pointerEvents = "none";

  deplacerLeFocusVers(error);
}

export function hideError() {
  const error = modal.querySelector(".modal__error");
  error.classList.remove("modal__error--is-active");
  modalClose.style.visibility = "visible";
  modalOverlay.style.pointerEvents = "auto";
}

/**
 * Amène le focus sur le premier bouton du panneau qui vient de s'afficher.
 *
 * Le formulaire disparaît au profit d'un panneau d'état : sans ce déplacement,
 * le focus resterait sur un élément devenu invisible et l'utilisateur au
 * clavier se retrouverait sans point de repère.
 */
function deplacerLeFocusVers(panneau) {
  requestAnimationFrame(() => {
    const bouton = panneau.querySelector("button");
    if (bouton) bouton.focus();
  });
}

// === Fonctions utilitaires de téléchargement ===

function generateErrorFile(errors) {
  const date = new Date().toLocaleString("fr-FR");
  let textContent = `Rapport d'erreurs d'importation PEGASUS\n`;
  textContent += `Généré le : ${date}\n`;
  textContent += `=======================================\n\n`;

  errors.forEach((err) => {
    textContent += `- ${err}\n`;
  });

  const blob = new Blob([textContent], { type: "text/plain;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "rapport_erreurs_pegasus.txt";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function downloadGeneratedCsv(filename) {
  const downloadUrl = `${baseUrl()}/api/download?filename=${encodeURIComponent(filename)}`;
  const link = document.createElement("a");
  link.href = downloadUrl;
  link.setAttribute("download", filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
