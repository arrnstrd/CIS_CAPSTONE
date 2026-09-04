<?php

namespace App\Libraries\PDF;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFInstance;

/**
 * Agnostic technical wrapper for Barryvdh DomPDF.
 * Zero business rules, models, or domain awareness.
 */
class DomPdfWrapper
{
    /**
     * Load a Blade view template into DomPDF.
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @param string|null $encoding
     * @return DomPDFInstance
     */
    public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): DomPDFInstance
    {
        return Pdf::loadView($view, $data, $mergeData, $encoding);
    }

    /**
     * Load an HTML string into DomPDF.
     *
     * @param string $string
     * @param string|null $encoding
     * @return DomPDFInstance
     */
    public function loadHtml(string $string, ?string $encoding = null): DomPDFInstance
    {
        return Pdf::loadHTML($string, $encoding);
    }

    /**
     * Load an HTML file into DomPDF.
     *
     * @param string $file
     * @return DomPDFInstance
     */
    public function loadFile(string $file): DomPDFInstance
    {
        return Pdf::loadFile($file);
    }
}
