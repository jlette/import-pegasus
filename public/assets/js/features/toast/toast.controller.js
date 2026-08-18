const toast = document.querySelector(".toast");
const toastTitle = toast.querySelector(".toast__title strong");
const toastText = toast.querySelector(".toast__text");
const toastClose = toast.querySelector(".toast__close");

export function initToast() {
  toastClose.addEventListener("click", () => {
    toast.classList.remove("toast--visible");
  });
}

export function showToast() {
  toast.classList.add("toast--visible");
  // Masquer le toast après 3 secondes
  setTimeout(() => {
    toast.classList.remove("toast--visible");
  }, 3000);
}

export function setToastContent(title, text) {
  toastTitle.textContent = title;
  toastText.textContent = text;
}
