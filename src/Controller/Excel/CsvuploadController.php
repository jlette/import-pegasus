<?php

namespace App\Controller\Csv;
use App\Core\Controller;


class CsvuploadController extends Controller
{
    /**
     * Affiche la page d'importation de fichiers CSV.
     */
    public function upload()
    {
        $data = [
            'title' => 'Import Pegasus - Import CSV',
            'description' => 'Importez votre fichier CSV ici.'
        ];

        return $this->render('Csv/upload', $data);
    }
}