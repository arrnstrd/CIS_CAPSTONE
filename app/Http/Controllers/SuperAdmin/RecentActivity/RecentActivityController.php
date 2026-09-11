<?php

namespace App\Http\Controllers\SuperAdmin\RecentActivity;

use App\Http\Controllers\Controller;
use App\Libraries\PDF\DomPdfWrapper;
use App\Models\AdminActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecentActivityController extends Controller
{
    public function __construct(
        private readonly DomPdfWrapper $pdfWrapper = new DomPdfWrapper()
    ) {}
    /**
     * Display paginated list of administrative activity events with search and date filters.
     */
    public function index(Request $request)
    {
        $query = AdminActivityLog::with('actor');

        $currentFilter = $request->input('date_filter', 'all');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');
        $search = strtolower(trim((string) $request->input('query')));
        $result = $request->input('result', 'all');

        $query = $this->applyFilters($query, $currentFilter, $customStartDate, $customEndDate, $search, $result);

        $recentActivities = $query->latest('created_at')
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

        return view('pov.super-admin.recent-activity.recent-activity', compact(
            'recentActivities',
            'currentFilter',
            'dateFilters',
            'customStartDate',
            'customEndDate',
            'search',
            'result'
        ));
    }

    /**
     * Download Filtered Administrative Activities as an Excel (.xlsx) file.
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
        $result = $request->input('result', 'all');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->diffInDays($end) > 62) {
            return back()->with('error', 'The selected export range cannot exceed 62 days.')->withInput();
        }

        $logs = AdminActivityLog::with('actor')
            ->whereBetween('created_at', [$start, $end])
            ->when($result !== 'all' && !empty($result), function ($q) use ($result) {
                $q->where('result', $result);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(actor_email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(actor_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(action) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(target_identifier, \'\')) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(details, \'\')) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Administrative Activity');

        $columns = ['Actor Name', 'Actor Email', 'Action Performed', 'Target Resource', 'Device / Browser', 'IP Address', 'Result', 'Details', 'Date & Time'];

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
            $actorName = $log->actor ? ($log->actor->first_name . ' ' . $log->actor->last_name) : ($log->actor_name ?? 'System');
            $target = ($log->target_type ? $log->target_type . ': ' : '') . ($log->target_identifier ?? '-');

            $sheet->setCellValue('A' . $rowNum, $actorName);
            $sheet->setCellValue('B' . $rowNum, $log->actor_email ?? '-');
            $sheet->setCellValue('C' . $rowNum, $log->action ?? '-');
            $sheet->setCellValue('D' . $rowNum, $target);
            $sheet->setCellValue('E' . $rowNum, $log->formatted_device ?? '-');
            $sheet->setCellValue('F' . $rowNum, $log->ip_address ?? '-');
            $sheet->setCellValue('G' . $rowNum, ucfirst($log->result ?? 'logged'));
            $sheet->setCellValue('H' . $rowNum, $log->details ?? '-');
            $sheet->setCellValue('I' . $rowNum, $log->created_at ? Carbon::parse($log->created_at)->format('Y-m-d H:i:s') : '-');
            $rowNum++;
        }

        $filename = 'Administrative-Activity-Log-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.xlsx';

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
     * Download Filtered Administrative Activities as a PDF report.
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
        $result = $request->input('result', 'all');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $logs = AdminActivityLog::with('actor')
            ->whereBetween('created_at', [$start, $end])
            ->when($result !== 'all' && !empty($result), function ($q) use ($result) {
                $q->where('result', $result);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(actor_email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(actor_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(action) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(target_identifier, \'\')) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(details, \'\')) LIKE ?', ["%{$search}%"]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' – ' . Carbon::parse($endDate)->format('M d, Y');

        $pdf = $this->pdfWrapper->loadView('pdf.audit.recent-activity-report', compact('logs', 'dateRangeLabel'));
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Administrative-Activity-Log-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Apply date, search, and result filters.
     */
    private function applyFilters($query, string $dateFilter, ?string $customStart, ?string $customEnd, string $search, string $result)
    {
        $today = Carbon::today();

        match ($dateFilter) {
            'today' => $query->whereDate('created_at', $today),
            'yesterday' => $query->whereDate('created_at', $today->copy()->subDay()),
            'last_7_days' => $query->whereBetween('created_at', [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()]),
            'month' => $query->whereBetween('created_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
            'custom' => ($customStart && $customEnd) ? $query->whereBetween('created_at', [Carbon::parse($customStart)->startOfDay(), Carbon::parse($customEnd)->endOfDay()]) : $query,
            default => $query,
        };

        if ($result !== 'all' && !empty($result)) {
            $query->where('result', $result);
        }

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->whereRaw('LOWER(actor_email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(actor_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(action) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(COALESCE(target_identifier, \'\')) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(COALESCE(details, \'\')) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query;
    }
}
