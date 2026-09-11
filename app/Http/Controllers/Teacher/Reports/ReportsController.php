<?php

namespace App\Http\Controllers\Teacher\Reports;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\TermGrade;
use App\Models\TeachingAssignment;
use App\Libraries\PDF\DomPdfWrapper;
use App\Services\Grading\RiskScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignments = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->get()
            : collect();

        $gradingPeriods = GradingPeriod::trimester()
            ->orderBy('sequence')
            ->get();

        return view('pov.teacher.reports.reports',
            compact('teachingAssignments', 'gradingPeriods')
        );
    }

    /**
     * Main report endpoint.
     *
     * The Blade file sends:
     * - report_type
     * - term_id
     * - student_id
     */
    public function classRecordData(
        Request $request,
        int $teachingAssignmentId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        $reportType = $request->input('report_type', 'class-grade');
        $selectedTermId = $request->input('term_id');

        /*
         * Validate selected term against the actual 3-term grading periods.
         */
        $allGradingPeriods = GradingPeriod::trimester()
            ->orderBy('sequence')
            ->get();

        $gradingPeriods = $allGradingPeriods;

        if ($selectedTermId) {
            $gradingPeriods = $allGradingPeriods
                ->where('id', (int) $selectedTermId)
                ->values();
        }

        /*
         * Academic Record is the only report that requires
         * one specific student.
         */
        $enrollmentsQuery = Enrollment::where('section_id', $ta->section_id)
            ->where('status', 'active')
            ->with('student');

        if (
            $reportType === 'academic-record' &&
            $request->filled('student_id')
        ) {
            $enrollmentsQuery->where(
                'id',
                (int) $request->input('student_id')
            );
        }

        $enrollments = $enrollmentsQuery
            ->get()
            ->filter(fn ($enrollment) => $enrollment->student !== null)
            ->sortBy(
                fn ($enrollment) =>
                    ($enrollment->student->last_name ?? '') .
                    ($enrollment->student->first_name ?? '')
            )
            ->values();

        /*
         * Different report types are rendered separately.
         */
        return match ($reportType) {
            'academic-record' => $this->studentAcademicRecord(
                $ta,
                $enrollments,
                $gradingPeriods
            ),

            'grade-submission' => $this->gradeSubmissionReport(
                $ta,
                $enrollments,
                $gradingPeriods
            ),

            'at-risk' => $this->atRiskReport(
                $ta,
                $enrollments,
                $gradingPeriods
            ),

            'attendance' => $this->attendanceReport(
                $ta,
                $enrollments,
                $request
            ),

            default => $this->classGradeReport(
                $ta,
                $enrollments,
                $gradingPeriods
            ),
        };
    }

    /**
     * Class Grade Report
     *
     * Shows all learners and their grades per term.
     */
    private function classGradeReport(
        TeachingAssignment $ta,
        $enrollments,
        $gradingPeriods
    ) {
        $grades = TermGrade::where(
            'teaching_assignment_id',
            $ta->id
        )
            ->whereIn(
                'enrollment_id',
                $enrollments->pluck('id')
            )
            ->whereIn(
                'grading_period_id',
                $gradingPeriods->pluck('id')
            )
            ->get()
            ->groupBy('enrollment_id');

        $rows = $enrollments->map(function ($enrollment) use (
            $grades,
            $gradingPeriods
        ) {
            $studentGrades = $grades
                ->get($enrollment->id, collect())
                ->keyBy('grading_period_id');

            $termGrades = $gradingPeriods
                ->map(
                    fn ($period) =>
                        $studentGrades
                            ->get($period->id)
                            ?->transmuted_grade
                )
                ->values();

            $validGrades = $termGrades->filter(
                fn ($grade) => $grade !== null
            );

            $final = $validGrades->count()
                ? round($validGrades->avg(), 1)
                : null;

            return [
                'enrollment_id' => $enrollment->id,
                'name' => $this->studentName($enrollment),
                'terms' => $termGrades,
                'final' => $final,
            ];
        })->values();

        return $this->jsonReport(
            $ta,
            $gradingPeriods,
            $rows,
            'class-grade'
        );
    }

    /**
     * Student Academic Record
     *
     * Shows the selected learner only.
     */
    private function studentAcademicRecord(
        TeachingAssignment $ta,
        $enrollments,
        $gradingPeriods
    ) {
        $grades = TermGrade::where(
            'teaching_assignment_id',
            $ta->id
        )
            ->whereIn(
                'enrollment_id',
                $enrollments->pluck('id')
            )
            ->get()
            ->groupBy('enrollment_id');

        $rows = $enrollments->map(function ($enrollment) use (
            $grades,
            $gradingPeriods
        ) {
            $studentGrades = $grades
                ->get($enrollment->id, collect())
                ->keyBy('grading_period_id');

            $termGrades = $gradingPeriods
                ->map(
                    fn ($period) =>
                        $studentGrades
                            ->get($period->id)
                            ?->transmuted_grade
                )
                ->values();

            $validGrades = $termGrades->filter(
                fn ($grade) => $grade !== null
            );

            $final = $validGrades->count()
                ? round($validGrades->avg(), 1)
                : null;

            return [
                'enrollment_id' => $enrollment->id,
                'name' => $this->studentName($enrollment),
                'terms' => $termGrades,
                'final' => $final,
            ];
        })->values();

        return $this->jsonReport(
            $ta,
            $gradingPeriods,
            $rows,
            'academic-record'
        );
    }

    /**
     * Grade Submission Report
     *
     * Counts submitted/available grades per term.
     *
     * We use the actual TermGrade records because those are
     * the grade records already used by the grading system.
     */
    private function gradeSubmissionReport(
        TeachingAssignment $ta,
        $enrollments,
        $gradingPeriods
    ) {
        $grades = TermGrade::where(
            'teaching_assignment_id',
            $ta->id
        )
            ->whereIn(
                'enrollment_id',
                $enrollments->pluck('id')
            )
            ->get()
            ->groupBy('grading_period_id');

        $rows = $gradingPeriods->map(function ($period) use (
            $grades,
            $enrollments
        ) {
            $periodGrades = $grades->get($period->id, collect());

            $submitted = $periodGrades
                ->filter(
                    fn ($grade) =>
                        $grade->transmuted_grade !== null
                )
                ->count();

            $total = $enrollments->count();

            $missing = max(0, $total - $submitted);

            $percentage = $total > 0
                ? round(($submitted / $total) * 100, 1)
                : 0;

            return [
                'term' => 'Term ' . $period->sequence,
                'submitted' => $submitted,
                'missing' => $missing,
                'total' => $total,
                'completion' => $percentage,
            ];
        })->values();

        return response()->json([
            'report_type' => 'grade-submission',
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => [],
            'rows' => $rows,
        ]);
    }

    /**
     * At-Risk Monitoring Report
     *
     * Uses the same RiskScoreService already used by AtRiskController.
     */
    private function atRiskReport(
        TeachingAssignment $ta,
        $enrollments,
        $gradingPeriods
    ) {
        $period = $gradingPeriods->first();

        if (!$period) {
            return response()->json([
                'report_type' => 'at-risk',
                'section' => $ta->section->name,
                'grade_level' => $ta->section->grade_level,
                'subject' => $ta->subject->name,
                'terms' => [],
                'rows' => [],
            ]);
        }

        $riskService = app(RiskScoreService::class);

        $riskData = $riskService->calculateRiskScoresForClass(
            $ta,
            $period
        );

        $riskScores = collect($riskData['risk_scores'] ?? [])
            ->keyBy('enrollment_id');

        $rows = $enrollments
            ->filter(
                fn ($enrollment) =>
                    $riskScores->has($enrollment->id)
            )
            ->map(function ($enrollment) use (
                $riskScores,
                $riskService,
                $ta,
                $period
            ) {
                $risk = $riskScores->get($enrollment->id);

                $attendanceRate = $riskService->getAttendanceRate(
                    $enrollment->id,
                    $period->id
                );

                return [
                    'enrollment_id' => $enrollment->id,
                    'name' => $this->studentName($enrollment),
                    'risk_score' => $risk['risk_score'] ?? null,
                    'risk_level' => $risk['risk_level'] ?? 'Unknown',
                    'indicators' => $risk['indicators'] ?? [],
                    'attendance_rate' => $attendanceRate,
                ];
            })
            ->filter(
                fn ($row) =>
                    in_array(
                        $row['risk_level'],
                        ['Moderate', 'High']
                    )
            )
            ->sortByDesc('risk_score')
            ->values();

        return response()->json([
            'report_type' => 'at-risk',
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => [
                'Term ' . $period->sequence,
            ],
            'rows' => $rows,
        ]);
    }

    /**
     * Attendance Report
     *
     * Shows attendance records with dates and statuses for each learner.
     */
    private function attendanceReport(
        TeachingAssignment $ta,
        $enrollments,
        Request $request
    ) {
        $data = $this->generateAttendanceData(
            $ta,
            $enrollments,
            $request
        );

        return response()->json([
            'report_type' => 'attendance',
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => [],
            'rows' => $data['rows'],
        ]);
    }

    /**
     * Students dropdown.
     */
    public function studentsData(
        Request $request,
        int $teachingAssignmentId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $ta = TeachingAssignment::where(
            'id',
            $teachingAssignmentId
        )
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->firstOrFail();

        $enrollments = Enrollment::where(
            'section_id',
            $ta->section_id
        )
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->filter(
                fn ($enrollment) =>
                    $enrollment->student !== null
            )
            ->sortBy(
                fn ($enrollment) =>
                    ($enrollment->student->last_name ?? '') .
                    ($enrollment->student->first_name ?? '')
            )
            ->values();

        return response()->json(
            $enrollments->map(
                function ($enrollment) {
                    return [
                        'enrollment_id' => $enrollment->id,
                        'name' => $this->studentName($enrollment),
                        'lrn' => $enrollment->student->lrn ?? null,
                    ];
                }
            )
        );
    }

    /**
     * Common response structure for grade-based reports.
     */
    private function jsonReport(
        TeachingAssignment $ta,
        $gradingPeriods,
        $rows,
        string $reportType
    ) {
        return response()->json([
            'report_type' => $reportType,
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => $gradingPeriods
                ->map(
                    fn ($period) =>
                        'Term ' . $period->sequence
                )
                ->values(),
            'rows' => $rows,
        ]);
    }

    /**
     * Consistent student display name.
     */
    private function studentName($enrollment): string
    {
        $student = $enrollment->student;

        if (!$student) {
            return 'Unknown Student';
        }

        if (!empty($student->full_name)) {
            return $student->full_name;
        }

        return trim(
            ($student->last_name ?? '') .
            ', ' .
            ($student->first_name ?? '')
        );
    }

        /**
         * Export report data to Excel (.xlsx) format.
         */
        public function exportExcel(Request $request, int $teachingAssignmentId)
        {
            $teacher = $request->user()->teacher;

            abort_unless($teacher, 403);

            // Validate teaching assignment belongs to this teacher
            $ta = TeachingAssignment::where('id', $teachingAssignmentId)
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->firstOrFail();

            $reportType = $request->input('report_type', 'class-grade');
            $selectedTermId = $request->input('term_id');
            $studentId = $request->input('student_id');

            // Validate selected term against the actual 3-term grading periods
            $allGradingPeriods = GradingPeriod::trimester()
                ->orderBy('sequence')
                ->get();

            $gradingPeriods = $allGradingPeriods;

            if ($selectedTermId) {
                $gradingPeriods = $allGradingPeriods
                    ->where('id', (int) $selectedTermId)
                    ->values();
            }

            // Get enrollments for this teaching assignment
            $enrollmentsQuery = Enrollment::where('section_id', $ta->section_id)
                ->where('status', 'active')
                ->with('student');

            if ($reportType === 'academic-record' && $request->filled('student_id')) {
                $enrollmentsQuery->where('id', (int) $request->input('student_id'));
            }

            $enrollments = $enrollmentsQuery
                ->get()
                ->filter(fn ($enrollment) => $enrollment->student !== null)
                ->sortBy(fn ($enrollment) => ($enrollment->student->last_name ?? '') . ($enrollment->student->first_name ?? ''))
                ->values();

            // Generate report data based on type
            $data = match ($reportType) {
                'academic-record' => $this->generateAcademicRecordData($ta, $enrollments, $gradingPeriods),
                'grade-submission' => $this->generateGradeSubmissionData($ta, $enrollments, $gradingPeriods),
                'at-risk' => $this->generateAtRiskData($ta, $enrollments, $gradingPeriods),
                'attendance' => $this->generateAttendanceData($ta, $enrollments, $request),
                default => $this->generateClassGradeData($ta, $enrollments, $gradingPeriods),
            };

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set filename based on report type
            $typeLabel = str_replace('-', ' ', ucfirst($reportType));
            $filename = "{$typeLabel}-Report-{$ta->section->grade_level}-{$ta->section->name}-{$ta->subject->name}.xlsx";

            // Generate spreadsheet based on report type
            switch ($reportType) {
                case 'class-grade':
                    $this->generateClassGradeExcel($sheet, $data, $ta);
                    break;
                case 'academic-record':
                    $this->generateAcademicRecordExcel($sheet, $data, $ta);
                    break;
                case 'grade-submission':
                    $this->generateGradeSubmissionExcel($sheet, $data, $ta);
                    break;
                case 'at-risk':
                    $this->generateAtRiskExcel($sheet, $data, $ta);
                    break;
                case 'attendance':
                    $this->generateAttendanceExcel($sheet, $data, $ta);
                    break;
            }

            // Return streamed response for download
            return new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'max-age=0',
            ]);
        }

        /**
         * Generate Class Grade Excel report
         */
        private function generateClassGradeExcel($sheet, $data, $ta)
        {
            $sheet->setTitle('Class Grade Report');

            // Headers
            $headers = ['Learner Name'];
            foreach ($data['terms'] as $term) {
                $headers[] = $term;
            }
            $headers[] = 'Final Grade';

            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Data rows
            $rowNum = 2;
            foreach ($data['rows'] as $row) {
                $rowData = [$row['name']];
                foreach ($row['terms'] as $grade) {
                    $rowData[] = $grade;
                }
                $rowData[] = $row['final'];
                $sheet->fromArray($rowData, null, 'A' . $rowNum);
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        /**
         * Generate Academic Record Excel report
         */
        private function generateAcademicRecordExcel($sheet, $data, $ta)
        {
            $sheet->setTitle('Academic Record');

            // Student info
            $student = $data['rows'][0] ?? null;
            if ($student) {
                $sheet->setCellValue('A1', 'Student: ' . $student['name']);
                $sheet->setCellValue('A2', 'Final Grade: ' . ($student['final'] ?? ''));
                $sheet->setCellValue('A3', 'Grade ' . $ta->section->grade_level . ' - ' . $ta->section->name);
                $sheet->setCellValue('A4', 'Subject: ' . $ta->subject->name);

                // Headers
                $sheet->setCellValue('A6', 'Term');
                $sheet->setCellValue('B6', 'Grade');

                // Style header
                $sheet->getStyle('A6:B6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Data rows
                $rowNum = 7;
                foreach ($data['terms'] as $index => $term) {
                    $grade = $student['terms'][$index] ?? null;
                    $sheet->setCellValue('A' . $rowNum, $term);
                    $sheet->setCellValue('B' . $rowNum, $grade);
                    $rowNum++;
                }
            }

            // Auto-size columns
            foreach (range('A', 'B') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        /**
         * Generate Grade Submission Excel report
         */
        private function generateGradeSubmissionExcel($sheet, $data, $ta)
        {
            $sheet->setTitle('Grade Submission Report');

            // Headers
            $headers = ['Term', 'Submitted', 'Missing', 'Total', 'Completion'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Data rows
            $rowNum = 2;
            foreach ($data['rows'] as $row) {
                $rowData = [
                    $row['term'],
                    $row['submitted'],
                    $row['missing'],
                    $row['total'],
                    $row['completion'] . '%'
                ];
                $sheet->fromArray($rowData, null, 'A' . $rowNum);
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        /**
         * Generate At-Risk Excel report
         */
        private function generateAtRiskExcel($sheet, $data, $ta)
        {
            $sheet->setTitle('At-Risk Monitoring Report');

            // Headers
            $headers = ['Learner Name', 'Risk Score', 'Risk Level', 'Attendance Rate', 'Risk Indicators'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Data rows
            $rowNum = 2;
            foreach ($data['rows'] as $row) {
                $indicators = is_array($row['indicators']) ? implode('; ', $row['indicators']) : $row['indicators'];
                $rowData = [
                    $row['name'],
                    $row['risk_score'],
                    $row['risk_level'],
                    $row['attendance_rate'] . '%',
                    $indicators
                ];
                $sheet->fromArray($rowData, null, 'A' . $rowNum);
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        /**
         * Generate Attendance Excel report
         */
        private function generateAttendanceExcel($sheet, $data, $ta)
        {
            $sheet->setTitle('Attendance Report');

            // Headers
            $headers = ['Attendance Date', 'Learner Name', 'Status', 'Time In'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header row
            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Data rows
            $rowNum = 2;
            foreach ($data['rows'] as $row) {
                $rowData = [
                    $row['date'],
                    $row['name'],
                    $row['status'],
                    $row['time_in'] ?? '—',
                ];
                $sheet->fromArray($rowData, null, 'A' . $rowNum);
                $rowNum++;
            }

            // Auto-size columns
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        /**
         * Generate data for Class Grade report
         */
        private function generateClassGradeData($ta, $enrollments, $gradingPeriods)
        {
            $grades = TermGrade::where('teaching_assignment_id', $ta->id)
                ->whereIn('enrollment_id', $enrollments->pluck('id'))
                ->whereIn('grading_period_id', $gradingPeriods->pluck('id'))
                ->get()
                ->groupBy('enrollment_id');

            $rows = $enrollments->map(function ($enrollment) use ($grades, $gradingPeriods) {
                $studentGrades = $grades->get($enrollment->id, collect())->keyBy('grading_period_id');
                $termGrades = $gradingPeriods->map(fn ($period) => $studentGrades->get($period->id)?->transmuted_grade)->values();
                $validGrades = $termGrades->filter(fn ($grade) => $grade !== null);
                $final = $validGrades->count() ? round($validGrades->avg(), 1) : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'name' => $this->studentName($enrollment),
                    'terms' => $termGrades,
                    'final' => $final,
                ];
            })->values();

            return [
                'terms' => $gradingPeriods->map(fn ($period) => 'Term ' . $period->sequence)->values(),
                'rows' => $rows,
            ];
        }

        /**
         * Generate data for Academic Record report
         */
        private function generateAcademicRecordData($ta, $enrollments, $gradingPeriods)
        {
            $grades = TermGrade::where('teaching_assignment_id', $ta->id)
                ->whereIn('enrollment_id', $enrollments->pluck('id'))
                ->get()
                ->groupBy('enrollment_id');

            $rows = $enrollments->map(function ($enrollment) use ($grades, $gradingPeriods) {
                $studentGrades = $grades->get($enrollment->id, collect())->keyBy('grading_period_id');
                $termGrades = $gradingPeriods->map(fn ($period) => $studentGrades->get($period->id)?->transmuted_grade)->values();
                $validGrades = $termGrades->filter(fn ($grade) => $grade !== null);
                $final = $validGrades->count() ? round($validGrades->avg(), 1) : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'name' => $this->studentName($enrollment),
                    'terms' => $termGrades,
                    'final' => $final,
                ];
            })->values();

            return [
                'terms' => $gradingPeriods->map(fn ($period) => 'Term ' . $period->sequence)->values(),
                'rows' => $rows,
            ];
        }

        /**
         * Generate data for Grade Submission report
         */
        private function generateGradeSubmissionData($ta, $enrollments, $gradingPeriods)
        {
            $grades = TermGrade::where('teaching_assignment_id', $ta->id)
                ->whereIn('enrollment_id', $enrollments->pluck('id'))
                ->get()
                ->groupBy('grading_period_id');

            $rows = $gradingPeriods->map(function ($period) use ($grades, $enrollments) {
                $periodGrades = $grades->get($period->id, collect());
                $submitted = $periodGrades->filter(fn ($grade) => $grade->transmuted_grade !== null)->count();
                $total = $enrollments->count();
                $missing = max(0, $total - $submitted);
                $percentage = $total > 0 ? round(($submitted / $total) * 100, 1) : 0;

                return [
                    'term' => 'Term ' . $period->sequence,
                    'submitted' => $submitted,
                    'missing' => $missing,
                    'total' => $total,
                    'completion' => $percentage,
                ];
            })->values();

            return ['rows' => $rows];
        }

        /**
         * Generate data for At-Risk report
         */
        private function generateAtRiskData($ta, $enrollments, $gradingPeriods)
        {
            $period = $gradingPeriods->first();
            if (!$period) {
                return ['rows' => []];
            }

            $riskService = app(RiskScoreService::class);
            $riskData = $riskService->calculateRiskScoresForClass($ta, $period);
            $riskScores = collect($riskData['risk_scores'] ?? [])->keyBy('enrollment_id');

            $rows = $enrollments
                ->filter(fn ($enrollment) => $riskScores->has($enrollment->id))
                ->map(function ($enrollment) use ($riskScores, $riskService, $ta, $period) {
                    $risk = $riskScores->get($enrollment->id);
                    $attendanceRate = $riskService->getAttendanceRate($enrollment->id, $period->id);

                    return [
                        'enrollment_id' => $enrollment->id,
                        'name' => $this->studentName($enrollment),
                        'risk_score' => $risk['risk_score'] ?? null,
                        'risk_level' => $risk['risk_level'] ?? 'Unknown',
                        'indicators' => $risk['indicators'] ?? [],
                        'attendance_rate' => $attendanceRate,
                    ];
                })
                ->filter(fn ($row) => in_array($row['risk_level'], ['Moderate', 'High']))
                ->sortByDesc('risk_score')
                ->values();

            return ['rows' => $rows];
        }

        /**
         * Generate data for Attendance report
         */
        private function generateAttendanceData($ta, $enrollments, ?Request $request = null)
        {
            $enrollmentIds = $enrollments->pluck('id');

            if ($enrollmentIds->isEmpty()) {
                return ['rows' => []];
            }

            $dateFrom = $request?->input('date_from');
            $dateTo = $request?->input('date_to');
            $termId = $request?->input('term_id');

            $startDate = null;
            $endDate = null;

            if ($dateFrom) {
                try {
                    $startDate = Carbon::parse($dateFrom)->startOfDay();
                } catch (\Exception $e) {
                    $startDate = null;
                }
            }

            if ($dateTo) {
                try {
                    $endDate = Carbon::parse($dateTo)->endOfDay();
                } catch (\Exception $e) {
                    $endDate = null;
                }
            }

            // If no explicit date range, check if term is selected
            if (!$startDate && !$endDate && $termId) {
                $period = GradingPeriod::find($termId);
                if ($period && $period->start_date && $period->end_date) {
                    $startDate = Carbon::parse($period->start_date)->startOfDay();
                    $endDate = Carbon::parse($period->end_date)->endOfDay();
                }
            }

            $dates = collect();

            if ($startDate && $endDate) {
                // Generate all weekdays in the range up to today
                $cursor = $startDate->copy();
                $maxDate = min($endDate, now()->endOfDay());

                while ($cursor->lte($maxDate)) {
                    if (!$cursor->isWeekend()) {
                        $dates->push($cursor->toDateString());
                    }
                    $cursor->addDay();
                }
            } else {
                // Find distinct dates that have attendance logs or verifications for this section
                $logDatesQuery = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                    ->where('scan_type', 'IN');

                $verificationDatesQuery = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds);

                if ($startDate) {
                    $logDatesQuery->where('scan_time', '>=', $startDate);
                    $verificationDatesQuery->where('attendance_date', '>=', $startDate->toDateString());
                }

                if ($endDate) {
                    $logDatesQuery->where('scan_time', '<=', $endDate);
                    $verificationDatesQuery->where('attendance_date', '<=', $endDate->toDateString());
                }

                $logDates = $logDatesQuery
                    ->selectRaw('DATE(scan_time) as date')
                    ->distinct()
                    ->pluck('date');

                $verificationDates = $verificationDatesQuery
                    ->selectRaw('DATE(attendance_date) as date')
                    ->distinct()
                    ->pluck('date');

                $dates = $logDates->concat($verificationDates)
                    ->filter()
                    ->map(fn ($d) => Carbon::parse($d)->toDateString())
                    ->unique();
            }

            // Sort dates descending (newest first)
            $sortedDates = $dates->sortDesc()->values();

            if ($sortedDates->isEmpty()) {
                return ['rows' => []];
            }

            // Fetch logs for these dates
            $minDateStr = $sortedDates->last();
            $maxDateStr = $sortedDates->first();

            $logs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->whereBetween('scan_time', [
                    Carbon::parse($minDateStr)->startOfDay(),
                    Carbon::parse($maxDateStr)->endOfDay(),
                ])
                ->orderBy('scan_time', 'asc')
                ->get()
                ->groupBy(function ($log) {
                    return $log->enrollment_id . '_' . Carbon::parse($log->scan_time)->toDateString();
                });

            // Fetch verifications for these dates
            $verifications = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                ->whereBetween('attendance_date', [$minDateStr, $maxDateStr])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy(function ($v) {
                    return $v->enrollment_id . '_' . Carbon::parse($v->attendance_date)->toDateString();
                })
                ->map(fn ($group) => $group->first());

            $rows = [];

            foreach ($sortedDates as $dateStr) {
                $dateCarbon = Carbon::parse($dateStr);
                $formattedDate = $dateCarbon->format('M d, Y');

                foreach ($enrollments as $enrollment) {
                    $key = $enrollment->id . '_' . $dateStr;

                    $verification = $verifications->get($key);
                    $studentLogs = $logs->get($key, collect());
                    $firstLog = $studentLogs->first();

                    if ($verification) {
                        $status = $verification->statusLabel();
                        $statusCode = $verification->status;
                    } elseif ($firstLog) {
                        $status = 'Present';
                        $statusCode = 'present';
                    } else {
                        $status = 'Absent';
                        $statusCode = 'absent';
                    }

                    $timeIn = $firstLog ? Carbon::parse($firstLog->scan_time)->format('h:i A') : '—';

                    $rows[] = [
                        'enrollment_id' => $enrollment->id,
                        'name' => $this->studentName($enrollment),
                        'date' => $formattedDate,
                        'date_raw' => $dateStr,
                        'status' => $status,
                        'status_code' => $statusCode,
                        'time_in' => $timeIn,
                    ];
                }
            }

            return ['rows' => $rows];
        }

        /**
         * Export report data to PDF format.
         */
        public function exportPdf(Request $request, int $teachingAssignmentId)
        {
            $teacher = $request->user()->teacher;

            abort_unless($teacher, 403);

            // Validate teaching assignment belongs to this teacher
            $ta = TeachingAssignment::where('id', $teachingAssignmentId)
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->firstOrFail();

            $reportType = $request->input('report_type', 'class-grade');
            $selectedTermId = $request->input('term_id');
            $studentId = $request->input('student_id');

            // Validate selected term against the actual 3-term grading periods
            $allGradingPeriods = GradingPeriod::trimester()
                ->orderBy('sequence')
                ->get();

            $gradingPeriods = $allGradingPeriods;
            if ($selectedTermId) {
                $gradingPeriods = $allGradingPeriods
                    ->where('id', (int) $selectedTermId)
                    ->values();
            }

            // Get enrollments for this teaching assignment
            $enrollmentsQuery = Enrollment::where('section_id', $ta->section_id)
                ->where('status', 'active')
                ->with('student');

            if ($reportType === 'academic-record' && $request->filled('student_id')) {
                $enrollmentsQuery->where('id', (int) $request->input('student_id'));
            }

            $enrollments = $enrollmentsQuery
                ->get()
                ->filter(fn ($enrollment) => $enrollment->student !== null)
                ->sortBy(fn ($enrollment) => ($enrollment->student->last_name ?? '') . ($enrollment->student->first_name ?? ''))
                ->values();

            // Generate report data based on type
            $data = match ($reportType) {
                'academic-record' => $this->generateAcademicRecordData($ta, $enrollments, $gradingPeriods),
                'grade-submission' => $this->generateGradeSubmissionData($ta, $enrollments, $gradingPeriods),
                'at-risk' => $this->generateAtRiskData($ta, $enrollments, $gradingPeriods),
                'attendance' => $this->generateAttendanceData($ta, $enrollments, $request),
                default => $this->generateClassGradeData($ta, $enrollments, $gradingPeriods),
            };

            // Create PDF using DomPdfWrapper
            $pdfWrapper = new DomPdfWrapper();

            // Set filename based on report type
            $typeLabel = str_replace('-', ' ', ucfirst($reportType));
            $filename = "{$typeLabel}-Report-{$ta->section->grade_level}-{$ta->section->name}-{$ta->subject->name}.pdf";

            // Generate HTML content for PDF based on report type
            $html = $this->generateReportHtml($reportType, $data, $ta);

            // Load HTML into PDF
            $pdf = $pdfWrapper->loadHTML($html);
            $pdf->setPaper('a4', 'portrait');

            // Return streamed response for download
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        }

        /**
         * Generate HTML content for PDF based on report type
         */
        private function generateReportHtml($reportType, $data, $ta)
        {
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .subtitle { font-size: 16px; color: #666; margin-bottom: 20px; }
        .meta { margin-bottom: 20px; }
        .meta-row { margin-bottom: 5px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .stat-label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .stat-value { font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
        .badge-neutral { background-color: #f0f0f0; color: #333; }
        .badge-danger { background-color: #fee; color: #c33; }
        .badge-warning { background-color: #ffc; color: #840; }
    </style>
</head>
<body>';

            // Add common header
            $html .= '<div class="header">';
            $html .= '<div class="title">' . str_replace('-', ' ', ucfirst($reportType)) . ' Report</div>';
            $html .= '<div class="meta">';
            $html .= '<div class="meta-row"><strong>Grade:</strong> ' . $ta->section->grade_level . '</div>';
            $html .= '<div class="meta-row"><strong>Section:</strong> ' . $ta->section->name . '</div>';
            $html .= '<div class="meta-row"><strong>Subject:</strong> ' . $ta->subject->name . '</div>';
            $html .= '</div>';
            $html .= '</div>';

            // Generate content based on report type
            switch ($reportType) {
                case 'class-grade':
                    $html .= $this->generateClassGradeHtml($data, $ta);
                    break;
                case 'academic-record':
                    $html .= $this->generateAcademicRecordHtml($data, $ta);
                    break;
                case 'grade-submission':
                    $html .= $this->generateGradeSubmissionHtml($data, $ta);
                    break;
                case 'at-risk':
                    $html .= $this->generateAtRiskHtml($data, $ta);
                    break;
                case 'attendance':
                    $html .= $this->generateAttendanceHtml($data, $ta);
                    break;
            }

            $html .= '</body></html>';
            return $html;
        }

        /**
         * Generate HTML for Class Grade report
         */
        private function generateClassGradeHtml($data, $ta)
        {
            $html = '<div class="stats">';
            $html .= '<div class="stat"><div class="stat-label">Total Learners</div><div class="stat-value">' . count($data['rows']) . '</div></div>';
            $html .= '</div>';

            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Learner Name</th>';
            foreach ($data['terms'] as $term) {
                $html .= '<th class="text-center">' . $term . '</th>';
            }
            $html .= '<th class="text-center">Final Grade</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($data['rows'] as $row) {
                $html .= '<tr>';
                $html .= '<td>' . $row['name'] . '</td>';
                foreach ($row['terms'] as $grade) {
                    $html .= '<td class="text-center">' . ($grade ?? '&#8212;') . '</td>';
                }
                $html .= '<td class="text-center"><strong>' . ($row['final'] ?? '&#8212;') . '</strong></td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }

        /**
         * Generate HTML for Academic Record report
         */
        private function generateAcademicRecordHtml($data, $ta)
        {
            $student = $data['rows'][0] ?? null;
            if (!$student) {
                return '<p>No student data available.</p>';
            }

            $html = '<div class="stats">';
            $html .= '<div class="stat"><div class="stat-label">Student</div><div class="stat-value">' . $student['name'] . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Final Grade</div><div class="stat-value">' . ($student['final'] ?? '&#8212;') . '</div></div>';
            $html .= '</div>';

            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Term</th>';
            $html .= '<th class="text-center">Grade</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($data['terms'] as $index => $term) {
                $grade = $student['terms'][$index] ?? null;
                $html .= '<tr>';
                $html .= '<td>' . $term . '</td>';
                $html .= '<td class="text-center">' . ($grade ?? '&#8212;') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }

        /**
         * Generate HTML for Grade Submission report
         */
        private function generateGradeSubmissionHtml($data, $ta)
        {
            $totalSubmitted = array_sum(array_column($data['rows'], 'submitted'));
            $totalMissing = array_sum(array_column($data['rows'], 'missing'));
            $totalRecords = array_sum(array_column($data['rows'], 'total'));

            $html = '<div class="stats">';
            $html .= '<div class="stat"><div class="stat-label">Submitted</div><div class="stat-value">' . $totalSubmitted . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Missing</div><div class="stat-value">' . $totalMissing . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Overall Completion</div><div class="stat-value">' . round(($totalRecords > 0 ? $totalSubmitted / $totalRecords : 0) * 100, 1) . '%</div></div>';
            $html .= '</div>';

            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Term</th>';
            $html .= '<th class="text-center">Submitted</th>';
            $html .= '<th class="text-center">Missing</th>';
            $html .= '<th class="text-center">Total</th>';
            $html .= '<th class="text-center">Completion</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($data['rows'] as $row) {
                $html .= '<tr>';
                $html .= '<td>' . $row['term'] . '</td>';
                $html .= '<td class="text-center">' . $row['submitted'] . '</td>';
                $html .= '<td class="text-center">' . $row['missing'] . '</td>';
                $html .= '<td class="text-center">' . $row['total'] . '</td>';
                $html .= '<td class="text-center">' . $row['completion'] . '%</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }

        /**
         * Generate HTML for At-Risk report
         */
        private function generateAtRiskHtml($data, $ta)
        {
            $highRisk = count(array_filter($data['rows'], fn($row) => $row['risk_level'] === 'High'));
            $moderateRisk = count(array_filter($data['rows'], fn($row) => $row['risk_level'] === 'Moderate'));

            $html = '<div class="stats">';
            $html .= '<div class="stat"><div class="stat-label">Students at Risk</div><div class="stat-value">' . count($data['rows']) . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">High Risk</div><div class="stat-value">' . $highRisk . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Moderate Risk</div><div class="stat-value">' . $moderateRisk . '</div></div>';
            $html .= '</div>';

            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Learner Name</th>';
            $html .= '<th class="text-center">Risk Score</th>';
            $html .= '<th class="text-center">Risk Level</th>';
            $html .= '<th class="text-center">Attendance Rate</th>';
            $html .= '<th>Risk Indicators</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($data['rows'] as $row) {
                $html .= '<tr>';
                $html .= '<td>' . $row['name'] . '</td>';
                $html .= '<td class="text-center">' . ($row['risk_score'] ?? '-') . '</td>';
                $html .= '<td class="text-center">';
                if ($row['risk_level'] === 'High') {
                    $html .= '<span class="badge badge-danger">' . $row['risk_level'] . '</span>';
                } elseif ($row['risk_level'] === 'Moderate') {
                    $html .= '<span class="badge badge-warning">' . $row['risk_level'] . '</span>';
                } else {
                    $html .= $row['risk_level'];
                }
                $html .= '</td>';
                $html .= '<td class="text-center">' . ($row['attendance_rate'] ?? '-') . '%</td>';
                $html .= '<td>';
                if (is_array($row['indicators']) && !empty($row['indicators'])) {
                    foreach ($row['indicators'] as $indicator) {
                        $html .= '<span class="badge badge-neutral me-1">' . $indicator . '</span>';
                    }
                }
                $html .= '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }

        /**
         * Generate HTML for Attendance report
         */
        private function generateAttendanceHtml($data, $ta)
        {
            $totalRecords = count($data['rows']);
            $totalPresent = count(array_filter($data['rows'], fn($row) => in_array($row['status'], ['Present', 'Late', 'Excused'])));
            $totalAbsent = count(array_filter($data['rows'], fn($row) => in_array($row['status'], ['Absent', 'Not in Classroom'])));

            $html = '<div class="stats">';
            $html .= '<div class="stat"><div class="stat-label">Total Records</div><div class="stat-value">' . $totalRecords . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Present / Excused</div><div class="stat-value" style="color: #198754;">' . $totalPresent . '</div></div>';
            $html .= '<div class="stat"><div class="stat-label">Absent</div><div class="stat-value" style="color: #dc3545;">' . $totalAbsent . '</div></div>';
            $html .= '</div>';

            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Attendance Date</th>';
            $html .= '<th>Learner Name</th>';
            $html .= '<th class="text-center">Status</th>';
            $html .= '<th class="text-center">Time In</th>';
            $html .= '</tr></thead>';
            $html .= '<tbody>';

            foreach ($data['rows'] as $row) {
                $status = $row['status'];
                $badgeClass = match ($status) {
                    'Present' => 'badge badge-neutral',
                    'Late' => 'badge badge-warning',
                    'Absent' => 'badge badge-danger',
                    'Excused' => 'badge badge-neutral',
                    'Not in Classroom' => 'badge badge-warning',
                    default => 'badge badge-neutral',
                };
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($row['date']) . '</td>';
                $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
                $html .= '<td class="text-center"><span class="' . $badgeClass . '">' . htmlspecialchars($status) . '</span></td>';
                $html .= '<td class="text-center">' . htmlspecialchars($row['time_in'] ?? '—') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }
}



