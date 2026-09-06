<?php

namespace App\Http\Controllers\Teacher\AtRisk;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\TermGrade;
use App\Models\RiskRemark;
use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

class AtRiskController extends Controller
{
    protected RiskScoreService $riskScoreService;

    public function __construct(RiskScoreService $riskScoreService)
    {
        $this->riskScoreService = $riskScoreService;
    }

    /**
     * At-Risk Student Registry
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return view('pov.teacher.at-risk.at-risk-index', [
                'students' => collect(),
                'gradeLevels' => collect(),
                'selectedGradeLevel' => null,
                'selectedRiskLevel' => null,
                'stats' => [
                    'low' => 0,
                    'moderate' => 0,
                    'high' => 0,
                ],
                'currentPeriod' => null,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Current Term
        |--------------------------------------------------------------------------
        */
        $currentPeriod = GradingPeriod::where('is_active', true)
            ->trimester()
            ->orderBy('sequence')
            ->first()
            ?? GradingPeriod::trimester()
                ->orderBy('sequence')
                ->first();

        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedRiskLevel = $request->input('risk_level') ?: null;

        /*
        |--------------------------------------------------------------------------
        | Teacher Assignments
        |--------------------------------------------------------------------------
        */
        $teachingAssignmentsQuery = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->with(['section', 'subject']);

        if ($selectedGradeLevel) {
            $teachingAssignmentsQuery->whereHas(
                'section',
                fn ($q) => $q->where(
                    'grade_level',
                    $selectedGradeLevel
                )
            );
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Grade Levels
        |--------------------------------------------------------------------------
        */
        $gradeLevels = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->with('section')
            ->get()
            ->pluck('section.grade_level')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Build At-Risk Student Registry
        |--------------------------------------------------------------------------
        */
        $allStudents = collect();

        if ($currentPeriod) {
            foreach ($teachingAssignments as $ta) {

                if (!$ta->section || !$ta->subject) {
                    continue;
                }

                $riskData = $this->riskScoreService
                    ->calculateRiskScoresForClass(
                        $ta,
                        $currentPeriod
                    );

                foreach ($riskData['risk_scores'] as $studentRisk) {

                    /*
                    |--------------------------------------------------------------------------
                    | Only Moderate / High Risk Students
                    |--------------------------------------------------------------------------
                    */
                    if (!in_array(
                        $studentRisk['risk_level'],
                        ['Moderate', 'High'],
                        true
                    )) {
                        continue;
                    }

                    $enrollment = Enrollment::with('student')
                        ->find($studentRisk['enrollment_id']);

                    if (!$enrollment || !$enrollment->student) {
                        continue;
                    }

                    $attendanceRate =
                        $this->riskScoreService->getAttendanceRate(
                            $enrollment->id,
                            $currentPeriod->id
                        );

                    $allStudents->push((object) [
                        'enrollment_id' =>
                            $enrollment->id,

                        'name' =>
                            $enrollment->student->full_name
                            ?? 'Unknown Student',

                        'student_number' =>
                            $enrollment->student->student_number
                            ?? 'N/A',

                        'grade_level' =>
                            $ta->section->grade_level,

                        'section_name' =>
                            $ta->section->name,

                        'subject_name' =>
                            $ta->subject->name,

                        'avg_grade' =>
                            $this->getStudentAverageGrade(
                                $enrollment->id,
                                $ta->id,
                                $currentPeriod->id
                            ),

                        'attendance_rate' =>
                            $attendanceRate,

                        'risk_score' =>
                            $studentRisk['risk_score'],

                        'risk_level' =>
                            $studentRisk['risk_level'],

                        'indicators' =>
                            $studentRisk['indicators'],

                        'teaching_assignment_id' =>
                            $ta->id,
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Apply Risk Filter
        |--------------------------------------------------------------------------
        */
        if ($selectedRiskLevel) {
            $allStudents = $allStudents->where(
                'risk_level',
                $selectedRiskLevel
            );
        }

        $students = $allStudents
            ->sortBy('name')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */
        $stats = [
            'low' => $students
                ->where('risk_level', 'Low')
                ->count(),

            'moderate' => $students
                ->where('risk_level', 'Moderate')
                ->count(),

            'high' => $students
                ->where('risk_level', 'High')
                ->count(),
        ];

        return view('pov.teacher.at-risk.at-risk-index',
            compact(
                'students',
                'gradeLevels',
                'selectedGradeLevel',
                'selectedRiskLevel',
                'stats',
                'currentPeriod'
            )
        );
    }

    /**
     * Dedicated At-Risk Student Detail
     *
     * This page focuses specifically on the student's
     * current academic risk condition.
     */
    public function show(Request $request, int $enrollmentId)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        /*
        |--------------------------------------------------------------------------
        | Enrollment
        |--------------------------------------------------------------------------
        */
        $enrollment = Enrollment::with([
            'student',
            'section',
        ])->findOrFail($enrollmentId);

        /*
        |--------------------------------------------------------------------------
        | Teacher Assignments
        |--------------------------------------------------------------------------
        */
        $teacherAssignments = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->with([
                'section',
                'subject',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Verify Teacher Access
        |--------------------------------------------------------------------------
        |
        | Teacher must actually teach the student's section.
        |
        */
        $teacherSectionIds = $teacherAssignments
            ->pluck('section_id')
            ->unique();

        abort_unless(
            $teacherSectionIds->contains($enrollment->section_id),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Current Term
        |--------------------------------------------------------------------------
        */
        $currentPeriod = GradingPeriod::where(
            'is_active',
            true
        )
            ->trimester()
            ->orderBy('sequence')
            ->first()
            ?? GradingPeriod::where(
                'sequence',
                '<=',
                3
            )
                ->orderBy('sequence')
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Determine Assignment
        |--------------------------------------------------------------------------
        |
        | Prefer the assignment associated with the current term's
        | academic records. If unavailable, fall back to the teacher's
        | assignment in the student's section.
        |
        */
        $assignment = null;

        if ($currentPeriod) {
            $assignment = $teacherAssignments
                ->filter(
                    fn ($ta) =>
                        (int) $ta->section_id ===
                        (int) $enrollment->section_id
                )
                ->first(
                    fn ($ta) =>
                        TermGrade::where(
                            'enrollment_id',
                            $enrollment->id
                        )
                            ->where(
                                'teaching_assignment_id',
                                $ta->id
                            )
                            ->where(
                                'grading_period_id',
                                $currentPeriod->id
                            )
                            ->exists()
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback Assignment
        |--------------------------------------------------------------------------
        */
        if (!$assignment) {
            $assignment = $teacherAssignments
                ->firstWhere(
                    'section_id',
                    $enrollment->section_id
                );
        }

        abort_unless($assignment, 403);

        /*
        |--------------------------------------------------------------------------
        | Risk Analysis
        |--------------------------------------------------------------------------
        */
        $riskData = $currentPeriod
            ? $this->riskScoreService->calculateRiskScore(
                $enrollment,
                $assignment,
                $currentPeriod
            )
            : $this->riskScoreService->getDefaultRiskScore();

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */
        $attendanceRate = $currentPeriod
            ? $this->riskScoreService->getAttendanceRate(
                $enrollment->id,
                $currentPeriod->id
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Grade Records
        |--------------------------------------------------------------------------
        */
        $grades = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $assignment->id
            )
            ->whereHas(
                'gradingPeriod',
                fn ($q) => $q->where(
                    'sequence',
                    '<=',
                    3
                )
            )
            ->with('gradingPeriod')
            ->get()
            ->sortBy(
                fn ($grade) =>
                    $grade->gradingPeriod->sequence ?? 99
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Current Grade
        |--------------------------------------------------------------------------
        */
        $currentGrade = null;

        if ($currentPeriod) {
            $currentTermGrades = $grades
                ->where(
                    'grading_period_id',
                    $currentPeriod->id
                )
                ->whereNotNull('transmuted_grade');

            if ($currentTermGrades->isNotEmpty()) {
                $currentGrade = round(
                    $currentTermGrades->avg('transmuted_grade'),
                    1
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Grade History
        |--------------------------------------------------------------------------
        */
        $gradeHistory = [];

        foreach ([1, 2, 3] as $term) {

            $termGrades = $grades
                ->filter(
                    fn ($grade) =>
                        ($grade->gradingPeriod->sequence ?? null)
                        == $term
                )
                ->whereNotNull('transmuted_grade');

            $average = $termGrades->isNotEmpty()
                ? $termGrades->avg('transmuted_grade')
                : null;

            $gradeHistory[] = [
                'term' => 'Term ' . $term,
                'average' => $average !== null
                    ? round($average, 1)
                    : null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Active Assessments
        |--------------------------------------------------------------------------
        |
        | These are used only to determine missing grade entries.
        |
        */
        $activeAssessments = $currentPeriod
            ? Assessment::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where(
                    'grading_period_id',
                    $currentPeriod->id
                )
                ->where('status', 'active')
                ->get()
            : collect();

        /*
        |--------------------------------------------------------------------------
        | Recorded Assessment Scores
        |--------------------------------------------------------------------------
        */
        $recordedAssessmentIds = collect();

        if ($activeAssessments->isNotEmpty()) {
            $recordedAssessmentIds = StudentAssessmentScore::where(
                'enrollment_id',
                $enrollment->id
            )
                ->whereIn(
                    'assessment_id',
                    $activeAssessments->pluck('id')
                )
                ->whereNotNull('score')
                ->pluck('assessment_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Missing Grade Entries
        |--------------------------------------------------------------------------
        */
        $missingGrades = $activeAssessments
            ->whereNotIn(
                'id',
                $recordedAssessmentIds
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Below Passing Grades
        |--------------------------------------------------------------------------
        */
        $belowPassing = $grades
            ->filter(
                fn ($grade) =>
                    $grade->transmuted_grade !== null
                    && $grade->transmuted_grade < 75
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Grade Trend
        |--------------------------------------------------------------------------
        */
        $trend = 'No Previous Data';

        if ($currentPeriod) {

            $gradeComparison =
                $this->riskScoreService->getGradeComparison(
                    $enrollment,
                    $assignment,
                    $currentPeriod
                );

            if ($gradeComparison['has_previous_data']) {

                $current =
                    $gradeComparison['current_grade'];

                $previous =
                    $gradeComparison['previous_grade'];

                if ($current > $previous) {
                    $trend = 'Improving';
                } elseif ($current < $previous) {
                    $trend = 'Declining';
                } else {
                    $trend = 'Stable';
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Risk Indicator Definitions
        |--------------------------------------------------------------------------
        */
        $indicatorDefinitions = [

            'low_grade' => [
                'label' => 'Low Grade',
                'description' =>
                    'The current academic performance is below the expected passing level.',
                'icon' => 'fa-chart-line',
            ],

            'missing_grades' => [
                'label' => 'Missing Grades',
                'description' =>
                    'One or more expected grade records are not yet available.',
                'icon' => 'fa-file-circle-exclamation',
            ],

            'low_attendance' => [
                'label' => 'Low Attendance',
                'description' =>
                    'Attendance records indicate that the student may need attendance monitoring.',
                'icon' => 'fa-calendar-xmark',
            ],

            'declining_performance' => [
                'label' => 'Declining Performance',
                'description' =>
                    'Recent academic performance is lower than the previous available period.',
                'icon' => 'fa-arrow-trend-down',
            ],
        ];

        $activeIndicators = [];

        foreach (
            ($riskData['indicators'] ?? [])
            as $key => $isActive
        ) {
            if (
                $isActive
                && isset($indicatorDefinitions[$key])
            ) {
                $activeIndicators[$key] =
                    $indicatorDefinitions[$key];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Attendance
        |--------------------------------------------------------------------------
        |
        | Keep this limited to recent IN scans for the compact
        | At-Risk Monitoring view.
        |
        */
        $recentAttendance = AttendanceLog::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where('scan_type', 'IN')
            ->orderByDesc('scan_time')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Teacher Risk Remarks
        |--------------------------------------------------------------------------
        */
        $riskRemarks = RiskRemark::where(
            'enrollment_id',
            $enrollment->id
        )
            ->with('teacher')
            ->orderByDesc('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Student / Subject Information
        |--------------------------------------------------------------------------
        */
        $subjectName =
            $assignment->subject->name
            ?? 'Assigned Subject';

        $selectedGrade =
            $enrollment->section->grade_level
            ?? null;

        $selectedSection =
            $enrollment->section->name
            ?? null;

        $selectedRiskLevel =
            $riskData['risk_level']
            ?? null;

        $studentName =
            $enrollment->student->full_name
            ?? 'Unknown Student';

        $studentNumber =
            $enrollment->student->student_number
            ?? 'N/A';

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */
        return view('pov.teacher.at-risk.at-risk-student-detail',
            [
                'enrollment' => $enrollment,
                'assignment' => $assignment,

                'subjectName' => $subjectName,
                'currentPeriod' => $currentPeriod,

                'riskData' => $riskData,
                'activeIndicators' => $activeIndicators,

                'attendanceRate' => $attendanceRate,

                'currentGrade' => $currentGrade,

                'missingGrades' => $missingGrades,
                'belowPassing' => $belowPassing,

                'trend' => $trend,
                'gradeHistory' => $gradeHistory,

                'recentAttendance' => $recentAttendance,

                'riskRemarks' => $riskRemarks,

                'selectedGrade' => $selectedGrade,
                'selectedSection' => $selectedSection,
                'selectedRiskLevel' => $selectedRiskLevel,

                'studentName' => $studentName,
                'studentNumber' => $studentNumber,
            ]
        );
    }

    /**
     * Store Teacher Risk Remark
     */
    public function storeRemark(
        Request $request,
        int $enrollmentId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail(
            $enrollmentId
        );

        /*
        |--------------------------------------------------------------------------
        | Verify Teacher Access
        |--------------------------------------------------------------------------
        */
        $teacherSectionIds = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless(
            $teacherSectionIds->contains(
                $enrollment->section_id
            ),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Remark
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'remark' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Remark
        |--------------------------------------------------------------------------
        */
        RiskRemark::create([
            'enrollment_id' =>
                $enrollment->id,

            'teacher_id' =>
                $teacher->id,

            'remark' =>
                $validated['remark'],
        ]);

        return redirect()
            ->route(
                'teacher.grading-system.at-risk.show',
                $enrollment->id
            )
            ->with(
                'success',
                'Remark added.'
            );
    }

    /**
     * Update Teacher Risk Remark
     */
    public function updateRemark(
        Request $request,
        int $enrollmentId,
        int $remarkId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        /*
        |--------------------------------------------------------------------------
        | Verify Teacher Access
        |--------------------------------------------------------------------------
        */
        $teacherSectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless(
            $teacherSectionIds->contains($enrollment->section_id),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Verify Remark Ownership
        |--------------------------------------------------------------------------
        */
        $remark = RiskRemark::where('id', $remarkId)
            ->where('enrollment_id', $enrollment->id)
            ->firstOrFail();

        abort_unless($remark->teacher_id === $teacher->id, 403);

        /*
        |--------------------------------------------------------------------------
        | Validate Remark
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'remark' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Existing Remark Record (No Duplicates)
        |--------------------------------------------------------------------------
        */
        $remark->update([
            'remark' => $validated['remark'],
        ]);

        return redirect()
            ->route(
                'teacher.grading-system.at-risk.show',
                $enrollment->id
            )
            ->with(
                'success',
                'Remark updated successfully.'
            );
    }

    /**
     * Delete Teacher Risk Remark
     */
    public function destroyRemark(
        Request $request,
        int $enrollmentId,
        int $remarkId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        /*
        |--------------------------------------------------------------------------
        | Verify Teacher Access
        |--------------------------------------------------------------------------
        */
        $teacherSectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless(
            $teacherSectionIds->contains($enrollment->section_id),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Verify Remark Ownership
        |--------------------------------------------------------------------------
        */
        $remark = RiskRemark::where('id', $remarkId)
            ->where('enrollment_id', $enrollment->id)
            ->firstOrFail();

        abort_unless($remark->teacher_id === $teacher->id, 403);

        /*
        |--------------------------------------------------------------------------
        | Delete Remark
        |--------------------------------------------------------------------------
        */
        $remark->delete();

        return redirect()
            ->route(
                'teacher.grading-system.at-risk.show',
                $enrollment->id
            )
            ->with(
                'success',
                'Remark deleted successfully.'
            );
    }

    /**
     * Get student's average grade for a specific
     * teaching assignment and grading period.
     */
    private function getStudentAverageGrade(
        int $enrollmentId,
        int $teachingAssignmentId,
        int $gradingPeriodId
    ): ?float {
        $grades = TermGrade::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignmentId
            )
            ->where(
                'grading_period_id',
                $gradingPeriodId
            )
            ->whereNotNull('transmuted_grade')
            ->get();

        if ($grades->isEmpty()) {
            return null;
        }

        return round(
            $grades->avg('transmuted_grade'),
            1
        );
    }
}