<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\ScheduleConfig;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        // validate input
        $request->validate([
            'code' => 'required|string'
        ]);

        // find QR
        $qr = QrCode::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$qr) {
            return response()->json([
                'message' => 'Invalid QR Code'
            ], 404);
        }

        // get student safely
        $student = $qr->student;

        if (!$student) {
            return response()->json([
                'message' => 'Student not found for this QR'
            ], 404);
        }

        // get schedule config
        $schedule = ScheduleConfig::where('level', $student->level ?? 'hs')
            ->where('session_type', $student->session_type ?? 'morning')
            ->first();

        // late check (safe + readable)
        $isLate = false;

        if ($schedule) {
            $isLate = now()->format('H:i:s') > $schedule->late_threshold;
        }

        // response only (no storage logic here)
        return response()->json([
            'message' => 'Scan successful',
            'student' => $student,
            'late' => $isLate
        ]);
    }
}