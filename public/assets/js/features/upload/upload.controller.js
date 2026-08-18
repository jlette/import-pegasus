/**
 * upload.controller.js
 * Gère les interactions utilisateur sur la zone d'upload.
 */

import { showToast, setToastContent } from "../toast/toast.controller.js";
import { validateFile } from "./upload.validator.js";
import { importFile } from "./upload.service.js";
import {
  openModal,
  showLoading,
  hideLoading,
  showSuccess,
  showError,
} from "../modal/modal.controller.js";

const dropZone = document.querySelector(".upload");
const input = document.querySelector(".upload input[type='file']");
const startBtn = document.querySelector(".modal__button--start");

let currentFile = null;

export function initUpload() {
  input.addEventListener("change", (e) => handleFile(e.target.files[0]));

  dropZone.addEventListener("dragenter", () =>
    dropZone.classList.add("upload--isdragover"),
  );

  dropZone.addEventListener("dragleave", (e) => {
    if (dropZone.contains(e.relatedTarget)) return;
    dropZone.classList.remove("upload--isdragover");
  });

  dropZone.addEventListener("dragover", onDragOver);
  dropZone.addEventListener("drop", onDrop);

  window.addEventListener("dragover", onWindowDragOver);
  window.addEventListener("drop", onWindowDrop);

  startBtn.addEventListener("click", handleStartUpload);

  // Clic souris : toute la zone en pointillés réagit. Le <label> et l'input
  // portent déjà l'activation au clavier, prise en charge nativement.
  dropZone.addEventListener("click", (e) => {
    if (e.target === input || e.target.closest(".upload__label")) return;
    input.click();
  });
}

function onDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = "copy";
}

function onDrop(e) {
  e.preventDefault();
  dropZone.classList.remove("upload--isdragover");
  handleFile(e.dataTransfer.files[0]);
}

function onWindowDragOver(e) {
  if ([...e.dataTransfer.items].some((item) => item.kind === "file")) {
    e.preventDefault();
    if (!dropZone.contains(e.target)) e.dataTransfer.dropEffect = "none";
  }
}

function onWindowDrop(e) {
  if ([...e.dataTransfer.items].some((item) => item.kind === "file"))
    e.preventDefault();
}

function handleFile(file) {
  currentFile = file;
  if (!file) return;

  if (!validateFile(file)) {
    setToastContent(
      "Format non supporté",
      "Veuillez sélectionner un fichier au format xls ou xlsx.",
    );
    showToast();
    input.value = "";
    return;
  }

  openModal(file.name);
}

/**
 * Fonction asynchrone qui orchestre l'import (Validation -> Loader -> Service -> Success/Error)
 */
async function handleStartUpload() {
  if (!currentFile) return;

  const studentSelect = document.getElementById("student-select");
  const typeEtudiant = studentSelect ? studentSelect.value : "";
  const cursusSelect = document.getElementById("cursus-select");
  const cursus = cursusSelect
    ? cursusSelect.value
    : typeEtudiant === "dri"
      ? "dri"
      : "";

  if (
    typeEtudiant === "" ||
    (typeEtudiant !== "agreg" && typeEtudiant !== "dri" && cursus === "")
  ) {
    setToastContent(
      "Information manquante",
      "Veuillez sélectionner un type d'étudiant et un cursus.",
    );
    showToast();
    return;
  }

  const anneeInput = document.getElementById("annee-campagne");
  const annee = anneeInput ? anneeInput.value : "";

  showLoading();

  try {
    // On attend la réponse du service
    const result = await importFile(currentFile, typeEtudiant, cursus, annee);

    // Succès
    hideLoading();
    showSuccess(result.filename, result.nb_importes, result.nb_ecartes);
    input.value = ""; // Réinitialise l'input pour pouvoir relancer le même fichier
  } catch (errorResult) {
    // Erreur métier ou réseau
    if (errorResult.erreurs && errorResult.erreurs.length > 0) {
      const ul = document.createElement("ul");
      ul.classList.add("modal__error--list");
      errorResult.erreurs.forEach((err) => {
        const li = document.createElement("li");
        li.textContent = err;
        ul.appendChild(li);
      });
      showError(errorResult.message, ul, errorResult.erreurs);
    } else {
      showError(errorResult.message || "Une erreur inconnue est survenue.");
    }
  }
}
