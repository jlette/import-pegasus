/**
 * upload.controller.js
 * Gère les interactions utilisateur sur la zone d'upload.
 * Fait le lien entre le DOM, upload.validator.js et upload.service.js.
 */

import { showToast, setToastContent } from "../toast/toast.controller.js";
import { validateFile } from "./upload.validator.js";
import { readFile, sendFile, importFile } from "./upload.service.js";
import { openModal } from "../modal/modal.controller.js";

const dropZone = document.querySelector(".upload");
const input = document.querySelector(".upload input[type='file']");
const startBtn = document.querySelector(".modal__button--start");

let currentFile = null; // ← stocke le fichier courant
export function initUpload() {
  input.addEventListener("change", (e) => handleFile(e.target.files[0]));

  dropZone.addEventListener("dragenter", () =>
    dropZone.classList.add("upload--isdragover"),
  );

  dropZone.addEventListener("dragleave", (e) => {
    // Si on survole un enfant de la dropzone, on ne retire pas la classe
    if (dropZone.contains(e.relatedTarget)) return;
    dropZone.classList.remove("upload--isdragover");
  });
  dropZone.addEventListener("dragover", onDragOver);
  dropZone.addEventListener("drop", onDrop);

  // Bloque l'ouverture native du fichier si lâché hors de la dropZone
  window.addEventListener("dragover", onWindowDragOver);
  window.addEventListener("drop", onWindowDrop);

  startBtn.addEventListener("click", handleStartUpload);
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
    if (!dropZone.contains(e.target)) {
      e.dataTransfer.dropEffect = "none";
    }
  }
}

function onWindowDrop(e) {
  if ([...e.dataTransfer.items].some((item) => item.kind === "file")) {
    e.preventDefault();
  }
}

function handleFile(file) {
  currentFile = file;
  console.log("Fichier", file);
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

  readFile(file);
  openModal(file.name); // Ouvre la modal avec le vrai nom du fichier
}

/**
 * Fonction dédiée à la préparation et au lancement de l'import
 * Responsabilité : Récupérer les données du DOM, valider, et appeler le service.
 */
function handleStartUpload() {
  if (!currentFile) return; // Sécurité

  // 1. On récupère les valeurs
  const studentSelect = document.getElementById("student-select");
  const typeEtudiant = studentSelect ? studentSelect.value : "";

  const cursusSelect = document.getElementById("cursus-select");
  const cursus = cursusSelect ? cursusSelect.value : "";

  // 2. Validation
  if (typeEtudiant === "" || (typeEtudiant !== "agreg" && cursus === "")) {
    setToastContent(
      "Information manquante",
      "Veuillez sélectionner un type d'étudiant et un cursus.",
    );
    showToast();
    return; // On stoppe l'exécution
  }

  // 3. Appel du service
  importFile(currentFile, typeEtudiant, cursus);
}
