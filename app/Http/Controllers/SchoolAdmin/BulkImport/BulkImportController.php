<?php

namespace App\Http\Controllers\SchoolAdmin\BulkImport;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\ConfirmImportRequest;
use App\Http\Requests\SchoolAdmin\UploadImportRequest;
use App\Http\Resources\BulkImportResource;
use App\Models\BulkImport;
use App\Models\BulkImportIssue;
use App\Services\SchoolAdmin\BulkImportService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BulkImportController extends Controller
{
    public function __construct(
        private readonly BulkImportService $bulkImportService,
    ) {}

    // ── Upload ──────────────────────────────────────────────────────────

    public function upload(UploadImportRequest $request): BulkImportResource
    {
        $import = $this->bulkImportService->upload(
            $request->file('file'),
            $request->user(),
        );

        return new BulkImportResource($import);
    }

    // ── Show / Status ───────────────────────────────────────────────────

    public function show(BulkImport $import): BulkImportResource
    {
        $this->authorizeAccess($import);

        $import->loadCount('issues');

        return new BulkImportResource($import);
    }

    public function status(BulkImport $import): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAccess($import);

        $total = max($import->total_rows, 1);

        return response()->json([
            'status'              => $import->status?->value ?? $import->status,
            'total_rows'          => $import->total_rows,
            'processed_rows'      => $import->success_count + $import->failed_count,
            'success_count'       => $import->success_count,
            'failed_count'        => $import->failed_count,
            'progress_percentage' => round((($import->success_count + $import->failed_count) / $total) * 100, 1),
        ]);
    }

    // ── Active import (state restoration) ───────────────────────────────

    public function active(Request $request): \Illuminate\Http\JsonResponse
    {
        $import = BulkImport::where('created_by', $request->user()->id)
            ->whereNotIn('status', ['completed', 'completed_with_issues', 'cancelled', 'failed'])
            ->latest()
            ->first();

        if (!$import) {
            return response()->json(['data' => null]);
        }

        $import->loadCount('issues');

        return response()->json(['data' => new BulkImportResource($import)]);
    }

    // ── Validate ────────────────────────────────────────────────────────

    public function validate(BulkImport $import): BulkImportResource
    {
        $this->authorizeAccess($import);

        $import = $this->bulkImportService->validate($import);

        return new BulkImportResource($import);
    }

    // ── Replace file ────────────────────────────────────────────────────

    public function replaceFile(UploadImportRequest $request, BulkImport $import): BulkImportResource
    {
        $this->authorizeAccess($import);

        $allowedStates = ['pending', 'validated', 'completed_with_issues'];

        if (!in_array($import->status?->value ?? $import->status, $allowedStates, true)) {
            abort(422, 'File can only be replaced in pending, validated, or completed with issues state.');
        }

        $import = $this->bulkImportService->replaceFile(
            $request->file('file'),
            $import,
        );

        return new BulkImportResource($import);
    }

    // ── Confirm ─────────────────────────────────────────────────────────

    public function confirm(ConfirmImportRequest $request, BulkImport $import): BulkImportResource
    {
        $this->authorizeAccess($import);

        $import = $this->bulkImportService->confirm($import);

        $import->loadCount('issues');

        return new BulkImportResource($import);
    }

    // ── Cancel ──────────────────────────────────────────────────────────

    public function cancel(BulkImport $import): BulkImportResource
    {
        $this->authorizeAccess($import);

        $this->bulkImportService->cancel($import);

        return new BulkImportResource($import->fresh());
    }

    // ── Issues ──────────────────────────────────────────────────────────

    public function issues(Request $request, BulkImport $import)
    {
        $this->authorizeAccess($import);

        $query = $import->issues()->orderBy('row_number');

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $issues  = $query->paginate($perPage);

        return response()->json($issues);
    }

    public function acknowledge(BulkImportIssue $issue): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAccess($issue->bulkImport);

        $issue->acknowledge();

        return response()->json([
            'message' => 'Issue acknowledged successfully.',
            'data'    => $issue->fresh(),
        ]);
    }

    public function acknowledgeAll(BulkImport $import): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAccess($import);

        $count = $import->issues()
            ->where('status', 'unresolved')
            ->update([
                'status'      => 'acknowledged',
                'resolved_at' => now(),
            ]);

        return response()->json([
            'message' => "{$count} issue(s) acknowledged successfully.",
            'count'   => $count,
        ]);
    }

    // ── History ─────────────────────────────────────────────────────────

    public function history(Request $request)
    {
        $query = BulkImport::with('createdBy')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $imports = $query->paginate($perPage);

        return response()->json([
            'data' => BulkImportResource::collection($imports),
            'meta' => [
                'current_page' => $imports->currentPage(),
                'last_page'    => $imports->lastPage(),
                'per_page'     => $imports->perPage(),
                'total'        => $imports->total(),
            ],
        ]);
    }

    // ── Template download ───────────────────────────────────────────────

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = storage_path(config('import.sf1_template_path'));

        if (!file_exists($path)) {
            abort(404, 'SF1 template file not found.');
        }

        return response()->download($path, 'School-Forms-1-Template-File.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── Export errors ───────────────────────────────────────────────────

    public function exportErrors(BulkImport $import): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeAccess($import);

        $issues = $import->issues()->orderBy('row_number')->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $columns = ['Row', 'Issue Type', 'Severity', 'Field', 'Message', 'Status'];

        foreach ($columns as $colIndex => $header) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $rowNum = 2;

        foreach ($issues as $issue) {
            $sheet->setCellValue('A' . $rowNum, $issue->row_number);
            $sheet->setCellValue('B' . $rowNum, $issue->issue_type);
            $sheet->setCellValue('C' . $rowNum, $issue->severity);
            $sheet->setCellValue('D' . $rowNum, $issue->field ?? '');
            $sheet->setCellValue('E' . $rowNum, $issue->message);
            $sheet->setCellValue('F' . $rowNum, $issue->status);
            $rowNum++;
        }

        $headerRange = 'A1:' . Coordinate::stringFromColumnIndex(count($columns)) . '1';
        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E8E8E8');

        $tempPath = tempnam(sys_get_temp_dir(), 'import_errors_') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $filename = 'import-errors-' . $import->id . '-' . now()->format('Ymd') . '.xlsx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ── Authorization ───────────────────────────────────────────────────

    /**
     * Ensure the authenticated user owns this import.
     * Prevents one admin from modifying another admin's import.
     */
    private function authorizeAccess(BulkImport $import): void
    {
        if ($import->created_by !== request()->user()->id) {
            abort(403, 'You do not have access to this import.');
        }
    }
}
