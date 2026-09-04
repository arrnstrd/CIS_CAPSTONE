<?php

namespace App\Libraries\Spreadsheet;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Agnostic technical service for PhpOffice PhpSpreadsheet.
 * Zero business rules, models, or domain awareness.
 */
class ExcelSpreadsheetService
{
    /**
     * Load a spreadsheet from a given file path.
     *
     * @param string $filePath
     * @return Spreadsheet
     */
    public function loadFile(string $filePath): Spreadsheet
    {
        return IOFactory::load($filePath);
    }

    /**
     * Create a new blank spreadsheet instance.
     *
     * @return Spreadsheet
     */
    public function createSpreadsheet(): Spreadsheet
    {
        return new Spreadsheet();
    }

    /**
     * Write spreadsheet to an output stream or file.
     *
     * @param Spreadsheet $spreadsheet
     * @param string $path Default is 'php://output'
     * @return void
     */
    public function writeToStream(Spreadsheet $spreadsheet, string $path = 'php://output'): void
    {
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }
}
