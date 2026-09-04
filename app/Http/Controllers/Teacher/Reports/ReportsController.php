<?php

namespace App\Http\Controllers\Teacher\Reports;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

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

        $gradingPeriods = GradingPeriod::where('sequence', '<=', 3)
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
        $allGradingPeriods = GradingPeriod::where('sequence', '<=', 3)
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
                $enrollments
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
        $grades = QuarterlyGrade::where(
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
        $grades = QuarterlyGrade::where(
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
     * We use the actual QuarterlyGrade records because those are
     * the grade records already used by the grading system.
     */
    private function gradeSubmissionReport(
        TeachingAssignment $ta,
        $enrollments,
        $gradingPeriods
    ) {
        $grades = QuarterlyGrade::where(
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
     * Shows attendance totals for each learner.
     */
    private function attendanceReport(
        TeachingAssignment $ta,
        $enrollments
    ) {
        $enrollmentIds = $enrollments->pluck('id');

        $attendance = AttendanceLog::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->where('scan_type', 'IN')
            ->get()
            ->groupBy('enrollment_id');

        $rows = $enrollments->map(function ($enrollment) use (
            $attendance
        ) {
            $logs = $attendance->get(
                $enrollment->id,
                collect()
            );

            $presentDays = $logs
                ->groupBy(
                    fn ($log) =>
                        optional($log->scan_time)->toDateString()
                )
                ->count();

            $lastAttendance = $logs
                ->sortByDesc('scan_time')
                ->first();

            return [
                'enrollment_id' => $enrollment->id,
                'name' => $this->studentName($enrollment),
                'present_days' => $presentDays,
                'last_attendance' => $lastAttendance
                    ? $lastAttendance->scan_time
                    : null,
            ];
        })->values();

        return response()->json([
            'report_type' => 'attendance',
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => [],
            'rows' => $rows,
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
}
