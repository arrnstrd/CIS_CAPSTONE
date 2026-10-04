<?php

namespace App\Services\QrSystem;

use App\Libraries\QRCode\SimpleQrCodeAdapter;
use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    public function __construct(
        private readonly SimpleQrCodeAdapter $qrCodeAdapter = new SimpleQrCodeAdapter()
    ) {}

    public function ensureImage(QrCode $qrCode): QrCode
    {
        if ($qrCode->image_path && Storage::disk('public')->exists($qrCode->image_path)) {
            return $qrCode;
        }

        // Advisory file lock — prevents concurrent requests from double-writing
        // the same QR image when two processes reach this point simultaneously.
        $lockPath = sys_get_temp_dir() . "/qr_lock_student_{$qrCode->student_id}.lock";
        $lockFile = fopen($lockPath, 'c');

        if (!$lockFile) {
            // Fallback: proceed without lock (non-critical, just may double-write).
            return $this->generateAndPersist($qrCode);
        }

        try {
            flock($lockFile, LOCK_EX);

            // Re-check after acquiring lock — another process may have already written it.
            $qrCode->refresh();
            if ($qrCode->image_path && Storage::disk('public')->exists($qrCode->image_path)) {
                return $qrCode;
            }

            return $this->generateAndPersist($qrCode);
        } finally {
            flock($lockFile, LOCK_UN);
            fclose($lockFile);
        }
    }

    private function generateAndPersist(QrCode $qrCode): QrCode
    {
        try {
            if (extension_loaded('imagick')) {
                $path  = "qr-codes/student-{$qrCode->student_id}.png";
                $image = $this->qrCodeAdapter->generatePng($qrCode->code, 300, 1);
            } else {
                $path  = "qr-codes/student-{$qrCode->student_id}.svg";
                $image = $this->qrCodeAdapter->generateSvg($qrCode->code, 300, 1);
            }
        } catch (\Throwable $e) {
            $path  = "qr-codes/student-{$qrCode->student_id}.svg";
            $image = $this->qrCodeAdapter->generateSvg($qrCode->code, 300, 1);
        }

        Storage::disk('public')->put($path, $image);

        $qrCode->forceFill([
            'image_path' => $path,
        ])->save();

        return $qrCode->refresh();
    }

    public function imageBase64(QrCode $qrCode): string
    {
        $qrCode = $this->ensureImage($qrCode);

        return base64_encode(Storage::disk('public')->get($qrCode->image_path));
    }

    public function publicUrl(QrCode $qrCode): ?string
    {
        if (! $qrCode->image_path) {
            return null;
        }

        return Storage::disk('public')->url($qrCode->image_path);
    }
}
