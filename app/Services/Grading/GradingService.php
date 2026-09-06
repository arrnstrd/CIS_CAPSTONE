<?php

namespace App\Services\Grading;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\StudentAssessmentScore;
use App\Models\TermGrade;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
use App\Models\GradingConfig;
use App\Services\Grading\SubjectWeightResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradingService
{
    protected SubjectWeightResolver $weightResolver;

    public function __construct(SubjectWeightResolver $weightResolver)
    {
        $this->weightResolver = $weightResolver;
    }

    /**
     * Record or update a student's score for an assessment.
     *
     * @param array{assessment_id: int, enrollment_id: int, score: float, remarks?: string} $data
     * @return StudentAssessmentScore
     * @throws \InvalidArgumentException
     */
    public function recordScore(array $data): StudentAssessmentScore
    {
        $assessment = Assessment::findOrFail($data['assessment_id']);

        if ($data['score'] < 0 || $data['score'] > $assessment->total_items) {
            throw new \InvalidArgumentException("The score cannot exceed the assessment total items ({$assessment->total_items}) or be less than 0.");
        }

        return DB::transaction(function () use ($data, $assessment) {
            $score = StudentAssessmentScore::updateOrCreate(
                [
                    'assessment_id' => $data['assessment_id'],
                    'enrollment_id' => $data['enrollment_id'],
                ],
                [
                    'score' => $data['score'],
                    'remarks' => $data['remarks'] ?? null,
                ]
            );

            // Recalculate Term Grade for this student, teaching assignment, and grading period
            $this->recalculateTermGrade(
                $data['enrollment_id'],
                $assessment->teaching_assignment_id,
                $assessment->grading_period_id
            );

            return $score;
        });
    }

    /**
     * Recalculate the term grade for a student.
     *
     * @param int $enrollmentId
     * @param int $teachingAssignmentId
     * @param int $gradingPeriodId
     * @return TermGrade|null
     */
    public function recalculateTermGrade(
        int $enrollmentId,
        int $teachingAssignmentId,
        int $gradingPeriodId
    ): ?TermGrade {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $teachingAssignment = TeachingAssignment::with('subject')->findOrFail($teachingAssignmentId);
        $subject = $teachingAssignment->subject;

        if (!$subject) {
            Log::error("Subject not found for teaching assignment ID: {$teachingAssignmentId}");
            return null;
        }

        // Fetch all categories to match assessments
        $categories = AssessmentCategory::all();

        $writtenCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'));
        $performanceCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'));
        $termAssessmentCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment') || str_contains(strtolower($c->name), 'exam'));

        $weights = $this->resolveWeights(
            $teachingAssignmentId,
            $subject,
            [
                'written' => $writtenCategory?->id,
                'performance' => $performanceCategory?->id,
                'term_assessment' => $termAssessmentCategory?->id,
            ]
        );

        // Load all assessments for this assignment and grading period
        $assessments = Assessment::where('teaching_assignment_id', $teachingAssignmentId)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('status', 'active')
            ->get();

        // Get all existing scores for this student in these assessments
        $scores = StudentAssessmentScore::where('enrollment_id', $enrollmentId)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        $componentSummaries = $this->calculateComponentSummaries(
            $assessments,
            $scores,
            $weights,
            [
                'written' => $writtenCategory?->id,
                'performance' => $performanceCategory?->id,
                'term_assessment' => $termAssessmentCategory?->id,
            ]
        );

        // If no assessments exist at all, we cannot compute any grade yet
        if (collect($componentSummaries)->every(fn (array $summary) => $summary['hps'] === 0.0)) {
            return DB::transaction(function () use ($teachingAssignmentId, $enrollmentId, $gradingPeriodId) {
                return TermGrade::updateOrCreate(
                    [
                        'teaching_assignment_id' => $teachingAssignmentId,
                        'enrollment_id' => $enrollmentId,
                        'grading_period_id' => $gradingPeriodId,
                    ],
                    [
                        'written_work_grade' => null,
                        'performance_task_grade' => null,
                        'term_assessment_grade' => null,
                        'initial_grade' => null,
                        'transmuted_grade' => null,
                    ]
                );
            });
        }

        $computedGrades = collect($componentSummaries)->mapWithKeys(
            fn (array $summary, string $key) => [$key => $summary['ws']]
        )->all();
        $initialGrade = round(collect($componentSummaries)->sum('ws'), 2);
        $transmutedGrade = $this->transmute($initialGrade);

        return DB::transaction(function () use ($teachingAssignmentId, $enrollmentId, $gradingPeriodId, $computedGrades, $initialGrade, $transmutedGrade) {
            return TermGrade::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignmentId,
                    'enrollment_id' => $enrollmentId,
                    'grading_period_id' => $gradingPeriodId,
                ],
                [
                    'written_work_grade' => $computedGrades['written'],
                    'performance_task_grade' => $computedGrades['performance'],
                    'term_assessment_grade' => $computedGrades['term_assessment'],
                    'initial_grade' => $initialGrade,
                    'transmuted_grade' => $transmutedGrade,
                ]
            );
        });
    }

    /**
     * Calculate School Class Record component totals and scores.
     * Missing learner scores remain zero while each active assessment HPS is included.
     *
     * @return array<string, array{total: float, hps: float, ps: ?float, ws: ?float, weight: float}>
     */
    public function calculateComponentSummaries($assessments, $scores, array $weights, array $categoryIds): array
    {
        $summaries = [
            'written' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['written_work']],
            'performance' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['performance_task']],
            'term_assessment' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['term_assessment']],
        ];

        foreach ($assessments as $assessment) {
            $component = array_search($assessment->assessment_category_id, $categoryIds, true);
            if ($component === false) {
                continue;
            }

            $summaries[$component]['total'] += $scores->has($assessment->id) ? (float) $scores[$assessment->id]->score : 0.0;
            $summaries[$component]['hps'] += (float) $assessment->total_items;
        }

        foreach ($summaries as $component => $summary) {
            $ps = $summary['hps'] > 0 ? ($summary['total'] / $summary['hps']) * 100 : null;
            $summaries[$component]['ps'] = $ps === null ? null : round($ps, 2);
            $summaries[$component]['ws'] = $ps === null ? null : round($ps * $summary['weight'], 2);
            $summaries[$component]['total'] = round($summary['total'], 2);
            $summaries[$component]['hps'] = round($summary['hps'], 2);
        }

        return $summaries;
    }

    /** Return the authoritative Class Record summaries for a saved Grade Sheet row. */
    public function getComponentSummaries(int $enrollmentId, int $teachingAssignmentId, int $gradingPeriodId): array
    {
        $subject = TeachingAssignment::with('subject')->findOrFail($teachingAssignmentId)->subject;
        $categories = AssessmentCategory::all();
        $categoryIds = [
            'written' => $categories->first(fn ($category) => str_contains(strtolower($category->name), 'written'))?->id,
            'performance' => $categories->first(fn ($category) => str_contains(strtolower($category->name), 'performance'))?->id,
            'term_assessment' => $categories->first(fn ($category) => str_contains(strtolower($category->name), 'term assessment') || str_contains(strtolower($category->name), 'exam'))?->id,
        ];
        $assessments = Assessment::where('teaching_assignment_id', $teachingAssignmentId)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('status', 'active')
            ->get();
        $scores = StudentAssessmentScore::where('enrollment_id', $enrollmentId)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        return $this->calculateComponentSummaries(
            $assessments,
            $scores,
            $this->resolveWeights($teachingAssignmentId, $subject, $categoryIds),
            $categoryIds
        );
    }

    /**
     * Prefer a complete per-assignment configuration; otherwise preserve the
     * established subject-based resolver as the fallback.
     */
    public function resolveWeights(int $teachingAssignmentId, $subject, array $categoryIds): array
    {
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

        return $this->weightResolver->resolve($subject);
    }

    /**
     * Transmute an initial grade into a report card grade.
     * Matches official DepEd Transmutation Table (DO 8, s. 2015).
     *
     * @param float $initialGrade
     * @return float
     */
    public function transmute(float $initialGrade): float
    {
        $g = round($initialGrade, 2);

        if ($g >= 100) return 100;
        if ($g >= 98.40) return 99;
        if ($g >= 96.80) return 98;
        if ($g >= 95.20) return 97;
        if ($g >= 93.60) return 96;
        if ($g >= 92.00) return 95;
        if ($g >= 90.40) return 94;
        if ($g >= 88.80) return 93;
        if ($g >= 87.20) return 92;
        if ($g >= 85.60) return 91;
        if ($g >= 84.00) return 90;
        if ($g >= 82.40) return 89;
        if ($g >= 80.80) return 88;
        if ($g >= 79.20) return 87;
        if ($g >= 77.60) return 86;
        if ($g >= 76.00) return 85;
        if ($g >= 74.40) return 84;
        if ($g >= 72.80) return 83;
        if ($g >= 71.20) return 82;
        if ($g >= 69.60) return 81;
        if ($g >= 68.00) return 80;
        if ($g >= 66.40) return 79;
        if ($g >= 64.80) return 78;
        if ($g >= 63.20) return 77;
        if ($g >= 61.60) return 76;
        if ($g >= 60.00) return 75;
        if ($g >= 56.00) return 74;
        if ($g >= 52.00) return 73;
        if ($g >= 48.00) return 72;
        if ($g >= 44.00) return 71;
        if ($g >= 40.00) return 70;
        if ($g >= 36.00) return 69;
        if ($g >= 32.00) return 68;
        if ($g >= 28.00) return 67;
        if ($g >= 24.00) return 66;
        if ($g >= 20.00) return 65;
        if ($g >= 16.00) return 64;
        if ($g >= 12.00) return 63;
        if ($g >= 8.00) return 62;
        if ($g >= 4.00) return 61;
        return 60;
    }
}
