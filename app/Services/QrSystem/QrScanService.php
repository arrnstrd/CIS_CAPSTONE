<?php

namespace App\Services\QrSystem;

use App\Mail\GateScanMail;
use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\QrAttendance;
use App\Models\QrCode;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Services\ScheduleResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QrScanService
{
    public function processScan(array $data)
    {
        $now = now();
        $currentTime = $now->format('H:i');

        // 1. Resolve student from QR code or student_id
        $student = $this->resolveStudent($data);
        if (!$student) {
            return response()->json(['message' => 'Invalid QR or student not found'], 404);
        }
        $guardian = $student->guardian;

        // 2. Resolve active enrollment + section
        $enrollment = Enrollment::with('sectionModel')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'No active enrollment'], 404);
        }
        if (!$enrollment->sectionModel) {
            return response()->json(['message' => 'Enrollment section not found'], 404);
        }

        // 3. Resolve today's active schedule
        $schedule = (new ScheduleResolver())->resolve(
            $enrollment->sectionModel->level,
            $enrollment->session_type,
            $currentTime
        );

        if (!$schedule) {
            return response()->json(['message' => 'No active schedule for current time'], 400);
        }

        // Normalize schedule window boundaries
        $inStart = date('H:i', strtotime($schedule->in_start));
        $inEnd = date('H:i', strtotime($schedule->in_end));
        $lateThreshold = date('H:i', strtotime($schedule->late_threshold));
        $outStart = date('H:i', strtotime($schedule->out_start));
        $outEnd = date('H:i', strtotime($schedule->out_end));

        // 4. Fetch today's official attendance record (if any)
        $qrAttendance = QrAttendance::where('enrollment_id', $enrollment->id)
            ->where('attendance_date', $now->toDateString())
            ->first();

        // 5. Spam shield: block scans still inside the cooldown window
        $cooldownSeconds = (int) (SystemSetting::getValue('spam_cooldown_seconds') ?? 300);
        $maxDuplicateAttempts = (int) (SystemSetting::getValue('max_duplicate_attempts') ?? 3);
        if ($qrAttendance && $qrAttendance->cooldown_expires_at && $qrAttendance->cooldown_expires_at->isFuture()) {
            $qrAttendance->increment('spam_offense_count');
            FlaggedScan::create([
                'attendance_log_id' => null,
                'flag_type' => 'excess_scan',
                'description' => 'Scan blocked by cooldown (spam shield)',
            ]);

            return response()->json([
                'message' => 'Too many scans. Please wait for the cooldown to expire.',
                'retry_after' => $qrAttendance->cooldown_expires_at->diffInSeconds($now),
            ], 429);
        }

        // 6. State machine: no record OR already checked out -> IN, otherwise OUT
        $scanType = (!$qrAttendance || $qrAttendance->time_out_log_id !== null) ? 'IN' : 'OUT';

        // 7. Duplicate IN: intended OUT but outside the OUT window -> second IN scan (3-strike rule)
        if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
            return $this->handleDuplicateIn(
                $enrollment,
                $now,
                $qrAttendance,
                $maxDuplicateAttempts,
                $cooldownSeconds
            );
        }

        // 8. Reject duplicate IN/OUT scans
        if ($scanType === 'IN' && $qrAttendance && $qrAttendance->time_in_log_id !== null) {
            return $this->rejectDuplicate($enrollment, $now, 'IN');
        }
        if ($scanType === 'OUT' && $qrAttendance && $qrAttendance->time_out_log_id !== null) {
            return $this->rejectDuplicate($enrollment, $now, 'OUT');
        }

        // 9. Enforce IN/OUT time windows
        // Note: OUT window only reaches this check when inside it (duplicate-IN returned above)
        if ($scanType === 'IN' && !($currentTime >= $inStart && $currentTime <= $inEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-in window',
                'in_window' => "{$inStart} – {$inEnd}",
                'current_time' => $currentTime,
            ], 400);
        }
        if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-out window',
                'out_window' => "{$outStart} – {$outEnd}",
                'current_time' => $currentTime,
            ], 400);
        }

        // 9. Late arrival / early timeout flags
        $isLate = $scanType === 'IN' && $currentTime > $lateThreshold;
        $isEarlyTimeout = $scanType === 'OUT' && $currentTime < $outStart;

        // 10. Atomic write: raw log + official record + flags
        $log = DB::transaction(function () use ($enrollment, $scanType, $now, $cooldownSeconds, $data, $isLate, $isEarlyTimeout) {
            $log = AttendanceLog::create([
                'enrollment_id' => $enrollment->id,
                'scan_type' => $scanType,
                'session_type' => $enrollment->session_type,
                'scan_time' => $now,
                'scanned_by_user_id' => $data['scanned_by_user_id'] ?? null,
                'device_id' => $data['device_id'] ?? null,
            ]);

            $qrAttendance = QrAttendance::firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'attendance_date' => $now->toDateString()],
                ['spam_offense_count' => 0]
            );

            if ($scanType === 'IN') {
                $qrAttendance->time_in_log_id = $log->id;
            } else {
                $qrAttendance->time_out_log_id = $log->id;
            }
            $qrAttendance->cooldown_expires_at = $now->copy()->addSeconds($cooldownSeconds);
            $qrAttendance->save();

            if ($isLate) {
                FlaggedScan::create([
                    'attendance_log_id' => $log->id,
                    'flag_type' => 'late_arrival',
                    'description' => 'Student arrived late',
                ]);
            }
            if ($isEarlyTimeout) {
                FlaggedScan::create([
                    'attendance_log_id' => $log->id,
                    'flag_type' => 'early_timeout',
                    'description' => 'Student checked out before the allowed time',
                ]);
            }

            return $log;
        });

        // 11. Notify guardian by email
        $this->sendGuardianEmail($student, $guardian, $log, $scanType, $now);

        return response()->json([
            'message' => 'Scan successful',
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'student_number' => $student->student_number,
                'level' => $schedule->level,
                'session_type' => $enrollment->session_type,
            ],
            'attendance_log' => [
                'id' => $log->id,
                'scan_type' => $scanType,
                'scan_time' => $log->scan_time,
            ],
            'late' => $isLate,
        ]);
    }

    private function resolveStudent(array $data): ?Student
    {
        if (!empty($data['code'])) {
            return QrCode::with('student.guardian')
                ->where('code', $data['code'])
                ->where('is_active', true)
                ->first()?->student;
        }

        return Student::with('guardian')->find($data['student_id']);
    }

    private function rejectDuplicate(Enrollment $enrollment, $now, string $scanType)
    {
        $log = AttendanceLog::create([
            'enrollment_id' => $enrollment->id,
            'scan_type' => $scanType,
            'session_type' => $enrollment->session_type,
            'scan_time' => $now,
        ]);

        FlaggedScan::create([
            'attendance_log_id' => $log->id,
            'flag_type' => 'duplicate_scan',
            'description' => "Duplicate {$scanType} scan rejected",
        ]);

        return response()->json([
            'message' => "Duplicate {$scanType} scan rejected",
        ], 400);
    }

    private function handleDuplicateIn(
        Enrollment $enrollment,
        $now,
        QrAttendance $qrAttendance,
        int $maxAttempts,
        int $cooldownSeconds
    ) {
        $log = AttendanceLog::create([
            'enrollment_id' => $enrollment->id,
            'scan_type' => 'IN',
            'session_type' => $enrollment->session_type,
            'scan_time' => $now,
        ]);

        if ($qrAttendance->spam_offense_count < $maxAttempts) {
            FlaggedScan::create([
                'attendance_log_id' => $log->id,
                'flag_type' => 'duplicate_scan',
                'description' => 'Duplicate IN scan',
            ]);

            $qrAttendance->increment('spam_offense_count');

            return response()->json([
                'message' => 'Duplicate scan warning. Please wait for check-out time.',
            ], 400);
        }

        FlaggedScan::create([
            'attendance_log_id' => $log->id,
            'flag_type' => 'excess_scan',
            'description' => 'Exceeded duplicate attempts, cooldown applied',
        ]);

        $qrAttendance->cooldown_expires_at = $now->copy()->addSeconds($cooldownSeconds);
        $qrAttendance->save();

        return response()->json([
            'message' => 'Too many duplicate scans. You are now on cooldown.',
        ], 429);
    }

    private function sendGuardianEmail($student, $guardian, AttendanceLog $log, string $scanType, $now): void
    {
        if (!$guardian || !$guardian->email) {
            return;
        }

        $emailLog = EmailLog::create([
            'attendance_log_id' => $log->id,
            'student_id' => $student->id,
            'email' => $guardian->email,
            'scan_type' => $scanType,
            'status' => 'pending',
            'attempt_count' => 0,
        ]);

        try {
            Mail::to($guardian->email)->send(
                new GateScanMail($student, $scanType, $now->format('F d, Y - h:i A'))
            );

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => $now,
                'attempt_count' => 1,
                'last_attempt_at' => $now,
            ]);
        } catch (\Exception $e) {
            Log::error("Email failed for student {$student->id}: {$e->getMessage()}");

            $emailLog->update([
                'status' => 'failed',
                'attempt_count' => 1,
                'last_attempt_at' => $now,
            ]);
        }
    }
}
