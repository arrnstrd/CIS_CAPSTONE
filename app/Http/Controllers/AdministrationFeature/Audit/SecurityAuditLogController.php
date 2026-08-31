<?php

namespace App\Http\Controllers\AdministrationFeature\Audit;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityAuditLogController extends Controller
{
    /**
     * Display paginated list of security audit login events with search and date filters.
     */
    public function index(Request $request)
    {
        $query = LoginLog::with('user');

        $currentFilter = $request->input('date_filter', 'all');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');
        $search = strtolower(trim((string) $request->input('query')));
        $status = $request->input('status', 'all');

        $query = $this->applyFilters($query, $currentFilter, $customStartDate, $customEndDate, $search, $status);

        $activityLogs = $query->latest('attempted_at')
            ->paginate(20)
            ->appends($request->query());

        $dateFilters = [
            'all' => 'All Time',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        return view('admin-modules.management.security-audit-log', compact(
            'activityLogs',
            'currentFilter',
            'dateFilters',
            'customStartDate',
            'customEndDate',
            'search',
            'status'
        ));
    }

    /**
     * Download Filtered Security Audit Logs as an Excel (.xlsx) file.
     */
    public function downloadExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = strtolower(trim((string) $request->input('query')));
        $status = $request->input('status', 'all');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->diffInDays($end) > 62) {
            return back()->with('error', 'The selected export range cannot exceed 62 days.')->withInput();
        }

        $logs = LoginLog::with('user')
            ->whereBetween('attempted_at', [$start, $end])
            ->when($status !== 'all' && !empty($status), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(email_attempted) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(ip_address) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"]);
                        });
                });
            })
            ->orderBy('attempted_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Security Audit Log');

        $columns = ['User Account', 'Email Attempted', 'Authentication Event', 'IP Address', 'Device / Browser', 'Date & Time', 'Status'];

        foreach ($columns as $colIndex => $header) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($column . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
            $sheet->getStyle($column . '1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $rowNum = 2;
        foreach ($logs as $log) {
            $userName = $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'Guest / System';
            $sheet->setCellValue('A' . $rowNum, $userName);
            $sheet->setCellValue('B' . $rowNum, $log->email_attempted ?? '-');
            $sheet->setCellValue('C' . $rowNum, 'Login Attempt');
            $sheet->setCellValue('D' . $rowNum, $log->ip_address ?? '-');
            $sheet->setCellValue('E' . $rowNum, $log->formatted_device ?? '-');
            $sheet->setCellValue('F' . $rowNum, $log->attempted_at ? Carbon::parse($log->attempted_at)->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue('G' . $rowNum, ucfirst($log->status ?? 'unknown'));
            $rowNum++;
        }

        $filename = 'Security-Audit-Log-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download Filtered Security Audit Logs as a PDF report.
     */
    public function downloadPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = strtolower(trim((string) $request->input('query')));
        $status = $request->input('status', 'all');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $logs = LoginLog::with('user')
            ->whereBetween('attempted_at', [$start, $end])
            ->when($status !== 'all' && !empty($status), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(email_attempted) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(ip_address) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                               ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"]);
                        });
                });
            })
            ->orderBy('attempted_at', 'desc')
            ->get();

        $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' – ' . Carbon::parse($endDate)->format('M d, Y');

        $pdf = Pdf::loadView('pdf.security-audit-log-report', compact('logs', 'dateRangeLabel'));
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Security-Audit-Log-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Apply date, search, and status filters.
     */
    private function applyFilters($query, string $dateFilter, ?string $customStart, ?string $customEnd, string $search, string $status)
    {
        $today = Carbon::today();

        match ($dateFilter) {
            'today' => $query->whereDate('attempted_at', $today),
            'yesterday' => $query->whereDate('attempted_at', $today->copy()->subDay()),
            'last_7_days' => $query->whereBetween('attempted_at', [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()]),
            'month' => $query->whereBetween('attempted_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
            'custom' => ($customStart && $customEnd) ? $query->whereBetween('attempted_at', [Carbon::parse($customStart)->startOfDay(), Carbon::parse($customEnd)->endOfDay()]) : $query,
            default => $query,
        };

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->whereRaw('LOWER(email_attempted) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(ip_address) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->whereRaw('LOWER(first_name) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        return $query;
    }
}
