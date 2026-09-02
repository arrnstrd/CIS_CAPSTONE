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

class GradeSheetController extends Controller
{
    protected SchoolLevelDetector $levelDetector;
    protected AssessmentTemplateService $templateService;

    public function __construct(SchoolLevelDetector $levelDetector, AssessmentTemplateService $templateService)
    {
        $this->levelDetector = $levelDetector;
        $this->templateService = $templateService;
    }

    public function show(Request $request, int $teachingAssignmentId, GradingService $gradingService)
    {
        $teacher = $request->user()->teacher;

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        // Detect school level based on grade level
        $schoolLevel = $this->levelDetector->detect($ta->section->grade_level);
        
        // Get fixed assessment slots for this level
        $fixedSlots = $this->templateService->getFixedSlots($schoolLevel);
        
        // Get assessment labels for fixed slots
        $slotLabels = $this->templateService->getSlotLabels($schoolLevel);
        
        // Get category labels for display
        $categoryLabels = $this->levelDetector->getCategoryLabels($schoolLevel);

        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();
        $selectedPeriodId = (int) $request->input(
            'grading_period_id',
            $gradingPeriods->where('is_active', true)->sortByDesc('sequence')->first()?->id
                ?? $gradingPeriods->sortByDesc('sequence')->first()?->id
        );

        $assessmentComponentKey = $schoolLevel === 'shs' ? 'quarterly' : 'exam';
        $categories = AssessmentCategory::all()->keyBy(function ($c) use ($assessmentComponentKey) {
            $n = strtolower($c->name);
            if (str_contains($n, 'written')) return 'written';
            if (str_contains($n, 'performance')) return 'performance';
            return $assessmentComponentKey;
        });

        // Load assessments for fixed slots only
        $assessmentsByCategory = ['written' => collect(), 'performance' => collect(), $assessmentComponentKey => collect()];
        $assessmentsBySlot = ['written' => [], 'performance' => [], $assessmentComponentKey => []];

        foreach ($assessmentsByCategory as $key => $_) {
            if (! isset($categories[$key])) {
                continue;
            }
            
            $categoryAssessments = Assessment::where('teaching_assignment_id', $ta->id)
                ->where('grading_period_id', $selectedPeriodId)
                ->where('assessment_category_id', $categories[$key]->id)
                ->where('status', 'active')
                ->orderBy('slot_number')
                ->get();
            
            $assessmentsByCategory[$key] = $categoryAssessments;
            
            // Organize assessments by slot number
            foreach ($categoryAssessments as $assessment) {
                if (!isset($assessment->slot_number)) {
                    $assessment->slot_number = $this->templateService->findNextAvailableSlot(
                        $categoryAssessments->where('id', '!=', $assessment->id), 
                        $fixedSlots[$key] ?? 5
                    );
                }
                
                $slotIndex = $assessment->slot_number - 1;
                if ($slotIndex >= 0 && $slotIndex < ($fixedSlots[$key] ?? 5)) {
                    $assessmentsBySlot[$key][$slotIndex] = $assessment;
                }
            }
            
            // Fill empty slots with null
            for ($i = 0; $i < ($fixedSlots[$key] ?? 5); $i++) {
                if (!isset($assessmentsBySlot[$key][$i])) {
                    $assessmentsBySlot[$key][$i] = null;
                }
            }
        }

        $enrollments = Enrollment::where('section_id', $ta->section_id)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->filter(fn ($e) => $e->student !== null)
            ->sortBy(fn ($e) => $e->student->last_name . $e->student->first_name)
            ->values();

        $allAssessmentIds = collect($assessmentsByCategory)->flatten(1)->pluck('id');

        $scores = StudentAssessmentScore::whereIn('assessment_id', $allAssessmentIds)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->groupBy('enrollment_id');

        $quarterlyGrades = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
            ->where('grading_period_id', $selectedPeriodId)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        $componentCategoryIds = [
            'written' => $categories['written']?->id,
            'performance' => $categories['performance']?->id,
            'quarterly' => $categories[$assessmentComponentKey]?->id,
        ];
        $resolvedWeights = $gradingService->resolveWeights($ta->id, $ta->subject, $componentCategoryIds);

        $rows = $enrollments->map(function ($enrollment) use ($scores, $quarterlyGrades, $assessmentsByCategory, $gradingService, $componentCategoryIds, $resolvedWeights) {
            $studentScores = $scores->get($enrollment->id, collect())->keyBy('assessment_id');

            $componentSummaries = $gradingService->calculateComponentSummaries(
                collect($assessmentsByCategory)->flatten(1),
                $studentScores,
                $resolvedWeights,
                $componentCategoryIds
            );

            $hasAssessment = collect($componentSummaries)->contains(fn (array $summary) => $summary['hps'] > 0);
            $initialGrade = $hasAssessment ? round(collect($componentSummaries)->sum('ws'), 2) : null;
            $transmutedGrade = $initialGrade === null ? null : $gradingService->transmute($initialGrade);
            $mi = $enrollment->student->middle_name ? ' ' . mb_substr($enrollment->student->middle_name, 0, 1) . '.' : '';

            return (object) [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student->last_name . ', ' . $enrollment->student->first_name . $mi,
                'scores' => $studentScores,
                'component_summaries' => $componentSummaries,
                'initial_grade' => $initialGrade,
                'transmuted_grade' => $transmutedGrade,
                'descriptor' => $transmutedGrade === null ? null : PerformanceDescriptorResolver::resolve($transmutedGrade)['description'],
            ];
        });

        $totalStudents = $enrollments->count();
        $gradesWithValue = $rows->filter(fn ($row) => $row->transmuted_grade !== null);
        $completedCount = $gradesWithValue->count();
        $gradeCompletionPercent = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : null;
        $classAverage = $completedCount > 0 ? round($gradesWithValue->avg('transmuted_grade'), 1) : null;
        $passingCount = $gradesWithValue->where('transmuted_grade', '>=', 75)->count();
        $passingRate = $completedCount > 0 ? round(($passingCount / $completedCount) * 100, 1) : null;

        return view('teacher-modules.grading.grade-sheet', [
            'ta' => $ta,
            'gradingPeriods' => $gradingPeriods,
            'selectedPeriodId' => $selectedPeriodId,
            'assessmentsByCategory' => $assessmentsByCategory,
            'assessmentsBySlot' => $assessmentsBySlot,
            'rows' => $rows,
            'totalStudents' => $totalStudents,
            'gradeCompletionPercent' => $gradeCompletionPercent,
            'classAverage' => $classAverage,
            'passingRate' => $passingRate,
            'schoolLevel' => $schoolLevel,
            'fixedSlots' => $fixedSlots,
            'slotLabels' => $slotLabels,
            'categoryLabels' => $categoryLabels,
            'assessmentComponentKey' => $assessmentComponentKey,
        ]);
    }

    public function storeAssessment(Request $request)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'teaching_assignment_id' => 'required|integer',
            'grading_period_id' => 'required|integer',
            'category' => 'required|in:written,performance,quarterly,exam',
            'title' => 'required|string|max:100',
            'total_items' => 'required|integer|min:1',
            'assessment_date' => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $ta = TeachingAssignment::where('id', $data['teaching_assignment_id'])
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Get school level and fixed slots
        $schoolLevel = $this->levelDetector->detect($ta->section->grade_level);
        $fixedSlots = $this->templateService->getFixedSlots($schoolLevel);

        $categoryNameMap = [
            'written' => 'Written Work',
            'performance' => 'Performance Task',
            'quarterly' => 'Quarterly Assessment',
            'exam' => 'Quarterly Assessment',
        ];

        $category = AssessmentCategory::where('name', $categoryNameMap[$data['category']])->firstOrFail();

        // Find next available slot for this category
        $existingAssessments = Assessment::where('teaching_assignment_id', $ta->id)
            ->where('grading_period_id', $data['grading_period_id'])
            ->where('assessment_category_id', $category->id)
            ->where('status', 'active')
            ->orderBy('slot_number')
            ->get();
        
        $nextSlot = $this->templateService->findNextAvailableSlot($existingAssessments, $fixedSlots[$data['category']]);

        $assessment = Assessment::create([
            'teaching_assignment_id' => $ta->id,
            'assessment_category_id' => $category->id,
            'grading_period_id' => $data['grading_period_id'],
            'title' => $data['title'],
            'total_items' => $data['total_items'],
            'assessment_date' => $data['assessment_date'] ?? now(),
            'description' => $data['description'] ?? null,
            'status' => 'active',
            'slot_number' => $nextSlot,
        ]);

        return response()->json([
            'id' => $assessment->id,
            'title' => $assessment->title,
            'total_items' => $assessment->total_items,
            'category' => $data['category'],
            'slot_number' => $nextSlot,
            'is_fixed_slot' => $nextSlot <= $fixedSlots[$data['category']],
        ]);
    }

    public function storeScore(Request $request, GradingService $gradingService)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'assessment_id' => 'required|integer',
            'enrollment_id' => 'required|integer',
            'score' => 'required|numeric|min:0',
        ]);

        $assessment = Assessment::with('teachingAssignment')->findOrFail($data['assessment_id']);

        if (! $assessment->teachingAssignment || $assessment->teachingAssignment->teacher_id !== $teacher->id) {
            abort(403);
        }

        try {
            $gradingService->recordScore($data);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $qg = QuarterlyGrade::where('teaching_assignment_id', $assessment->teaching_assignment_id)
            ->where('enrollment_id', $data['enrollment_id'])
            ->where('grading_period_id', $assessment->grading_period_id)
            ->first();

        // Recompute class-level stats for this teaching assignment + grading period
        $activeEnrollmentIds = \App\Models\Enrollment::where('section_id', $assessment->teachingAssignment->section_id)
            ->where('status', 'active')
            ->pluck('id');
        $totalStudents = $activeEnrollmentIds->count();

        $allGrades = QuarterlyGrade::where('teaching_assignment_id', $assessment->teaching_assignment_id)
            ->where('grading_period_id', $assessment->grading_period_id)
            ->whereIn('enrollment_id', $activeEnrollmentIds)
            ->get();

        $gradesWithValue = $allGrades->whereNotNull('transmuted_grade');
        $completedCount = $gradesWithValue->count();
        $gradeCompletionPercent = $totalStudents > 0 ? round(($completedCount / $totalStudents) * 100, 1) : null;
        $classAverage = $completedCount > 0 ? round($gradesWithValue->avg('transmuted_grade'), 1) : null;
        $passingCount = $gradesWithValue->where('transmuted_grade', '>=', 75)->count();
        $passingRate = $completedCount > 0 ? round(($passingCount / $completedCount) * 100, 1) : null;

        return response()->json([
            'written_work_grade' => $qg->written_work_grade,
            'performance_task_grade' => $qg->performance_task_grade,
            'quarterly_assessment_grade' => $qg->quarterly_assessment_grade,
            'component_summaries' => $gradingService->getComponentSummaries(
                $data['enrollment_id'],
                $assessment->teaching_assignment_id,
                $assessment->grading_period_id
            ),
            'initial_grade' => $qg->initial_grade,
            'transmuted_grade' => $qg->transmuted_grade,
            'descriptor' => $qg->transmuted_grade === null ? null : PerformanceDescriptorResolver::resolve((float) $qg->transmuted_grade)['description'],
            'grade_completion_percent' => $gradeCompletionPercent,
            'class_average' => $classAverage,
            'passing_rate' => $passingRate,
        ]);
    }

    /**
     * Get assessment log for a teaching assignment and grading period.
     */
    public function getAssessmentLog(Request $request)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignmentId = $request->input('teaching_assignment_id');
        $gradingPeriodId = $request->input('grading_period_id');

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->firstOrFail();

        $assessments = Assessment::where('teaching_assignment_id', $ta->id)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('status', 'active')
            ->orderBy('assessment_category_id')
            ->orderBy('slot_number')
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'category' => $this->getCategoryKey($assessment->assessment_category_id),
                    'total_items' => $assessment->total_items,
                    'assessment_date' => $assessment->assessment_date,
                    'description' => $assessment->description,
                    'slot_number' => $assessment->slot_number,
                ];
            });

        return response()->json($assessments);
    }

    /**
     * Get category key from assessment category ID.
     */
    private function getCategoryKey($assessmentCategoryId)
    {
        $category = AssessmentCategory::find($assessmentCategoryId);
        if (!$category) return 'unknown';

        $name = strtolower($category->name);
        if (str_contains($name, 'written')) return 'written';
        if (str_contains($name, 'performance')) return 'performance';
        if (str_contains($name, 'quarterly')) return 'quarterly';
        return 'exam';
    }
}
