<?php

namespace App\Http\Controllers\QrSystemFeature\Logs;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    // Attendance logs module

    public function index(Request $request)
    {
        $t0 = microtime(true); // TEMP
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        $attendanceLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
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
            ->paginate(15)
            ->withQueryString();

        \Illuminate\Support\Facades\Log::debug('[PROFILE-CTRL:time-in-time-out-history] ms=' . round((microtime(true) - $t0) * 1000, 1)); // TEMP

        return view(
            'admin-modules.monitoring.time-in-time-out-history',
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
     * Teacher index method for time in time out history
     */
    public function teacherIndex(Request $request)
    {
        return $this->index($request);
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

            case 'week':
                return $query->thisWeekOnly();

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
