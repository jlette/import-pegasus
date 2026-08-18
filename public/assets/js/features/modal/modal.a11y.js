/**
 * modal.a11y.js
 * Comportement clavier de la modale.
 *
 * Une modale accessible doit garantir quatre choses, qu'un simple basculement
 * de classe CSS n'apporte pas :
 *   - le focus entre dans la modale à l'ouverture ;
 *   - la tabulation y reste enfermée tant qu'elle est ouverte ;
 *   - la touche Échap la ferme ;
 *   - le focus revient à l'élément qui l'avait avant l'ouverture.
 *
 * L'élément <dialog> natif rendrait tout cela gratuitement, mais imposerait de
 * revoir les transitions CSS : le navigateur bascule `display`, ce qui coupe
 * l'animation d'ouverture. Le passage à <dialog> reste la cible propre, à
 * faire par quelqu'un qui peut vérifier le rendu.
 */

const SELECTEUR_FOCALISABLE = [
  "a[href]",
  "button:not([disabled])",
  "input:not([disabled])",
  "select:not([disabled])",
  "textarea:not([disabled])",
  '[tabindex]:not([tabindex="-1"])',
].join(", ");

/** Éléments d'arrière-plan rendus inertes pendant l'ouverture. */
const SELECTEUR_ARRIERE_PLAN = "header, main, footer";

let elementFocalisePrecedemment = null;

/**
 * Éléments focalisables réellement visibles de la modale.
 *
 * Le filtre sur offsetParent écarte les panneaux d'état masqués : sans lui, la
 * tabulation traverserait les boutons du panneau d'erreur alors que le
 * formulaire est affiché.
 */
function elementsFocalisables(modal) {
  return Array.from(modal.querySelectorAll(SELECTEUR_FOCALISABLE)).filter(
    (el) => el.offsetParent !== null && !el.hasAttribute("inert"),
  );
}

function surTabulation(modal, e) {
  const focalisables = elementsFocalisables(modal);

  if (focalisables.length === 0) {
    e.preventDefault();
    return;
  }

  const premier = focalisables[0];
  const dernier = focalisables[focalisables.length - 1];

  if (e.shiftKey && document.activeElement === premier) {
    e.preventDefault();
    dernier.focus();
  } else if (!e.shiftKey && document.activeElement === dernier) {
    e.preventDefault();
    premier.focus();
  }
}

/**
 * Active le comportement clavier. Retourne le gestionnaire à détacher.
 */
export function activerPiegeDeFocus(modal, fermer) {
  elementFocalisePrecedemment = document.activeElement;

  modal.setAttribute("aria-hidden", "false");

  document.querySelectorAll(SELECTEUR_ARRIERE_PLAN).forEach((el) => {
    el.setAttribute("inert", "");
  });

  const gestionnaire = (e) => {
    if (e.key === "Escape") {
      e.preventDefault();
      fermer();
    } else if (e.key === "Tab") {
      surTabulation(modal, e);
    }
  };

  modal.addEventListener("keydown", gestionnaire);

  // Le focus est donné après le rendu, l'ouverture étant animée.
  requestAnimationFrame(() => {
    const [premier] = elementsFocalisables(modal);
    if (premier) premier.focus();
  });

  return gestionnaire;
}

/**
 * Rend l'arrière-plan de nouveau utilisable et restitue le focus.
 */
export function libererPiegeDeFocus(modal, gestionnaire) {
  if (gestionnaire) modal.removeEventListener("keydown", gestionnaire);

  modal.setAttribute("aria-hidden", "true");

  document.querySelectorAll(SELECTEUR_ARRIERE_PLAN).forEach((el) => {
    el.removeAttribute("inert");
  });

  // Sans cela, le focus retomberait sur <body> et l'utilisateur au clavier
  // devrait retraverser toute la page.
  if (elementFocalisePrecedemment instanceof HTMLElement) {
    elementFocalisePrecedemment.focus();
  }
  elementFocalisePrecedemment = null;
}
