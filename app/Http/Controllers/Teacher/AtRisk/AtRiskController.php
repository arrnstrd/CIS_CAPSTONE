<?php

namespace App\Http\Controllers\Teacher\AtRisk;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\TermGrade;
use App\Models\RiskFollowUp;
use App\Models\RiskRemark;
use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Mail\StudentRiskInterventionMail;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        | Monitoring Follow-Ups
        |--------------------------------------------------------------------------
        */
        $followUps = RiskFollowUp::where(
            'enrollment_id',
            $enrollment->id
        )
            ->with('teacher')
            ->orderByDesc('follow_up_date')
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
                'followUps' => $followUps,

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
     * Store Teacher Monitoring Follow-Up
     */
    public function storeFollowUp(
        Request $request,
        int $enrollmentId
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
        | Validate Follow-Up
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'follow_up_date' => [
                'required',
                'date',
            ],
            'intervention' => [
                'required',
                'string',
                'max:255',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'status' => [
                'required',
                'string',
                'in:Completed,Ongoing',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Follow-Up Record
        |--------------------------------------------------------------------------
        */
        RiskFollowUp::create([
            'enrollment_id' => $enrollment->id,
            'teacher_id' => $teacher->id,
            'follow_up_date' => $validated['follow_up_date'],
            'intervention' => $validated['intervention'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route(
                'teacher.grading-system.at-risk.show',
                $enrollment->id
            )
            ->with(
                'success',
                'Follow-up record added successfully.'
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

    /**
     * Prepare student intervention data and email preview(s).
     */
    public function prepareIntervention(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. Teacher profile not found.',
            ], 403);
        }

        $rawIds = $request->input('enrollment_ids', $request->input('enrollment_id'));

        if (!$rawIds) {
            return response()->json([
                'success' => false,
                'message' => 'No student was selected for intervention.',
            ], 422);
        }

        $enrollmentIds = is_array($rawIds) ? $rawIds : [$rawIds];
        $enrollmentIds = array_filter(array_map('intval', $enrollmentIds));

        if (empty($enrollmentIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student selection.',
            ], 422);
        }

        $currentPeriod = GradingPeriod::where('is_active', true)
            ->trimester()
            ->orderBy('sequence')
            ->first()
            ?? GradingPeriod::trimester()->orderBy('sequence')->first();

        $teacherAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $students = [];

        foreach ($enrollmentIds as $id) {
            $enrollment = Enrollment::with(['student.guardian', 'section'])->find($id);

            if (!$enrollment) {
                continue;
            }

            $data = $this->buildStudentInterventionData($enrollment, $teacher, $currentPeriod, $teacherAssignments);

            if ($data) {
                $students[] = $data;
            }
        }

        if (empty($students)) {
            return response()->json([
                'success' => false,
                'message' => 'Could not find or authorize the selected student(s).',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'count' => count($students),
            'students' => $students,
        ]);
    }

    /**
     * Send intervention email(s) and record follow-ups.
     */
    public function sendIntervention(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. Teacher profile not found.',
            ], 403);
        }

        $validated = $request->validate([
            'interventions' => ['required', 'array', 'min:1'],
            'interventions.*.enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'interventions.*.teacher_note' => ['nullable', 'string', 'max:2000'],
            'interventions.*.language' => ['nullable', 'string', 'in:en,tl'],
        ]);

        $currentPeriod = GradingPeriod::where('is_active', true)
            ->trimester()
            ->orderBy('sequence')
            ->first()
            ?? GradingPeriod::trimester()->orderBy('sequence')->first();

        $teacherAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($validated['interventions'] as $item) {
            $enrollmentId = (int) $item['enrollment_id'];
            $teacherNote = !empty($item['teacher_note']) ? trim((string) $item['teacher_note']) : null;
            $lang = (!empty($item['language']) && in_array($item['language'], ['en', 'tl'], true)) ? $item['language'] : 'en';

            $enrollment = Enrollment::with(['student.guardian', 'section'])->find($enrollmentId);

            if (!$enrollment) {
                $failedCount++;
                $errors[] = "Enrollment #{$enrollmentId} not found.";
                continue;
            }

            $data = $this->buildStudentInterventionData($enrollment, $teacher, $currentPeriod, $teacherAssignments);

            if (!$data) {
                $failedCount++;
                $errors[] = "Unauthorized access to student: " . ($enrollment->student?->full_name ?? "#{$enrollmentId}");
                continue;
            }

            if (empty($data['guardian_email']) || !filter_var($data['guardian_email'], FILTER_VALIDATE_EMAIL)) {
                $failedCount++;
                $errors[] = "No valid parent/guardian email on file for {$data['student_name']}.";
                continue;
            }

            // Apply selected language package
            if (isset($data['languages'][$lang])) {
                $data['language'] = $lang;
                $data['subject'] = $data['languages'][$lang]['subject'];
                $data['message_body'] = $data['languages'][$lang]['message_body'];
                $data['salutation'] = $data['languages'][$lang]['salutation'];
                $data['risk_condition_label'] = $data['languages'][$lang]['condition_label'];
                $data['closing'] = $data['languages'][$lang]['closing'];
                $data['teacher_note_label'] = $data['languages'][$lang]['teacher_note_label'];
                $data['status_label'] = $data['languages'][$lang]['status_label'];
            }

            try {
                // Send intervention email
                Mail::to($data['guardian_email'])->send(new StudentRiskInterventionMail($data, $teacherNote));

                // Log RiskFollowUp
                $langLabel = $lang === 'tl' ? 'Tagalog' : 'English';
                RiskFollowUp::create([
                    'enrollment_id' => $enrollment->id,
                    'teacher_id' => $teacher->id,
                    'follow_up_date' => now()->toDateString(),
                    'intervention' => 'Parent Email Notice (' . $data['risk_condition_label'] . ' - ' . $langLabel . ')',
                    'notes' => 'Dispatched ' . $langLabel . ' intervention notice to parent (' . $data['guardian_email'] . ')'
                        . ($teacherNote ? "\nTeacher Note: " . $teacherNote : ''),
                    'status' => 'Completed',
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                Log::error("Failed to send intervention email for enrollment #{$enrollmentId}: " . $e->getMessage());
                $failedCount++;
                $errors[] = "Failed to send email to guardian of {$data['student_name']}. Please try again later.";
            }
        }

        $message = $sentCount > 0
            ? ($sentCount === 1 ? 'Intervention email sent successfully.' : "{$sentCount} intervention emails sent successfully.")
            : 'Could not send intervention email(s).';

        return response()->json([
            'success' => $sentCount > 0,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'message' => $message,
            'errors' => $errors,
        ]);
    }

    /**
     * Build isolated student intervention payload with risk conditions and template messages.
     */
    private function buildStudentInterventionData(
        Enrollment $enrollment,
        $teacher,
        $currentPeriod = null,
        $teacherAssignments = null
    ): ?array {
        if (!$teacherAssignments) {
            $teacherAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->get();
        }

        $teacherSectionIds = $teacherAssignments->pluck('section_id')->unique();

        if (!$teacherSectionIds->contains($enrollment->section_id)) {
            return null;
        }

        // Determine assignment
        $assignment = null;
        if ($currentPeriod) {
            $assignment = $teacherAssignments
                ->filter(fn ($ta) => (int) $ta->section_id === (int) $enrollment->section_id)
                ->first(fn ($ta) => TermGrade::where('enrollment_id', $enrollment->id)
                    ->where('teaching_assignment_id', $ta->id)
                    ->where('grading_period_id', $currentPeriod->id)
                    ->exists()
                );
        }

        if (!$assignment) {
            $assignment = $teacherAssignments->firstWhere('section_id', $enrollment->section_id);
        }

        $riskData = ($currentPeriod && $assignment)
            ? $this->riskScoreService->calculateRiskScore($enrollment, $assignment, $currentPeriod)
            : $this->riskScoreService->getDefaultRiskScore();

        $attendanceRate = $currentPeriod
            ? $this->riskScoreService->getAttendanceRate($enrollment->id, $currentPeriod->id)
            : null;

        $currentGrade = null;
        if ($currentPeriod && $assignment) {
            $currentGrade = $this->getStudentAverageGrade($enrollment->id, $assignment->id, $currentPeriod->id);
        }

        $rawIndicators = $riskData['indicators'] ?? [];
        $activeIndicators = [];
        $indicatorLabels = [
            'low_grade' => 'Low Grade',
            'missing_grades' => 'Missing Grades',
            'low_attendance' => 'Low Attendance',
            'declining_performance' => 'Declining Performance',
        ];

        foreach ($rawIndicators as $ind => $isTrue) {
            if ($isTrue && isset($indicatorLabels[$ind])) {
                $activeIndicators[] = $indicatorLabels[$ind];
            }
        }

        // Evaluate risk conditions
        $isAcademic = !empty($rawIndicators['low_grade'])
            || !empty($rawIndicators['missing_grades'])
            || !empty($rawIndicators['declining_performance'])
            || ($currentGrade !== null && $currentGrade < 75);

        $isAttendance = !empty($rawIndicators['low_attendance'])
            || ($attendanceRate !== null && $attendanceRate < 85);

        if ($isAcademic && $isAttendance) {
            $riskCondition = 'dual';
        } elseif ($isAttendance) {
            $riskCondition = 'attendance';
        } else {
            $riskCondition = 'academic';
        }

        $student = $enrollment->student;
        $studentName = $student?->full_name ?? 'Unknown Student';
        $studentNumber = $student?->student_number ?? 'N/A';
        $sectionName = $enrollment->section?->name ?? 'N/A';
        $gradeLevel = $enrollment->section?->grade_level ?? 'N/A';
        $subjectName = $assignment?->subject?->name ?? 'Assigned Subject';

        $guardian = $student?->guardian;
        $guardianName = $guardian?->name ?? 'Parent / Guardian';
        $guardianEmail = $guardian?->email ?? null;
        $guardianRelationship = $guardian?->relationship ?? 'Parent/Guardian';
        $hasGuardianEmail = !empty($guardianEmail) && filter_var($guardianEmail, FILTER_VALIDATE_EMAIL) !== false;
        $teacherName = $teacher->full_name ?? 'Teacher';

        // 1. English Version Templates
        if ($riskCondition === 'academic') {
            $subjectEn = "Academic Support Notice: {$studentName} - {$subjectName}";
            $messageBodyEn = "We are reaching out to inform you regarding the current academic standing of your child, {$studentName} (Grade {$gradeLevel} - {$sectionName}), in {$subjectName}.\n\n"
                . "Our academic records indicate that {$studentName} is currently experiencing learning challenges"
                . ($currentGrade !== null ? ", with a recorded grade average of {$currentGrade}%" : "")
                . (!empty($rawIndicators['missing_grades']) ? ", alongside missing assessment submissions" : "")
                . (!empty($rawIndicators['declining_performance']) ? ", and a recent decline in performance" : "")
                . ".\n\n"
                . "We believe that prompt collaboration between home and school can significantly help {$studentName} improve. We encourage you to discuss these academic areas at home. Our faculty is readily available for guidance, consultation, and remedial assistance.";
            $salutationEn = "Dear " . $guardianName;
            $conditionLabelEn = "Academic Risk";
        } elseif ($riskCondition === 'attendance') {
            $subjectEn = "Attendance Monitoring Notice: {$studentName} - Grade {$gradeLevel} {$sectionName}";
            $messageBodyEn = "We are reaching out to bring to your attention the attendance record of your child, {$studentName} (Grade {$gradeLevel} - {$sectionName}).\n\n"
                . "Our attendance monitoring system reflects a current attendance rate of " . ($attendanceRate !== null ? "{$attendanceRate}%" : "below standard") . ", which has fallen below our expected threshold. Regular classroom attendance and participation are crucial to academic progress.\n\n"
                . "We kindly ask for your cooperation in ensuring regular school attendance. If {$studentName} is experiencing any health, transport, or family difficulties, please inform us so that we may extend necessary accommodations and support.";
            $salutationEn = "Dear " . $guardianName;
            $conditionLabelEn = "Attendance Risk";
        } else { // dual
            $subjectEn = "Important Academic & Attendance Notice: {$studentName} - {$subjectName}";
            $messageBodyEn = "We are writing to share an important monitoring update regarding your child, {$studentName} (Grade {$gradeLevel} - {$sectionName}), in {$subjectName}.\n\n"
                . "Our continuous tracking shows that {$studentName} is currently facing challenges in both attendance and academic performance. Specifically, the recorded attendance rate is " . ($attendanceRate !== null ? "{$attendanceRate}%" : "below standard")
                . ($currentGrade !== null ? " and the current subject grade stands at {$currentGrade}%" : "") . ".\n\n"
                . "Because frequent absences directly affect daily classroom instruction and test scores, urgent coordinated support between school and home is essential. We encourage you to connect with us at your earliest opportunity so we can establish an intervention and recovery plan together.";
            $salutationEn = "Dear " . $guardianName;
            $conditionLabelEn = "Academic + Attendance Risk";
        }

        // 2. Tagalog / Filipino Version Templates (Polite, natural Philippine school communication, not overly archaic)
        if ($riskCondition === 'academic') {
            $subjectTl = "Abiso sa Academic Standing: {$studentName} - {$subjectName}";
            $messageBodyTl = "Nais po naming ipagbigay-alam sa inyo ang kasalukuyang academic standing ng inyong anak na si {$studentName} (Grade {$gradeLevel} - {$sectionName}) sa asignaturang {$subjectName}.\n\n"
                . "Ayon po sa aming academic monitoring records, kasalukuyang nangangailangan ng karagdagang gabay si {$studentName}"
                . ($currentGrade !== null ? ", na may naitalang grade average na {$currentGrade}%" : "")
                . (!empty($rawIndicators['missing_grades']) ? ", kasama ang mga assessment na hindi pa naisusumite" : "")
                . (!empty($rawIndicators['declining_performance']) ? ", at pagbaba ng kanyang performance sa klase" : "")
                . ".\n\n"
                . "Naniniwala po kami na sa pamamagitan ng maagang pagtutulungan ng paaralan at ng inyong tahanan, mas madaling makakabawi si {$studentName}. Hinihikayat po namin kayo na kausapin at subaybayan siya sa kanyang mga gawain. Handa po ang aming guro para sa consultation at remedial support.";
            $salutationTl = "Magandang araw po, " . $guardianName;
            $conditionLabelTl = "Academic Risk (Nangangailangan ng Gabay)";
        } elseif ($riskCondition === 'attendance') {
            $subjectTl = "Abiso sa Attendance: {$studentName} - Grade {$gradeLevel} {$sectionName}";
            $messageBodyTl = "Nais po naming ipaalam sa inyo ang ulat patungkol sa attendance ng inyong anak na si {$studentName} (Grade {$gradeLevel} - {$sectionName}).\n\n"
                . "Ayon po sa aming attendance monitoring, ang kanyang attendance rate sa kasalukuyan ay nasa " . ($attendanceRate !== null ? "{$attendanceRate}%" : "mababa sa standard") . ", na mas mababa sa inaasahang attendance standard ng paaralan. Mahalaga po ang regular na pagpasok sa klase upang makasabay sa araw-araw na talakayan at mga pagsusulit.\n\n"
                . "Hinihiling po namin ang inyong tulong at pakikipagtulungan upang matiyak ang kanyang regular na pagpasok. Kung may nararanasan pong karamdaman o anumang suliranin si {$studentName}, mangyaring ipagbigay-alam po sa amin upang makapagbigay kami ng kaukulang tulong.";
            $salutationTl = "Magandang araw po, " . $guardianName;
            $conditionLabelTl = "Attendance Risk (Mababang Pagpasok)";
        } else { // dual
            $subjectTl = "Mahalagang Abiso sa Pag-aaral at Attendance: {$studentName} - {$subjectName}";
            $messageBodyTl = "Nais po naming magbahagi ng mahalagang monitoring update tungkol sa inyong anak na si {$studentName} (Grade {$gradeLevel} - {$sectionName}) sa asignaturang {$subjectName}.\n\n"
                . "Ipinapakita po ng aming monitoring na kasalukuyang nahaharap sa mga hamon si {$studentName} sa parehong attendance at academic performance. Sa kasalukuyan, ang kanyang attendance rate ay nasa " . ($attendanceRate !== null ? "{$attendanceRate}%" : "mababa sa standard")
                . ($currentGrade !== null ? " at ang kanyang subject grade ay nasa {$currentGrade}%" : "") . ".\n\n"
                . "Dahil ang madalas na pagliban ay may direktang epekto sa kanyang pagkatuto sa klase at mga pagsusulit, napakahalaga po ng maagap na pagtutulungan natin. Hinihikayat po namin kayo na makipag-ugnayan sa amin sa lalong madaling panahon upang makabuo tayo ng magkatuwang na intervention at recovery plan para kay {$studentName}.";
            $salutationTl = "Magandang araw po, " . $guardianName;
            $conditionLabelTl = "Academic + Attendance Risk (Dual Risk)";
        }

        return [
            'enrollment_id' => $enrollment->id,
            'student_name' => $studentName,
            'student_number' => $studentNumber,
            'grade_level' => $gradeLevel,
            'section_name' => $sectionName,
            'subject_name' => $subjectName,
            'teacher_name' => $teacherName,
            'guardian_name' => $guardianName,
            'guardian_email' => $guardianEmail,
            'guardian_relationship' => $guardianRelationship,
            'has_guardian_email' => $hasGuardianEmail,
            'current_grade' => $currentGrade,
            'attendance_rate' => $attendanceRate,
            'risk_level' => $riskData['risk_level'] ?? 'Moderate',
            'risk_score' => $riskData['risk_score'] ?? 0,
            'active_indicators' => $activeIndicators,
            'risk_condition' => $riskCondition,
            'risk_condition_label' => $conditionLabelEn,
            'language' => 'en',
            'subject' => $subjectEn,
            'message_body' => $messageBodyEn,
            'salutation' => $salutationEn,
            'languages' => [
                'en' => [
                    'subject' => $subjectEn,
                    'message_body' => $messageBodyEn,
                    'salutation' => $salutationEn,
                    'condition_label' => $conditionLabelEn,
                    'closing' => "Warm regards,\n{$teacherName}\nSubject Teacher / Faculty\nConcepcion Integrated School",
                    'teacher_note_label' => "Note from Teacher ({$teacherName}):",
                    'status_label' => "Status: {$conditionLabelEn}",
                ],
                'tl' => [
                    'subject' => $subjectTl,
                    'message_body' => $messageBodyTl,
                    'salutation' => $salutationTl,
                    'condition_label' => $conditionLabelTl,
                    'closing' => "Lubos na gumagalang,\n{$teacherName}\nSubject Teacher / Faculty\nConcepcion Integrated School",
                    'teacher_note_label' => "Mensahe mula sa Guro ({$teacherName}):",
                    'status_label' => "Katayuan: {$conditionLabelTl}",
                ],
            ],
        ];
    }
}