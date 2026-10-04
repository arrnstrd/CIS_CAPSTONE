<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceMonitoringController extends Controller
{
    public function resync(Request $request): JsonResponse
    {
        $startOfDay = Carbon::today();
        $startOfNextDay = $startOfDay->copy()->addDay();

        $baseQuery = AttendanceLog::query()
            ->where('scan_time', '>=', $startOfDay)
            ->where('scan_time', '<', $startOfNextDay)
            ->withoutExcessScanFlags();

        $stats = (clone $baseQuery)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN scan_type = 'IN' THEN 1 ELSE 0 END) AS in_count")
            ->selectRaw("SUM(CASE WHEN scan_type = 'OUT' THEN 1 ELSE 0 END) AS out_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN scan_type = 'IN' AND NOT EXISTS (SELECT 1 FROM attendance_logs AS outs WHERE outs.enrollment_id = attendance_logs.enrollment_id AND outs.scan_type = 'OUT' AND outs.scan_time >= ? AND outs.scan_time < ?) THEN enrollment_id END) AS present_count", [$startOfDay, $startOfNextDay])
            ->selectRaw('SUM(CASE WHEN EXISTS (SELECT 1 FROM flagged_scans WHERE flagged_scans.attendance_log_id = attendance_logs.id) THEN 1 ELSE 0 END) AS flagged_count')
            ->selectRaw("SUM(CASE WHEN EXISTS (SELECT 1 FROM flagged_scans WHERE flagged_scans.attendance_log_id = attendance_logs.id AND flagged_scans.flag_type = 'late_arrival') THEN 1 ELSE 0 END) AS late_count")
            ->first();

        $recentLogs = (clone $baseQuery)
            ->with(['enrollment.student', 'enrollment.section', 'flagged_scans'])
            ->latest('scan_time')
            ->limit(20)
            ->get()
            ->map(function (AttendanceLog $log): array {
                $student = $log->enrollment?->student;
                $section = $log->enrollment?->section;

                return [
                    'id' => $log->id,
                    'student_name' => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: 'Unknown',
                    'student_number' => $student?->student_number ?? '-',
                    'grade' => $section?->grade_level ? 'Grade ' . $section->grade_level : '-',
                    'section' => $section?->name ?? '-',
                    'scan_type' => $log->scan_type,
                    'scan_time' => $log->scan_time?->toIso8601String(),
                    'session_type' => $log->session_type,
                    'flags' => $log->flagged_scans->pluck('flag_type')->filter()->unique()->values()->all(),
                ];
            })
            ->values();

        $now = now();
        $intervalMinutes = 15;
        $chartStart = $startOfDay->copy()->setTime(6, 0);
        $todayEnd = $startOfDay->copy()->setTime(18, 0);
        $chartEnd = $now->greaterThan($todayEnd) ? $now->copy() : $todayEnd;

        $todayLogs = (clone $baseQuery)->orderBy('scan_time')->get();

        $scanBuckets = [];
        $cursor = $chartStart->copy();
        while ($cursor->lt($chartEnd)) {
            $bucketEnd = $cursor->copy()->addMinutes($intervalMinutes);
            $bucketLogs = $todayLogs->filter(function (AttendanceLog $log) use ($cursor, $bucketEnd) {
                return $log->scan_time
                    && $log->scan_time->gte($cursor)
                    && $log->scan_time->lt($bucketEnd);
            });

            $timeIn = $bucketLogs->where('scan_type', 'IN')->count();
            $timeOut = $bucketLogs->where('scan_type', 'OUT')->count();

            $scanBuckets[] = [
                'label' => $cursor->format('g:i A'),
                'range' => $cursor->format('g:i A') . ' - ' . $bucketEnd->format('g:i A'),
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'total' => $timeIn + $timeOut,
            ];

            $cursor = $bucketEnd;
        }

        $weeklyWindowStart = $now->copy()->startOfWeek(Carbon::MONDAY)->subWeeks(5)->startOfDay();
        $weeklyWindowEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $weeklyLogs = AttendanceLog::query()
            ->withoutExcessScanFlags()
            ->whereBetween('scan_time', [$weeklyWindowStart, $weeklyWindowEnd])
            ->get();

        $weeklyScanBuckets = collect(range(0, 5))->map(function ($offset) use ($weeklyWindowStart, $weeklyLogs) {
            $weekStart = $weeklyWindowStart->copy()->addWeeks($offset)->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $bucketLogs = $weeklyLogs->filter(function (AttendanceLog $log) use ($weekStart, $weekEnd) {
                return $log->scan_time
                    && $log->scan_time->gte($weekStart)
                    && $log->scan_time->lte($weekEnd);
            });

            return [
                'label' => $weekStart->format('M j'),
                'range' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'total' => $bucketLogs->count(),
            ];
        })->values()->all();

        return response()->json([
            'overview' => [
                'total' => (int) ($stats->total ?? 0),
                'present' => (int) ($stats->present_count ?? 0),
                'in' => (int) ($stats->in_count ?? 0),
                'out' => (int) ($stats->out_count ?? 0),
                'late' => (int) ($stats->late_count ?? 0),
                'flagged' => (int) ($stats->flagged_count ?? 0),
            ],
            'recent_logs' => $recentLogs,
            'scan_buckets' => $scanBuckets,
            'weekly_buckets' => $weeklyScanBuckets,
        ]);
    }
}
