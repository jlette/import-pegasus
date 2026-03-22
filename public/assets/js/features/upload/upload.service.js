/**
 * upload.service.js
 * Traitement métier du fichier (parsing, envoi serveur...).
 * Aucune dépendance au DOM ni à la validation.
 */

export function readFile(file) {
  // TODO: lire et parser le contenu du fichier
  console.log("Lecture du fichier :", file.name);
}

export function sendFile(file) {
  // TODO: envoi au serveur via fetch/FormData
  console.log("Envoi du fichier :", file.name);
}
