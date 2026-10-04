<?php

namespace App\Http\Controllers\ScannerOperator\QrStation;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class QrStationController extends Controller
{
    public function index(Request $request)
    {
        $recentLogs = AttendanceLog::with(['enrollment.student', 'enrollment.section', 'flagged_scans'])
            ->withoutExcessScanFlags()
            ->todayOnly()
            ->orderBy('scan_time', 'desc')
            ->limit(25)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'student_name' => trim(
                    ($log->enrollment?->student?->first_name ?? '') . ' ' .
                        ($log->enrollment?->student?->last_name ?? '')
                ) ?: 'Unknown',
                'student_number' => $log->enrollment?->student?->student_number ?? '-',
                'grade' => $log->enrollment?->section?->grade_level
                    ? 'Grade ' . $log->enrollment->section->grade_level
                    : '-',
                'section' => $log->enrollment?->section?->name ?? '-',
                'scan_type' => $log->scan_type,
                'scan_time' => $log->scan_time?->format('h:i A'),
                'session_type' => $log->session_type,
                'flags' => $log->flagged_scans
                    ->pluck('flag_type')
                    ->filter()
                    ->unique()
                    ->values(),
            ]);

        // All live overview metrics in a single aggregate query (was 7 separate counts).
        $stats = AttendanceLog::todayOnly()
            ->withoutExcessScanFlags()
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN scan_type = 'IN' THEN 1 ELSE 0 END) AS in_count,
                SUM(CASE WHEN scan_type = 'OUT' THEN 1 ELSE 0 END) AS out_count,
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM flagged_scans
                    WHERE flagged_scans.attendance_log_id = attendance_logs.id
                ) THEN 1 ELSE 0 END) AS flagged,
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM flagged_scans
                    WHERE flagged_scans.attendance_log_id = attendance_logs.id
                        AND flagged_scans.flag_type = 'late_arrival'
                ) THEN 1 ELSE 0 END) AS late
            ")
            ->first();



        return view('pov.scanner-operator.qr-station.station', [
            'initialData' => [
                'recent_logs' => $recentLogs,
                'overview' => [
                    'total' => (int) $stats->total,
                    'in' => (int) $stats->in_count,
                    'out' => (int) $stats->out_count,
                    'late' => (int) $stats->late,
                    'flagged' => (int) $stats->flagged,
                ],

            ],
        ]);
    }
}
