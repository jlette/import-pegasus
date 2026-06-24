<?php

namespace App\Service;

use App\Model\Student\AbstractStudent;

class CsvExportService
{
    public function generateCsv(array $etudiants, string $outputDir): string
    {
        // 1. Sécurité : s'il n'y a pas d'étudiants, on s'arrête
        if (empty($etudiants)) {
            return '';
        }

        $dateStr = date('Ymd_His');
        $filename = "import_pegasus_scei_{$dateStr}.csv";
        $filePath = rtrim($outputDir, '/') . '/' . $filename;

        $file = fopen($filePath, 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // --- 2. CRÉATION DES EN-TÊTES DANS L'ORDRE STRICT DU DOSSIER ---
        $premierEtudiant = $etudiants[0];

        // Colonnes 1 à 14
        $headers = [
            'Date lot',
            'No_lot',
            'No_Ssl',
            'Type_occ',
            'Recrutement',
            'Annee',
            'Produit / Programme',
            'No_annee',
            'Session',
            'Status etudiant',
            'Genre',
            'Nom',
            'Prenom',
            'Sexe'
        ];

        // 15. Dérouler les Connaissances générales d'abord
        $nbConn = property_exists($premierEtudiant, 'connaissance')
            ? count($premierEtudiant->connaissance ?? [])
            : 0;

        for ($i = 1; $i <= $nbConn; $i++) {
            $headers[] = "Connaissance $i Type";
            $headers[] = "Connaissance $i Valeur";
        }

        // 16. Dérouler les Connaissance_fop_ins ensuite
        $nbFopIns = property_exists($premierEtudiant, 'connaissance_fop_ins')
            ? count($premierEtudiant->connaissance_fop_ins ?? [])
            : 0;

        for ($i = 1; $i <= $nbFopIns; $i++) {
            $headers[] = "Connaissance_fop_ins $i Type";
            $headers[] = "Connaissance_fop_ins $i Valeur";
        }

        // Colonnes 17 à 28 : Identité, naissance et coordonnées (placées APRÈS les boucles)
        $headers[] = 'Situation familiale';
        $headers[] = 'Ville de naissance';
        $headers[] = 'Date de naissance';
        $headers[] = 'Pays de naissance';
        $headers[] = 'Nationalite principal';
        $headers[] = 'Code INSEE';
        $headers[] = 'Courrier Voie 1';
        $headers[] = 'Courrier Voie 2';
        $headers[] = 'Courrier Code Postal';
        $headers[] = 'Courrier Ville';
        $headers[] = 'Courrier Pays';
        $headers[] = 'Courrier Telephone';

        // 29. Fin de ligne
        $headers[] = 'EOL';

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

            // 15. Dérouler les données de Connaissances générales
            if (property_exists($etudiant, 'connaissance')) {
                foreach (($etudiant->connaissance ?? []) as $type => $valeur) {
                    $row[] = $type;
                    $row[] = $valeur;
                }
            }

            // 16. Dérouler les données de Connaissance Fop Ins
            if (property_exists($etudiant, 'connaissance_fop_ins')) {
                foreach (($etudiant->connaissance_fop_ins ?? []) as $type => $valeur) {
                    $row[] = $type;
                    $row[] = $valeur;
                }
            }

            // Extraction sécurisée des propriétés d'identité et d'adresse (17 à 28)
            $situationFamiliale = property_exists($etudiant, 'situation_familiale') ? $etudiant->situation_familiale : '';
            $villeNaissance = property_exists($etudiant, 'ville_de_naissance') ? $etudiant->ville_de_naissance : '';
            $paysNaissance = property_exists($etudiant, 'pays_de_naissance') ? $etudiant->pays_de_naissance : '';
            $nationalite = property_exists($etudiant, 'nationalite_principal') ? $etudiant->nationalite_principal : '';
            $codeInsee = property_exists($etudiant, 'code_insee') ? $etudiant->code_insee : '';
            $voie1 = property_exists($etudiant, 'courrier_voie_1') ? $etudiant->courrier_voie_1 : '';
            $voie2 = property_exists($etudiant, 'courrier_voie_2') ? $etudiant->courrier_voie_2 : '';
            $cp = property_exists($etudiant, 'courrier_code_postal') ? $etudiant->courrier_code_postal : '';
            $ville = property_exists($etudiant, 'courrier_ville') ? $etudiant->courrier_ville : '';
            $pays = property_exists($etudiant, 'courrier_pays') ? $etudiant->courrier_pays : '';
            $tel = property_exists($etudiant, 'courrier_telephone') ? $etudiant->courrier_telephone : '';

            $dateNaissanceStr = '';
            if (property_exists($etudiant, 'date_de_naissance') && $etudiant->date_de_naissance instanceof \DateTime) {
                $dateNaissanceStr = $etudiant->date_de_naissance->format('d/m/Y');
            }

            // Ajout des données dans l'ordre de la fin du tableau
            $row[] = $situationFamiliale;
            $row[] = $villeNaissance;
            $row[] = $dateNaissanceStr;
            $row[] = $paysNaissance;
            $row[] = $nationalite;
            $row[] = $codeInsee;
            $row[] = $voie1;
            $row[] = $voie2;
            $row[] = $cp;
            $row[] = $ville;
            $row[] = $pays;
            $row[] = $tel;

            // 29. Clôture de la ligne
            $row[] = $etudiant->eol;

            fputcsv($file, $row, ';', '"', "\\");
        }

        fclose($file);

        return $filename;
    }
}
