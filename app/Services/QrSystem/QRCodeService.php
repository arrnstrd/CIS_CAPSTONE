<?php

namespace App\Services\QrSystem;

use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;

class QRCodeService
{
    public function ensureImage(QrCode $qrCode): QrCode
    {
        if ($qrCode->image_path && Storage::disk('public')->exists($qrCode->image_path)) {
            return $qrCode;
        }

        try {
            if (extension_loaded('imagick')) {
                $path = "qr-codes/student-{$qrCode->student_id}.png";
                $image = QrGenerator::format('png')->size(300)->margin(1)->generate($qrCode->code);
            } else {
                $path = "qr-codes/student-{$qrCode->student_id}.svg";
                $image = QrGenerator::format('svg')->size(300)->margin(1)->generate($qrCode->code);
            }
        } catch (\Throwable $e) {
            $path = "qr-codes/student-{$qrCode->student_id}.svg";
            $image = QrGenerator::format('svg')->size(300)->margin(1)->generate($qrCode->code);
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
