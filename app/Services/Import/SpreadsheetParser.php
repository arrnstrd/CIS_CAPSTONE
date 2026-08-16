<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
 *  - Resolve Sex from the G/H marker columns (see resolveSex())
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
    private const MAX_ROW_CEILING = 200;

    /** @var array<int, string> Map: grade level → department level */
    private const GRADE_TO_DEPARTMENT = [
        1 => 'elementary', 2 => 'elementary', 3 => 'elementary',
        4 => 'elementary', 5 => 'elementary', 6 => 'elementary',
        7 => 'highschool', 8 => 'highschool', 9 => 'highschool',
        10 => 'highschool',
        11 => 'senior_high_school', 12 => 'senior_high_school',
    ];

    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Import file not found: ' . $filePath);
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet   = $spreadsheet->getSheetByName(self::SF1_SHEET_NAME);

        if ($worksheet === null) {
            throw new \RuntimeException(
                'Sheet "' . self::SF1_SHEET_NAME . '" not found in the workbook.'
            );
        }

        $a1 = (string) $worksheet->getCell('A1')->getValue();

        if (!str_contains($a1, self::SF1_A1_ANCHOR)) {
            throw new \RuntimeException(
                'Cell A1 does not contain "School Form 1". This does not look like an SF1 form.'
            );
        }

        return $this->parseRows($worksheet);
    }

    /**
     * @return ImportRowData[]
     */
    private function parseRows(Worksheet $worksheet): array
    {
        $rows = [];

        // Form-level values from the SF1 header area (applied to every row)
        $gradeLevel  = $this->cellToString($worksheet->getCell('S6')->getValue());
        $sectionName = $this->cellToString($worksheet->getCell('W6')->getValue());

        // File-level header guard: if these are missing/invalid, every row
        // would fail with the same error. Fail fast so the service can log
        // ONE issue instead of one per student row.
        $missing = [];

        if ($gradeLevel === null || $this->deriveDepartmentLevel($gradeLevel) === null) {
            $missing[] = 'Grade Level (cell S6)';
        }

        if ($sectionName === null) {
            $missing[] = 'Section (cell W6)';
        }

        if (!empty($missing)) {
            throw new MissingFormHeaderException(
                'Missing or invalid form-level header: ' . implode(', ', $missing) . '.'
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
        $sexResult = $this->resolveSex($ws, $rowNumber);

        return new ImportRowData(
            rowNumber: $rowNumber,
            lrn: $this->cellToString($ws->getCell('B' . $rowNumber)->getValue()),
            learnerName: $name,
            firstName: $first,
            lastName: $last,
            middleName: $middle,
            sex: $sexResult['sex'],
            sexAmbiguous: $sexResult['ambiguous'],
            age: $this->cellToString($ws->getCell('I' . $rowNumber)->getValue()),
            birthplace: $this->cellToString($ws->getCell('J' . $rowNumber)->getValue()),
            motherTongue: $this->cellToString($ws->getCell('L' . $rowNumber)->getValue()),
            ipEthnicGroup: $this->cellToString($ws->getCell('M' . $rowNumber)->getValue()),
            religion: $this->cellToString($ws->getCell('N' . $rowNumber)->getValue()),
            address: $this->concatAddress($ws, $rowNumber),
            fatherName: $this->cellToString($ws->getCell('T' . $rowNumber)->getValue()),
            motherMaidenName: $this->cellToString($ws->getCell('V' . $rowNumber)->getValue()),
            guardianName: $this->cellToString($ws->getCell('X' . $rowNumber)->getValue()),
            guardianRelationship: $this->cellToString($ws->getCell('Y' . $rowNumber)->getValue()),
            guardianContactNumber: $this->cellToString($ws->getCell('Z' . $rowNumber)->getValue()),
            guardianEmail: $this->normalizeEmail($ws->getCell('AA' . $rowNumber)->getValue()),
            departmentLevel: $departmentLevel,
            gradeLevel: $gradeLevel,
            sectionName: $sectionName,
        );
    }

    // -----------------------------------------------------------------
    //  Value normalizers / helpers
    // -----------------------------------------------------------------

    /**
     * Resolve Sex from the G/H marker columns.
     *
     * UNVERIFIED ASSUMPTION (blank template, no filled sample): G is a
     * male marker, H is a female marker. When both are marked, default to
     * male and flag the row (sexAmbiguous) so the UI shows a warning.
     *
     * @return array{sex: ?string, ambiguous: bool}
     */
    private function resolveSex(Worksheet $ws, int $row): array
    {
        $g = $this->cellToString($ws->getCell('G' . $row)->getValue());
        $h = $this->cellToString($ws->getCell('H' . $row)->getValue());

        if ($g !== null && $h !== null) {
            return ['sex' => 'male', 'ambiguous' => true];
        }

        if ($g !== null) {
            return ['sex' => 'male', 'ambiguous' => false];
        }

        if ($h !== null) {
            return ['sex' => 'female', 'ambiguous' => false];
        }

        return ['sex' => null, 'ambiguous' => false];
    }

    /**
     * Concatenate the 4 address sub-cells into one string, skipping blanks:
     * "House/Street, Barangay, Municipality/City, Province".
     */
    private function concatAddress(Worksheet $ws, int $row): ?string
    {
        $parts = [];

        foreach (['O', 'P', 'Q', 'R'] as $col) {
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
                fn (string $part) => $part !== '',
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
            fn (string $word) => $word !== '',
        ));

        $result['first'] = array_shift($restWords) ?: null;

        if (!empty($restWords)) {
            $result['middle'] = implode(' ', $restWords);
        }

        return $result;
    }
}
