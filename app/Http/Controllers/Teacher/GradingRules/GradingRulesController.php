<?php

namespace App\Http\Controllers\Teacher\GradingRules;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use App\Models\AssessmentCategory;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\GradingConfig;
use App\Services\Grading\GradingService;
use App\Services\Grading\SubjectWeightResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingRulesController extends Controller
{
    protected SubjectWeightResolver $weightResolver;
    protected GradingService $gradingService;

    public function __construct(
        SubjectWeightResolver $weightResolver,
        GradingService $gradingService
    ) {
        $this->weightResolver = $weightResolver;
        $this->gradingService = $gradingService;
    }
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;
        $activeAssignments = $teacher->teachingAssignments()
            ->with(['subject', 'section', 'schoolYear'])
            ->where('status', 'active')
            ->get();

        $selectedAssignmentId = $request->input('teaching_assignment_id');
        $selectedAssignment = null;

        if ($selectedAssignmentId) {
            $selectedAssignment = $activeAssignments->firstWhere('id', $selectedAssignmentId);

            if (!$selectedAssignment) {
                return redirect()->route('teacher.grading-system.grading-rules')
                    ->with('error', 'Invalid teaching assignment selected.');
            }
        }

        if ($activeAssignments->isEmpty()) {
            return view('pov.teacher.grading-rules.grading-rules', [
                'policyName' => "DepEd Standard K-12",
                'schoolYear' => SchoolYear::where('is_active', true)->first()?->school_year ?? 'No active school year',
                'lastUpdated' => "No configuration",
                'configuredBy' => "Teacher",
                'gradingComponents' => [],
                'formula' => "No active assignments",
                'workedExample' => [],
                'otherRules' => [],
                'activeAssignments' => $activeAssignments,
                'selectedAssignment' => null,
            ]);
        }

        if (!$selectedAssignment && $activeAssignments->count() === 1) {
            $selectedAssignment = $activeAssignments->first();
        }


        $policyName = 'DepEd Standard K-12';
        $schoolYear = $selectedAssignment->schoolYear?->school_year ?? SchoolYear::where('is_active', true)->first()?->school_year ?? 'No active school year';
        $configuredBy = 'Teacher';
        if ($selectedAssignment) {
            $weights = $this->resolveWeightsForAssignment($selectedAssignment->id);
            $gradingComponents = [
                'written_work' => ['name' => 'Written Work (WW)', 'weight' => $weights['written_work'] * 100, 'color' => '#4CAF50'],
                'performance_task' => ['name' => 'Performance Task (PT)', 'weight' => $weights['performance_task'] * 100, 'color' => '#2196F3'],
                'term_assessment' => ['name' => 'Term Assessment (EX)', 'weight' => $weights['term_assessment'] * 100, 'color' => '#FF9800'],
            ];
            $formula = "Final Grade = (WW Average x {$weights['written_work']}) + (PT Average x {$weights['performance_task']}) + (TA Average x {$weights['term_assessment']})";

            $workedExample = [
                'ww_score' => 85,
                'pt_score' => 90,
                'ta_score' => 85,
                'ww_weight' => $weights['written_work'],
                'pt_weight' => $weights['performance_task'],
                'ta_weight' => $weights['term_assessment'],
                'ww_contribution' => round(85 * $weights['written_work'], 2),
                'pt_contribution' => round(90 * $weights['performance_task'], 2),
                'ta_contribution' => round(85 * $weights['term_assessment'], 2),
                'final_grade' => round((85 * $weights['written_work']) + (90 * $weights['performance_task']) + (85 * $weights['term_assessment']), 2),
            ];
            $lastUpdated = $this->getLastUpdatedDate($selectedAssignment->id);
        } else {
            $gradingComponents = [];
            $formula = "Select an assignment to view configuration";
            $workedExample = [];
            $lastUpdated = "No configuration";
        }

        $otherRules = [
            ['rule' => 'Passing Grade', 'value' => '75.00'],
            ['rule' => 'Rounding', 'value' => 'round to nearest whole number'],
            ['rule' => 'Decimal Handling', 'value' => '2 decimal places max'],
            ['rule' => 'Zero Score Handling', 'value' => 'counts as 0% in component average'],
            ['rule' => 'Attendance Threshold', 'value' => 'below 85% triggers Risk Indicator'],
            ['rule' => 'Incomplete Policy', 'value' => 'missing scores result in "Incomplete" status'],
            ['rule' => 'Finalization Policy', 'value' => 'locks editing, requires Admin request for changes'],
            ['rule' => 'Honors Criteria', 'value' => 'average >= 90 with no failing grades in any subject'],
        ];


        return view('pov.teacher.grading-rules.grading-rules', compact(
            'policyName', 'schoolYear', 'lastUpdated', 'configuredBy',
            'gradingComponents', 'formula', 'workedExample', 'otherRules',
            'activeAssignments', 'selectedAssignment'
        ));
    }

    public function update(Request $request)
    {

        $validated = $request->validate([
            'teaching_assignment_id' => 'required|exists:teaching_assignments,id',
            'written_work' => 'required|numeric|min:0|max:100',
            'performance_task' => 'required|numeric|min:0|max:100',
            'term_assessment' => 'required|numeric|min:0|max:100',
        ]);

        // Verify the assignment belongs to the authenticated teacher and is active
        $assignment = TeachingAssignment::where('id', $validated['teaching_assignment_id'])
            ->where('teacher_id', Auth::user()->teacher->id)
            ->where('status', 'active')
            ->first();
        if (!$assignment) {
            return redirect()->route('teacher.grading-system.grading-rules')
                ->with('error', 'Invalid teaching assignment.');
        }

        // Validate total equals 100
        $total = $validated['written_work'] + $validated['performance_task'] + $validated['term_assessment'];
        if (abs($total - 100) > 0.01) {
            return redirect()->route('teacher.grading-system.grading-rules', [
                'teaching_assignment_id' => $validated['teaching_assignment_id']
            ])->with('error', 'The sum of all weights must equal exactly 100%.');
        }

        // Get assessment categories
        $categories = AssessmentCategory::all();
        $writtenCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'));
        $performanceCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'));
        $termAssessmentCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment'));

        if (!$writtenCategory || !$performanceCategory || !$termAssessmentCategory) {
            return redirect()->route('teacher.grading-system.grading-rules')
                ->with('error', 'Required assessment categories not found.');
        }

        // Save or update the grading configuration
        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $writtenCategory->id,
            ],
            ['weight' => $validated['written_work']]
        );

        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $performanceCategory->id,
            ],
            ['weight' => $validated['performance_task']]
        );

        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $termAssessmentCategory->id,
            ],
            ['weight' => $validated['term_assessment']]
        );

        $this->syncTermGradesForAssignment($assignment);

        return redirect()->route('teacher.grading-system.grading-rules', [
            'teaching_assignment_id' => $validated['teaching_assignment_id']
        ])->with('success', 'Grading rules updated successfully.');
    }

    public function restoreDefaults(Request $request)
    {
        $validated = $request->validate([
            'teaching_assignment_id' => 'required|exists:teaching_assignments,id',
        ]);

        // Verify the assignment belongs to the authenticated teacher and is active
        $assignment = TeachingAssignment::where('id', $validated['teaching_assignment_id'])
            ->where('teacher_id', Auth::user()->teacher->id)
            ->where('status', 'active')
            ->first();
        if (!$assignment) {
            return redirect()->route('teacher.grading-system.grading-rules')
                ->with('error', 'Invalid teaching assignment.');
        }

        // Get assessment categories using same logic as GradingService
        $categories = AssessmentCategory::all();
        $writtenCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'));
        $performanceCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'));
        $termAssessmentCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment') || str_contains(strtolower($c->name), 'exam'));

        if (!$writtenCategory || !$performanceCategory || !$termAssessmentCategory) {
            return redirect()->route('teacher.grading-system.grading-rules')
                ->with('error', 'Required assessment categories not found.');
        }

        // Get the actual fallback weights that the backend would use
        $fallbackWeights = $this->weightResolver->resolve($assignment->subject);
        
        // Create configuration with the actual fallback weights
        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $writtenCategory->id,
            ],
            ['weight' => $fallbackWeights['written_work'] * 100]
        );

        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $performanceCategory->id,
            ],
            ['weight' => $fallbackWeights['performance_task'] * 100]
        );

        GradingConfig::updateOrCreate(
            [
                'teaching_assignment_id' => $validated['teaching_assignment_id'],
                'assessment_category_id' => $termAssessmentCategory->id,
            ],
            ['weight' => $fallbackWeights['term_assessment'] * 100]
        );

        $this->syncTermGradesForAssignment($assignment);

        return redirect()->route('teacher.grading-system.grading-rules', [
            'teaching_assignment_id' => $validated['teaching_assignment_id']
        ])->with('success', 'Default grading rules restored successfully.');
    }

    /**
     * Recalculate term grades for all enrolled students in assignments with active assessments.
     */
    private function syncTermGradesForAssignment(TeachingAssignment $assignment): void
    {
        $enrollmentIds = Enrollment::where('section_id', $assignment->section_id)
            ->whereIn('status', ['active', 'enrolled'])
            ->pluck('id');

        $gradingPeriodIds = Assessment::where('teaching_assignment_id', $assignment->id)
            ->where('status', 'active')
            ->distinct()
            ->pluck('grading_period_id');

        foreach ($gradingPeriodIds as $gpId) {
            foreach ($enrollmentIds as $enrollmentId) {
                $this->gradingService->recalculateTermGrade($enrollmentId, $assignment->id, $gpId);
            }
        }
    }

    private function resolveWeightsForAssignment(int $teachingAssignmentId): array
    {
        // Get the teaching assignment to access the subject
        $teachingAssignment = TeachingAssignment::findOrFail($teachingAssignmentId);
        $subject = $teachingAssignment->subject;

        // Get category IDs using same logic as GradingService
        $categories = AssessmentCategory::all();
        $categoryIds = [
            'written' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'))?->id,
            'performance' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'))?->id,
            'term_assessment' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment') || str_contains(strtolower($c->name), 'exam'))?->id,
        ];

        // Use the same logic as GradingService.resolveWeights()
        $configuredWeights = GradingConfig::where('teaching_assignment_id', $teachingAssignmentId)
            ->whereIn('assessment_category_id', array_filter($categoryIds))
            ->pluck('weight', 'assessment_category_id');

        if (collect($categoryIds)->every(fn ($id) => $id && $configuredWeights->has($id))) {
            return [
                'written_work' => (float) $configuredWeights[$categoryIds['written']] / 100,
                'performance_task' => (float) $configuredWeights[$categoryIds['performance']] / 100,
                'term_assessment' => (float) $configuredWeights[$categoryIds['term_assessment']] / 100,
            ];
        }

        // Fall back to SubjectWeightResolver (same as GradingService)
        return $this->weightResolver->resolve($subject);
    }

    private function getLastUpdatedDate(int $teachingAssignmentId): string
    {
        $latestConfig = GradingConfig::where('teaching_assignment_id', $teachingAssignmentId)
            ->latest('updated_at')
            ->first();

        return $latestConfig
            ? $latestConfig->updated_at->format('F j, Y')
            : 'No configuration';
    }
}





