/**
 * upload.service.js
 * Traitement métier (requête HTTP).
 * ZÉRO dépendance au DOM (plus de modale, plus de boutons).
 */

export function readFile(file) {
  console.log("Lecture du fichier :", file.name, "-", file.size, "octets");
}

/**
 * Envoie le fichier Excel au serveur et retourne les données.
 * @returns {Promise<Object>} La réponse JSON du serveur
 */
export async function importFile(file, typeEtudiant, cursus) {
  const formData = new FormData();
  formData.append("admis_file", file);
  formData.append("type_etudiant", typeEtudiant);
  formData.append("cursus", cursus);

  try {
    const response = await fetch("/import-pegasus/public/api/import", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    // Si le serveur signale une erreur métier (422) ou système (500)
    if (!response.ok) {
      throw result; // On "jette" l'erreur pour que le contrôleur l'attrape
    }

    return result; // Si tout est OK, on retourne les données
  } catch (error) {
    // Si c'est notre objet JSON d'erreur renvoyé par PHP (ex: code 422)
    if (error && error.message) {
      throw error;
    }
    // Si c'est une vraie erreur réseau (serveur éteint, coupure internet)
    throw {
      message: "Erreur critique de communication avec le serveur.",
      erreurs: [],
    };
  }
}
