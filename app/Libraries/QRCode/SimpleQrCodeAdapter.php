<?php

namespace App\Libraries\QRCode;

use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;

/**
 * Agnostic technical adapter for SimpleSoftwareIO Simple QrCode.
 * Zero business rules, models, or domain awareness.
 */
class SimpleQrCodeAdapter
{
    /**
     * Generate raw QR code content with specified format, size, and margin.
     *
     * @param string $data
     * @param int $size
     * @param int $margin
     * @param string $format 'png', 'svg', or 'eps'
     * @return string
     */
    public function generate(string $data, int $size = 300, int $margin = 1, string $format = 'svg'): string
    {
        return (string) QrGenerator::format($format)
            ->size($size)
            ->margin($margin)
            ->generate($data);
    }

    /**
     * Generate PNG QR code binary string.
     *
     * @param string $data
     * @param int $size
     * @param int $margin
     * @return string
     */
    public function generatePng(string $data, int $size = 300, int $margin = 1): string
    {
        return $this->generate($data, $size, $margin, 'png');
    }

    /**
     * Generate SVG QR code string.
     *
     * @param string $data
     * @param int $size
     * @param int $margin
     * @return string
     */
    public function generateSvg(string $data, int $size = 300, int $margin = 1): string
    {
        return $this->generate($data, $size, $margin, 'svg');
    }
}
