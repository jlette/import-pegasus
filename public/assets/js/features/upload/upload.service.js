import {
  showLoading,
  hideLoading,
  showSuccess,
} from "../modal/modal.controller.js";

/**
 * upload.service.js
 * Traitement métier du fichier (parsing, envoi serveur...).
 * Aucune dépendance au DOM ni à la validation.
 */

export function readFile(file) {
  // TODO: lire et parser le contenu du fichier
  console.log("Lecture du fichier :", file.name);
  console.log("Taille du fichier :", file.size, "octets");
}

export function sendFile(file) {
  showLoading(); // ← déclenche le loader
  // TODO: envoi au serveur via fetch/FormData
  console.log("Envoi du fichier :", file.name);
  console.log("Taille du fichier :", file.size, "octets");

  setTimeout(() => {
    // Simule une réponse serveur après 2 secondes
    console.log("Fichier envoyé avec succès !");
    hideLoading(); // ← cache le loader
    showSuccess(); // ← affiche le message de succès
  }, 2000);
}

// Fonction asynchrone (ES7)
export async function importFile(file, typeEtudiant, cursus) {
  showLoading();

  // 1. On prépare les données (comme un vrai formulaire d'envoi de fichier)
  const formData = new FormData();
  formData.append("admis_file", file); // 'file' sera la clé dans ton $_FILES en PHP
  formData.append("type_etudiant", typeEtudiant); // 'type_etudiant' sera la clé dans ton $_POST en PHP
  formData.append("cursus", cursus); // 'cursus' sera la clé dans ton $_POST en PHP

  try {
    // 2. On lance la requête AJAX avec Fetch
    const response = await fetch("/import-pegasus/public/api/import", {
      method: "POST",
      body: formData,
      // Note : avec FormData, il ne faut surtout pas mettre de 'Content-Type' manuel dans les headers,
      // le navigateur va générer le 'multipart/form-data' tout seul avec la bonne boundary !
    });

    // 3. On vérifie si PHP a renvoyé une erreur HTTP (ex: 400 ou 500)
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    // 4. On récupère la réponse de ton PHP (idéalement du JSON)
    const result = await response.json();

    // Afficher ton Toast de succès ici !
    console.log("Réponse PHP :", result);
    hideLoading();
    showSuccess(result.filename); // Affiche le nom du fichier traité dans la modal de succès
    console.log("Fichier traité avec succès", result);
  } catch (error) {
    hideLoading();
    // Afficher ton Toast Warning/Erreur ici !
    console.error("Échec de l'upload :", error);
  }
}
