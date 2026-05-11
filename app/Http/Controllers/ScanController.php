<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\QrCode;
use App\Models\ScheduleConfig;
use App\Services\ScheduleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        // =========================
        // 1. Validate QR
        // =========================
        $request->validate([
            'code' => ['required', 'string']
        ]);

        // =========================
        // 2. Resolve QR + Student
        // =========================
        $qr = QrCode::with('student.guardian')
            ->where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$qr || !$qr->student) {
            return response()->json(['message' => 'Invalid QR or student not found'], 404);
        }

        $student = $qr->student;
        $guardian = $student->guardian;

        // =========================
        // 3. Active Enrollment
        // =========================
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'No active enrollment'], 404);
        }

        // =========================
        // 4. TIME NORMALIZATION
        // =========================
        $now = now();
        $currentTime = $now->format('H:i');

        // =========================
        // 5. Resolve Active Schedule
        // =========================
        $resolver = new ScheduleResolver();
        $activeSchedule = $resolver->resolve($enrollment->level, $currentTime);

        if (!$activeSchedule) {
            return response()->json([
                'message' => 'No active schedule for current time'
            ], 400);
        }

        // Normalize all schedule time boundaries to H:i for consistent comparison
        $inStart       = date('H:i', strtotime($activeSchedule->in_start));
        $inEnd         = date('H:i', strtotime($activeSchedule->in_end));
        $lateThreshold = date('H:i', strtotime($activeSchedule->late_threshold));
        $outStart      = date('H:i', strtotime($activeSchedule->out_start));
        $outEnd        = date('H:i', strtotime($activeSchedule->out_end));

        // =========================
        // 6. Last scan (session-aware)
        // =========================
        $lastLog = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', today())
            ->where('session_type', $activeSchedule->session_type)
            ->latest('id')
            ->first();

        $isInside = $lastLog && $lastLog->scan_type === 'IN';

        // =========================
        // 7. IN / OUT TOGGLE with window enforcement
        // =========================
        $scanType = $isInside ? 'OUT' : 'IN';

        // Enforce IN window: must be between in_start and in_end
        if ($scanType === 'IN' && !($currentTime >= $inStart && $currentTime <= $inEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-in window',
                'in_window' => "{$inStart} – {$inEnd}",
                'current_time' => $currentTime,
            ], 400);
        }

        // Enforce OUT window: must be between out_start and out_end
        if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-out window',
                'out_window' => "{$outStart} – {$outEnd}",
                'current_time' => $currentTime,
            ], 400);
        }

        // =========================
        // 8. LATE CHECK (ONLY FOR IN)
        // =========================
        $isLate = false;

        if ($scanType === 'IN') {
            $isLate = $currentTime > $lateThreshold;
        }

        // =========================
        // 9. STORE ATTENDANCE
        // =========================
        $attendanceLog = DB::transaction(function () use (
            $enrollment,
            $activeSchedule,
            $scanType
        ) {
            return AttendanceLog::create([
                'enrollment_id' => $enrollment->id,
                'scan_type' => $scanType,
                'session_type' => $activeSchedule->session_type,
                'scan_time' => now(),
            ]);
        });

        // =========================
        // 10. FLAGS (FIXED OUT BUG)
        // =========================

        // Late IN
        if ($scanType === 'IN' && $isLate) {
            FlaggedScan::create([
                'attendance_log_id' => $attendanceLog->id,
                'flag_type' => 'late_arrival',
                'description' => 'Student arrived late'
            ]);
        }

        // INVALID OUT FIXED HERE
        if ($scanType === 'OUT' && !$isInside) {
            FlaggedScan::create([
                'attendance_log_id' => $attendanceLog->id,
                'flag_type' => 'invalid_checkout',
                'description' => 'OUT scan without valid IN'
            ]);
        }

        // =========================
        // 11. EMAIL NOTIFICATION
        // =========================
        if ($guardian && $guardian->email) {

            $emailLog = EmailLog::create([
                'attendance_log_id' => $attendanceLog->id,
                'student_id' => $student->id,
                'email' => $guardian->email,
                'scan_type' => $scanType,
                'status' => 'pending',
                'attempt_count' => 0
            ]);

            try {
                Mail::raw(
                    "{$student->first_name} {$student->last_name} has a {$scanType} scan at " . now()->format('h:i A'),
                    function ($message) use ($guardian, $student) {
                        $message->to($guardian->email)
                            ->subject("Attendance Update: {$student->first_name}");
                    }
                );

                $emailLog->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'attempt_count' => 1,
                    'last_attempt_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error("Email failed for student {$student->id}: {$e->getMessage()}");

                $emailLog->update([
                    'status' => 'failed',
                    'attempt_count' => 1,
                    'last_attempt_at' => now(),
                ]);
            }
        }

        // =========================
        // 12. RESPONSE
        // =========================
        return response()->json([
            'message' => 'Scan successful',
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'student_number' => $student->student_number,
                'level' => $enrollment->level,
            ],
            'attendance_log' => [
                'id' => $attendanceLog->id,
                'scan_type' => $scanType,
                'session_type' => $activeSchedule->session_type,
                'scan_time' => $attendanceLog->scan_time,
            ],
            'late' => $isLate,
        ]);
    }
}