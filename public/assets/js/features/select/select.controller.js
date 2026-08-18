import { CURSUS_DENS, STUDENTS } from "./select.validator.js";
import { modalClose, modalOverlay } from "../modal/modal.controller.js";
const form = document.querySelector(".modal__form");
const studentSelected = document.getElementById("student-select");
const button = form.querySelector(".modal__submit-area");
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
}

function exitSelect() {
  fieldset.parentNode.removeChild(fieldset);
  studentSelected.value = "";
}

function resetSelect() {
  fieldset.parentNode.removeChild(fieldset);
}

function setFieldset() {
  fieldset = document.createElement("div");
  fieldset.classList.add("form-group", "modal__field--anim");

  const label = document.createElement("label");
  label.textContent = "Choix du cursus";
  label.classList.add("form-label");
  label.setAttribute("for", "cursus-select");

  select = document.createElement("select");
  select.id = "cursus-select";
  select.classList.add("form-select");

  const option = document.createElement("option");
  option.value = "";
  option.textContent = "-- Sélectionnez le type de cursus --";
  select.appendChild(option);

  fieldset.appendChild(label);
  fieldset.appendChild(select);

  return fieldset;
}

function setSelectWithLabel(cursus) {
  cursus.forEach((cursusType) => {
    const optgroup = document.createElement("optgroup");
    optgroup.label = cursusType.label;

    // On extrait 'value', 'label' et la nouvelle propriété optionnelle 'disabled'
    cursusType.options.forEach(({ value, label, disabled }) => {
      const option = document.createElement("option");
      option.value = value;
      option.textContent = label;

      // Si disabled est true dans le dictionnaire, on désactive l'option HTML
      if (disabled) {
        option.disabled = true;
      }

      optgroup.appendChild(option);
    });

    select.appendChild(optgroup);
  });

  return fieldset;
}
