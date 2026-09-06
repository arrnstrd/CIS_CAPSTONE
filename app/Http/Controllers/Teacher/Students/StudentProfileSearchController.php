<?php

namespace App\Http\Controllers\Teacher\Students;

use App\Http\Controllers\Controller;
use App\Models\AcademicNote;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\TermGrade;
use App\Models\TeachingAssignment;
use App\Models\GradingPeriod;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Services\Grading\GradingPeriodService;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentProfileSearchController extends Controller
{
    protected RiskScoreService $riskScoreService;
    protected GradingPeriodService $gradingPeriodService;

    public function __construct(
        RiskScoreService $riskScoreService,
        GradingPeriodService $gradingPeriodService
    ) {
        $this->riskScoreService = $riskScoreService;
        $this->gradingPeriodService = $gradingPeriodService;
    }

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $query = trim($request->input('q', ''));
        $gradeLevel = $request->input('grade_level');
        $sectionId = $request->input('section_id');

        /*
        |--------------------------------------------------------------------------
        | Teacher Scope
        |--------------------------------------------------------------------------
        */

        $teachingAssignments = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $sections = $teachingAssignments
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy([
                ['grade_level', 'asc'],
                ['name', 'asc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Filter Available Sections
        |--------------------------------------------------------------------------
        */

        $filteredSectionIds = $sections
            ->when(
                filled($gradeLevel),
                fn ($collection) =>
                    $collection->where('grade_level', $gradeLevel)
            )
            ->when(
                filled($sectionId),
                fn ($collection) =>
                    $collection->where('id', (int) $sectionId)
            )
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Enrollments
        |--------------------------------------------------------------------------
        */

        $enrollments = Enrollment::whereIn(
            'section_id',
            $filteredSectionIds
        )
            ->where('status', 'active')
            ->with(['student', 'section'])
            ->get();

        $currentPeriod = $this->gradingPeriodService->resolveSelectedPeriod($request, $teacher);

        $results = collect();

        foreach ($enrollments as $enrollment) {

            $student = $enrollment->student;

            if (! $student) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            if ($query !== '') {

                $fullName = strtolower(
                    $student->full_name ?? ''
                );

                $studentNumber = strtolower(
                    $student->student_number ?? ''
                );

                $needle = strtolower($query);

                if (
                    ! str_contains($fullName, $needle)
                    && ! str_contains($studentNumber, $needle)
                ) {
                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Teaching Assignment
            |--------------------------------------------------------------------------
            */

            $assignment = $teachingAssignments
                ->firstWhere(
                    'section_id',
                    $enrollment->section_id
                );

            if (! $assignment) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Grades
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
                    fn ($q) =>
                        $q->trimester()
                            ->where('period_type', 'trimester')
                )
                ->get();

            $avgGrade = $grades->count()
                ? round(
                    $grades
                        ->whereNotNull('transmuted_grade')
                        ->avg('transmuted_grade'),
                    1
                )
                : null;

            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            $hasQrAttendance = AttendanceLog::where(
                'enrollment_id',
                $enrollment->id
            )
                ->where('scan_type', 'IN')
                ->exists();

            $attendanceRate = $hasQrAttendance && $currentPeriod
                ? $this->riskScoreService->getAttendanceRate(
                    $enrollment->id,
                    $currentPeriod->id
                )
                : 0.0;

            /*
            |--------------------------------------------------------------------------
            | Risk
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
            | Trend
            |--------------------------------------------------------------------------
            */

            $trend = 'N/A';

            if ($currentPeriod) {
                $gradeComparison = $this->riskScoreService->getGradeComparison(
                    $enrollment,
                    $assignment,
                    $currentPeriod
                );

                $trend = $gradeComparison['trend'];
            }

            /*
            |--------------------------------------------------------------------------
            | Subjects handled by this teacher
            |--------------------------------------------------------------------------
            */

            $subjectNames = $teachingAssignments
                ->where(
                    'section_id',
                    $enrollment->section_id
                )
                ->pluck('subject.name')
                ->filter()
                ->unique()
                ->implode(', ');

            $results->push((object) [

                'enrollment_id' =>
                    $enrollment->id,

                'name' =>
                    $student->full_name,

                'student_number' =>
                    $student->student_number,

                'lrn' =>
                    $student->lrn,

                'grade_level' =>
                    $enrollment->section->grade_level,

                'section_name' =>
                    $enrollment->section->name,

                'section_id' =>
                    $enrollment->section_id,

                'subjects' =>
                    $subjectNames,

                'average' =>
                    $avgGrade,

                'attendance' =>
                    $attendanceRate,

                'trend' =>
                    $trend,

                'risk_level' =>
                    $riskData['risk_level'],
            ]);
        }

        $results = $results
            ->sortBy([
                ['grade_level', 'asc'],
                ['section_name', 'asc'],
                ['name', 'asc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Grade Levels
        |--------------------------------------------------------------------------
        */

        $gradeLevels = $sections
            ->pluck('grade_level')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Available Sections
        |--------------------------------------------------------------------------
        */

        $availableSections = $sections
            ->when(
                filled($gradeLevel),
                fn ($collection) =>
                    $collection->where(
                        'grade_level',
                        $gradeLevel
                    )
            )
            ->values();

        return view('pov.teacher.student-management.student-profile-search',
            compact(
                'query',
                'results',
                'gradeLevels',
                'sections',
                'availableSections',
                'gradeLevel',
                'sectionId',
                'currentPeriod'
            )
        );
    }

    public function show(
        Request $request,
        int $enrollmentId
    ) {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        /*
        |--------------------------------------------------------------------------
        | Verify Teacher Access
        |--------------------------------------------------------------------------
        */

        $enrollment = Enrollment::with([
            'student',
            'section'
        ])->findOrFail($enrollmentId);

        $teachingSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $advisedSectionIds = \App\Models\Section::query()
            ->where('advisor_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('id');

        $teacherSectionIds = $teachingSectionIds
            ->concat($advisedSectionIds)
            ->unique()
            ->values()
            ->all();

        $isInTeacherClass = $enrollment->student?->enrollments()
            ->whereIn('section_id', $teacherSectionIds)
            ->where('status', 'active')
            ->exists();

        abort_unless($isInTeacherClass, 403, 'You can only view students in your own classes.');

        /*
        |--------------------------------------------------------------------------
        | Student's Active Enrollments
        |--------------------------------------------------------------------------
        */

        $allEnrollments = Enrollment::where(
            'student_id',
            $enrollment->student_id
        )
            ->where('status', 'active')
            ->with('section')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Teacher Assignments for Student's Sections
        |--------------------------------------------------------------------------
        */

        $studentSectionIds = $allEnrollments
            ->pluck('section_id')
            ->unique();

        $allTeachingAssignments = TeachingAssignment::where(
            'teacher_id',
            $teacher->id
        )
            ->where('status', 'active')
            ->whereIn(
                'section_id',
                $studentSectionIds
            )
            ->with([
                'subject',
                'section'
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Valid Terms
        |--------------------------------------------------------------------------
        */

        $terms = GradingPeriod::where(
            'sequence',
            '<=',
            3
        )
            ->where(
                'period_type',
                'trimester'
            )
            ->orderBy('sequence')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Grades
        |--------------------------------------------------------------------------
        */

        $allTaIds = $allTeachingAssignments
            ->pluck('id');

        $allGrades = TermGrade::whereIn(
            'teaching_assignment_id',
            $allTaIds
        )
            ->where(
                'enrollment_id',
                $enrollment->id
            )
            ->whereHas(
                'gradingPeriod',
                fn ($q) =>
                    $q->where(
                        'sequence',
                        '<=',
                        3
                    )->where(
                        'period_type',
                        'trimester'
                    )
            )
            ->with('gradingPeriod')
            ->get()
            ->groupBy(
                'teaching_assignment_id'
            );

        /*
        |--------------------------------------------------------------------------
        | Subjects / Grade Summary
        |--------------------------------------------------------------------------
        */

        $allSubjects = $allTeachingAssignments
            ->map(function ($ta) use ($allGrades, $terms) {

                $subjectGradesMap = $allGrades
                    ->get(
                        $ta->id,
                        collect()
                    )
                    ->keyBy(
                        fn ($g) =>
                            $g->gradingPeriod->sequence ?? 0
                    );

                $subjectGrades = $terms
                    ->map(function ($term) use ($subjectGradesMap) {
                        $gradeRecord = $subjectGradesMap->get($term->sequence);

                        return (object) [
                            'term_label' => 'Term ' . $term->sequence,
                            'grade' => $gradeRecord?->transmuted_grade,
                            'grading_period_id' => $term->id,
                            'sequence' => $term->sequence,
                        ];
                    })
                    ->values();

                return (object) [

                    'teaching_assignment_id' =>
                        $ta->id,

                    'subject_name' =>
                        $ta->subject->name,

                    'grade_level' =>
                        $ta->section->grade_level,

                    'section_name' =>
                        $ta->section->name,

                    'periods' =>
                        $subjectGrades,

                    'average' =>
                        $subjectGrades
                            ->filter(
                                fn ($g) =>
                                    $g->grade !== null
                            )
                            ->avg('grade'),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Overall Average
        |--------------------------------------------------------------------------
        */

        $overallAvg = $allSubjects
            ->filter(
                fn ($s) =>
                    $s->average !== null
            )
            ->avg('average');

        /*
        |--------------------------------------------------------------------------
        | Current Term
        |--------------------------------------------------------------------------
        */

        $currentPeriod = GradingPeriod::where(
            'is_active',
            true
        )
            ->where(
                'sequence',
                '<=',
                3
            )
            ->where(
                'period_type',
                'trimester'
            )
            ->orderBy('sequence')
            ->first()
            ?? GradingPeriod::where(
                'sequence',
                '<=',
                3
            )
                ->where(
                    'period_type',
                    'trimester'
                )
                ->orderBy('sequence')
                ->first();

        /*
        |--------------------------------------------------------------------------
        | ASSESSMENT SUMMARY
        |--------------------------------------------------------------------------
        |
        | This is intentionally separate from the Grade Summary.
        |
        | Grade Summary = final/transmuted grades per subject and term.
        |
        | Assessment Summary = individual assessments and student's
        | recorded score for each assessment.
        |
        */

        $assessments = Assessment::whereIn(
            'teaching_assignment_id',
            $allTaIds
        )
            ->where(
                'status',
                'active'
            )
            ->whereHas(
                'gradingPeriod',
                fn ($q) =>
                    $q->where(
                        'sequence',
                        '<=',
                        3
                    )->where(
                        'period_type',
                        'trimester'
                    )
            )
            ->with([
                'assessmentCategory',
                'gradingPeriod',
                'teachingAssignment.subject',
                'teachingAssignment.section',
            ])
            ->with([
                'studentAssessmentScores' => function ($query) use (
                    $enrollmentId
                ) {
                    $query->where(
                        'enrollment_id',
                        $enrollmentId
                    );
                },
            ])
            ->orderBy(
                'grading_period_id'
            )
            ->orderBy(
                'assessment_category_id'
            )
            ->orderBy(
                'slot_number'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Assessment Summary Rows
        |--------------------------------------------------------------------------
        */

        $assessmentSummary = $assessments
            ->map(function ($assessment) {

                $scoreRecord = $assessment
                    ->studentAssessmentScores
                    ->first();

                $categoryName = strtolower(
                    $assessment
                        ->assessmentCategory
                        ->name ?? ''
                );

                if (
                    str_contains(
                        $categoryName,
                        'written'
                    )
                ) {
                    $categoryKey = 'written';
                    $categoryLabel = 'Written Work';
                } elseif (
                    str_contains(
                        $categoryName,
                        'performance'
                    )
                ) {
                    $categoryKey = 'performance';
                    $categoryLabel = 'Performance Task';
                } else {
                    $categoryKey = 'term_assessment';
                    $categoryLabel = 'Term Assessment';
                }

                $score = $scoreRecord?->score;

                $percentage = (
                    $score !== null
                    && $assessment->total_items > 0
                )
                    ? round(
                        (
                            (float) $score
                            / (float) $assessment->total_items
                        ) * 100,
                        1
                    )
                    : null;

                return (object) [

                    'id' =>
                        $assessment->id,

                    'title' =>
                        $assessment->title,

                    'category_key' =>
                        $categoryKey,

                    'category_label' =>
                        $categoryLabel,

                    'subject_name' =>
                        $assessment
                            ->teachingAssignment
                            ->subject
                            ->name
                            ?? '—',

                    'term_label' =>
                        'Term ' .
                        (
                            $assessment
                                ->gradingPeriod
                                ->sequence
                                ?? '—'
                        ),

                    'grading_period_id' =>
                        $assessment->grading_period_id,

                    'assessment_date' =>
                        $assessment->assessment_date,

                    'total_items' =>
                        $assessment->total_items,

                    'score' =>
                        $score,

                    'percentage' =>
                        $percentage,

                    'remarks' =>
                        $scoreRecord?->remarks,

                    'has_score' =>
                        $score !== null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Assessment Counts
        |--------------------------------------------------------------------------
        */

        $assessmentCounts = [

            'total' =>
                $assessmentSummary->count(),

            'recorded' =>
                $assessmentSummary
                    ->where('has_score', true)
                    ->count(),

            'pending' =>
                $assessmentSummary
                    ->where('has_score', false)
                    ->count(),

            'written' =>
                $assessmentSummary
                    ->where(
                        'category_key',
                        'written'
                    )
                    ->count(),

            'performance' =>
                $assessmentSummary
                    ->where(
                        'category_key',
                        'performance'
                    )
                    ->count(),

            'term_assessment' =>
                $assessmentSummary
                    ->where(
                        'category_key',
                        'term_assessment'
                    )
                    ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Attendance Logs
        |--------------------------------------------------------------------------
        */

        $attendanceLogs = AttendanceLog::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'scan_type',
                'IN'
            )
            ->orderBy(
                'scan_time',
                'asc'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendance Rate
        |--------------------------------------------------------------------------
        */

        $hasQrAttendance =
            $attendanceLogs->isNotEmpty();

        $attendanceRate =
            $hasQrAttendance && $currentPeriod
                ? $this->riskScoreService->getAttendanceRate(
                    $enrollmentId,
                    $currentPeriod->id
                )
                : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Section Attendance Tracking Start
        |--------------------------------------------------------------------------
        */

        $sectionTrackingStart = AttendanceLog::whereHas(
            'enrollment',
            fn ($query) =>
                $query->where(
                    'section_id',
                    $enrollment->section_id
                )
        )
            ->where(
                'scan_type',
                'IN'
            )
            ->min('scan_time');

        $sectionTrackingStart =
            $sectionTrackingStart
                ? Carbon::parse(
                    $sectionTrackingStart
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | Present Count
        |--------------------------------------------------------------------------
        */

        $presentQuery = AttendanceLog::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'scan_type',
                'IN'
            );

        if (
            $currentPeriod
            && $currentPeriod->start_date
            && $currentPeriod->end_date
        ) {
            $presentQuery->whereBetween(
                'scan_time',
                [
                    $currentPeriod->start_date,
                    $currentPeriod->end_date,
                ]
            );
        }

        $presentDates = $presentQuery
            ->selectRaw(
                'DATE(scan_time) as attendance_date'
            )
            ->distinct()
            ->get();

        $presentCount =
            $presentDates->count();

        /*
        |--------------------------------------------------------------------------
        | Missing / Below Passing
        |--------------------------------------------------------------------------
        */

        $allGradeRecords = $allGrades->flatten();

        $missingGradesCount = $allGradeRecords
            ->filter(
                fn ($g) =>
                    $g->transmuted_grade === null
            )
            ->count();

        $belowPassingCount = $allGradeRecords
            ->filter(
                fn ($g) =>
                    $g->transmuted_grade !== null
                    && $g->transmuted_grade < 75
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Risk
        |--------------------------------------------------------------------------
        */

        $assignmentForRisk = $allTeachingAssignments
            ->firstWhere(
                'section_id',
                $enrollment->section_id
            );

        $riskData =
            $currentPeriod && $assignmentForRisk
                ? $this->riskScoreService->calculateRiskScore(
                    $enrollment,
                    $assignmentForRisk,
                    $currentPeriod
                )
                : $this->riskScoreService->getDefaultRiskScore();

        /*
        |--------------------------------------------------------------------------
        | Grade History
        |--------------------------------------------------------------------------
        */

        $gradeHistory = [];

        foreach ([1, 2, 3] as $term) {

            $termGrades = $allGradeRecords
                ->filter(
                    fn ($g) =>
                        (
                            $g->gradingPeriod
                                ->sequence
                            ?? null
                        ) == $term
                        && $g->transmuted_grade !== null
                );

            $termAverage =
                $termGrades->avg(
                    'transmuted_grade'
                );

            $gradeHistory[] = [

                'term' =>
                    'Term ' . $term,

                'average' =>
                    $termAverage !== null
                        ? round(
                            $termAverage,
                            1
                        )
                        : null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance History
        |--------------------------------------------------------------------------
        */

        $attendanceVerifications =
            AttendanceVerification::where(
                'enrollment_id',
                $enrollmentId
            )
                ->orderBy(
                    'created_at',
                    'desc'
                )
                ->get()
                ->groupBy(
                    fn ($verification) =>
                        $verification
                            ->attendance_date
                            ->toDateString()
                );

        $attendanceLogsByDate =
            $attendanceLogs->groupBy(
                fn ($log) =>
                    $log
                        ->scan_time
                        ->toDateString()
            );

        /*
        |--------------------------------------------------------------------------
        | Build Attendance Dates
        |--------------------------------------------------------------------------
        */

        $attendanceDates = collect();

        if ($sectionTrackingStart) {

            $attendanceDates =
                $attendanceLogsByDate->keys();

            $validVerificationDates =
                $attendanceVerifications
                    ->keys()
                    ->filter(
                        function ($date)
                        use ($sectionTrackingStart) {

                            $dateCarbon =
                                Carbon::parse(
                                    $date
                                )->startOfDay();

                            return $dateCarbon->gte(
                                $sectionTrackingStart
                                    ->copy()
                                    ->startOfDay()
                            );
                        }
                    );

            $attendanceDates =
                $attendanceDates
                    ->merge(
                        $validVerificationDates
                    );
        }

        $attendanceDates =
            $attendanceDates
                ->unique()
                ->sortDesc()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Attendance History Rows
        |--------------------------------------------------------------------------
        */

        $attendanceHistory =
            $attendanceDates
                ->map(
                    function ($date)
                    use (
                        $attendanceLogsByDate,
                        $attendanceVerifications,
                        $sectionTrackingStart
                    ) {

                        $dateCarbon =
                            Carbon::parse(
                                $date
                            )->startOfDay();

                        if (
                            ! $sectionTrackingStart
                            || $dateCarbon->lt(
                                $sectionTrackingStart
                                    ->copy()
                                    ->startOfDay()
                            )
                        ) {
                            return null;
                        }

                        $logsForDate =
                            $attendanceLogsByDate
                                ->get(
                                    $date,
                                    collect()
                                );

                        $log =
                            $logsForDate
                                ->sortBy(
                                    'scan_time'
                                )
                                ->first();

                        $verification =
                            $attendanceVerifications
                                ->get(
                                    $date,
                                    collect()
                                )
                                ->sortByDesc(
                                    'created_at'
                                )
                                ->first();

                        if ($verification) {

                            $status =
                                $verification
                                    ->statusLabel();

                            $remarks =
                                $verification
                                    ->remarks;

                            $source =
                                'Teacher Verification';

                        } elseif ($log) {

                            $status =
                                AttendanceVerification::STATUSES[
                                    AttendanceVerification::STATUS_PRESENT
                                ];

                            $remarks = null;

                            $source =
                                'QR Scan';

                        } else {

                            $status =
                                AttendanceVerification::STATUSES[
                                    AttendanceVerification::STATUS_ABSENT
                                ];

                            $remarks = null;

                            $source =
                                'Attendance Tracking';
                        }

                        return (object) [

                            'date' =>
                                $date,

                            'scan_time' =>
                                $log?->scan_time,

                            'status' =>
                                $status,

                            'remarks' =>
                                $remarks,

                            'source' =>
                                $source,

                            'has_qr_scan' =>
                                $log !== null,

                            'has_verification' =>
                                $verification !== null,
                        ];
                    }
                )
                ->filter()
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Return Student Profile
        |--------------------------------------------------------------------------
        */

        return view('pov.teacher.student-management.student-profile-detail',
            [

                'enrollment' =>
                    $enrollment,

                'subjects' =>
                    $allSubjects,

                'overallAvg' =>
                    $overallAvg !== null
                        ? round(
                            $overallAvg,
                            1
                        )
                        : null,

                'presentCount' =>
                    $presentCount,

                'attendanceRate' =>
                    $attendanceRate,

                'missingGradesCount' =>
                    $missingGradesCount,

                'belowPassingCount' =>
                    $belowPassingCount,

                'riskData' =>
                    $riskData,

                'gradeHistory' =>
                    $gradeHistory,

                'attendanceHistory' =>
                    $attendanceHistory,

                'currentPeriod' =>
                    $currentPeriod,

                'terms' =>
                    $terms,

                /*
                | Academic Notes
                */

                'academicNotes' =>
                    AcademicNote::where(
                        'enrollment_id',
                        $enrollment->id
                    )
                        ->with(['teacher.user'])
                        ->latest('created_at')
                        ->get(),

                /*
                | Assessment Summary data
                */

                'assessmentSummary' =>
                    $assessmentSummary,

                'assessmentCounts' =>
                    $assessmentCounts,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACADEMIC NOTES ACTIONS
    |--------------------------------------------------------------------------
    */

    public function storeAcademicNote(Request $request, int $enrollmentId)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $teacherSectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless($teacherSectionIds->contains($enrollment->section_id), 403, 'Unauthorized access to student.');

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        AcademicNote::create([
            'enrollment_id' => $enrollment->id,
            'teacher_id' => $teacher->id,
            'note' => trim($validated['note']),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic note added successfully.']);
        }

        return redirect()->back()->with('success', 'Academic note added successfully.');
    }

    public function updateAcademicNote(Request $request, int $enrollmentId, int $noteId)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $teacherSectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless($teacherSectionIds->contains($enrollment->section_id), 403, 'Unauthorized access to student.');

        $note = AcademicNote::where('id', $noteId)
            ->where('enrollment_id', $enrollment->id)
            ->firstOrFail();

        abort_unless($note->teacher_id === $teacher->id, 403, 'You can only edit your own academic notes.');

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $note->update([
            'note' => trim($validated['note']),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic note updated successfully.']);
        }

        return redirect()->back()->with('success', 'Academic note updated successfully.');
    }

    public function destroyAcademicNote(Request $request, int $enrollmentId, int $noteId)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        $teacherSectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        abort_unless($teacherSectionIds->contains($enrollment->section_id), 403, 'Unauthorized access to student.');

        $note = AcademicNote::where('id', $noteId)
            ->where('enrollment_id', $enrollment->id)
            ->firstOrFail();

        abort_unless($note->teacher_id === $teacher->id, 403, 'You can only delete your own academic notes.');

        $note->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Academic note deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Academic note deleted successfully.');
    }
}