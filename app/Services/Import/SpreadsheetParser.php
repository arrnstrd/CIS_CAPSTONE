<?php

namespace App\Services\Import;

use App\DTOs\ImportRowData;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Reads an SF-1 Excel spreadsheet and converts every data row into
 * a normalized ImportRowData DTO.
 *
 * Responsibilities:
 *  - Validate that all 12 required headers are present (resilient matching)
 *  - Normalize raw cell values per the SF-1 specification
 *  - Parse the "Learner Name" column into first/middle/last
 *  - Skip completely empty rows and stop after consecutive blanks
 *
 * This service performs NO validation — it normalizes and preserves.
 * Invalid values are passed through as-is so ImportRowValidator can flag them.
 */
class SpreadsheetParser
{
    /** @var array<string, string> */
    private const SEX_MAP = [
        'm'      => 'male',
        'male'   => 'male',
        'f'      => 'female',
        'female' => 'female',
    ];

    /** @var array<string, string> */
    private const DEPARTMENT_LEVEL_MAP = [
        'elementary'          => 'elementary',
        'elem'                => 'elementary',
        'high school'         => 'highschool',
        'hs'                  => 'highschool',
        'junior high'         => 'highschool',
        'senior high school'  => 'senior_high_school',
        'shs'                 => 'senior_high_school',
    ];

    /** @var array<string, string> */
    private const SESSION_TYPE_MAP = [
        'morning'           => 'morning',
        'am'                => 'morning',
        'afternoon'         => 'afternoon',
        'pm'                => 'afternoon',
        'whole day'         => 'whole_day',
        'wholeday'          => 'whole_day',
        'whole day session' => 'whole_day',
    ];

    /**
     * Required headers in their normalized (lowercased, single-spaced) form.
     *
     * @var list<string>
     */
    private const REQUIRED_HEADERS = [
        'lrn',
        'learner name',
        'sex',
        'birth date',
        'complete address',
        'guardian name',
        'guardian relationship',
        'guardian email',
        'department level',
        'grade level',
        'section',
        'session type',
    ];

    /**
     * Maximum number of consecutive completely-empty rows before parsing stops.
     */
    private const MAX_CONSECUTIVE_EMPTY = 10;

    // -----------------------------------------------------------------
    //  Public API
    // -----------------------------------------------------------------

    /**
     * Parse an Excel file and return an array of normalized ImportRowData DTOs.
     *
     * @param  string  $filePath  Absolute path to the .xlsx file
     * @return ImportRowData[]
     *
     * @throws \RuntimeException  If the file cannot be read or required headers are missing
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Import file not found: ' . $filePath);
        }

        $spreadsheet = IOFactory::load($filePath);
        $worksheet   = $spreadsheet->getActiveSheet();

        $rawHeaders = $this->readHeaders($worksheet);
        $this->validateHeaders($rawHeaders);

        $columnMap = $this->buildColumnMap($rawHeaders);

        return $this->parseRows($worksheet, $columnMap);
    }

    /**
     * Return the ordered list of expected header names for template generation.
     *
     * @return list<string>
     */
    public function getTemplateHeaders(): array
    {
        return [
            'LRN',
            'Learner Name',
            'Sex',
            'Birth Date',
            'Complete Address',
            'Guardian Name',
            'Guardian Relationship',
            'Guardian Email',
            'Department Level',
            'Grade Level',
            'Section',
            'Session Type',
        ];
    }

    // -----------------------------------------------------------------
    //  Header handling
    // -----------------------------------------------------------------

    /**
     * Read row 1 as raw header strings.
     *
     * @return array<int, string>  [colIndex => rawValue]
     */
    private function readHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet): array
    {
        $highestColumn = $worksheet->getHighestDataColumn();
        $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headers = [];

        for ($col = 1; $col <= $highestColIdx; $col++) {
            $cell = $worksheet->getCell([$col, 1]);
            $value = $cell->getValue();

            if ($value !== null && $value !== '') {
                $headers[$col] = (string) $value;
            }
        }

        return $headers;
    }

    /**
     * Throw if any required header is absent after normalization.
     *
     * @param  array<int, string>  $rawHeaders
     *
     * @throws \RuntimeException
     */
    private function validateHeaders(array $rawHeaders): void
    {
        $normalized = array_map([$this, 'normalizeHeader'], $rawHeaders);
        $present    = array_values($normalized);
        $missing    = array_diff(self::REQUIRED_HEADERS, $present);

        if (!empty($missing)) {
            throw new \RuntimeException(
                'Missing required columns: ' . implode(', ', array_map('ucwords', $missing))
            );
        }
    }

    /**
     * Normalize a raw header string for resilient comparison:
     * trim, collapse whitespace, remove line breaks, lowercase.
     */
    private function normalizeHeader(string $raw): string
    {
        $normalized = trim($raw);
        $normalized = str_replace(["\r\n", "\r", "\n"], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return strtolower((string) $normalized);
    }

    /**
     * Build a map: normalizedHeaderName → columnIndex (1-based).
     *
     * @param  array<int, string>  $rawHeaders
     * @return array<string, int>
     */
    private function buildColumnMap(array $rawHeaders): array
    {
        $map = [];

        foreach ($rawHeaders as $colIdx => $raw) {
            $normalized = $this->normalizeHeader($raw);

            if (in_array($normalized, self::REQUIRED_HEADERS, true)) {
                $map[$normalized] = $colIdx;
            }
        }

        return $map;
    }

    // -----------------------------------------------------------------
    //  Row parsing
    // -----------------------------------------------------------------

    /**
     * Iterate from row 2 to getHighestDataRow(), skipping empties.
     * Stops after MAX_CONSECUTIVE_EMPTY consecutive empty rows.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $worksheet
     * @param  array<string, int>                              $columnMap  [header => colIdx]
     * @return ImportRowData[]
     */
    private function parseRows(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet,
        array $columnMap,
    ): array {
        $highestRow = $worksheet->getHighestDataRow();
        $rows       = [];
        $emptyStreak = 0;
        $dataStarted = false;

        for ($row = 2; $row <= $highestRow; $row++) {
            $rawRow = $this->readRow($worksheet, $row, $columnMap);

            if ($this->isEmptyRow($rawRow)) {
                $emptyStreak++;

                // Only stop on consecutive empties AFTER data has started
                if ($dataStarted && $emptyStreak >= self::MAX_CONSECUTIVE_EMPTY) {
                    break;
                }

                continue;
            }

            $dataStarted = true;
            $emptyStreak = 0;

            $rows[] = $this->buildDto($row, $rawRow);
        }

        return $rows;
    }

    /**
     * Read a single data row, keyed by normalized header name.
     *
     * @return array<string, mixed>  [header => cellValue]
     */
    private function readRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet,
        int $row,
        array $columnMap,
    ): array {
        $data = [];

        foreach ($columnMap as $header => $colIdx) {
            $cell  = $worksheet->getCell([$colIdx, $row]);
            $value = $cell->getValue();

            $data[$header] = ($value !== null && $value !== '') ? $value : null;
        }

        return $data;
    }

    /**
     * Whether every cell in the row is null (completely empty).
     */
    private function isEmptyRow(array $rowData): bool
    {
        foreach ($rowData as $value) {
            if ($value !== null) {
                return false;
            }
        }

        return true;
    }

    // -----------------------------------------------------------------
    //  DTO construction with normalization
    // -----------------------------------------------------------------

    private function buildDto(int $rowNumber, array $raw): ImportRowData
    {
        // Parse name BEFORE normalization so the original is preserved
        $learnerNameRaw = $this->cellToString($raw['learner name'] ?? null);
        ['last' => $last, 'first' => $first, 'middle' => $middle] = $this->parseName($learnerNameRaw);

        return new ImportRowData(
            rowNumber: $rowNumber,
            lrn: $this->cellToString($raw['lrn'] ?? null),
            learnerName: $learnerNameRaw,
            firstName: $first,
            lastName: $last,
            middleName: $middle,
            sex: $this->normalizeSex($raw['sex'] ?? null),
            birthdate: $this->normalizeDate($raw['birth date'] ?? null),
            address: $this->cellToString($raw['complete address'] ?? null),
            guardianName: $this->cellToString($raw['guardian name'] ?? null),
            guardianRelationship: $this->normalizeGuardianRelationship($raw['guardian relationship'] ?? null),
            guardianEmail: $this->normalizeEmail($raw['guardian email'] ?? null),
            departmentLevel: $this->normalizeDepartmentLevel($raw['department level'] ?? null),
            gradeLevel: $this->cellToString($raw['grade level'] ?? null),
            sectionName: $this->cellToString($raw['section'] ?? null),
            sessionType: $this->normalizeSessionType($raw['session type'] ?? null),
        );
    }

    // -----------------------------------------------------------------
    //  Value normalizers — each method returns ?string
    // -----------------------------------------------------------------

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

    private function normalizeSex(mixed $value): ?string
    {
        $key = strtolower((string) ($value ?? ''));

        return self::SEX_MAP[$key] ?? null;
    }

    /**
     * Normalize a date cell to "Y-m-d", preserving the original on failure.
     *
     * Handles:
     *  - PhpSpreadsheet DateTime objects (formatted date cells)
     *  - Excel serial numbers (numeric date cells)
     *  - String dates (plain text cells)
     */
    private function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // DateTime object from PhpSpreadsheet (formatted date cell)
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        // Excel serial number (numeric date cell)
        if (is_numeric($value) && $value > 1) {
            try {
                $date = ExcelDate::excelToDateTimeObject((float) $value);

                return $date->format('Y-m-d');
            } catch (\Throwable) {
                // Fall through to string handling
            }
        }

        // String date — try to parse it
        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        $timestamp = strtotime($string);

        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // Unparseable — preserve original so the validator can report it
        return $string;
    }

    /**
     * Normalize guardian relationship: trim + lowercase only.
     * Accepted-value validation belongs to ImportRowValidator.
     */
    private function normalizeGuardianRelationship(mixed $value): ?string
    {
        $cleaned = $this->cellToString($value);

        if ($cleaned === null) {
            return null;
        }

        return strtolower($cleaned);
    }

    private function normalizeEmail(mixed $value): ?string
    {
        $cleaned = $this->cellToString($value);

        if ($cleaned === null) {
            return null;
        }

        return strtolower($cleaned);
    }

    private function normalizeDepartmentLevel(mixed $value): ?string
    {
        $key = strtolower((string) ($value ?? ''));

        return self::DEPARTMENT_LEVEL_MAP[$key] ?? null;
    }

    private function normalizeSessionType(mixed $value): ?string
    {
        $key = strtolower((string) ($value ?? ''));

        return self::SESSION_TYPE_MAP[$key] ?? null;
    }

    // -----------------------------------------------------------------
    //  Name parsing
    // -----------------------------------------------------------------

    /**
     * Parse "LASTNAME, FIRSTNAME MIDDLENAME" into its three parts.
     *
     * If the comma-delimited format is not detected, all three parsed
     * fields are left null so ImportRowValidator can flag the format
     * issue. The original string is preserved in ImportRowData::learnerName.
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

        $restWords = preg_split('/\s+/', $rest);
        $result['first'] = array_shift($restWords) ?: null;
        $result['middle'] = !empty($restWords) ? implode(' ', $restWords) : null;

        return $result;
    }
}
