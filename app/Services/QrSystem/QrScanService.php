<?php

namespace App\Services\QrSystem;

use App\Jobs\SendGateScanNotification;
use App\Events\AttendanceRecorded;
use App\Events\EmailLogCreated;
use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\Guardian;
use App\Models\QrAttendance;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\SchoolYear;
use App\Services\ScheduleResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrScanService
{
    public function processScan(array $data)
    {
        try {
            $now = now();
            $currentTime = $now->format('H:i');

            // 1. Resolve student from QR code or student_id
            $student = $this->resolveStudent($data);
            if (!$student) {
                return response()->json([
                    'message' => "We couldn't recognize this QR code. Please make sure the QR code is visible and try again."
                ], 404);
            }
            $guardian = $student->guardian;

            // 2. Resolve active enrollment + section (single round trip)
            $activeSchoolYearId = SchoolYear::query()->active()->value('id');

            $enrollment = Enrollment::query()
                ->select(
                    'enrollments.*',
                    'sections.level as section_level',
                    'sections.name as section_name',
                    'sections.grade_level as section_grade',
                    'sections.session_type as section_session_type'
                )
                ->leftJoin('sections', 'sections.id', '=', 'enrollments.section_id')
                ->where('enrollments.student_id', $student->id)
                ->where('enrollments.status', 'active')
                ->where('enrollments.school_year_id', $activeSchoolYearId)
                ->orderBy('enrollments.id')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'message' => 'No active enrollment record found for this student for the current school year. Please contact the school administrator.'
                ], 404);
            }
            if (!$enrollment->section_level) {
                return response()->json([
                    'message' => 'Section assignment could not be found for this student. Please contact the school administrator.'
                ], 404);
            }

            // 3. Resolve today's active schedule
            $schedule = (new ScheduleResolver())->resolve(
                $enrollment->section_level,
                $enrollment->section_session_type,
                $currentTime
            );

            if (!$schedule) {
                return response()->json([
                    'message' => 'No active schedule found for the current time. Scanning is only available during scheduled school hours.'
                ], 400);
            }

            // Normalize schedule window boundaries
            $inStart = date('H:i', strtotime($schedule->in_start));
            $inEnd = date('H:i', strtotime($schedule->in_end));
            $lateThreshold = date('H:i', strtotime($schedule->late_threshold));
            $outStart = date('H:i', strtotime($schedule->out_start));
            $outEnd = date('H:i', strtotime($schedule->out_end));

            $inStartFormatted = date('g:i A', strtotime($schedule->in_start));
            $inEndFormatted = date('g:i A', strtotime($schedule->in_end));
            $outStartFormatted = date('g:i A', strtotime($schedule->out_start));
            $outEndFormatted = date('g:i A', strtotime($schedule->out_end));

            // 4. Resolve cooldown settings before the transaction; the database remains
            // authoritative for the attendance state itself.
            $settings = $this->scanSettings();
            $cooldownSeconds = $settings['cooldown_seconds'];
            $maxDuplicateAttempts = $settings['max_duplicate_attempts'];

            // 5. Atomic state transition: create the daily row if needed, lock it,
            // then make every cooldown/state decision from the locked database row.
            $result = DB::transaction(function () use ($enrollment, $now, $currentTime, $cooldownSeconds, $maxDuplicateAttempts, $data, $inStart, $inEnd, $lateThreshold, $outStart, $outEnd, $inStartFormatted, $inEndFormatted, $outStartFormatted, $outEndFormatted) {
                $attendanceDate = $now->toDateString();
                $timestamp = $now->toDateTimeString();

                DB::table('qr_attendances')->insertOrIgnore([
                    'enrollment_id' => $enrollment->id,
                    'attendance_date' => $attendanceDate,
                    'spam_offense_count' => 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $qrAttendance = QrAttendance::where('enrollment_id', $enrollment->id)
                    ->where('attendance_date', $attendanceDate)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($qrAttendance->cooldown_expires_at && $qrAttendance->cooldown_expires_at->isFuture()) {
                    $qrAttendance->increment('spam_offense_count');
                    FlaggedScan::create([
                        'attendance_log_id' => null,
                        'flag_type' => 'excess_scan',
                        'description' => 'Scan blocked by cooldown (spam shield)',
                    ]);

                    $remainingSeconds = $qrAttendance->cooldown_expires_at->diffInSeconds($now);

                    return response()->json([
                        'message' => "Please wait {$remainingSeconds} seconds before scanning again.",
                        'retry_after' => $remainingSeconds,
                    ], 429);
                }

                $scanType = $qrAttendance->time_out_log_id !== null ? 'IN' : 'OUT';
                if ($qrAttendance->time_in_log_id === null) {
                    $scanType = 'IN';
                }

                // If already timed in and scanning again before the dismissal window opens
                if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
                    $log = AttendanceLog::create([
                        'enrollment_id' => $enrollment->id,
                        'scan_type' => 'IN',
                        'session_type' => $enrollment->section_session_type,
                        'scan_time' => $now,
                        'scanned_by_user_id' => $data['scanned_by_user_id'] ?? null,
                        'device_id' => $data['device_id'] ?? null,
                    ]);

                    $timeInLog = $qrAttendance->time_in_log_id ? AttendanceLog::find($qrAttendance->time_in_log_id) : null;
                    $timeInFormatted = $timeInLog?->scan_time ? \Carbon\Carbon::parse($timeInLog->scan_time)->format('g:i A') : $currentTime;

                    if ($qrAttendance->spam_offense_count < $maxDuplicateAttempts) {
                        FlaggedScan::create([
                            'attendance_log_id' => $log->id,
                            'flag_type' => 'duplicate_scan',
                            'description' => 'Duplicate IN scan',
                        ]);
                        $qrAttendance->increment('spam_offense_count');

                        return response()->json([
                            'message' => "Your time-in has already been recorded at {$timeInFormatted}. Check-out will open at {$outStartFormatted}.",
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
                        'message' => "Too many duplicate scans. Your time-in was recorded at {$timeInFormatted}. Please wait {$cooldownSeconds} seconds before trying again.",
                        'retry_after' => $cooldownSeconds,
                    ], 429);
                }

                // If both time-in and time-out are already recorded for today
                if ($scanType === 'IN' && $qrAttendance->time_in_log_id !== null && $qrAttendance->time_out_log_id !== null) {
                    $log = AttendanceLog::create([
                        'enrollment_id' => $enrollment->id,
                        'scan_type' => 'IN',
                        'session_type' => $enrollment->section_session_type,
                        'scan_time' => $now,
                        'scanned_by_user_id' => $data['scanned_by_user_id'] ?? null,
                        'device_id' => $data['device_id'] ?? null,
                    ]);
                    FlaggedScan::create([
                        'attendance_log_id' => $log->id,
                        'flag_type' => 'duplicate_scan',
                        'description' => 'Duplicate IN scan rejected',
                    ]);

                    $timeOutLog = AttendanceLog::find($qrAttendance->time_out_log_id);
                    $timeOutFormatted = $timeOutLog?->scan_time ? \Carbon\Carbon::parse($timeOutLog->scan_time)->format('g:i A') : $currentTime;

                    return response()->json([
                        'message' => "Your time-out has already been recorded at {$timeOutFormatted}. Attendance for today is complete.",
                    ], 400);
                }

                if ($scanType === 'IN' && !($currentTime >= $inStart && $currentTime <= $inEnd)) {
                    return response()->json([
                        'message' => "Check-in is currently closed. Allowed check-in window is {$inStartFormatted} – {$inEndFormatted}.",
                        'in_window' => "{$inStartFormatted} – {$inEndFormatted}",
                        'current_time' => $now->format('g:i A'),
                    ], 400);
                }
                if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
                    return response()->json([
                        'message' => "Check-out is currently closed. Allowed check-out window is {$outStartFormatted} – {$outEndFormatted}.",
                        'out_window' => "{$outStartFormatted} – {$outEndFormatted}",
                        'current_time' => $now->format('g:i A'),
                    ], 400);
                }

                $isLate = $scanType === 'IN' && $currentTime > $lateThreshold;
                $log = AttendanceLog::create([
                    'enrollment_id' => $enrollment->id,
                    'scan_type' => $scanType,
                    'session_type' => $enrollment->section_session_type,
                    'scan_time' => $now,
                    'scanned_by_user_id' => $data['scanned_by_user_id'] ?? null,
                    'device_id' => $data['device_id'] ?? null,
                ]);

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
                return $log;
            });

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            $log = $result;

            try {
                AttendanceRecorded::dispatch([
                    'attendance_log_id' => $log->id,
                    'enrollment_id' => $enrollment->id,
                    'section_id' => $enrollment->section_id,
                    'student' => [
                        'id' => $student->id,
                        'name' => trim("{$student->first_name} {$student->last_name}"),
                        'student_number' => $student->student_number,
                        'grade' => $enrollment->section_grade,
                        'section' => $enrollment->section_name,
                    ],
                    'scan_type' => $log->scan_type,
                    'scan_time' => $log->scan_time?->toIso8601String(),
                    'formatted_time' => $log->scan_time?->format('h:i A'),
                    'late' => $log->flagged_scans()->where('flag_type', 'late_arrival')->exists(),
                    'flags' => $log->flagged_scans()->pluck('flag_type')->values()->all(),
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Attendance realtime dispatch failed', [
                    'attendance_log_id' => $log->id,
                    'error' => $exception->getMessage(),
                ]);
            }

            // 11. Notify guardian by email
            $this->sendGuardianEmail($student, $guardian, $log, $log->scan_type, $now);

            $timeFormatted = $now->format('g:i A');
            $successMessage = $log->scan_type === 'OUT'
                ? "Time-out recorded successfully at {$timeFormatted}."
                : ($log->scan_type === 'IN' && $currentTime > $lateThreshold
                    ? "Late time-in recorded successfully at {$timeFormatted}."
                    : "Time-in recorded successfully at {$timeFormatted}.");

            return response()->json([
                'message' => $successMessage,
                'student' => [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'student_number' => $student->student_number,
                    'level' => $schedule->level,
                    'session_type' => $enrollment->section_session_type,
                    'section' => $enrollment->section_grade
                        ? "Grade {$enrollment->section_grade} - {$enrollment->section_name}"
                        : null,
                ],
                'attendance_log' => [
                    'id' => $log->id,
                    'scan_type' => $log->scan_type,
                    'scan_time' => $log->scan_time,
                ],
                'late' => $log->scan_type === 'IN' && $currentTime > $lateThreshold,
            ]);
        } catch (\Throwable $e) {
            Log::error('Attendance scan processing failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return response()->json([
                'message' => 'Attendance service is temporarily unavailable. Please try again in a moment.'
            ], 500);
        }
    }

    /**
     * Cooldown / duplicate settings for the scan flow, cached in Redis since they
     * change rarely. Falls back to a single query (or the original lookups) if
     * Redis is unavailable.
     */
    private function scanSettings(): array
    {
        try {
            return Cache::store('redis')->remember('qr:scan-settings', 3600, function () {
                $values = SystemSetting::whereIn('key', ['spam_cooldown_seconds', 'max_duplicate_attempts'])
                    ->pluck('value', 'key');

                return [
                    'cooldown_seconds' => (int) ($values['spam_cooldown_seconds'] ?? 300),
                    'max_duplicate_attempts' => (int) ($values['max_duplicate_attempts'] ?? 3),
                ];
            });
        } catch (\Throwable $e) {
            return [
                'cooldown_seconds' => (int) (SystemSetting::getValue('spam_cooldown_seconds') ?? 300),
                'max_duplicate_attempts' => (int) (SystemSetting::getValue('max_duplicate_attempts') ?? 3),
            ];
        }
    }



    private function resolveStudent(array $data): ?Student
    {
        if (!empty($data['code'])) {
            // Single round trip: qr_codes -> students -> guardians via joins.
            $student = Student::query()
                ->select(
                    'students.*',
                    'guardians.id as guardian_id',
                    'guardians.email as guardian_email'
                )
                ->join('qr_codes', 'qr_codes.student_id', '=', 'students.id')
                ->leftJoin('guardians', 'guardians.student_id', '=', 'students.id')
                ->where('qr_codes.code', $data['code'])
                ->where('qr_codes.is_active', true)
                ->first();

            if ($student) {
                if ($student->guardian_id) {
                    $guardian = new Guardian();
                    $guardian->setRawAttributes([
                        'id' => $student->guardian_id,
                        'student_id' => $student->id,
                        'email' => $student->guardian_email,
                    ]);
                    $student->setRelation('guardian', $guardian);
                } else {
                    // Mark as loaded (null) so no lazy query runs for guardian-less students.
                    $student->setRelation('guardian', null);
                }
            }

            return $student;
        }

        return Student::with('guardian')->find($data['student_id']);
    }

    private function rejectDuplicate(Enrollment $enrollment, $now, string $scanType)
    {
        $log = AttendanceLog::create([
            'enrollment_id' => $enrollment->id,
            'scan_type' => $scanType,
            'session_type' => $enrollment->section_session_type,
            'scan_time' => $now,
        ]);

        FlaggedScan::create([
            'attendance_log_id' => $log->id,
            'flag_type' => 'duplicate_scan',
            'description' => "Duplicate {$scanType} scan rejected",
        ]);

        $formattedTime = \Carbon\Carbon::parse($now)->format('g:i A');
        $label = $scanType === 'OUT' ? 'time-out' : 'time-in';

        return response()->json([
            'message' => "Your {$label} has already been recorded for today at {$formattedTime}.",
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
            'session_type' => $enrollment->section_session_type,
            'scan_time' => $now,
        ]);

        $timeInLog = $qrAttendance->time_in_log_id ? AttendanceLog::find($qrAttendance->time_in_log_id) : null;
        $formattedTime = $timeInLog?->scan_time ? \Carbon\Carbon::parse($timeInLog->scan_time)->format('g:i A') : \Carbon\Carbon::parse($now)->format('g:i A');

        if ($qrAttendance->spam_offense_count < $maxAttempts) {
            FlaggedScan::create([
                'attendance_log_id' => $log->id,
                'flag_type' => 'duplicate_scan',
                'description' => 'Duplicate IN scan',
            ]);

            $qrAttendance->increment('spam_offense_count');

            return response()->json([
                'message' => "Your time-in has already been recorded at {$formattedTime}. Please wait for check-out time.",
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
            'message' => "Too many duplicate scans. Your time-in was recorded at {$formattedTime}. Please wait {$cooldownSeconds} seconds before trying again.",
            'retry_after' => $cooldownSeconds,
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
            EmailLogCreated::dispatch([
                'id' => $emailLog->id,
                'student_id' => $student->id,
                'student_name' => trim("{$student->first_name} {$student->last_name}"),
                'student_number' => $student->student_number,
                'email' => $guardian->email,
                'scan_type' => $scanType,
                'status' => 'pending',
                'time' => now()->format('h:i A'),
                'date' => now()->format('M d, Y'),
            ]);
        } catch (\Throwable $e) {
            // Log realtime broadcast error without interrupting request
        }

        SendGateScanNotification::dispatch($emailLog->id)->afterCommit();
    }
}
