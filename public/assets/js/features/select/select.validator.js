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
      {
        value: "nel",
        label: "NEL (Normaliens étudiant lettres) - À venir",
        disabled: true,
      },
      {
        value: "nes",
        label: "NES (Normaliens étudiant sciences) - À venir",
        disabled: true,
      },
      { value: "nemh", label: "NEMH (Normalien étudiant médecine humanité)" },
      { value: "nems", label: "NEMS (Normalien étudiant médecine science)" },
    ],
  },
  {
    label: "Frontiers of cognition and neuroscience",
    options: [
      { value: "frontcog", label: "FrontCog - À venir", disabled: true },
    ],
  },
  {
    label: "Olympiades",
    options: [
      { value: "olympiades", label: "Olympiades - À venir", disabled: true },
    ],
  },
];
