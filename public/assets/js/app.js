document.addEventListener("DOMContentLoaded", () => {
  // On instancie la classe uniquement si le formulaire existe
  if (document.querySelector("#csvForm")) {
    new CsvUploader("#csvForm", "#statusMessage");
  }
});
