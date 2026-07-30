<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;
use App\Models\BulkImport;
use App\Models\BulkImportIssue;
use App\Services\EnrollmentService;
use App\Services\StudentService;
use Illuminate\Database\QueryException;

/**
 * Processes validated rows one by one through existing services.
 *
 * Responsibilities:
 *  - Call StudentService::createStudent() for each row
 *  - Call EnrollmentService::createEnrollment() when section is resolved
 *  - Log per-row issues (error for student failures, warning for enrollment)
 *  - Update import progress counters
 *
 * @see docs/bulk-import/03_business_rules.md  §8 Processing Rules
 * @see docs/bulk-import/sequence-diagram.txt
 */
class ImportProcessor
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly EnrollmentService $enrollmentService,
    ) {}

    /**
     * Process a batch of validated rows.
     *
     * @param  BulkImport       $import         The import session (status will be updated in-place)
     * @param  ImportRowData[]  $validRows      Rows that passed validation
     * @param  array<int, int|null> $sectionIds Map of rowNumber → section_id (null = no enrollment)
     * @param  int              $schoolYearId   Active school year ID
     */
    public function process(
        BulkImport $import,
        array $validRows,
        array $sectionIds,
        int $schoolYearId,
    ): void {
        $import->update(['status' => 'processing']);
        $import->refresh();

        $successCount = 0;
        $failedCount  = 0;

        foreach ($validRows as $row) {
            try {
                // ── Student creation ─────────────────────────────────────
                $student = $this->studentService->createStudent(
                    $row->toStudentData(),
                    $row->toGuardianData(),
                );

                $successCount++;

                // ── Enrollment (if section is available) ─────────────────
                $sectionId = $sectionIds[$row->rowNumber] ?? null;

                if ($sectionId !== null && $row->hasEnrollmentData()) {
                    $enrollmentData = $row->toEnrollmentData();
                    $enrollmentData['section_id'] = $sectionId;
                    $enrollmentData['student_id'] = $student->id;

                    try {
                        $this->enrollmentService->createEnrollment($enrollmentData, $schoolYearId);
                    } catch (\RuntimeException $e) {
                        $this->logIssue($import, $row, $this->classifyEnrollmentError($e), 'warning', $e->getMessage());
                    }
                } elseif ($sectionId === null && $row->hasEnrollmentData()) {
                    // Only log if no section_not_found issue already exists for this row
                    // (validation may have already created one)
                    $alreadyLogged = $import->issues()
                        ->where('row_number', $row->rowNumber)
                        ->where('issue_type', 'section_not_found')
                        ->exists();

                    if (!$alreadyLogged) {
                        $this->logIssue(
                            $import,
                            $row,
                            'section_not_found',
                            'warning',
                            "Enrollment skipped because section \"{$row->sectionName}\" could not be found " .
                                "for {$row->departmentLevel} grade {$row->gradeLevel}.",
                        );
                    }
                }
            } catch (\Throwable $e) {
                $failedCount++;

                $this->logIssue(
                    $import,
                    $row,
                    $this->classifyStudentError($e),
                    'error',
                    $e->getMessage(),
                );
            }

            // Update progress after each row so the status endpoint
            // returns up-to-date numbers during processing.
            $import->updateQuietly([
                'success_count'  => $successCount,
                'failed_count'   => $failedCount,
            ]);
        }

        // Final status
        $hasIssues = $import->issues()->where('severity', 'error')->exists();
        $finalStatus = $failedCount > 0 || $hasIssues ? 'completed_with_issues' : 'completed';

        $import->update([
            'status'        => $finalStatus,
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
        ]);
    }

    // ── Issue helpers ───────────────────────────────────────────────────

    private function logIssue(BulkImport $import, ImportRowData $row, string $issueType, string $severity, string $message): void
    {
        BulkImportIssue::create([
            'bulk_import_id' => $import->id,
            'row_number'     => $row->rowNumber,
            'issue_type'     => $issueType,
            'severity'       => $severity,
            'field'          => null,
            'message'        => $message,
            'raw_data'       => $row->toRawData(),
            'status'         => 'unresolved',
        ]);
    }

    private function classifyStudentError(\Throwable $e): string
    {
        if ($e instanceof QueryException && $e->getCode() == 23000) {
            $prevMsg = strtolower((string) $e->getPrevious()?->getMessage());

            if (str_contains($prevMsg, 'duplicate') && str_contains($prevMsg, 'lrn')) {
                return 'duplicate_lrn';
            }
        }

        return 'system_error';
    }

    private function classifyEnrollmentError(\RuntimeException $e): string
    {
        return match (true) {
            str_contains($e->getMessage(), 'inactive')     => 'section_inactive',
            str_contains($e->getMessage(), 'capacity')     => 'section_full',
            str_contains($e->getMessage(), 'already enrolled') => 'already_enrolled',
            default                                        => 'enrollment_skipped',
        };
    }
}
