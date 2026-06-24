/**
 * upload.service.js
 * Traitement métier du fichier (parsing, envoi serveur...).
 * Aucune dépendance au DOM ni à la validation.
 */

import {
  showLoading,
  hideLoading,
  showSuccess,
} from "../modal/modal.controller.js";

/**
 * Lit et parse le contenu du fichier (Gargé de ta version initiale)
 */
export function readFile(file) {
  console.log("Lecture du fichier :", file.name);
  console.log("Taille du fichier :", file.size, "octets");
}

/**
 * Simule l'envoi d'un fichier (Gardé de ta version initiale si tu en as besoin)
 */
export function sendFile(file) {
  showLoading();
  console.log("Envoi du fichier :", file.name);
  console.log("Taille du fichier :", file.size, "octets");

  setTimeout(() => {
    console.log("Fichier envoyé avec succès !");
    hideLoading();
    showSuccess();
  }, 2000);
}

/**
 * Force le téléchargement du fichier CSV généré par le PHP
 * @param {string} filename Le nom du fichier retourné par le serveur
 */
function downloadGeneratedCsv(filename) {
  // CORRECTION : On utilise bien 'filename=' pour que le PHP s'y retrouve
  const downloadUrl = `/import-pegasus/public/api/download?filename=${encodeURIComponent(filename)}`;

  const link = document.createElement("a");
  link.href = downloadUrl;
  link.setAttribute("download", filename);

  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
/**
 * Envoie le fichier Excel au serveur et active le bouton de téléchargement de la modal
 */
export async function importFile(file, typeEtudiant, cursus) {
  showLoading();

  const formData = new FormData();
  formData.append("admis_file", file);
  formData.append("type_etudiant", typeEtudiant);
  formData.append("cursus", cursus);

  try {
    const response = await fetch("/import-pegasus/public/api/import", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    const result = await response.json();

    // 1. On cache le loader et on affiche la vue "Succès" de ta modal
    hideLoading();
    showSuccess(result.filename);

    // 2. Activation du bouton physique de téléchargement présent dans ta vue
    const downloadBtn = document.querySelector(".modal__button--download");
    if (downloadBtn && result.filename) {
      downloadBtn.onclick = () => {
        downloadGeneratedCsv(result.filename);
      };
    }

    console.log("Fichier traité avec succès", result);
  } catch (error) {
    hideLoading();
    console.error("Échec de l'upload :", error);
  }
}
