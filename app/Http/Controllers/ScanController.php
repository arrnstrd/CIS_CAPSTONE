<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\QrCode;
use App\Models\ScheduleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        // validate QR input
        $request->validate([
            'code' => ['required', 'string']
        ]);

        // find active QR code
        $qr = QrCode::with('student.guardian')
            ->where('code', $request->code)
            ->where('is_active', true)
            ->first();

        // invalid QR
        if (!$qr) {
            return response()->json([
                'message' => 'Invalid QR Code'
            ], 404);
        }

        // resolve student from QR
        $student = $qr->student;

        // student missing
        if (!$student) {
            return response()->json([
                'message' => 'Student not found for this QR'
            ], 404);
        }

        $guardian = $student->guardian;

        // resolve active enrollment
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        // enrollment missing
        if (!$enrollment) {
            return response()->json([
                'message' => 'No active enrollment found for this student'
            ], 404);
        }

        // get schedule configuration based on enrollment
        $schedule = ScheduleConfig::where('level', $enrollment->level)
            ->where('session_type', $enrollment->session_type)
            ->first();

        // determine if late
        $isLate = false;

        if ($schedule) {
            $isLate = now()->format('H:i:s') > $schedule->late_threshold;
        }

        // check for duplicate scan BEFORE inserting
        $existingToday = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', today())
            ->exists();

        // start transaction
        $attendanceLog = DB::transaction(function () use (
            $enrollment,
            $isLate,
            $existingToday
        ) {
            // create attendance log
            $attendanceLog = AttendanceLog::create([
                'enrollment_id' => $enrollment->id,
                'scan_type' => 'IN',
                'session_type' => $enrollment->session_type,
                'scan_time' => now(),
            ]);

            // flag duplicate scan
            if ($existingToday) {
                FlaggedScan::create([
                    'attendance_log_id' => $attendanceLog->id,
                    'flag_type' => 'duplicate_scan',
                    'description' => 'Multiple scans detected today'
                ]);
            }

            // flag late arrival
            if ($isLate) {
                FlaggedScan::create([
                    'attendance_log_id' => $attendanceLog->id,
                    'flag_type' => 'late_arrival',
                    'description' => 'Student arrived after allowed schedule'
                ]);
            }

            return $attendanceLog;
        });

        // email flow
        if ($guardian && !empty($guardian->email)) {
            $emailLog = EmailLog::create([
                'attendance_log_id' => $attendanceLog->id,
                'student_id' => $student->id,
                'email' => $guardian->email,
                'scan_type' => 'IN',
                'status' => 'pending',
                'attempt_count' => 0
            ]);

            try {
                Mail::raw(
                    "Magandang araw! Si {$student->first_name} {$student->last_name} ay naka-scan ng pasok ngayong " . now()->format('h:i A') . ".",
                    function ($message) use ($guardian, $student) {
                        $message->to($guardian->email)
                            ->subject("CIS Attendance: {$student->first_name} ay nakapasok na");
                    }
                );

                $emailLog->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'attempt_count' => 1,
                    'last_attempt_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("Mail failed for student {$student->id}: " . $e->getMessage());

                $emailLog->update([
                    'status' => 'failed',
                    'attempt_count' => 1,
                    'last_attempt_at' => now(),
                ]);
            }
        }

        // success response — limited fields only, no sensitive data
        return response()->json([
            'message' => 'Scan successful',
            'student' => [
                'id' => $student->id,
                'name' => "{$student->first_name} {$student->last_name}",
                'student_number' => $student->student_number,
                'section' => $enrollment->section,
                'level' => $enrollment->level,
            ],
            'attendance_log' => [
                'id' => $attendanceLog->id,
                'scan_type' => $attendanceLog->scan_type,
                'session_type' => $attendanceLog->session_type,
                'scan_time' => $attendanceLog->scan_time,
            ],
            'late' => $isLate,
            'flagged' => $existingToday || $isLate,
        ]);
    }
}