<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Services\Grading\GradingService;
use App\Services\Grading\SchoolLevelDetector;
use App\Services\Grading\AssessmentTemplateService;
use App\Services\Grading\PerformanceDescriptorResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeSheetController extends Controller
{
    protected SchoolLevelDetector $levelDetector;
    protected AssessmentTemplateService $templateService;

    public function __construct(
        SchoolLevelDetector $levelDetector,
        AssessmentTemplateService $templateService
    ) {
        $this->levelDetector = $levelDetector;
        $this->templateService = $templateService;
    }

    /**
     * Display the grade sheet.
     *
     * The grading system uses three terms only:
     * Term 1, Term 2, and Term 3.
     */
    public function show(
        Request $request,
        int $teachingAssignmentId,
        GradingService $gradingService
    ) {
        $teacher = $request->user()->teacher;

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        $schoolLevel = $this->levelDetector->detect(
            $ta->section->grade_level
        );

        $fixedSlots = $this->templateService->getFixedSlots($schoolLevel);
        $slotLabels = $this->templateService->getSlotLabels($schoolLevel);
        $categoryLabels = $this->levelDetector->getCategoryLabels($schoolLevel);

        /*
         * Only Term 1, Term 2, and Term 3 are valid.
         */
        $gradingPeriods = GradingPeriod::where('sequence', '<=', 3)
            ->where('period_type', 'trimester')
            ->orderBy('sequence')
            ->get();

        $selectedPeriodId = (int) $request->input(
            'grading_period_id',
            $gradingPeriods
                ->where('is_active', true)
                ->sortByDesc('sequence')
                ->first()?->id
                ?? $gradingPeriods->sortByDesc('sequence')->first()?->id
        );

        /*
         * Make sure the requested grading period belongs to
         * the three valid trimester terms.
         */
        if (! $gradingPeriods->contains('id', $selectedPeriodId)) {
            $selectedPeriodId = $gradingPeriods->first()?->id;
        }

        /*
         * Unified 3-component structure for all levels:
         * Written Work (WW), Performance Task (PT), Examinations (EX).
         */
        $assessmentComponentKey = 'exam';

        $categories = AssessmentCategory::all()->keyBy(
            function ($category) {
                $name = strtolower($category->name);

                if (str_contains($name, 'written')) {
                    return 'written';
                }

                if (str_contains($name, 'performance')) {
                    return 'performance';
                }

                return 'exam';
            }
        );

        $assessmentsByCategory = [
            'written' => collect(),
            'performance' => collect(),
            'exam' => collect(),
        ];

        $assessmentsBySlot = [
            'written' => [],
            'performance' => [],
            'exam' => [],
        ];

        foreach ($assessmentsByCategory as $key => $_) {
            if (! isset($categories[$key])) {
                continue;
            }

            $categoryAssessments = Assessment::where(
                    'teaching_assignment_id',
                    $ta->id
                )
                ->where('grading_period_id', $selectedPeriodId)
                ->where(
                    'assessment_category_id',
                    $categories[$key]->id
                )
                ->where('status', 'active')
                ->orderBy('slot_number')
                ->get();

            $assessmentsByCategory[$key] = $categoryAssessments;

            foreach ($categoryAssessments as $assessment) {
                if (! isset($assessment->slot_number)) {
                    $assessment->slot_number =
                        $this->templateService->findNextAvailableSlot(
                            $categoryAssessments->where(
                                'id',
                                '!=',
                                $assessment->id
                            ),
                            $fixedSlots[$key] ?? 5
                        );
                }

                $slotIndex = $assessment->slot_number - 1;

                if (
                    $slotIndex >= 0 &&
                    $slotIndex < ($fixedSlots[$key] ?? 5)
                ) {
                    $assessmentsBySlot[$key][$slotIndex] = $assessment;
                }
            }

            for (
                $i = 0;
                $i < ($fixedSlots[$key] ?? 5);
                $i++
            ) {
                if (! isset($assessmentsBySlot[$key][$i])) {
                    $assessmentsBySlot[$key][$i] = null;
                }
            }
        }

        /*
         * Get all active learners in the assigned section.
         *
         * Students are sorted by surname first.
         */
        $enrollments = Enrollment::where(
                'section_id',
                $ta->section_id
            )
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->filter(fn ($enrollment) =>
                $enrollment->student !== null
            )
            ->sortBy(
                fn ($enrollment) =>
                    $enrollment->student->last_name .
                    $enrollment->student->first_name
            )
            ->values();

        $allAssessmentIds = collect($assessmentsByCategory)
            ->flatten(1)
            ->pluck('id');

        $scores = StudentAssessmentScore::whereIn(
                'assessment_id',
                $allAssessmentIds
            )
            ->whereIn(
                'enrollment_id',
                $enrollments->pluck('id')
            )
            ->get()
            ->groupBy('enrollment_id');

        $quarterlyGrades = QuarterlyGrade::where(
                'teaching_assignment_id',
                $ta->id
            )
            ->where(
                'grading_period_id',
                $selectedPeriodId
            )
            ->whereIn(
                'enrollment_id',
                $enrollments->pluck('id')
            )
            ->get()
            ->keyBy('enrollment_id');

        $componentCategoryIds = [
            'written' => $categories['written']?->id,
            'performance' => $categories['performance']?->id,
            'quarterly' => $categories['exam']?->id,
            'exam' => $categories['exam']?->id,
        ];

        $resolvedWeights = $gradingService->resolveWeights(
            $ta->id,
            $ta->subject,
            $componentCategoryIds
        );

        $rows = $enrollments->map(
            function ($enrollment) use (
                $scores,
                $quarterlyGrades,
                $assessmentsByCategory,
                $gradingService,
                $componentCategoryIds,
                $resolvedWeights
            ) {
                $studentScores = $scores
                    ->get($enrollment->id, collect())
                    ->keyBy('assessment_id');

                $componentSummaries =
                    $gradingService->calculateComponentSummaries(
                        collect($assessmentsByCategory)->flatten(1),
                        $studentScores,
                        $resolvedWeights,
                        $componentCategoryIds
                    );

                if (isset($componentSummaries['quarterly']) && !isset($componentSummaries['exam'])) {
                    $componentSummaries['exam'] = $componentSummaries['quarterly'];
                }
                if (isset($componentSummaries['exam']) && !isset($componentSummaries['quarterly'])) {
                    $componentSummaries['quarterly'] = $componentSummaries['exam'];
                }

                $hasAssessment = collect($componentSummaries)
                    ->contains(
                        fn (array $summary) =>
                            $summary['hps'] > 0
                    );

                $initialGrade = $hasAssessment
                    ? round(
                        collect($componentSummaries)
                            ->sum('ws'),
                        2
                    )
                    : null;

                $transmutedGrade = $initialGrade === null
                    ? null
                    : $gradingService->transmute($initialGrade);

                $middleInitial =
                    $enrollment->student->middle_name
                        ? ' ' .
                            mb_substr(
                                $enrollment->student->middle_name,
                                0,
                                1
                            ) .
                            '.'
                        : '';

                /*
                 * Normalize sex so the Blade can reliably group
                 * learners even if the database contains
                 * different capitalization.
                 */
                $sex = strtolower(
                    trim((string) $enrollment->student->sex)
                );

                if (in_array($sex, ['male', 'm'])) {
                    $sex = 'male';
                } elseif (in_array($sex, ['female', 'f'])) {
                    $sex = 'female';
                } else {
                    $sex = 'unknown';
                }

                return (object) [
                    'enrollment_id' => $enrollment->id,

                    'student_name' =>
                        $enrollment->student->last_name .
                        ', ' .
                        $enrollment->student->first_name .
                        $middleInitial,

                    /*
                     * Added for Male/Female grouping.
                     */
                    'sex' => $sex,

                    'scores' => $studentScores,

                    'component_summaries' =>
                        $componentSummaries,

                    'initial_grade' => $initialGrade,

                    'transmuted_grade' => $transmutedGrade,

                    'descriptor' =>
                        $transmutedGrade === null
                            ? null
                            : PerformanceDescriptorResolver::resolve(
                                $transmutedGrade
                            )['description'],
                ];
            }
        );

        /*
         * Group learners by sex.
         *
         * Unknown/missing sex is kept separately so no learner
         * disappears from the grade sheet.
         */
        $maleRows = $rows
            ->where('sex', 'male')
            ->values();

        $femaleRows = $rows
            ->where('sex', 'female')
            ->values();

        $unknownRows = $rows
            ->where('sex', 'unknown')
            ->values();

        /*
         * Keep the original $rows variable available for any
         * existing code that may still use it.
         */
        $groupedRows = collect([
            'male' => $maleRows,
            'female' => $femaleRows,
            'unknown' => $unknownRows,
        ]);

        $totalStudents = $enrollments->count();

        $gradesWithValue = $rows->filter(
            fn ($row) =>
                $row->transmuted_grade !== null
        );

        $completedCount = $gradesWithValue->count();

        $gradeCompletionPercent =
            $totalStudents > 0
                ? round(
                    ($completedCount / $totalStudents) * 100,
                    1
                )
                : null;

        $classAverage =
            $completedCount > 0
                ? round(
                    $gradesWithValue->avg('transmuted_grade'),
                    1
                )
                : null;

        $passingCount = $gradesWithValue
            ->where('transmuted_grade', '>=', 75)
            ->count();

        $passingRate =
            $completedCount > 0
                ? round(
                    ($passingCount / $completedCount) * 100,
                    1
                )
                : null;

        return view(
            'teacher-modules.grading.grade-sheet',
            [
                'ta' => $ta,

                'gradingPeriods' =>
                    $gradingPeriods,

                'selectedPeriodId' =>
                    $selectedPeriodId,

                'assessmentsByCategory' =>
                    $assessmentsByCategory,

                'assessmentsBySlot' =>
                    $assessmentsBySlot,

                /*
                 * Original rows.
                 */
                'rows' => $rows,

                /*
                 * Gender-separated rows for the Blade.
                 */
                'maleRows' => $maleRows,

                'femaleRows' => $femaleRows,

                /*
                 * Learners with no valid sex value.
                 */
                'unknownRows' => $unknownRows,

                /*
                 * All groups in one collection if needed.
                 */
                'groupedRows' => $groupedRows,

                'totalStudents' =>
                    $totalStudents,

                'gradeCompletionPercent' =>
                    $gradeCompletionPercent,

                'classAverage' =>
                    $classAverage,

                'passingRate' =>
                    $passingRate,

                'schoolLevel' =>
                    $schoolLevel,

                'fixedSlots' =>
                    $fixedSlots,

                'slotLabels' =>
                    $slotLabels,

                'categoryLabels' =>
                    $categoryLabels,

                'assessmentComponentKey' =>
                    $assessmentComponentKey,
            ]
        );
    }

    /**
     * Store a new assessment in a specific trimester term.
     */
    public function storeAssessment(Request $request)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'teaching_assignment_id' => 'required|integer',
            'grading_period_id' => 'required|integer|exists:grading_periods,id',
            'category' => 'required|in:written,performance,quarterly,exam',
            'title' => 'required|string|max:100',
            'total_items' => 'required|integer|min:1',
            'assessment_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $gradingPeriod = GradingPeriod::where('id', $data['grading_period_id'])
            ->where('sequence', '<=', 3)
            ->where('period_type', 'trimester')
            ->firstOrFail();

        $ta = TeachingAssignment::where(
                'id',
                $data['teaching_assignment_id']
            )
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->firstOrFail();

        $schoolLevel = $this->levelDetector->detect(
            $ta->section->grade_level
        );

        $fixedSlots = $this->templateService->getFixedSlots(
            $schoolLevel
        );

        $categoryNameMap = [
            'written' => 'Written Work',
            'performance' => 'Performance Task',
            'quarterly' => 'Quarterly Assessment',
            'exam' => 'Quarterly Assessment',
        ];

        $category = AssessmentCategory::where(
            'name',
            $categoryNameMap[$data['category']]
        )->firstOrFail();

        $existingAssessments = Assessment::where(
                'teaching_assignment_id',
                $ta->id
            )
            ->where(
                'grading_period_id',
                $gradingPeriod->id
            )
            ->where(
                'assessment_category_id',
                $category->id
            )
            ->where('status', 'active')
            ->orderBy('slot_number')
            ->get();

        $categoryKey = $data['category'];
        if (! isset($fixedSlots[$categoryKey])) {
            $categoryKey = ($categoryKey === 'exam') ? 'quarterly' : 'exam';
        }
        $maxSlots = $fixedSlots[$categoryKey] ?? ($fixedSlots[$data['category']] ?? 3);

        $nextSlot =
            $existingAssessments->count() > 0
                ? $existingAssessments->max('slot_number') + 1
                : 1;

        $assessment = Assessment::create([
            'teaching_assignment_id' =>
                $ta->id,

            'assessment_category_id' =>
                $category->id,

            'grading_period_id' =>
                $gradingPeriod->id,

            'title' =>
                $data['title'],

            'total_items' =>
                $data['total_items'],

            'assessment_date' =>
                $data['assessment_date'] ?? now(),

            'description' =>
                $data['description'] ?? null,

            'status' =>
                'active',

            'slot_number' =>
                $nextSlot,
        ]);

        return response()->json([
            'id' =>
                $assessment->id,

            'title' =>
                $assessment->title,

            'total_items' =>
                $assessment->total_items,

            'category' =>
                $data['category'],

            'slot_number' =>
                $nextSlot,

            'is_fixed_slot' =>
                $nextSlot <= $maxSlots,
        ]);
    }

    /**
     * Store a student score.
     */
    public function storeScore(
        Request $request,
        GradingService $gradingService
    ) {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'assessment_id' => 'required|integer',
            'enrollment_id' => 'required|integer',
            'score' => 'nullable|numeric|min:0',
        ]);

        $assessment = Assessment::with(
            'teachingAssignment'
        )->findOrFail(
            $data['assessment_id']
        );

        if (
            ! $assessment->teachingAssignment ||
            $assessment->teachingAssignment->teacher_id !== $teacher->id
        ) {
            abort(403);
        }

        /*
         * Make sure scores cannot accidentally be recorded
         * against the legacy fourth quarter.
         */
        $gradingPeriod = GradingPeriod::where(
                'id',
                $assessment->grading_period_id
            )
            ->where('sequence', '<=', 3)
            ->where('period_type', 'trimester')
            ->first();

        if (! $gradingPeriod) {
            return response()->json([
                'error' =>
                    'Scores can only be recorded for Term 1, Term 2, or Term 3.',
            ], 422);
        }

        try {
            if ($data['score'] !== null && $data['score'] !== '') {
                $gradingService->recordScore($data);
            } else {
                // Clear score
                StudentAssessmentScore::where('assessment_id', $data['assessment_id'])
                    ->where('enrollment_id', $data['enrollment_id'])
                    ->delete();

                $gradingService->recalculateQuarterlyGrade(
                    $data['enrollment_id'],
                    $assessment->teaching_assignment_id,
                    $assessment->grading_period_id
                );
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        $qg = QuarterlyGrade::where(
                'teaching_assignment_id',
                $assessment->teaching_assignment_id
            )
            ->where(
                'enrollment_id',
                $data['enrollment_id']
            )
            ->where(
                'grading_period_id',
                $assessment->grading_period_id
            )
            ->first();

        if (! $qg) {
            return response()->json([
                'error' =>
                    'Unable to retrieve the updated term grade.',
            ], 422);
        }

        $activeEnrollmentIds = Enrollment::where(
                'section_id',
                $assessment->teachingAssignment->section_id
            )
            ->where('status', 'active')
            ->pluck('id');

        $totalStudents =
            $activeEnrollmentIds->count();

        $allGrades = QuarterlyGrade::where(
                'teaching_assignment_id',
                $assessment->teaching_assignment_id
            )
            ->where(
                'grading_period_id',
                $assessment->grading_period_id
            )
            ->whereIn(
                'enrollment_id',
                $activeEnrollmentIds
            )
            ->get();

        $gradesWithValue = $allGrades->whereNotNull(
            'transmuted_grade'
        );

        $completedCount =
            $gradesWithValue->count();

        $gradeCompletionPercent =
            $totalStudents > 0
                ? round(
                    ($completedCount / $totalStudents) * 100,
                    1
                )
                : null;

        $classAverage =
            $completedCount > 0
                ? round(
                    $gradesWithValue->avg('transmuted_grade'),
                    1
                )
                : null;

        $passingCount =
            $gradesWithValue
                ->where('transmuted_grade', '>=', 75)
                ->count();

        $passingRate =
            $completedCount > 0
                ? round(
                    ($passingCount / $completedCount) * 100,
                    1
                )
                : null;

        $summaries = $gradingService->getComponentSummaries(
            $data['enrollment_id'],
            $assessment->teaching_assignment_id,
            $assessment->grading_period_id
        );
        if (isset($summaries['quarterly']) && !isset($summaries['exam'])) {
            $summaries['exam'] = $summaries['quarterly'];
        }
        if (isset($summaries['exam']) && !isset($summaries['quarterly'])) {
            $summaries['quarterly'] = $summaries['exam'];
        }

        return response()->json([
            'written_work_grade' =>
                $qg->written_work_grade,

            'performance_task_grade' =>
                $qg->performance_task_grade,

            'quarterly_assessment_grade' =>
                $qg->quarterly_assessment_grade,

            'component_summaries' =>
                $summaries,

            'initial_grade' =>
                $qg->initial_grade,

            'transmuted_grade' =>
                $qg->transmuted_grade,

            'descriptor' =>
                $qg->transmuted_grade === null
                    ? null
                    : PerformanceDescriptorResolver::resolve(
                        (float) $qg->transmuted_grade
                    )['description'],

            'grade_completion_percent' =>
                $gradeCompletionPercent,

            'class_average' =>
                $classAverage,

            'passing_rate' =>
                $passingRate,
        ]);
    }

    /**
     * Get assessment log for a teaching assignment and term.
     */
    /**
     * Update an assessment's details.
     */
    public function updateAssessment(
        Request $request,
        $assessmentId,
        GradingService $gradingService
    ) {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'total_items' => 'required|numeric|min:1',
            'assessment_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $assessment = Assessment::with('teachingAssignment')->findOrFail($assessmentId);

        if (
            ! $assessment->teachingAssignment ||
            $assessment->teachingAssignment->teacher_id !== $teacher->id
        ) {
            abort(403, 'Unauthorized.');
        }

        $oldTotalItems = $assessment->total_items;

        $assessment->update([
            'title' => $data['title'],
            'total_items' => $data['total_items'],
            'assessment_date' => $data['assessment_date'] ?? $assessment->assessment_date,
            'description' => $data['description'] ?? null,
        ]);

        // If total_items changed, recalculate quarterly grades for all enrollments in this section
        if ((float) $oldTotalItems !== (float) $data['total_items']) {
            $enrollmentIds = Enrollment::where('section_id', $assessment->teachingAssignment->section_id)
                ->pluck('id');

            foreach ($enrollmentIds as $enrollmentId) {
                $gradingService->recalculateQuarterlyGrade(
                    $enrollmentId,
                    $assessment->teaching_assignment_id,
                    $assessment->grading_period_id
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment updated successfully.',
            'assessment' => [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'total_items' => $assessment->total_items,
                'assessment_date' => $assessment->assessment_date ? $assessment->assessment_date->format('Y-m-d') : null,
                'description' => $assessment->description,
            ],
        ]);
    }

    /**
     * Delete an assessment and its associated scores safely.
     */
    public function destroyAssessment(
        Request $request,
        $assessmentId,
        GradingService $gradingService
    ) {
        $teacher = $request->user()->teacher;

        $assessment = Assessment::with('teachingAssignment')->findOrFail($assessmentId);

        if (
            ! $assessment->teachingAssignment ||
            $assessment->teachingAssignment->teacher_id !== $teacher->id
        ) {
            abort(403, 'Unauthorized.');
        }

        $taId = $assessment->teaching_assignment_id;
        $gpId = $assessment->grading_period_id;
        $sectionId = $assessment->teachingAssignment->section_id;

        DB::transaction(function () use ($assessment) {
            // Delete associated scores first to satisfy FK constraint
            StudentAssessmentScore::where('assessment_id', $assessment->id)->delete();
            $assessment->delete();
        });

        // Recalculate quarterly grades for all enrolled students
        $enrollmentIds = Enrollment::where('section_id', $sectionId)->pluck('id');
        foreach ($enrollmentIds as $enrollmentId) {
            $gradingService->recalculateQuarterlyGrade(
                $enrollmentId,
                $taId,
                $gpId
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment deleted successfully.',
        ]);
    }

    /**
     * Store/update/clear scores for an extra assessment.
     */
    public function storeExtraScores(
        Request $request,
        $assessmentId,
        GradingService $gradingService
    ) {
        $teacher = $request->user()->teacher;

        $assessment = Assessment::with('teachingAssignment')->findOrFail($assessmentId);

        if (
            ! $assessment->teachingAssignment ||
            $assessment->teachingAssignment->teacher_id !== $teacher->id
        ) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'scores' => 'required|array',
            'scores.*.enrollment_id' => 'required|integer',
            'scores.*.score' => 'nullable|numeric|min:0',
        ]);

        foreach ($data['scores'] as $item) {
            $enrollmentId = $item['enrollment_id'];
            $scoreValue = $item['score'];

            if ($scoreValue !== null && $scoreValue !== '') {
                if ((float) $scoreValue > (float) $assessment->total_items) {
                    return response()->json([
                        'error' => "Score cannot exceed total items ({$assessment->total_items}).",
                    ], 422);
                }

                StudentAssessmentScore::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'enrollment_id' => $enrollmentId,
                    ],
                    [
                        'score' => $scoreValue,
                    ]
                );
            } else {
                // Clear score
                StudentAssessmentScore::where('assessment_id', $assessment->id)
                    ->where('enrollment_id', $enrollmentId)
                    ->delete();
            }

            $gradingService->recalculateQuarterlyGrade(
                $enrollmentId,
                $assessment->teaching_assignment_id,
                $assessment->grading_period_id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Scores saved successfully.',
        ]);
    }

    /**
     * Get assessment log for a teaching assignment and term.
     */
    public function getAssessmentLog(Request $request)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignmentId =
            $request->input('teaching_assignment_id');

        $gradingPeriodId =
            $request->input('grading_period_id');

        $ta = TeachingAssignment::where(
                'id',
                $teachingAssignmentId
            )
            ->where(
                'teacher_id',
                $teacher->id
            )
            ->where(
                'status',
                'active'
            )
            ->firstOrFail();

        $gradingPeriod = GradingPeriod::where(
                'id',
                $gradingPeriodId
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
            ->firstOrFail();

        $enrollments = Enrollment::where('section_id', $ta->section_id)
            ->where(function ($query) {
                $query->where('status', 'enrolled')
                    ->orWhere('status', 'active');
            })
            ->with('student')
            ->get()
            ->sortBy(fn($e) => $e->student ? $e->student->last_name . ' ' . $e->student->first_name : '')
            ->values();

        if ($enrollments->isEmpty()) {
            $enrollments = Enrollment::where('section_id', $ta->section_id)
                ->with('student')
                ->get()
                ->sortBy(fn($e) => $e->student ? $e->student->last_name . ' ' . $e->student->first_name : '')
                ->values();
        }

        $totalEnrolledCount = $enrollments->count();

        $schoolLevel = $this->levelDetector->detect(
            $ta->section->grade_level
        );

        $fixedSlots = $this->templateService->getFixedSlots($schoolLevel);

        $assessments = Assessment::where(
                'teaching_assignment_id',
                $ta->id
            )
            ->where(
                'grading_period_id',
                $gradingPeriod->id
            )
            ->where(
                'status',
                'active'
            )
            ->with([
                'studentAssessmentScores.enrollment.student'
            ])
            ->orderBy(
                'assessment_category_id'
            )
            ->orderBy(
                'slot_number'
            )
            ->get()
            ->map(function ($assessment) use ($enrollments, $totalEnrolledCount, $fixedSlots) {
                $categoryKey = $this->getCategoryKey(
                    $assessment->assessment_category_id
                );

                $prefix = match ($categoryKey) {
                    'written' => 'WW',
                    'performance' => 'PT',
                    default => 'EX',
                };

                $slotCode = $assessment->slot_number
                    ? "{$prefix}{$assessment->slot_number}"
                    : null;

                $scoresByEnrollment = $assessment->studentAssessmentScores->keyBy('enrollment_id');

                $allStudents = $enrollments->map(function ($enrollment) use ($scoresByEnrollment) {
                    $student = $enrollment->student;
                    $middleInitial = $student?->middle_name
                        ? ' ' . mb_substr($student->middle_name, 0, 1) . '.'
                        : '';
                    $scoreObj = $scoresByEnrollment->get($enrollment->id);

                    return [
                        'enrollment_id' => $enrollment->id,
                        'student_name' => $student ? "{$student->last_name}, {$student->first_name}{$middleInitial}" : "Student #{$enrollment->id}",
                        'score' => $scoreObj && $scoreObj->score !== null ? (float) $scoreObj->score : null,
                    ];
                })->values();

                $scoredStudents = $allStudents->filter(fn($s) => $s['score'] !== null)->values();

                $maxVisible = match ($categoryKey) {
                    'written' => $fixedSlots['written'] ?? 5,
                    'performance' => $fixedSlots['performance'] ?? 5,
                    default => $fixedSlots['exam'] ?? 3,
                };

                $slotNumber = $assessment->slot_number ?? 0;

                // Only include assessments beyond the visible Grade Sheet columns.
                if ($slotNumber <= $maxVisible) {
                    return null;
                }

                return [
                    'id' =>
                        $assessment->id,

                    'title' =>
                        $assessment->title,

                    'slot_code' =>
                        $slotCode,

                    'category' =>
                        $categoryKey,

                    'total_items' =>
                        $assessment->total_items,

                    'assessment_date' =>
                        $assessment->assessment_date ? $assessment->assessment_date->format('Y-m-d') : null,

                    'description' =>
                        $assessment->description,

                    'slot_number' =>
                        $slotNumber,

                    'max_visible_slot' =>
                        $maxVisible,

                    'scores_count' =>
                        $scoredStudents->count(),

                    'students' =>
                        $scoredStudents,

                    'all_students' =>
                        $allStudents,
                ];
            })
            ->filter()     // drop null entries (visible assessments)
            ->values();    // re-index

        return response()->json($assessments);
    }

    /**
     * Get the internal category key from assessment category ID.
     */
    private function getCategoryKey($assessmentCategoryId)
    {
        $category =
            AssessmentCategory::find(
                $assessmentCategoryId
            );

        if (! $category) {
            return 'unknown';
        }

        $name =
            strtolower($category->name);

        if (str_contains($name, 'written')) {
            return 'written';
        }

        if (str_contains($name, 'performance')) {
            return 'performance';
        }

        if (str_contains($name, 'quarterly') || str_contains($name, 'exam')) {
            return 'exam';
        }

        return 'exam';
    }
}