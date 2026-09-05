<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;

/**
 * Validates a single ImportRowData DTO against the business rules.
 *
 * This validator receives ALREADY-NORMALIZED values from SpreadsheetParser.
 * It does NOT re-normalize or query the database.
 *
 * Each call to validate() is stateless and independent — one row at a time.
 * Intra-file duplicate detection and DB existence checks belong to
 * later orchestration phases.
 *
 * @see docs/bulk-import/03_business_rules.md  §7 Validation Rules
 */
class ImportRowValidator
{
    /** @var array<string, list<int>> Map: departmentLevel → valid gradeLevel values */
    private const GRADE_RANGES = [
        'elementary'          => [1, 2, 3, 4, 5, 6],
        'highschool'          => [7, 8, 9, 10],
        'senior_high_school'  => [11, 12],
    ];

    /**
     * Validate a single row of normalized import data.
     *
     * @return list<array{issue_type: string, severity: string, field: string, message: string}>
     *         Empty list means the row is valid.
     */
    public function validate(ImportRowData $row): array
    {
        $issues = [];

        // ── LRN ──────────────────────────────────────────────────────────
        if ($this->isEmpty($row->lrn)) {
            $issues[] = $this->error('lrn', 'LRN is required.');
        } elseif (!preg_match('/^\d{12}$/', $row->lrn)) {
            $issues[] = $this->error(
                'lrn',
                'LRN must be exactly 12 digits. If Excel removed leading zeros, format the LRN column as Text.'
            );
        }

        // ── Learner Name ─────────────────────────────────────────────────
        if ($this->isEmpty($row->learnerName)) {
            $issues[] = $this->error('learnerName', 'Learner Name is required.');
        } elseif ($this->isEmpty($row->lastName) || $this->isEmpty($row->firstName)) {
            $issues[] = $this->error(
                'learnerName',
                'Learner Name must be in the format "LAST NAME, FIRST NAME MIDDLE NAME". Example: "Santos, Juan Dela Cruz".'
            );
        }

        // ── Sex ──────────────────────────────────────────────────────────
        if ($this->isEmpty($row->sex)) {
            $issues[] = $this->error('sex', 'Sex is required. Accepted values: male, female.');
        } elseif (!in_array($row->sex, ['male', 'female'], true)) {
            $issues[] = $this->error('sex', 'Sex must be "male" or "female" (use M or F in the Sex column).');
        }

        // ── Age (optional; must be a whole number 1–100 if present) ─────
        if (!$this->isEmpty($row->age)) {
            if (!preg_match('/^\d+$/', $row->age)) {
                $issues[] = $this->error('age', 'Age must be a whole number.');
            } else {
                $age = (int) $row->age;

                if ($age < 1 || $age > 100) {
                    $issues[] = $this->error('age', 'Age must be between 1 and 100.');
                }
            }
        }

        // ── Parents / Guardian (soft warning, not a hard error) ─────────
        if (
            $this->isEmpty($row->fatherName)
            && $this->isEmpty($row->motherMaidenName)
            && $this->isEmpty($row->guardianName)
        ) {
            $issues[] = $this->warning(
                'guardian',
                'No father, mother, or guardian name provided. At least one parent/guardian is recommended.',
                'no_guardian',
            );
        }

        // ── Department Level ─────────────────────────────────────────────
        if ($this->isEmpty($row->departmentLevel)) {
            $issues[] = $this->error('departmentLevel', 'Department Level is required.');
        } elseif (!array_key_exists($row->departmentLevel, self::GRADE_RANGES)) {
            $issues[] = $this->error(
                'departmentLevel',
                'Department Level must be one of: elementary, highschool, senior_high_school.'
            );
        }

        // ── Grade Level (+ cross-field check) ────────────────────────────
        $gradeLevelIssues = $this->validateGradeLevel($row->gradeLevel, $row->departmentLevel);
        array_push($issues, ...$gradeLevelIssues);

        // ── Section ──────────────────────────────────────────────────────
        if ($this->isEmpty($row->sectionName)) {
            $issues[] = $this->error('sectionName', 'Section is required.');
        }

        return $issues;
    }

    // ── Private helpers ──────────────────────────────────────────────────

    /**
     * Validate grade level both as a standalone field and against the
     * department level.
     *
     * @return list<array{issue_type: string, severity: string, field: string, message: string}>
     */
    private function validateGradeLevel(?string $gradeLevel, ?string $departmentLevel): array
    {
        $issues = [];

        if ($this->isEmpty($gradeLevel)) {
            $issues[] = $this->error('gradeLevel', 'Grade Level is required.');
            return $issues; // Can't cross-validate without a value
        }

        if (!preg_match('/^\d+$/', $gradeLevel)) {
            $issues[] = $this->error('gradeLevel', 'Grade Level must be a number (1–12).');
            return $issues;
        }

        $levelInt = (int) $gradeLevel;

        if ($levelInt < 1 || $levelInt > 12) {
            $issues[] = $this->error('gradeLevel', 'Grade Level must be between 1 and 12.');
            return $issues;
        }

        // Cross-field: gradeLevel must be valid for the departmentLevel
        if ($departmentLevel !== null && array_key_exists($departmentLevel, self::GRADE_RANGES)) {
            $validGrades = self::GRADE_RANGES[$departmentLevel];

            if (!in_array($levelInt, $validGrades, true)) {
                $rangeDescriptions = [
                    'elementary'         => '1–6',
                    'highschool'         => '7–10',
                    'senior_high_school' => '11–12',
                ];

                $issues[] = $this->error(
                    'gradeLevel',
                    sprintf(
                        'Grade Level %d is not valid for %s. Expected grades: %s.',
                        $levelInt,
                        ucwords(str_replace('_', ' ', $departmentLevel)),
                        $rangeDescriptions[$departmentLevel]
                    )
                );
            }
        }

        return $issues;
    }

    private function isEmpty(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }

    /**
     * @return array{issue_type: string, severity: string, field: string, message: string}
     */
    private function error(string $field, string $message): array
    {
        return [
            'issue_type' => 'validation_error',
            'severity'   => 'error',
            'field'      => $field,
            'message'    => $message,
        ];
    }

    /**
     * Soft (non-blocking) warning — the row can still be imported.
     *
     * @return array{issue_type: string, severity: string, field: string, message: string}
     */
    private function warning(string $field, string $message, string $issueType): array
    {
        return [
            'issue_type' => $issueType,
            'severity'   => 'warning',
            'field'      => $field,
            'message'    => $message,
        ];
    }
}
