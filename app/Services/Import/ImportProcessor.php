<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;
use App\Models\BulkImport;
use App\Models\BulkImportIssue;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\EnrollmentService;
use App\Services\StudentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

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
            // ── Student creation ─────────────────────────────────────────
            // createStudent() wraps everything in a DB transaction, so if it
            // throws no student row is left behind. Only these genuine
            // failures count against the import; the LRN stays free to be
            // re-uploaded in a fresh file.
            try {
                $student = $this->studentService->createStudent(
                    $row->toStudentData(),
                    $row->toGuardianData(),
                );
            } catch (\Throwable $e) {
                $failedCount++;

                $issueType = $this->classifyStudentError($e);
                $friendlyMessage = match ($issueType) {
                    'duplicate_lrn' => 'A student with this LRN already exists in the system. The row was skipped; remove duplicates from the file before re-importing.',
                    default => 'An unexpected system error occurred while processing this row. Please contact the system administrator.',
                };

                Log::error('Bulk import row processing failed', [
                    'import_id' => $import->id,
                    'row_number' => $row->rowNumber,
                    'issue_type' => $issueType,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->logIssue(
                    $import,
                    $row,
                    $issueType,
                    'error',
                    $friendlyMessage,
                );
                $this->updateProgress($import, $successCount, $failedCount);

                continue;
            }

            // The student was created successfully — count it as a success
            // and never downgrade it to "failed" because of secondary steps
            // (father/mother records, enrollment) below.
            $successCount++;

            // Create/update father & mother records (the primary 'guardian'
            // record is created by StudentService from toGuardianData()).
            // Secondary data: a failure here is a warning, not a row failure,
            // so it can't leave a "failed" student in the system that blocks
            // a later re-upload.
            try {
                $this->createParentGuardians($student, $row);
            } catch (\Throwable $e) {
                Log::warning('Bulk import parent guardian creation failed', [
                    'import_id' => $import->id,
                    'row_number' => $row->rowNumber,
                    'exception' => $e->getMessage(),
                ]);

                $this->logIssue(
                    $import,
                    $row,
                    'guardian_creation_failed',
                    'warning',
                    'The student was imported, but the father/mother guardian records could not be saved.',
                );
            }

            // ── Enrollment (if section is available) ─────────────────────
            $sectionId = $sectionIds[$row->rowNumber] ?? null;

            if ($sectionId !== null && $row->hasEnrollmentData()) {
                $enrollmentData = $row->toEnrollmentData();
                $enrollmentData['section_id'] = $sectionId;
                $enrollmentData['student_id'] = $student->id;

                try {
                    $this->enrollmentService->createEnrollment($enrollmentData, $schoolYearId);
                } catch (\Throwable $e) {
                    $issueType = $this->classifyEnrollmentError($e);
                    $friendlyMessage = match ($issueType) {
                        'section_inactive' => 'Enrollment skipped because the assigned section is no longer active.',
                        'section_full' => 'Enrollment skipped because the section has reached its maximum capacity.',
                        'already_enrolled' => 'Enrollment skipped because this student is already enrolled for the current school year.',
                        default => 'Enrollment could not be completed due to an unexpected error.',
                    };

                    Log::error('Bulk import enrollment failed', [
                        'import_id' => $import->id,
                        'row_number' => $row->rowNumber,
                        'issue_type' => $issueType,
                        'exception' => $e->getMessage(),
                    ]);

                    $this->logIssue($import, $row, $issueType, 'warning', $friendlyMessage);
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

            // Update progress after each row so the status endpoint
            // returns up-to-date numbers during processing.
            $this->updateProgress($import, $successCount, $failedCount);
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

    /**
     * Persist the running success/failed counters without touching the
     * import status, so the status endpoint shows live numbers while
     * processing is still running.
     */
    private function updateProgress(BulkImport $import, int $successCount, int $failedCount): void
    {
        $import->updateQuietly([
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
        ]);
    }

    /**
     * Create or update the father and mother Guardian records for a student.
     *
     * The primary guardian (from toGuardianData(), relationship 'guardian')
     * is created by StudentService. Father/mother come from the SF1 form and
     * are keyed on (student_id, relationship) so re-imports update instead
     * of duplicating. Contact number and email are shared and nullable-safe.
     */
    private function createParentGuardians(Student $student, ImportRowData $row): void
    {
        $guardians = [
            ['name' => $row->fatherName, 'relationship' => 'father'],
            ['name' => $row->motherMaidenName, 'relationship' => 'mother'],
        ];

        // The guardians table enforces UNIQUE (student_id, email), and the
        // primary guardian row (relationship 'guardian', created by
        // StudentService) already stores the shared contact email. Copying
        // that same email onto the father/mother rows raised a unique
        // violation (SQLSTATE 23505 / "student_guardian_email_unique") which
        // used to make an already-created student look like a failed row.
        // Attach the email only when it is not already used by this student.
        $usedEmails = Guardian::where('student_id', $student->id)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(static fn(string $email) => strtolower(trim($email)))
            ->flip();

        foreach ($guardians as $g) {
            $name = trim((string) $g['name']);

            if ($name === '') {
                continue;
            }

            $email = $row->guardianEmail;
            $emailKey = ($email !== null && trim($email) !== '')
                ? strtolower(trim($email))
                : null;

            Guardian::updateOrCreate(
                ['student_id' => $student->id, 'relationship' => $g['relationship']],
                [
                    'name'           => strip_tags($name),
                    'contact_number' => $row->guardianContactNumber,
                    'email'          => $emailKey !== null && isset($usedEmails[$emailKey])
                        ? null
                        : $email,
                ]
            );

            // Reserve the email so a second parent row (father + mother) in
            // the same student can't collide with the first one.
            if ($emailKey !== null) {
                $usedEmails[$emailKey] = true;
            }
        }
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
        if ($e instanceof QueryException) {
            // MySQL reports 23000; PostgreSQL reports 23505 for unique violations.
            $code = (string) $e->getCode();

            if (in_array($code, ['23000', '23505'], true)) {
                $prevMsg = strtolower((string) $e->getPrevious()?->getMessage());

                if (str_contains($prevMsg, 'duplicate') && str_contains($prevMsg, 'lrn')) {
                    return 'duplicate_lrn';
                }
            }
        }

        return 'system_error';
    }

    private function classifyEnrollmentError(\Throwable $e): string
    {
        return match (true) {
            str_contains($e->getMessage(), 'inactive')     => 'section_inactive',
            str_contains($e->getMessage(), 'capacity')     => 'section_full',
            str_contains($e->getMessage(), 'already enrolled') => 'already_enrolled',
            default                                        => 'enrollment_skipped',
        };
    }
}
