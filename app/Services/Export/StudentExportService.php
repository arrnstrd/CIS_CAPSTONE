<?php

namespace App\Services\Export;

use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentExportService
{
    private const DATA_START_ROW = 10;
    private const FOOTER_LANDMARK_ROW = 59;

    /**
     * Export student list according to specified format ('sf1' or 'raw').
     */
    public function export(
        Collection|array $students,
        ?Section $section = null,
        ?SchoolYear $schoolYear = null,
        string|int|null $gradeLevel = null,
        string $format = 'sf1',
    ): string {
        return match (strtolower($format)) {
            'raw'   => $this->exportRaw($students, $section, $schoolYear, $gradeLevel),
            default => $this->exportSf1($students, $section, $schoolYear, $gradeLevel),
        };
    }

    /**
     * Export using official DepEd SF1 template.
     */
    public function exportSf1(
        Collection|array $students,
        ?Section $section = null,
        ?SchoolYear $schoolYear = null,
        string|int|null $gradeLevel = null,
    ): string {
        $templatePath = storage_path(config('import.sf1_template_path'));

        if (!file_exists($templatePath)) {
            $fallbackPath = resource_path('templates/' . basename(config('import.sf1_template_path')));
            if (file_exists($fallbackPath)) {
                $templatePath = $fallbackPath;
            } else {
                throw new \RuntimeException('SF1 template file not found at: ' . $templatePath);
            }
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Populate Header Cells
        $syString = $schoolYear?->school_year ?? '';
        $gradeString = $section?->grade_level ?? $gradeLevel ?? '';
        $sectionString = $section?->name ?? '';

        if (!empty($syString)) {
            $sheet->setCellValue('L6', $syString);
        }
        if (!empty($gradeString)) {
            $sheet->setCellValue('T6', (string) $gradeString);
        }
        if (!empty($sectionString)) {
            $sheet->setCellValue('W6', $sectionString);
        }

        // 2. Sort Students (Male A-Z, Female A-Z, Others A-Z)
        $sortedStudents = $this->sortStudents($students);

        // 3. Populate Student Rows
        $currentRow = self::DATA_START_ROW;
        $footerRow = self::FOOTER_LANDMARK_ROW;

        foreach ($sortedStudents as $index => $student) {
            if ($currentRow >= $footerRow) {
                $sheet->insertNewRowBefore($footerRow, 1);

                $sheet->mergeCells("C{$footerRow}:F{$footerRow}");
                $sheet->mergeCells("I{$footerRow}:J{$footerRow}");
                $sheet->mergeCells("Q{$footerRow}:R{$footerRow}");
                $sheet->mergeCells("S{$footerRow}:T{$footerRow}");
                $sheet->mergeCells("U{$footerRow}:V{$footerRow}");

                $footerRow++;
            }

            $lastName = trim($student->last_name ?? '');
            $firstName = trim($student->first_name ?? '');
            $middleName = trim($student->middle_name ?? '');
            $learnerName = strtoupper(trim("{$lastName}, {$firstName} {$middleName}"));

            $sex = !empty($student->sex) ? strtoupper(substr($student->sex, 0, 1)) : '';

            [$houseStreet, $barangay, $city, $province] = $this->splitAddress($student->address);

            $sheet->setCellValue("A{$currentRow}", $index + 1);
            $sheet->setCellValue("B{$currentRow}", $student->lrn ?? '');
            $sheet->setCellValue("C{$currentRow}", $learnerName);
            $sheet->setCellValue("G{$currentRow}", $sex);
            $sheet->setCellValue("H{$currentRow}", $student->age ?? '');
            $sheet->setCellValue("I{$currentRow}", $student->birthplace ?? '');
            $sheet->setCellValue("K{$currentRow}", $student->mother_tongue ?? '');
            $sheet->setCellValue("L{$currentRow}", $student->ip_ethnic_group ?? '');
            $sheet->setCellValue("M{$currentRow}", $student->religion ?? '');

            $sheet->setCellValue("N{$currentRow}", $houseStreet);
            $sheet->setCellValue("O{$currentRow}", $barangay);
            $sheet->setCellValue("P{$currentRow}", $city);
            $sheet->setCellValue("Q{$currentRow}", $province);

            $guardian = $student->guardian;
            if ($guardian) {
                $sheet->setCellValue("W{$currentRow}", $guardian->name ?? '');
                $sheet->setCellValue("X{$currentRow}", $guardian->relationship ?? '');
                $sheet->setCellValue("Y{$currentRow}", $guardian->contact_number ?? '');
                $sheet->setCellValue("Z{$currentRow}", $guardian->email ?? '');
            }

            $currentRow++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'sf1_export_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Export raw tabular XLSX with zero merged cells, styled headers, and separated fields.
     */
    public function exportRaw(
        Collection|array $students,
        ?Section $section = null,
        ?SchoolYear $schoolYear = null,
        string|int|null $gradeLevel = null,
    ): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students List');

        // Header Columns (Unmerged)
        $headers = [
            'A1' => '#',
            'B1' => 'LRN',
            'C1' => 'Student Number',
            'D1' => 'Student Name',
            'E1' => 'Sex',
            'F1' => 'Age',
            'G1' => 'Birthplace',
            'H1' => 'Mother Tongue',
            'I1' => 'IP / Ethnic Group',
            'J1' => 'Religion',
            'K1' => 'Address',
            'L1' => 'Guardian Name',
            'M1' => 'Guardian Relationship',
            'N1' => 'Guardian Contact Number',
            'O1' => 'Guardian Email',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Header Styling
        $headerRange = 'A1:O1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->freezePane('A2');

        // Populate Data (Male A-Z, Female A-Z, Others)
        $sortedStudents = $this->sortStudents($students);
        $row = 2;

        foreach ($sortedStudents as $index => $student) {
            $sexLabel = match (strtolower(substr((string) $student->sex, 0, 1))) {
                'm' => 'Male',
                'f' => 'Female',
                default => ucfirst((string) $student->sex),
            };

            $lastName = trim($student->last_name ?? '');
            $firstName = trim($student->first_name ?? '');
            $middleName = trim($student->middle_name ?? '');
            $studentName = strtoupper(trim("{$lastName}, {$firstName} {$middleName}"));

            $guardian = $student->guardian;

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $student->lrn ?? '');
            $sheet->setCellValue("C{$row}", $student->student_number ?? '');
            $sheet->setCellValue("D{$row}", $studentName);
            $sheet->setCellValue("E{$row}", $sexLabel);
            $sheet->setCellValue("F{$row}", $student->age ?? '');
            $sheet->setCellValue("G{$row}", $student->birthplace ?? '');
            $sheet->setCellValue("H{$row}", $student->mother_tongue ?? '');
            $sheet->setCellValue("I{$row}", $student->ip_ethnic_group ?? '');
            $sheet->setCellValue("J{$row}", $student->religion ?? '');
            $sheet->setCellValue("K{$row}", $student->address ?? '');
            $sheet->setCellValue("L{$row}", $guardian?->name ?? '');
            $sheet->setCellValue("M{$row}", $guardian?->relationship ?? '');
            $sheet->setCellValue("N{$row}", $guardian?->contact_number ?? '');
            $sheet->setCellValue("O{$row}", $guardian?->email ?? '');

            $row++;
        }

        // Auto-fit column widths
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'raw_export_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Sort students: Male A-Z, Female A-Z, Others A-Z.
     *
     * @param Collection|array $students
     * @return array<int, Student>
     */
    private function sortStudents(Collection|array $students): array
    {
        $studentCollection = is_array($students) ? collect($students) : $students;

        $males = $studentCollection
            ->filter(fn($s) => strtolower(substr((string) $s->sex, 0, 1)) === 'm')
            ->sortBy([
                ['last_name', 'asc'],
                ['first_name', 'asc'],
            ]);

        $females = $studentCollection
            ->filter(fn($s) => strtolower(substr((string) $s->sex, 0, 1)) === 'f')
            ->sortBy([
                ['last_name', 'asc'],
                ['first_name', 'asc'],
            ]);

        $others = $studentCollection
            ->reject(function ($s) {
                $sexChar = strtolower(substr((string) $s->sex, 0, 1));
                return $sexChar === 'm' || $sexChar === 'f';
            })
            ->sortBy([
                ['last_name', 'asc'],
                ['first_name', 'asc'],
            ]);

        return $males->concat($females)->concat($others)->values()->all();
    }

    /**
     * Splits a single address string into [House/Street, Barangay, City/Municipality, Province].
     *
     * @param string|null $address
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function splitAddress(?string $address): array
    {
        if (empty($address)) {
            return ['', '', '', ''];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));
        $count = count($parts);

        if ($count === 0) {
            return ['', '', '', ''];
        }

        if ($count >= 4) {
            return [
                $parts[0],
                $parts[1],
                $parts[2],
                implode(', ', array_slice($parts, 3)),
            ];
        }

        if ($count === 3) {
            return [$parts[0], $parts[1], $parts[2], ''];
        }

        if ($count === 2) {
            return [$parts[0], $parts[1], '', ''];
        }

        return [$parts[0], '', '', ''];
    }
}
