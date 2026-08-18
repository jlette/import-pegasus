/**
 * upload.service.js
 * Traitement métier (requête HTTP).
 */

/**
 * Préfixe d'URL de l'application, injecté par le serveur.
 *
 * Il était auparavant codé en dur : toute modification de APP_BASE_URL cassait
 * silencieusement le front, qui continuait d'appeler l'ancien chemin.
 */
export function baseUrl() {
  return document.body.dataset.baseUrl ?? "";
}

/**
 * Envoie le fichier Excel au serveur et retourne les données.
 * @returns {Promise<Object>} La réponse JSON du serveur
 */
export async function importFile(file, typeEtudiant, cursus, annee) {
  const formData = new FormData();
  formData.append("admis_file", file);
  formData.append("type_etudiant", typeEtudiant);
  formData.append("cursus", cursus);
  formData.append("annee", annee);

  try {
    const response = await fetch(`${baseUrl()}/api/import`, {
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
