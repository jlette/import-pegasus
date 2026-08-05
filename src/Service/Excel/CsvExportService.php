<?php

namespace App\Service;

use App\Model\Student\AbstractStudent;

class CsvExportService
{
    public function generateCsv(array $etudiants, string $outputDir, string $cursusPrefix): string
    {
        // 1. Sécurité : s'il n'y a pas d'étudiants, on s'arrête
        if (empty($etudiants)) {
            return '';
        }

        $dateStr = date('Ymd_His');
        $filename = "import_{$cursusPrefix}_{$dateStr}.csv";
        $filePath = rtrim($outputDir, '/') . '/' . $filename;

        $file = fopen($filePath, 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // --- 2. CRÉATION DES EN-TÊTES ---
        $premierEtudiant = $etudiants[0];

        // Colonnes 1 à 14
        $headers = [
            'Date_Lot',
            'No_Lot',
            'No_Ssl',
            'Type_occ',
            'Recrutement',
            'Année',
            'Produit Programme',
            'No Année',
            'Session',
            'Statut Etudiant',
            'Genre',
            'Nom',
            'Prénom',
            'Sexe'
        ];

        // 15. Connaissances générales dynamiques (Départ à 2)
        $nbConn = property_exists($premierEtudiant, 'connaissance')
            ? count($premierEtudiant->connaissance ?? [])
            : 0;

        // Si on a 4 connaissances, on boucle de 2 à 5
        for ($i = 2; $i <= $nbConn + 1; $i++) {
            $headers[] = "Connaissance $i Type";
            $headers[] = "Connaissance $i Valeur";
        }

        // 16. Connaissance_fop_ins dynamiques (Départ à 1)
        $nbFopIns = property_exists($premierEtudiant, 'connaissance_fop_ins')
            ? count($premierEtudiant->connaissance_fop_ins ?? [])
            : 0;

        for ($i = 1; $i <= $nbFopIns; $i++) {
            $headers[] = "Connaissance_fop_ins $i Type";
            $headers[] = "Connaissance_fop_ins $i Valeur";
        }

        // Colonnes 17 à 28 : Identité, naissance et coordonnées
        array_push(
            $headers,
            'Situation familiale',
            'Ville de Naissance',
            'Date de Naissance',
            'Département de naissance', // A COMPLETER
            'Pays de Naissance',
            'Nationalité Principal',
            'Code INSEE',
            'Courrier Voie 1',
            'Courrier Voie 2',
            'Courrier Code Postal',
            'Courrier Ville',
            'Courrier Pays',
            'CourrierTéléphone',
            'EOL'
        );

        fputcsv($file, $headers, ';', '"', "\\");

        // --- 3. REMPLISSAGE DES LIGNES ---
        foreach ($etudiants as $etudiant) {

            // Partie 1 : Données communes (1 à 14)
            $row = [
                $etudiant->date_lot->format('Ymd'),
                $etudiant->no_lot,
                $etudiant->no_ssl,
                $etudiant->type_occ,
                $etudiant->recrutement,
                $etudiant->annee,
                $etudiant->produit_programme,
                $etudiant->no_annee,
                $etudiant->session,
                $etudiant->status_etudiant,
                $etudiant->genre,
                $etudiant->nom,
                $etudiant->prenom,
                $etudiant->sexe
            ];

            // 15. Connaissances générales
            if (property_exists($etudiant, 'connaissance')) {
                foreach (($etudiant->connaissance ?? []) as $key => $item) {
                    if (is_array($item) && isset($item['type'])) {
                        $row[] = $item['type'];
                        $row[] = $item['valeur'] ?? '';
                    } else {
                        $row[] = $key;
                        $row[] = $item;
                    }
                }
            }

            // 16. Connaissance Fop Ins
            if (property_exists($etudiant, 'connaissance_fop_ins')) {
                foreach (($etudiant->connaissance_fop_ins ?? []) as $key => $item) {
                    if (is_array($item) && isset($item['type'])) {
                        $row[] = $item['type'];
                        $row[] = $item['valeur'] ?? '';
                    } else {
                        $row[] = $key;
                        $row[] = $item;
                    }
                }
            }

            // 17 à 28 : Adresses
            $row[] = property_exists($etudiant, 'situation_familiale') ? $etudiant->situation_familiale : '';
            $row[] = property_exists($etudiant, 'ville_de_naissance') ? $etudiant->ville_de_naissance : '';

            $dateNaissanceStr = '';
            if (property_exists($etudiant, 'date_de_naissance') && $etudiant->date_de_naissance instanceof \DateTime) {
                $dateNaissanceStr = $etudiant->date_de_naissance->format('d/m/Y');
            }
            $row[] = $dateNaissanceStr;

            $row[] = property_exists($etudiant, 'pays_de_naissance') ? $etudiant->pays_de_naissance : '';
            $row[] = property_exists($etudiant, 'nationalite_principal') ? $etudiant->nationalite_principal : '';
            $row[] = property_exists($etudiant, 'code_insee') ? $etudiant->code_insee : '';
            $row[] = property_exists($etudiant, 'courrier_voie_1') ? $etudiant->courrier_voie_1 : '';
            $row[] = property_exists($etudiant, 'courrier_voie_2') ? $etudiant->courrier_voie_2 : '';
            $row[] = property_exists($etudiant, 'courrier_code_postal') ? $etudiant->courrier_code_postal : '';
            $row[] = property_exists($etudiant, 'courrier_ville') ? $etudiant->courrier_ville : '';
            $row[] = property_exists($etudiant, 'courrier_pays') ? $etudiant->courrier_pays : '';
            $row[] = property_exists($etudiant, 'courrier_telephone') ? $etudiant->courrier_telephone : '';

            // 29. EOL
            $row[] = $etudiant->eol;

            fputcsv($file, $row, ';', '"', "\\");
        }

        fclose($file);

        return $filename;
    }
}
