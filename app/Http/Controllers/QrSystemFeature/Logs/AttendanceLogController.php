<?php

namespace App\Http\Controllers\QrSystemFeature\Logs;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        ])->withoutExcessScanFlags();

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
     * Export the filtered time in / time out logs to an Excel file.
     * Date range is supplied by the modal (max one month = 31 days).
     */
    public function download(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        // Enforce a maximum of one month (31 days) on the exported range.
        if ($start->diffInDays($end) > 31) {
            return back()
                ->with('error', 'The selected date range cannot exceed one month (31 days).')
                ->withInput();
        }

        $logs = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans',
        ])
            ->withoutExcessScanFlags()
            ->filterByDateRange($validated['start_date'], $validated['end_date'])
            ->search($request->input('query'))
            ->filterByScanType($request->input('scan_type'))
            ->filterBySessionType($request->input('session_type'))
            ->filterByFlagType($request->input('flag_type'))
            ->orderBy('scan_time', 'desc')
            ->get();

        // Build the spreadsheet following the same pattern as BulkImportController::exportErrors.
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $columns = ['Student', 'Date', 'Grade & Section', 'Scan Type', 'Session', 'Gate Time', 'Remarks'];

        foreach ($columns as $colIndex => $header) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $rowNum = 2;

        foreach ($logs as $log) {
            $student     = $log->enrollment?->student;
            $section     = $log->enrollment?->section;
            $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '-';
            $gradeSection = 'Grade ' . ($section?->grade_level ?? '-') . ' — ' . ($section?->name ?? '-');

            $flagTypes = $log->flagged_scans->pluck('flag_type')->filter()->unique();

            $flagText = $flagTypes->isNotEmpty()
                ? $flagTypes->map(fn ($flagType) => match ($flagType) {
                    'late_arrival' => 'Late arrival',
                    'invalid_checkout' => 'Checkout issue',
                    default => 'Needs review',
                })->join(', ')
                : '-';

            $sheet->setCellValue('A' . $rowNum, $studentName . ' (' . ($student?->student_number ?? '-') . ')');
            $sheet->setCellValue('B' . $rowNum, $log->scan_time?->format('Y-m-d') ?? '-');
            $sheet->setCellValue('C' . $rowNum, $gradeSection);
            $sheet->setCellValue('D' . $rowNum, $log->scan_type === 'IN' ? 'Time In' : ($log->scan_type === 'OUT' ? 'Time Out' : 'Unknown'));
            $sheet->setCellValue('E' . $rowNum, $log->session_type ? Str::headline(str_replace('_', ' ', $log->session_type)) : '-');
            $sheet->setCellValue('F' . $rowNum, $log->scan_time?->format('h:i A') ?? '-');
            $sheet->setCellValue('G' . $rowNum, $flagText);
            $rowNum++;
        }

        $headerRange = 'A1:' . Coordinate::stringFromColumnIndex(count($columns)) . '1';
        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E8E8E8');

        $sheet->freezePane('A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'attendance_export_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $filename = 'attendance-logs-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.xlsx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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
