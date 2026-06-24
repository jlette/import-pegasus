/**
 * upload.validator.js
 * Validation des fichiers uploadés (extension + MIME type).
 * Aucune dépendance au DOM — peut être testé indépendamment.
 */

const ALLOWED_EXTENSIONS = [".xls", ".xlsx"];

const ALLOWED_MIMES = [
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
];

export function validateFile(file) {
  const ext = file.name.slice(file.name.lastIndexOf(".")).toLowerCase();
  const isValidExt = ALLOWED_EXTENSIONS.includes(ext);
  const isValidMime = ALLOWED_MIMES.includes(file.type);
  return isValidExt && isValidMime;
}
