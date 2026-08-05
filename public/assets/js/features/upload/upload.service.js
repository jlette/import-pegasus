/**
 * upload.service.js
 * Traitement métier du fichier (parsing, envoi serveur...).
 * Aucune dépendance au DOM ni à la validation.
 */

import {
  showLoading,
  hideLoading,
  showSuccess,
  showError,
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

    // 1. On lit le JSON TOUT DE SUITE, même si c'est une erreur 422
    const result = await response.json();

    // 2. Si le serveur signale une erreur (ex: Code 422)
    if (!response.ok) {
      // S'il y a notre tableau d'erreurs (lignes invalides)
      if (result.erreurs && result.erreurs.length > 0) {
        // 1. Création de la liste <ul>
        const ul = document.createElement("ul");
        ul.classList.add("modal__error--list"); // Ta classe CSS qui gère le design

        // 2. Création et injection des <li> de manière sécurisée
        result.erreurs.forEach((err) => {
          const li = document.createElement("li");
          li.textContent = err; // Protège contre toute faille XSS
          ul.appendChild(li);
        });

        // 3. On passe le message général ET l'élément DOM à la modale
        showError(result.message, ul, result.erreurs);
      } else {
        // Erreur classique sans détails
        showError(result.message || "Une erreur inconnue est survenue.");
      }
      return; // 🛑 ON S'ARRÊTE ICI. On ne va pas au succès.
    }

    // 3. SI TOUT EST OK (Code 200)
    hideLoading();
    showSuccess(result.filename);

    const downloadBtn = document.querySelector(".modal__button--download");
    if (downloadBtn && result.filename) {
      downloadBtn.onclick = () => {
        downloadGeneratedCsv(result.filename);

        // Vider l'input pour pouvoir relancer le même fichier corrigé sans faire F5
        const fileInput = document.getElementById("file");
        if (fileInput) fileInput.value = "";
      };
    }
  } catch (error) {
    // Ce catch ne s'activera que si le serveur crash (500) ou si le PHP ne renvoie pas du JSON
    console.error("Erreur réseau :", error);
    showError("Erreur critique de communication avec le serveur.");
  }
}
