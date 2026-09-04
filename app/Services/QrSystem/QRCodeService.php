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

        try {
            if (extension_loaded('imagick')) {
                $path = "qr-codes/student-{$qrCode->student_id}.png";
                $image = $this->qrCodeAdapter->generatePng($qrCode->code, 300, 1);
            } else {
                $path = "qr-codes/student-{$qrCode->student_id}.svg";
                $image = $this->qrCodeAdapter->generateSvg($qrCode->code, 300, 1);
            }
        } catch (\Throwable $e) {
            $path = "qr-codes/student-{$qrCode->student_id}.svg";
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
