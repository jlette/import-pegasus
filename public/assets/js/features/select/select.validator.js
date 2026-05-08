export const STUDENTS = {
  dens: "dens",
  dri: "dri",
  agreg: "agreg",
};

export const CURSUS_DENS = [
  {
    label: "CPGE (Classe préparatoire aux grandes écoles)",
    options: [
      { value: "scei", label: "SCEI" },
      { value: "al", label: "A/L (Lettres)" },
      { value: "bl", label: "B/L (Lettres et sciences sociales)" },
    ],
  },
  {
    label: "SI (Sélection internationale)",
    options: [
      { value: "sil", label: "Lettre" },
      { value: "sis", label: "Sciences" },
    ],
  },
  {
    label: "NE (Normalien étudiant)",
    options: [
      { value: "nel", label: "NEL (Normaliens étudiant lettres)" },
      { value: "nes", label: "NES (Normaliens étudiant sciences)" },
      { value: "nemh", label: "NEMH (Normalien étudiant médecine humanité)" },
      { value: "nems", label: "NEMS (Normalien étudiant médecine science)" },
    ],
  },
];

export const CURSUS_DRI = [
  {
    options: [
      { value: "erasmus", label: "Erasmus" },
      { value: "pe", label: "Pensionnaire étranger" },
    ],
  },
];
