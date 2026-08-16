<?php

namespace App\Services\Import;

use App\Models\BulkImport;
use App\Models\BulkImportIssue;
use App\Models\SchoolYear;
use App\Models\User;
use App\Enums\ImportStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Orchestrates the full Bulk Import lifecycle.
 *
 * Responsibilities (per docs/bulk-import/05_system_architecture.md):
 *  - Upload file → create session
 *  - Validate rows (parser → validator → section resolver → issues)
 *  - Confirm (delegate to ImportProcessor)
 *  - Cancel
 *
 * @see docs/bulk-import/sequence-diagram.txt
 */
class BulkImportService
{
    public function __construct(
        private readonly SpreadsheetParser $parser,
        private readonly ImportRowValidator $validator,
        private readonly SectionResolver $sectionResolver,
        private readonly ImportProcessor $processor,
    ) {}

    // -----------------------------------------------------------------
    //  Upload
    // -----------------------------------------------------------------

    /**
     * Store the uploaded file and create a Pending import session.
     */
    public function upload(UploadedFile $file, User $user): BulkImport
    {
        $hash = hash_file('sha256', $file->getPathname());
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename, 'local');

        if (!$path) {
            throw new \RuntimeException('Failed to store uploaded file.');
        }

        $import = BulkImport::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path'         => $path,
            'file_hash'         => $hash,
            'status'            => 'pending',
            'total_rows'        => 0,
            'valid_count'       => 0,
            'error_count'       => 0,
            'warning_count'     => 0,
            'success_count'     => 0,
            'failed_count'      => 0,
            'created_by'        => $user->id,
        ]);

        return $import;
    }

    // -----------------------------------------------------------------
    //  Replace file
    // -----------------------------------------------------------------

    /**
     * Replace the file for an existing import and reset its state.
     */
    public function replaceFile(UploadedFile $file, BulkImport $import): BulkImport
    {
        // Delete old file
        Storage::disk('local')->delete($import->file_path);

        $hash     = hash_file('sha256', $file->getPathname());
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('imports', $filename, 'local');

        if (!$path) {
            throw new \RuntimeException('Failed to store uploaded file.');
        }

        // Clear previous issues and reset counters
        $import->issues()->delete();

        $import->update([
            'original_filename' => $file->getClientOriginalName(),
            'file_path'         => $path,
            'file_hash'         => $hash,
            'status'            => 'pending',
            'total_rows'        => 0,
            'valid_count'       => 0,
            'error_count'       => 0,
            'warning_count'     => 0,
            'success_count'     => 0,
            'failed_count'      => 0,
        ]);

        return $import->fresh();
    }

    // -----------------------------------------------------------------
    //  Validate
    // -----------------------------------------------------------------

    /**
     * Parse the spreadsheet, validate every row, resolve sections, and
     * persist all issues. Returns the updated import with counters set.
     */
    public function validate(BulkImport $import): BulkImport
    {
        $filePath = Storage::disk('local')->path($import->file_path);

        if (!file_exists($filePath)) {
            $import->update(['status' => 'failed']);
            throw new \RuntimeException('Import file not found. It may have been deleted.');
        }

        // ── Parse ───────────────────────────────────────────────────────
        try {
            $allRows = $this->parser->parse($filePath);
        } catch (MissingFormHeaderException $e) {
            // File-level SF1 header problem: record ONE blocking issue and
            // skip per-row processing (every row would fail identically).
            BulkImportIssue::create([
                'bulk_import_id' => $import->id,
                'row_number'     => null,
                'issue_type'     => 'missing_form_header',
                'severity'       => 'error',
                'field'          => 'form_header',
                'message'        => $e->getMessage(),
                'raw_data'       => null,
                'status'         => 'unresolved',
            ]);

            $import->update([
                'status'        => 'validated',
                'total_rows'    => 0,
                'valid_count'   => 0,
                'error_count'   => 1,
                'warning_count' => 0,
                'success_count' => 0,
                'failed_count'  => 0,
            ]);

            return $import->fresh();
        }

        $import->update(['total_rows' => count($allRows)]);

        // Pre-load sections once before the row loop
        $this->sectionResolver->loadActiveSections();

        // ── Validate + resolve sections + collect issues ─────────────────
        $validRows = [];
        $sectionIds = [];
        $seenLrns = [];
        $persistIssues = [];
        $errorCount = 0;
        $warningCount = 0;

        foreach ($allRows as $row) {
            $rowIssues = $this->validator->validate($row);

            // Intra-file LRN duplicate check (BR-020)
            if ($row->lrn !== null) {
                $lrnKey = strtolower(trim($row->lrn));

                if (isset($seenLrns[$lrnKey])) {
                    $rowIssues[] = [
                        'issue_type' => 'duplicate_lrn',
                        'severity'   => 'error',
                        'field'      => 'lrn',
                        'message'    => "Duplicate LRN within the import file (first occurrence at row {$seenLrns[$lrnKey]}).",
                    ];
                } else {
                    $seenLrns[$lrnKey] = $row->rowNumber;
                }
            }

            // SF1: both Sex marker columns (G and H) are marked → flag for review
            if ($row->sexAmbiguous) {
                $rowIssues[] = [
                    'issue_type' => 'sex_column_ambiguous',
                    'severity'   => 'warning',
                    'field'      => 'sex',
                    'message'    => 'Both Sex columns (G and H) are marked. Defaulted to male. Please verify against the source form.',
                ];
            }

            // Errors block the row; warnings are persisted but do NOT block it
            $hasErrors = false;

            foreach ($rowIssues as $issue) {
                if (($issue['severity'] ?? 'error') === 'error') {
                    $hasErrors = true;
                    break;
                }
            }

            if (!$hasErrors) {
                // Row is importable — resolve section
                $validRows[] = $row;

                if ($row->hasEnrollmentData()) {
                    $section = $this->sectionResolver->resolveSection(
                        $row->sectionName,
                        $row->departmentLevel,
                        $row->gradeLevel,
                    );

                    if ($section === null) {
                        $sectionIds[$row->rowNumber] = null;

                        $persistIssues[] = [
                            'bulk_import_id' => $import->id,
                            'row_number'     => $row->rowNumber,
                            'issue_type'     => 'section_not_found',
                            'severity'       => 'warning',
                            'field'          => 'sectionName',
                            'message'        => "Section \"{$row->sectionName}\" not found or inactive for {$row->departmentLevel} grade {$row->gradeLevel}.",
                            'raw_data'       => json_encode($row->toRawData()),
                            'status'         => 'unresolved',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];
                        $warningCount++;
                    } else {
                        $sectionIds[$row->rowNumber] = $section->id;
                    }
                } else {
                    $sectionIds[$row->rowNumber] = null;
                }
            } else {
                $sectionIds[$row->rowNumber] = null;
            }

            // Persist ALL issues for this row (warnings on valid rows,
            // errors + warnings on invalid rows)
            foreach ($rowIssues as $issue) {
                $persistIssues[] = [
                    'bulk_import_id' => $import->id,
                    'row_number'     => $row->rowNumber,
                    'issue_type'     => $issue['issue_type'],
                    'severity'       => $issue['severity'],
                    'field'          => $issue['field'] ?? null,
                    'message'        => $issue['message'],
                    'raw_data'       => json_encode($row->toRawData()),
                    'status'         => 'unresolved',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                if (($issue['severity'] ?? 'error') === 'error') {
                    $errorCount++;
                } else {
                    $warningCount++;
                }
            }
        }

        // ── Batch insert all issues ─────────────────────────────────────
        if (!empty($persistIssues)) {
            BulkImportIssue::insert($persistIssues);
        }

        // ── Update import counters ──────────────────────────────────────
        $validCount = count($validRows);

        $import->update([
            'status'        => 'validated',
            'total_rows'    => count($allRows),
            'valid_count'   => $validCount,
            'error_count'   => $errorCount,
            'warning_count' => $warningCount,
        ]);

        return $import->fresh();
    }

    // -----------------------------------------------------------------
    //  Confirm (start processing)
    // -----------------------------------------------------------------

    /**
     * Begin processing validated rows through ImportProcessor.
     *
     * @throws \RuntimeException  If the import cannot proceed
     */
    public function confirm(BulkImport $import): BulkImport
    {
        if ($import->status !== ImportStatus::Validated) {
            throw new \RuntimeException('Import must be in "validated" status before confirmation.');
        }

        $schoolYear = SchoolYear::query()->where('is_active', true)->first();

        if (!$schoolYear) {
            $import->update(['status' => 'failed']);
            throw new \RuntimeException('No active school year exists. Cannot process import.');
        }

        // Pre-load sections once so resolveSection() doesn't lazy-load per row
        $this->sectionResolver->loadActiveSections($schoolYear->id);

        // Re-parse and re-validate to get the current set of valid rows
        $filePath = Storage::disk('local')->path($import->file_path);

        if (!file_exists($filePath)) {
            $import->update(['status' => 'failed']);
            throw new \RuntimeException('Import file not found.');
        }

        $allRows = $this->parser->parse($filePath);
        $validRows = [];
        $sectionIds = [];

        foreach ($allRows as $row) {
            $rowIssues = $this->validator->validate($row);

            // Warnings (no_guardian, sex_column_ambiguous) do not block a
            // row — only hard errors exclude it from processing.
            $hasErrors = false;

            foreach ($rowIssues as $issue) {
                if (($issue['severity'] ?? 'error') === 'error') {
                    $hasErrors = true;
                    break;
                }
            }

            if (!$hasErrors) {
                $validRows[] = $row;

                if ($row->hasEnrollmentData()) {
                    $section = $this->sectionResolver->resolveSection(
                        $row->sectionName,
                        $row->departmentLevel,
                        $row->gradeLevel,
                    );
                    $sectionIds[$row->rowNumber] = $section?->id;
                } else {
                    $sectionIds[$row->rowNumber] = null;
                }
            }
        }

        if (empty($validRows)) {
            $import->update(['status' => 'failed']);
            throw new \RuntimeException('No valid rows to import after re-validation.');
        }

        try {
            $this->processor->process($import, $validRows, $sectionIds, $schoolYear->id);
        } catch (\Throwable $e) {
            Log::error('Bulk import processing failed', [
                'import_id' => $import->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $import->update(['status' => 'failed']);
            throw new \RuntimeException('Import processing failed. Please check the import issues for details.');
        }

        return $import->fresh();
    }

    // -----------------------------------------------------------------
    //  Cancel
    // -----------------------------------------------------------------

    /**
     * Cancel a pending or validated import.
     */
    public function cancel(BulkImport $import): void
    {
        if (!$import->isCancellable()) {
            throw new \RuntimeException('This import cannot be cancelled in its current state.');
        }

        $import->update(['status' => 'cancelled']);
    }
}
