import { initUpload } from "./features/upload/upload.controller.js";
import { initModal } from "./features/modal/modal.controller.js";
import { initToast } from "./features/toast/toast.controller.js";
import { initSelect } from "./features/select/select.controller.js";

document.addEventListener("DOMContentLoaded", () => {
  initUpload();
  initModal();
  initToast();
  initSelect();
});
