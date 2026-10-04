<?php

namespace App\Http\Controllers\SchoolAdmin\BulkImport;

use App\Enums\ImportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\ConfirmImportRequest;
use App\Http\Requests\SchoolAdmin\UploadImportRequest;
use App\Http\Resources\BulkImportResource;
use App\Models\BulkImport;
use App\Models\BulkImportIssue;
use App\Models\Student;
use App\Services\Import\SpreadsheetParser;
use App\Services\SchoolAdmin\BulkImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function detail(BulkImport $import): \Illuminate\View\View
    {
        $this->authorizeAccess($import);

        $import->load('createdBy')->loadCount('issues');

        return view('pov.school-admin.bulk-import.import-detail', compact('import'));
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

    // ── Rows (All records overview) ──────────────────────────────────────

    public function rows(Request $request, BulkImport $import): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAccess($import);

        $filter = $request->get('filter', ''); // '', 'clean', 'error', 'warning'
        $search = strtolower(trim((string) $request->get('search', '')));
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        $page = max((int) $request->get('page', 1), 1);

        $issues = $import->issues()->orderBy('row_number')->get();
        $issuesByRow = [];
        foreach ($issues as $issue) {
            $issuesByRow[$issue->row_number][] = $issue;
        }

        $allRows = [];

        $filePath = !empty($import->file_path) ? Storage::disk('local')->path($import->file_path) : null;
        if ($filePath && file_exists($filePath)) {
            try {
                $parsedRows = app(SpreadsheetParser::class)->parse($filePath);
                foreach ($parsedRows as $row) {
                    $rowNum = $row->rowNumber;
                    $rowIssues = $issuesByRow[$rowNum] ?? [];

                    $hasError = false;
                    $hasWarning = false;
                    $firstIssue = null;

                    foreach ($rowIssues as $iss) {
                        if ($iss->severity === 'error') {
                            $hasError = true;
                            $firstIssue = $iss;
                        } elseif ($iss->severity === 'warning' && !$hasError) {
                            $hasWarning = true;
                            $firstIssue = $iss;
                        }
                    }

                    $status = $hasError ? 'error' : ($hasWarning ? 'warning' : 'clean');
                    $severity = $status;
                    $issueType = $firstIssue ? $this->humanIssueType($firstIssue->issue_type) : null;

                    if ($hasError || $hasWarning) {
                        $message = $firstIssue->message;
                    } elseif ($import->status === ImportStatus::Completed || ($import->status?->value ?? $import->status) === 'completed') {
                        $message = 'Imported successfully and enrolled';
                    } else {
                        $message = 'Valid and ready for import';
                    }

                    $name = trim(($row->lastName ? $row->lastName . ', ' : '') . $row->firstName . ($row->middleName ? ' ' . $row->middleName : ''));
                    if (empty($name)) {
                        $name = $row->learnerName ?: 'Not recorded';
                    }

                    $grade = $row->gradeLevel;
                    $sec = $row->sectionName;
                    $gs = trim(($grade ? 'Grade ' . $grade : '') . ($grade && $sec ? ' — ' : '') . ($sec ?? ''));

                    $allRows[] = [
                        'row_number'      => $rowNum,
                        'student_name'    => $name,
                        'lrn'             => $row->lrn ?: '—',
                        'grade_section'   => $gs ?: '—',
                        'status'          => $status,
                        'severity'        => $severity,
                        'issue_type'      => $issueType,
                        'field'           => $firstIssue?->field,
                        'message'         => $message,
                        'issue_id'        => $firstIssue?->id,
                        'is_acknowledged' => $firstIssue?->status === 'acknowledged',
                    ];
                }
            } catch (\Throwable $e) {
                // If spreadsheet parsing fails, fallback below
            }
        }

        // Fallback if file does not exist or parsed 0 rows
        if (empty($allRows)) {
            foreach ($issuesByRow as $rowNum => $rowIssues) {
                $firstIssue = $rowIssues[0];
                $hasError = collect($rowIssues)->contains('severity', 'error');
                $hasWarning = collect($rowIssues)->contains('severity', 'warning');
                $status = $hasError ? 'error' : ($hasWarning ? 'warning' : 'clean');

                $raw = $firstIssue->raw_data;
                if (is_string($raw)) {
                    $raw = json_decode($raw, true) ?: [];
                }

                $last = $raw['Last Name'] ?? $raw['lastName'] ?? $raw['last_name'] ?? '';
                $first = $raw['First Name'] ?? $raw['firstName'] ?? $raw['first_name'] ?? '';
                $name = $last && $first ? "{$last}, {$first}" : ($raw['Learner Name'] ?? $raw['learnerName'] ?? '');
                $lrnVal = $raw['LRN'] ?? $raw['Learner Reference Number'] ?? $raw['lrn'] ?? '—';
                $grade = $raw['Grade Level'] ?? $raw['gradeLevel'] ?? '';
                $sec = $raw['Section'] ?? $raw['sectionName'] ?? '';
                $gs = trim(($grade ? 'Grade ' . $grade : '') . ($grade && $sec ? ' — ' : '') . $sec);

                $allRows[] = [
                    'row_number'      => $rowNum,
                    'student_name'    => $name ?: 'Not recorded',
                    'lrn'             => $lrnVal,
                    'grade_section'   => $gs ?: '—',
                    'status'          => $status,
                    'severity'        => $status,
                    'issue_type'      => $this->humanIssueType($firstIssue->issue_type),
                    'field'           => $firstIssue->field,
                    'message'         => $firstIssue->message,
                    'issue_id'        => $firstIssue->id,
                    'is_acknowledged' => $firstIssue->status === 'acknowledged',
                ];
            }

            // If import completed and has success_count, load imported students as clean rows
            $statusVal = $import->status?->value ?? $import->status;
            if ($statusVal === 'completed' && $import->success_count > 0) {
                $startTime = $import->created_at ? $import->created_at->subMinutes(5) : now()->subHours(24);
                $endTime   = $import->updated_at ? $import->updated_at->addMinutes(5) : now();

                $students = Student::with('enrollments.section')
                    ->whereBetween('created_at', [$startTime, $endTime])
                    ->orderBy('id')
                    ->take($import->success_count)
                    ->get();

                $existingRowNums = collect($allRows)->pluck('row_number')->all();
                $nextRowNum = 1;

                foreach ($students as $stu) {
                    while (in_array($nextRowNum, $existingRowNums, true)) {
                        $nextRowNum++;
                    }
                    $existingRowNums[] = $nextRowNum;

                    $sec = $stu->enrollments->first()?->section;
                    $gs = $sec ? "Grade {$sec->grade_level} — {$sec->name}" : '—';
                    $name = trim($stu->last_name . ', ' . $stu->first_name . ($stu->middle_name ? ' ' . $stu->middle_name : ''));

                    $allRows[] = [
                        'row_number'      => $nextRowNum,
                        'student_name'    => $name ?: 'Not recorded',
                        'lrn'             => $stu->lrn,
                        'grade_section'   => $gs,
                        'status'          => 'clean',
                        'severity'        => 'clean',
                        'issue_type'      => null,
                        'field'           => null,
                        'message'         => 'Imported successfully and enrolled',
                        'issue_id'        => null,
                        'is_acknowledged' => false,
                    ];
                    $nextRowNum++;
                }
            }
        }

        usort($allRows, fn($a, $b) => $a['row_number'] <=> $b['row_number']);

        $totalAll = count($allRows);
        $totalClean = count(array_filter($allRows, fn($r) => $r['status'] === 'clean'));
        $totalErrors = count(array_filter($allRows, fn($r) => $r['status'] === 'error'));
        $totalWarnings = count(array_filter($allRows, fn($r) => $r['status'] === 'warning'));

        $filtered = $allRows;
        if ($filter === 'error') {
            $filtered = array_values(array_filter($filtered, fn($r) => $r['status'] === 'error'));
        } elseif ($filter === 'warning') {
            $filtered = array_values(array_filter($filtered, fn($r) => $r['status'] === 'warning'));
        } elseif ($filter === 'clean') {
            $filtered = array_values(array_filter($filtered, fn($r) => $r['status'] === 'clean'));
        }

        if ($search !== '') {
            $filtered = array_values(array_filter($filtered, function ($r) use ($search) {
                return str_contains(strtolower($r['student_name'] ?? ''), $search)
                    || str_contains(strtolower($r['lrn'] ?? ''), $search)
                    || str_contains(strtolower($r['grade_section'] ?? ''), $search)
                    || str_contains(strtolower($r['message'] ?? ''), $search)
                    || str_contains((string) $r['row_number'], $search);
            }));
        }

        $totalFiltered = count($filtered);
        $lastPage = max((int) ceil($totalFiltered / $perPage), 1);
        $offset = ($page - 1) * $perPage;
        $pagedRows = array_slice($filtered, $offset, $perPage);

        return response()->json([
            'data' => $pagedRows,
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $totalFiltered,
                'counts'       => [
                    'all'     => $totalAll,
                    'clean'   => $totalClean,
                    'error'   => $totalErrors,
                    'warning' => $totalWarnings,
                ],
            ],
        ]);
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
            $fallbackPath = resource_path('templates/' . basename(config('import.sf1_template_path')));
            if (file_exists($fallbackPath)) {
                $path = $fallbackPath;
            } else {
                abort(404, 'SF1 template file not found.');
            }
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

    // ── Helpers & Authorization ──────────────────────────────────────────

    private function humanIssueType(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $labels = [
            'duplicate_lrn'     => 'Duplicate LRN',
            'duplicate_student' => 'Duplicate Student',
            'missing_field'     => 'Missing Required Field',
            'invalid_lrn'       => 'Invalid LRN Format',
            'invalid_email'     => 'Invalid Email Address',
            'section_not_found' => 'Section Not Found',
            'invalid_grade'     => 'Invalid Grade Level',
            'invalid_format'    => 'Invalid Data Format',
            'missing_lrn'       => 'Missing LRN',
            'missing_name'      => 'Missing Student Name',
            'lrn_exists'        => 'LRN Already Registered',
            'already_enrolled'  => 'Student Already Enrolled',
        ];

        return $labels[$raw] ?? ucwords(str_replace('_', ' ', $raw));
    }

    /**
     * Ensure the authenticated user has access to this import.
     */
    private function authorizeAccess(BulkImport $import): void
    {
        $user = request()->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
            return;
        }

        if ($import->created_by !== $user?->id) {
            abort(403, 'You do not have access to this import.');
        }
    }
}
