import { CURSUS_DENS, STUDENTS } from "./select.validator.js";
import { modalClose, modalOverlay } from "../modal/modal.controller.js";
const form = document.querySelector(".modal__form");
const studentSelected = document.getElementById("student-select");
const button = form.querySelector(".modal__submit");
let fieldset;
let select;
let isStudentSelected = "";

export function initSelect() {
  studentSelected.addEventListener("change", handleStudentChange);

  modalClose.addEventListener("click", exitSelect);

  modalOverlay.addEventListener("click", exitSelect);
}

function handleStudentChange() {
  getStudentOptions();
  const fieldsetExistedBefore = fieldset?.parentNode;
  if (fieldsetExistedBefore) {
    resetSelect();
  }

  if (
    isStudentSelected === STUDENTS.agreg ||
    isStudentSelected === STUDENTS.dri
  )
    return;

  if (isStudentSelected !== "") {
    const newFieldset = setFieldset();
    form.insertBefore(newFieldset, button);

    //Animation uniquement si le fieldset n'existait pas avant
    if (!fieldsetExistedBefore) {
      requestAnimationFrame(() => {
        fieldset.classList.add("field--is-visible");
      });
    } else {
      // Déjà visible, on ajoute la classe sans transition
      fieldset.classList.add("field--is-visible");
    }

    setCursusOptions(isStudentSelected);
  }
}

function setCursusOptions(studentType) {
  switch (studentType) {
    case STUDENTS.dens:
      return setSelectWithLabel(CURSUS_DENS);
    case STUDENTS.dri:
      return null;
    case STUDENTS.agreg:
      return null; // Pas de cursus spécifiques pour les agrégés
  }
}

function getStudentOptions() {
  isStudentSelected = studentSelected.value;
  console.log("Type d'étudiant sélectionné :", isStudentSelected);
}

function exitSelect() {
  fieldset.parentNode.removeChild(fieldset);
  studentSelected.value = "";
}

function resetSelect() {
  fieldset.parentNode.removeChild(fieldset);
}

function setFieldset() {
  fieldset = document.createElement("fieldset");
  const legend = document.createElement("legend");
  select = document.createElement("select");
  const option = document.createElement("option");

  fieldset.classList.add("modal__field", "modal__field--anim");

  legend.textContent = "Choix du cursus";
  select.id = "cursus-select";
  option.value = "";
  option.textContent = "-- Sélectionnez le type de cursus --";
  select.appendChild(option);
  fieldset.appendChild(legend);
  fieldset.appendChild(select);

  // ← écoute le changement de cursus
  select.addEventListener("change", () => {
    console.log("Cursus sélectionné :", select.value);
  });
  return fieldset;
}

function setSelectWithLabel(cursus) {
  cursus.forEach((cursusType) => {
    const optgroup = document.createElement("optgroup");
    optgroup.label = cursusType.label;

    cursusType.options.forEach(({ value, label }) => {
      const option = document.createElement("option");
      option.value = value;
      option.textContent = label;
      optgroup.appendChild(option);
    });

    select.appendChild(optgroup);
  });

  return fieldset;
}

function setSelectWithoutLabel(cursus) {
  cursus.forEach(({ value, label }) => {
    const option = document.createElement("option");
    option.value = value;
    option.textContent = label;
    select.appendChild(option);
  });

  return fieldset;
}
