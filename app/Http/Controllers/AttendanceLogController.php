<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    // Attendance logs module

    public function index(Request $request)
    {
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        $attendanceLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'flagged_scans'
        ]);

        $attendanceLogsQuery = $this->applyDateFilter(
            $attendanceLogsQuery,
            $dateFilter,
            $customStartDate,
            $customEndDate
        );

        $attendanceLogsQuery = $attendanceLogsQuery
            ->search($query)
            ->filterByScanType($scan_type)
            ->filterBySessionType($session_type)
            ->filterByFlagType($flag_type);

        $statusCounts = $attendanceLogsQuery->getStatistics();

        $attendance_logs = $attendanceLogsQuery
            ->orderBy('scan_time', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view(
            'admin-modules.monitoring.entry-exit',
            compact(
                'attendance_logs',
                'scan_type',
                'session_type',
                'flag_type',
                'statusCounts',
                'dateFilter',
                'customStartDate',
                'customEndDate',
                'query'
            )
        );
    }

    /**
     * Apply date filtering
     */
    private function applyDateFilter(
        $query,
        $dateFilter,
        $customStartDate = null,
        $customEndDate = null
    ) {
        switch ($dateFilter) {
            case 'today':
                return $query->todayOnly();

            case 'yesterday':
                return $query->yesterdayOnly();

            case 'last_7_days':
                return $query->last7Days();

            case 'month':
                return $query->filterByDateRange(now()->subMonth(), now());

            case 'custom':
                if ($customStartDate && $customEndDate) {
                    return $query->filterByDateRange(
                        $customStartDate,
                        $customEndDate
                    );
                }

                return $query->todayOnly();

            default:
                return $query->todayOnly();
        }
    }
}