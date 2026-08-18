<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reads an official DepEd School Form 1 (SF1) — School Register — and
 * converts every data row into a normalized ImportRowData DTO.
 *
 * Responsibilities:
 *  - Validate the expected SF1 sheet name and the A1 anchor text
 *  - Read rows from the fixed SF1 column layout (data starts at row 10)
 *  - Stop at the footer landmark ("List and Code of Indicators") instead
 *    of a hardcoded end row
 *  - Skip unfilled slots (blank Name in column C)
 *  - Read form-level Grade Level (S6) / Section (W6) and apply them to
 *    every row; derive the department level from the grade level
 *  - Concatenate the 4 address sub-cells into one address string
 *  - Resolve Sex from the single Sex (M/F) column (G) (see resolveSex())
 *
 * This service performs NO validation — it normalizes and preserves.
 * Invalid values are passed through as-is so ImportRowValidator can flag them.
 */
class SpreadsheetParser
{
    private const SF1_SHEET_NAME = 'School Form 1 (SF1)';
    private const SF1_A1_ANCHOR = 'School Form 1';
    private const SF1_FOOTER_LANDMARK = 'List and Code of Indicators';

    private const DATA_START_ROW = 10;
    private const MAX_ROW_CEILING = 1000;

    /** @var array<int, string> Map: grade level → department level */
    private const GRADE_TO_DEPARTMENT = [
        1 => 'elementary',
        2 => 'elementary',
        3 => 'elementary',
        4 => 'elementary',
        5 => 'elementary',
        6 => 'elementary',
        7 => 'highschool',
        8 => 'highschool',
        9 => 'highschool',
        10 => 'highschool',
        11 => 'senior_high_school',
        12 => 'senior_high_school',
    ];

    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Import file not found: ' . $filePath);
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet   = $this->findSf1Sheet($spreadsheet);

        if ($worksheet === null) {
            throw new \RuntimeException(
                'The workbook does not contain the "School Form 1 (SF1)" sheet. ' .
                'Please upload a file based on the provided SF-1 template.'
            );
        }

        $a1 = (string) $worksheet->getCell('A1')->getValue();

        if (!str_contains(strtolower($a1), strtolower(self::SF1_A1_ANCHOR))) {
            throw new \RuntimeException(
                'Cell A1 of the "School Form 1 (SF1)" sheet does not contain "School Form 1". ' .
                'Please upload a file based on the provided SF-1 template.'
            );
        }

        return $this->parseRows($worksheet);
    }

    /**
     * Locate the SF1 worksheet, tolerating minor naming differences such as
     * extra spaces, casing, or a trailing suffix (e.g. "School Form 1 (SF1) - Grade 5").
     */
    private function findSf1Sheet(Spreadsheet $spreadsheet): ?Worksheet
    {
        // 1) Exact match (case-insensitive, trimmed)
        foreach ($spreadsheet->getWorksheetIterator() as $ws) {
            if (strtolower(trim($ws->getTitle())) === strtolower(self::SF1_SHEET_NAME)) {
                return $ws;
            }
        }

        // 2) Any sheet whose title contains "School Form 1"
        foreach ($spreadsheet->getWorksheetIterator() as $ws) {
            if (str_contains(strtolower($ws->getTitle()), strtolower(self::SF1_A1_ANCHOR))) {
                return $ws;
            }
        }

        // 3) Any sheet whose title contains "SF1"
        foreach ($spreadsheet->getWorksheetIterator() as $ws) {
            if (str_contains(strtolower($ws->getTitle()), 'sf1')) {
                return $ws;
            }
        }

        return null;
    }

    /**
     * @return ImportRowData[]
     */
    private function parseRows(Worksheet $worksheet): array
    {
        $rows = [];

        // Form-level values from the SF1 header area (applied to every row).
        // R6:S6 is the "Grade Level" label; the value lives in T6:U6.
        // V6 is the "Section" label; the value lives in W6:Y6.
        $gradeLevel  = $this->cellToString($worksheet->getCell('T6')->getValue());
        $sectionName = $this->cellToString($worksheet->getCell('W6')->getValue());

        // File-level header guard: if these are missing/invalid, every row
        // would fail with the same error. Fail fast so the service can log
        // ONE issue instead of one per student row.
        $missing = [];

        if ($gradeLevel === null || $this->deriveDepartmentLevel($gradeLevel) === null) {
            $missing[] = 'Grade Level (cell T6)';
        }

        if ($sectionName === null) {
            $missing[] = 'Section (cell W6)';
        }

        if (!empty($missing)) {
            throw new MissingFormHeaderException(
                'Missing or invalid form-level header: ' . implode(', ', $missing) . '. ' .
                'Please make sure these cells are filled in before uploading.'
            );
        }

        $departmentLevel = $this->deriveDepartmentLevel($gradeLevel);

        for ($row = self::DATA_START_ROW; $row <= self::MAX_ROW_CEILING; $row++) {
            $colA = $worksheet->getCell('A' . $row)->getValue();

            // Footer landmark marks the END of student data
            if (is_string($colA) && str_contains($colA, self::SF1_FOOTER_LANDMARK)) {
                break;
            }

            // Column C (Name) is the data-row marker; skip unfilled slots
            $name = $this->cellToString($worksheet->getCell('C' . $row)->getValue());

            if ($name === null) {
                continue;
            }

            $rows[] = $this->buildDto($row, $worksheet, $gradeLevel, $sectionName, $departmentLevel);
        }

        return $rows;
    }

    private function buildDto(
        int $rowNumber,
        Worksheet $ws,
        ?string $gradeLevel,
        ?string $sectionName,
        ?string $departmentLevel,
    ): ImportRowData {
        $name = $this->cellToString($ws->getCell('C' . $rowNumber)->getValue());
        ['last' => $last, 'first' => $first, 'middle' => $middle] = $this->parseName($name);

        return new ImportRowData(
            rowNumber: $rowNumber,
            lrn: $this->cellToString($ws->getCell('B' . $rowNumber)->getValue()),
            learnerName: $name,
            firstName: $first,
            lastName: $last,
            middleName: $middle,
            sex: $this->resolveSex($ws, $rowNumber),
            age: $this->cellToString($ws->getCell('H' . $rowNumber)->getValue()),
            birthplace: $this->cellToString($ws->getCell('I' . $rowNumber)->getValue()),
            motherTongue: $this->cellToString($ws->getCell('K' . $rowNumber)->getValue()),
            ipEthnicGroup: $this->cellToString($ws->getCell('L' . $rowNumber)->getValue()),
            religion: $this->cellToString($ws->getCell('M' . $rowNumber)->getValue()),
            address: $this->concatAddress($ws, $rowNumber),
            fatherName: $this->cellToString($ws->getCell('S' . $rowNumber)->getValue()),
            motherMaidenName: $this->cellToString($ws->getCell('U' . $rowNumber)->getValue()),
            guardianName: $this->cellToString($ws->getCell('W' . $rowNumber)->getValue()),
            guardianRelationship: $this->cellToString($ws->getCell('X' . $rowNumber)->getValue()),
            guardianContactNumber: $this->cellToString($ws->getCell('Y' . $rowNumber)->getValue()),
            guardianEmail: $this->normalizeEmail($ws->getCell('Z' . $rowNumber)->getValue()),
            departmentLevel: $departmentLevel,
            gradeLevel: $gradeLevel,
            sectionName: $sectionName,
        );
    }

    // -----------------------------------------------------------------
    //  Value normalizers / helpers
    // -----------------------------------------------------------------

    /**
     * Resolve Sex from the single "Sex (M/F)" column (G) where the
     * registrar types M or F.
     *
     * @return string|null  'male' or 'female', or null if blank/unrecognized
     */
    private function resolveSex(Worksheet $ws, int $row): ?string
    {
        $value = $this->cellToString($ws->getCell('G' . $row)->getValue());

        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            'm', 'male'   => 'male',
            'f', 'female' => 'female',
            default       => null,
        };
    }

    /**
     * Concatenate the 4 address sub-cells into one string, skipping blanks:
     * "House/Street, Barangay, Municipality/City, Province".
     */
    private function concatAddress(Worksheet $ws, int $row): ?string
    {
        $parts = [];

        foreach (['N', 'O', 'P', 'Q'] as $col) {
            $value = $this->cellToString($ws->getCell($col . $row)->getValue());

            if ($value !== null) {
                $parts[] = $value;
            }
        }

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    private function deriveDepartmentLevel(string $gradeLevel): ?string
    {
        if (!preg_match('/^\d+$/', $gradeLevel)) {
            return null;
        }

        return self::GRADE_TO_DEPARTMENT[(int) $gradeLevel] ?? null;
    }

    /**
     * Convert a mixed cell value to a trimmed string, or null if empty.
     */
    private function cellToString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function normalizeEmail(mixed $value): ?string
    {
        $cleaned = $this->cellToString($value);

        return $cleaned === null ? null : strtolower($cleaned);
    }

    // -----------------------------------------------------------------
    //  Name parsing
    // -----------------------------------------------------------------

    /**
     * Parse "LAST, FIRST MIDDLE" (or "LAST, FIRST, MIDDLE") into its parts.
     *
     * Handles registrars omitting the space after commas (e.g.
     * "Dela Cruz,Juan,Miguel") by splitting on commas first, and only on
     * whitespace when no second comma is present. Returns nulls when the
     * comma-delimited format is not detected so ImportRowValidator can
     * flag it. The original string is preserved in ImportRowData::learnerName.
     *
     * @return array{last: ?string, first: ?string, middle: ?string}
     */
    private function parseName(?string $rawName): array
    {
        $result = ['last' => null, 'first' => null, 'middle' => null];

        if ($rawName === null) {
            return $result;
        }

        // Must contain a comma to be parseable
        if (!str_contains($rawName, ',')) {
            return $result;
        }

        $parts = explode(',', $rawName, 2);

        $result['last'] = trim($parts[0]) ?: null;

        $rest = trim($parts[1] ?? '');

        if ($rest === '') {
            return $result;
        }

        // "Last, First, Middle" — commas (with or without spaces) separate
        // the first name from the middle name(s)
        if (str_contains($rest, ',')) {
            $restParts = array_values(array_filter(
                array_map('trim', explode(',', $rest)),
                fn(string $part) => $part !== '',
            ));

            $result['first'] = $restParts[0] ?? null;

            $middle = array_slice($restParts, 1);

            if (!empty($middle)) {
                $result['middle'] = implode(' ', $middle);
            }

            return $result;
        }

        // "Last, First Middle" — whitespace separates the first name from
        // the middle name(s)
        $restWords = array_values(array_filter(
            preg_split('/\s+/', $rest),
            fn(string $word) => $word !== '',
        ));

        $result['first'] = array_shift($restWords) ?: null;

        if (!empty($restWords)) {
            $result['middle'] = implode(' ', $restWords);
        }

        return $result;
    }
}
