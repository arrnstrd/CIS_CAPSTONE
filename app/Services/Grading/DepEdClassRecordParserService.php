<?php

namespace App\Services\Grading;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Services\Grading\SchoolLevelDetector;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepEdClassRecordParserService
{
    protected SchoolLevelDetector $levelDetector;

    public function __construct(SchoolLevelDetector $levelDetector)
    {
        $this->levelDetector = $levelDetector;
    }

    /**
     * List available worksheet names in an Excel file.
     *
     * @param string $filePath
     * @return array<string>
     */
    public function getWorksheetNames(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        return $reader->listWorksheetNames($filePath);
    }

    /**
     * Inspect and parse a DepEd Class Record Excel file for a specific sheet and teaching assignment.
     *
     * @param string $filePath
     * @param string $sheetName
     * @param TeachingAssignment $ta
     * @return array
     */
    public function parseSheet(string $filePath, string $sheetName, TeachingAssignment $ta): array
    {
        @ini_set('memory_limit', '512M');

        $section = $ta->section;
        $expectedLevelType = $this->levelDetector->detect($section?->grade_level ?? 1); // 'elem', 'hs', 'shs'

        $reader = IOFactory::createReaderForFile($filePath);
        $allSheetNames = $reader->listWorksheetNames($filePath);
        $sheetsToLoad = [$sheetName];
        if (in_array('INPUT DATA', $allSheetNames)) {
            $sheetsToLoad[] = 'INPUT DATA';
        }
        $reader->setLoadSheetsOnly($sheetsToLoad);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        // 1. Detect Template Type ('elem_jhs' vs 'shs')
        $detectedType = $this->detectTemplateType($sheet);

        $isShsSection = ($expectedLevelType === 'shs');
        $isShsTemplate = ($detectedType === 'shs');

        $templateMismatch = false;
        $mismatchMessage = null;

        if ($isShsSection && !$isShsTemplate) {
            $templateMismatch = true;
            $mismatchMessage = "Template Mismatch: The selected class is Senior High School (Grade {$section->grade_level}), but the uploaded file appears to be an Elementary/JHS Class Record template.";
        } elseif (!$isShsSection && $isShsTemplate) {
            $templateMismatch = true;
            $mismatchMessage = "Template Mismatch: The selected class is Grade {$section->grade_level}, but the uploaded file appears to be a Senior High School Class Record template.";
        }

        // 2. Scan Header & Categories
        $structure = $this->scanStructure($sheet, $detectedType);

        // 3. Scan Student Rows (Male & Female sections)
        $extractedStudents = $this->extractStudentsAndScores($sheet, $structure);

        // 4. Strict Roster Reconciliation against Database Enrollments
        $dbEnrollments = Enrollment::where('section_id', $ta->section_id)
            ->where('school_year_id', $ta->school_year_id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $reconciliation = $this->reconcileRoster($dbEnrollments, $extractedStudents);

        $canImport = !$templateMismatch && empty($reconciliation['missing_students']) && empty($reconciliation['extra_students']);

        return [
            'template_type' => $detectedType,
            'template_mismatch' => $templateMismatch,
            'mismatch_message' => $mismatchMessage,
            'sheet_name' => $sheetName,
            'assessments' => $structure['assessments'],
            'extracted_students_count' => count($extractedStudents),
            'matched_students_count' => count($reconciliation['matched_records']),
            'matched_records' => $reconciliation['matched_records'],
            'missing_students' => $reconciliation['missing_students'],
            'extra_students' => $reconciliation['extra_students'],
            'can_import' => $canImport,
        ];
    }

    /**
     * Detect whether the sheet uses Elem/JHS layout or SHS layout.
     */
    protected function detectTemplateType(Worksheet $sheet): string
    {
        $title1 = (string) $sheet->getCell('A1')->getValue();
        $title2 = (string) $sheet->getCell('B2')->getValue();

        if (str_contains(strtolower($title1), 'senior high school') || str_contains(strtolower($title1), 'shs')) {
            return 'shs';
        }

        $row12val = (string) $sheet->getCell('F12')->getValue();
        if (str_contains(strtolower($row12val), 'written')) {
            return 'elem_jhs';
        }

        return 'elem_jhs';
    }

    /**
     * Scan assessment categories, slot numbers, and Highest Possible Scores (HPS).
     */
    protected function scanStructure(Worksheet $sheet, string $templateType): array
    {
        $hpsRow = ($templateType === 'shs') ? 11 : 15;
        $slotRow = ($templateType === 'shs') ? 10 : 14;

        $assessments = [];
        $maxCol = 29;

        $currentCategory = 'written_work';

        for ($col = 6; $col <= $maxCol; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            
            $headerCell12 = (string) $sheet->getCell([$col, 12])->getValue();
            $headerCell9 = (string) $sheet->getCell([$col, 9])->getValue();
            $headerCell8 = (string) $sheet->getCell([$col, 8])->getValue();

            if (str_contains(strtolower($headerCell12), 'written') || str_contains(strtolower($headerCell9), 'written') || str_contains(strtolower($headerCell8), 'written')) {
                $currentCategory = 'written_work';
            } elseif (str_contains(strtolower($headerCell12), 'product') || str_contains(strtolower($headerCell12), 'performance') || str_contains(strtolower($headerCell9), 'performance') || str_contains(strtolower($headerCell8), 'performance')) {
                $currentCategory = 'performance_task';
            } elseif (str_contains(strtolower($headerCell12), 'examination') || str_contains(strtolower($headerCell12), 'term assessment') || str_contains(strtolower($headerCell12), 'quarterly') || str_contains(strtolower($headerCell9), 'exam') || str_contains(strtolower($headerCell8), 'exam')) {
                $currentCategory = 'term_assessment';
            } elseif ($templateType === 'shs') {
                if ($col >= 6 && $col <= 10) {
                    $currentCategory = 'written_work';
                } elseif ($col >= 14 && $col <= 16) {
                    $currentCategory = 'performance_task';
                } elseif ($col >= 20 && $col <= 22) {
                    $currentCategory = 'term_assessment';
                }
            }

            $slotVal = $sheet->getCell([$col, $slotRow])->getFormattedValue();
            $hpsVal = $sheet->getCell([$col, $hpsRow])->getCalculatedValue();
            $slotNumber = $this->parseSlotNumber($slotVal, $currentCategory);

            if ($slotNumber !== null && is_numeric($hpsVal) && (float)$hpsVal > 0) {
                $assessments[] = [
                    'col' => $col,
                    'col_letter' => $colLetter,
                    'category' => $currentCategory,
                    'slot_number' => $slotNumber,
                    'hps' => (float) $hpsVal,
                ];
            }
        }

        return [
            'hps_row' => $hpsRow,
            'slot_row' => $slotRow,
            'assessments' => $assessments,
        ];
    }

    /**
     * Parse slot number from sheet header cell value (supports numbers and identifiers like ST1, ST2, TE, SA1, SA2, EX1..EX3).
     */
    protected function parseSlotNumber(mixed $slotVal, string $category): ?int
    {
        if ($slotVal === null || $slotVal === '') {
            return null;
        }

        $val = trim((string) $slotVal);

        if (is_numeric($val)) {
            $num = (int) $val;
            return ($num > 0 && $num <= 20) ? $num : null;
        }

        $upper = strtoupper($val);

        if ($category === 'term_assessment') {
            return match ($upper) {
                'ST1', 'SA1', 'EX1', 'TEST 1', 'TEST1' => 1,
                'ST2', 'SA2', 'EX2', 'TEST 2', 'TEST2' => 2,
                'TE', 'EX3', 'QE', 'TERM EXAM', 'EXAM' => 3,
                default => null,
            };
        }

        if (preg_match('/^(?:WW|PT|EX)(\d+)$/i', $upper, $matches)) {
            $num = (int) $matches[1];
            return ($num > 0 && $num <= 20) ? $num : null;
        }

        return null;
    }

    /**
     * Extract evaluated student names and scores across Male and Female sections.
     */
    protected function extractStudentsAndScores(Worksheet $sheet, array $structure): array
    {
        $students = [];
        $highestRow = min($sheet->getHighestRow(), 120);

        $currentGender = 'male';

        for ($row = 12; $row <= $highestRow; $row++) {
            $colA = trim((string) $sheet->getCell([1, $row])->getCalculatedValue());
            $colB = trim((string) $sheet->getCell([2, $row])->getCalculatedValue());
            $colC = trim((string) $sheet->getCell([3, $row])->getCalculatedValue());

            if (strtoupper($colA) === 'FEMALE' || strtoupper($colB) === 'FEMALE') {
                $currentGender = 'female';
                continue;
            }
            if (strtoupper($colA) === 'MALE' || strtoupper($colB) === 'MALE') {
                $currentGender = 'male';
                continue;
            }

            $rawName = !empty($colC) && !is_numeric($colC) ? $colC : (!empty($colB) && !is_numeric($colB) ? $colB : '');
            $cleanName = trim(preg_replace('/\s+/', ' ', (string) $rawName));

            // Strictly filter out empty rows, zeros, system labels, headers, and formula errors
            if (
                empty($cleanName) ||
                $cleanName === '0' ||
                $cleanName === '0.0' ||
                is_numeric($cleanName) ||
                str_contains($cleanName, '#REF!') ||
                str_contains($cleanName, '=') ||
                in_array(strtoupper($cleanName), ['MALE', 'FEMALE', 'LEARNERS NAMES', 'LEARNERS\' NAMES', 'HIGHEST POSSIBLE SCORE'])
            ) {
                continue;
            }

            $scores = [];
            foreach ($structure['assessments'] as $asm) {
                $colIndex = $asm['col'];
                $val = $sheet->getCell([$colIndex, $row])->getCalculatedValue();

                if ($val !== null && $val !== '' && is_numeric($val)) {
                    $scores[] = [
                        'category' => $asm['category'],
                        'slot_number' => $asm['slot_number'],
                        'score' => (float) $val,
                    ];
                }
            }

            $students[] = [
                'raw_name' => $cleanName,
                'normalized_name' => $this->normalizeName($cleanName),
                'gender' => $currentGender,
                'row' => $row,
                'scores' => $scores,
            ];
        }

        return $students;
    }

    /**
     * Reconcile extracted Excel students against DB active Enrollments.
     */
    protected function reconcileRoster(Collection $dbEnrollments, array $extractedStudents): array
    {
        $matchedRecords = [];
        $matchedEnrollmentIds = [];
        $matchedExtractedIndices = [];

        foreach ($extractedStudents as $extIdx => $extStudent) {
            $extRawName = $extStudent['raw_name'];

            foreach ($dbEnrollments as $enrollment) {
                if (in_array($enrollment->id, $matchedEnrollmentIds)) {
                    continue;
                }

                $student = $enrollment->student;
                if (!$student) continue;

                if ($this->isNameMatch($extRawName, $student)) {
                    $matchedEnrollmentIds[] = $enrollment->id;
                    $matchedExtractedIndices[] = $extIdx;

                    $matchedRecords[] = [
                        'enrollment_id' => $enrollment->id,
                        'student_name' => "{$student->last_name}, {$student->first_name}",
                        'lrn' => $student->lrn,
                        'excel_name' => $extStudent['raw_name'],
                        'scores' => $extStudent['scores'],
                    ];
                    break;
                }
            }
        }

        $missingStudents = [];
        foreach ($dbEnrollments as $enrollment) {
            if (!in_array($enrollment->id, $matchedEnrollmentIds)) {
                $st = $enrollment->student;
                $missingStudents[] = [
                    'enrollment_id' => $enrollment->id,
                    'name' => $st ? "{$st->last_name}, {$st->first_name}" : "Enrollment #{$enrollment->id}",
                    'lrn' => $st?->lrn ?? 'N/A',
                ];
            }
        }

        $extraStudents = [];
        foreach ($extractedStudents as $extIdx => $extStudent) {
            if (!in_array($extIdx, $matchedExtractedIndices)) {
                $extraStudents[] = [
                    'excel_name' => $extStudent['raw_name'],
                    'row' => $extStudent['row'],
                ];
            }
        }

        return [
            'matched_records' => $matchedRecords,
            'missing_students' => $missingStudents,
            'extra_students' => $extraStudents,
        ];
    }

    /**
     * Normalize a name string for super tolerant comparison.
     */
    public function normalizeName(string $name): string
    {
        // Transliterate accents/diacritics (e.g. Ñ -> N, é -> e)
        $str = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $str = mb_strtoupper(trim($str), 'UTF-8');
        // Remove all non-alphanumeric chars except space
        $str = preg_replace('/[^A-Z0-9\s]/', '', $str);
        return preg_replace('/\s+/', ' ', $str);
    }

    /**
     * Multi-tier tolerant name matching (LRN, Permutations, Token Containment, Fuzzy similarity).
     */
    public function isNameMatch(string $extName, Student $student): bool
    {
        $extNorm = $this->normalizeName($extName);
        if (empty($extNorm)) return false;

        $lastNorm = $this->normalizeName($student->last_name);
        $firstNorm = $this->normalizeName($student->first_name);
        $middleNorm = $this->normalizeName($student->middle_name ?? '');

        // Tier 0: LRN match if LRN is included in the string
        if (!empty($student->lrn) && str_contains($extNorm, (string)$student->lrn)) {
            return true;
        }

        // Tier 1: Full Permutations
        $full1 = $this->normalizeName("{$student->last_name}, {$student->first_name} {$student->middle_name}");
        $full2 = $this->normalizeName("{$student->last_name}, {$student->first_name}");
        $full3 = $this->normalizeName("{$student->first_name} {$student->last_name}");
        $full4 = $this->normalizeName("{$student->last_name} {$student->first_name}");

        if ($extNorm === $full1 || $extNorm === $full2 || $extNorm === $full3 || $extNorm === $full4) {
            return true;
        }

        // Tier 2: Token Containment (All Lastname tokens + At least 1 Firstname token)
        $lastTokens = array_filter(explode(' ', $lastNorm));
        $firstTokens = array_filter(explode(' ', $firstNorm));

        if (!empty($lastTokens) && !empty($firstTokens)) {
            $containsAllLast = true;
            foreach ($lastTokens as $lt) {
                if (!str_contains($extNorm, $lt)) {
                    $containsAllLast = false;
                    break;
                }
            }

            if ($containsAllLast) {
                $containsAnyFirst = false;
                foreach ($firstTokens as $ft) {
                    if (mb_strlen($ft) > 1 && str_contains($extNorm, $ft)) {
                        $containsAnyFirst = true;
                        break;
                    }
                }

                if ($containsAnyFirst) {
                    return true;
                }
            }
        }

        // Tier 3: Fuzzy Similarity Match (>= 80%)
        similar_text($extNorm, $full2, $percent);
        if ($percent >= 80) {
            return true;
        }

        return false;
    }
}
