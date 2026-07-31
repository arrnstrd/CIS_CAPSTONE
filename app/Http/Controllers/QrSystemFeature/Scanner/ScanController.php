<?php

namespace App\Http\Controllers\QrSystemFeature\Scanner;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\EmailLog;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\QrCode;
use App\Services\ScheduleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\GateScanMail;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        // Validate QR input
        $request->validate([
            'code' => ['required', 'string']
        ]);

        // Resolve QR + student
        $qr = QrCode::with('student.guardian')
            ->where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$qr || !$qr->student) {
            return response()->json(['message' => 'Invalid QR or student not found'], 404);
        }

        $student = $qr->student;
        $guardian = $student->guardian;

        // Resolve active enrollment
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'No active enrollment'], 404);
        }

        $enrollment->loadMissing('sectionModel');

        if (!$enrollment->sectionModel) {
            return response()->json(['message' => 'Enrollment section not found'], 404);
        }

        // Current time
        $now = now();
        $currentTime = $now->format('H:i');

        // Resolve schedule
        $resolver = new ScheduleResolver();
        $activeSchedule = $resolver->resolve(
            $enrollment->sectionModel->level,
            $enrollment->session_type,
            $currentTime
        );

        if (!$activeSchedule) {
            return response()->json([
                'message' => 'No active schedule for current time'
            ], 400);
        }

        // Normalize schedule times
        $inStart = date('H:i', strtotime($activeSchedule->in_start));
        $inEnd = date('H:i', strtotime($activeSchedule->in_end));
        $lateThreshold = date('H:i', strtotime($activeSchedule->late_threshold));
        $outStart = date('H:i', strtotime($activeSchedule->out_start));
        $outEnd = date('H:i', strtotime($activeSchedule->out_end));


        // Get last scan today
        $lastLog = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', today())
            ->where('session_type', $activeSchedule->session_type)
            ->latest('id')
            ->first();


        
        // // MVP STATE MACHINE (IN / OUT only)
        // $scanType = (!$lastLog || $lastLog->scan_type === 'OUT') ? 'IN' : 'OUT' ;
        if (!$lastLog) {

            $scanType = 'IN';

        } else {

            switch ($lastLog->scan_type) {

                case 'IN':
                    $scanType = 'OUT';
                    break;

                case 'OUT':
                    $scanType = 'RE_ENTRY';
                    break;

                case 'RE_ENTRY':
                    $scanType = 'RE_EXIT';
                    break;

                case 'RE_EXIT':
                    $scanType = 'RE_ENTRY';
                    break;

                default:
                    return response()->json([
                        'message' => 'Unknown scan state'
                    ], 400);
            }
        }

        // // IN window validation
        if ($scanType === 'IN' && !($currentTime >= $inStart && $currentTime <= $inEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-in window',
                'in_window' => "{$inStart} – {$inEnd}",
                'current_time' => $currentTime,
            ], 400);
        }

        // OUT window validation
        if ($scanType === 'OUT' && !($currentTime >= $outStart && $currentTime <= $outEnd)) {
            return response()->json([
                'message' => 'Outside of allowed check-out window',
                'out_window' => "{$outStart} – {$outEnd}",
                'current_time' => $currentTime,
            ], 400);
        }

        //student movements
        if (!$lastLog) {

            $scanType = 'IN';

        } elseif ($lastLog->scan_type === 'IN') {

            $scanType = 'OUT';

        } elseif ($lastLog->scan_type === 'OUT') {

            $scanType = 'RE_ENTRY';

        } elseif ($lastLog->scan_type === 'RE_ENTRY') {

            $scanType = 'RE_EXIT';

        } elseif ($lastLog->scan_type === 'RE_EXIT') {

            $scanType = 'RE_ENTRY';

        } else {

            return response()->json([
                'message' => 'Invalid scan state'
            ], 400);
        }






        // Late detection
        $isLate = $scanType === 'IN' && $currentTime > $lateThreshold;

        // Store attendance
        $attendanceLog = DB::transaction(function () use ($enrollment, $activeSchedule, $scanType) {
            return AttendanceLog::create([
                'enrollment_id' => $enrollment->id,
                'scan_type' => $scanType,
                'session_type' => $activeSchedule->session_type,
                'scan_time' => now(),
            ]);
        });
        

        // Flags
        if ($scanType === 'IN' && $isLate) {
            FlaggedScan::create([
                'attendance_log_id' => $attendanceLog->id,
                'flag_type' => 'late_arrival',
                'description' => 'Student arrived late'
            ]);
        }

        if ($scanType === 'OUT' && $lastLog && $lastLog->scan_type !== 'IN') {
            FlaggedScan::create([
                'attendance_log_id' => $attendanceLog->id,
                'flag_type' => 'invalid_checkout',
                'description' => 'OUT scan without valid IN'
            ]);
        }




        // Email notification
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

                Mail::to($guardian->email)->send(
                    new GateScanMail(
                        $student,
                        $scanType,
                        now()->format('F d, Y - h:i A')
                    )
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

            // Response
            return response()->json([
                'message' => 'Scan successful',
                'student' => [
                    'id' => $student->id,
                    'name' => "{$student->first_name} {$student->last_name}",
                    'student_number' => $student->student_number,
                    'level' => $activeSchedule->level,
                    'session_type' => $enrollment->session_type,
                ],
                'attendance_log' => [
                    'id' => $attendanceLog->id,
                    'scan_type' => $scanType,
                    'scan_time' => $attendanceLog->scan_time,
                ],
                'late' => $isLate,
            ]);
        }
    }
}
