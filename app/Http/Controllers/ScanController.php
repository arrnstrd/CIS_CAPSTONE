<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\Guardian;
use App\Models\QrCode;
use App\Models\ScheduleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $qr = QrCode::where('code', $request->code)
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

        // resolve enrollment
        $enrollment = Enrollment::where('student_id', $student->id)->first();

        // enrollment missing
        if (!$enrollment) {
            return response()->json([
                'message' => 'Enrollment not found'
            ], 404);
        }

        // get schedule configuration
        $schedule = ScheduleConfig::where('level', $student->level ?? 'hs')
            ->where('session_type', $student->session_type ?? 'morning')
            ->first();

        // determine if late
        $isLate = false;

        if ($schedule) {
            $isLate = now()->format('H:i:s') > $schedule->late_threshold;
        }

        // start transaction
        $attendanceLog = DB::transaction(function () use (
            $enrollment,
            $student,
            $isLate
        ) {

            // create attendance log
            $attendanceLog = AttendanceLog::create([
                'enrollment_id' => $enrollment->id,
                'scan_type' => 'IN',
                'session_type' => $student->session_type ?? 'morning',
                'scan_time' => now(),
            ]);

            // detect duplicate scan
            $duplicateScan = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->whereDate('scan_time', today())
                ->count();

            // flag duplicate
            if ($duplicateScan > 1) {
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

        // resolve guardian
        $guardian = Guardian::where('student_id', $student->id)->first();

        // email flow
        if ($guardian && $guardian->email) {

            // create email log
            $emailLog = EmailLog::create([
                'attendance_log_id' => $attendanceLog->id,
                'student_id' => $student->id,
                'email' => $guardian->email,
                'scan_type' => 'IN',
                'status' => 'pending',
                'attempt_count' => 0
            ]);

            try {

                // send email
                Mail::raw(
                    "Student {$student->first_name} scanned at " . now(),
                    function ($message) use ($guardian) {
                        $message->to($guardian->email)
                            ->subject('CIS Gate Scan Notification');
                    }
                );

                // update email log status
                $emailLog->update([
                    'status' => 'sent',
                    'attempt_count' => $emailLog->attempt_count + 1
                ]);

            } catch (\Exception $e) {

                // update failed email status
                $emailLog->update([
                    'status' => 'failed',
                    'attempt_count' => $emailLog->attempt_count + 1
                ]);
            }
        }

        // success response
        return response()->json([
            'message' => 'Scan successful',
            'student' => $student,
            'attendance_log' => $attendanceLog,
            'late' => $isLate
        ]);
    }
}