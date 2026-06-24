<?php

namespace App\Service;

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class XlsService
{

    public function readXls(string $filePath): array
    {
        // Fonctionne avec XLS et XLSX automatiquement
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
    }
}