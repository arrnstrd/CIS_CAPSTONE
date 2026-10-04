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
    protected SchoolLevelDetector $levelDetector;

    public function __construct(
        SubjectWeightResolver $weightResolver,
        ?SchoolLevelDetector $levelDetector = null
    ) {
        $this->weightResolver = $weightResolver;
        $this->levelDetector = $levelDetector ?? new SchoolLevelDetector();
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
        $teachingAssignment = TeachingAssignment::with(['subject', 'section'])->findOrFail($teachingAssignmentId);
        $subject = $teachingAssignment->subject;

        if (!$subject) {
            Log::error("Subject not found for teaching assignment ID: {$teachingAssignmentId}");
            return null;
        }

        $level = $this->levelDetector->detect($teachingAssignment->section?->grade_level ?? 1);

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
            ],
            $level
        );

        $computedGrades = collect($componentSummaries)->mapWithKeys(
            fn (array $summary, string $key) => [$key => $summary['ws']]
        )->all();

        $initialGrade = $this->calculateInitialGrade($componentSummaries, $level);
        $transmutedGrade = $initialGrade === null
            ? null
            : $this->transmute($initialGrade);

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
     * Calculate Initial Grade respecting level-specific ECR template rules:
     * - Grades 1-10 (Elementary & JHS): ROUND(sum(ws), 0) (whole number integer).
     * - SHS (Grades 11-12): ROUND(sum(ws), 2) (preserves 2 decimal places).
     *
     * @param array<string, array{total: float, hps: float, ps: ?float, ws: ?float, weight: float}> $componentSummaries
     * @param string|null $level
     * @return float|null
     */
    public function calculateInitialGrade(array $componentSummaries, ?string $level = null): ?float
    {
        $isComplete = collect($componentSummaries)->every(
            fn (array $summary) => ($summary['hps'] ?? 0) > 0 && ($summary['ws'] ?? null) !== null
        );

        if (!$isComplete) {
            return null;
        }

        $rawSum = (float) collect($componentSummaries)->sum('ws');

        return ($level === 'shs')
            ? round($rawSum, 2)
            : (float) round($rawSum, 0);
    }

    /**
     * Calculate School Class Record component totals and scores.
     * Missing learner scores remain zero while each active assessment HPS is included.
     *
     * @param \Illuminate\Support\Collection|array $assessments
     * @param \Illuminate\Support\Collection $scores
     * @param array $weights
     * @param array $categoryIds
     * @param string|null $level
     * @return array<string, array{total: float, hps: float, ps: ?float, ws: ?float, weight: float}>
     */
    public function calculateComponentSummaries($assessments, $scores, array $weights, array $categoryIds, ?string $level = null): array
    {
        $summaries = [
            'written' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['written_work'] ?? ($weights['written'] ?? 0.0)],
            'performance' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['performance_task'] ?? ($weights['performance'] ?? 0.0)],
            'term_assessment' => ['total' => 0.0, 'hps' => 0.0, 'weight' => $weights['term_assessment'] ?? ($weights['exam'] ?? 0.0)],
        ];

        $examAssessments = [];

        foreach ($assessments as $assessment) {
            $component = array_search($assessment->assessment_category_id, $categoryIds, true);
            if ($component === false) {
                continue;
            }

            if ($component === 'term_assessment') {
                $examAssessments[] = $assessment;
            }

            $summaries[$component]['total'] += $scores->has($assessment->id) ? (float) $scores[$assessment->id]->score : 0.0;
            $summaries[$component]['hps'] += (float) $assessment->total_items;
        }

        foreach ($summaries as $component => $summary) {
            if ($component === 'term_assessment' && in_array($level, ['elementary', 'jhs'], true)) {
                // Official DepEd Grades 1-10 (Elementary & JHS) ECR structure:
                // ST1 (Slot 1) = 30%, ST2 (Slot 2) = 30%, TE (Slot 3) = 40%
                if ($summary['hps'] <= 0 || empty($examAssessments)) {
                    $summaries[$component]['ps'] = null;
                    $summaries[$component]['ws'] = null;
                } else {
                    $examPs = 0.0;
                    $slotWeights = [1 => 30.0, 2 => 30.0, 3 => 40.0];

                    foreach ($examAssessments as $index => $exam) {
                        $slot = $exam->slot_number ?? ($index + 1);
                        $slotWeight = $slotWeights[$slot] ?? 0.0;
                        $itemHps = (float) $exam->total_items;
                        $itemScore = $scores->has($exam->id) ? (float) $scores[$exam->id]->score : 0.0;

                        if ($itemHps > 0 && $slotWeight > 0) {
                            $examPs += round(($itemScore / $itemHps) * $slotWeight, 2);
                        }
                    }

                    $roundedExamPs = round($examPs, 2);
                    $summaries[$component]['ps'] = $roundedExamPs;
                    $summaries[$component]['ws'] = round($roundedExamPs * $summary['weight'], 2);
                }
            } else {
                $rawPs = $summary['hps'] > 0 ? ($summary['total'] / $summary['hps']) * 100 : null;
                $roundedPs = $rawPs === null ? null : round($rawPs, 2);
                $summaries[$component]['ps'] = $roundedPs;
                $summaries[$component]['ws'] = $roundedPs === null ? null : round($roundedPs * $summary['weight'], 2);
            }

            $summaries[$component]['total'] = round($summary['total'], 2);
            $summaries[$component]['hps'] = round($summary['hps'], 2);
        }

        return $summaries;
    }

    /** Return the authoritative Class Record summaries for a saved Grade Sheet row. */
    public function getComponentSummaries(int $enrollmentId, int $teachingAssignmentId, int $gradingPeriodId): array
    {
        $ta = TeachingAssignment::with(['subject', 'section'])->findOrFail($teachingAssignmentId);
        $subject = $ta->subject;
        $level = $this->levelDetector->detect($ta->section?->grade_level ?? 1);
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
            $categoryIds,
            $level
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
     * Matches official DepEd 3-Term Electronic Class Record (ECR) Transmutation Table.
     *
     * @param float $initialGrade
     * @return float
     */
    public function transmute(float $initialGrade): float
    {
        $g = round($initialGrade, 0);

        if ($g >= 100) return 100;
        if ($g >= 99) return 99;
        if ($g >= 98) return 98;
        if ($g >= 96) return 97;
        if ($g >= 95) return 96;
        if ($g >= 94) return 95;
        if ($g >= 93) return 94;
        if ($g >= 92) return 93;
        if ($g >= 91) return 92;
        if ($g >= 89) return 91;
        if ($g >= 88) return 90;
        if ($g >= 87) return 89;
        if ($g >= 86) return 88;
        if ($g >= 85) return 87;
        if ($g >= 83) return 86;
        if ($g >= 82) return 85;
        if ($g >= 81) return 84;
        if ($g >= 80) return 83;
        if ($g >= 79) return 82;
        if ($g >= 78) return 81;
        if ($g >= 76) return 80;
        if ($g >= 75) return 79;
        if ($g >= 74) return 78;
        if ($g >= 73) return 77;
        if ($g >= 72) return 76;
        if ($g >= 70) return 75;
        if ($g >= 66) return 74;
        if ($g >= 61) return 73;
        if ($g >= 57) return 72;
        if ($g >= 52) return 71;
        if ($g >= 47) return 70;
        if ($g >= 43) return 69;
        if ($g >= 38) return 68;
        if ($g >= 33) return 67;
        if ($g >= 29) return 66;
        if ($g >= 24) return 65;
        if ($g >= 19) return 64;
        if ($g >= 15) return 63;
        if ($g >= 10) return 62;
        if ($g >= 5) return 61;
        return 60;
    }
}
