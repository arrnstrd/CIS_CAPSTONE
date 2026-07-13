<?php

namespace App\Services\Grading;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\StudentAssessmentScore;
use App\Models\QuarterlyGrade;
use App\Models\Enrollment;
use App\Models\TeachingAssignment;
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

            // Recalculate Quarterly Grade for this student, teaching assignment, and grading period
            $this->recalculateQuarterlyGrade(
                $data['enrollment_id'],
                $assessment->teaching_assignment_id,
                $assessment->grading_period_id
            );

            return $score;
        });
    }

    /**
     * Recalculate the quarterly grade for a student.
     *
     * @param int $enrollmentId
     * @param int $teachingAssignmentId
     * @param int $gradingPeriodId
     * @return QuarterlyGrade|null
     */
    public function recalculateQuarterlyGrade(
        int $enrollmentId,
        int $teachingAssignmentId,
        int $gradingPeriodId
    ): ?QuarterlyGrade {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $teachingAssignment = TeachingAssignment::with('subject')->findOrFail($teachingAssignmentId);
        $subject = $teachingAssignment->subject;

        if (!$subject) {
            Log::error("Subject not found for teaching assignment ID: {$teachingAssignmentId}");
            return null;
        }

        // Get standard weights from resolver
        $weights = $this->weightResolver->resolve($subject);

        // Fetch all categories to match assessments
        $categories = AssessmentCategory::all();

        $writtenCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'));
        $performanceCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'));
        $quarterlyCategory = $categories->first(fn($c) => str_contains(strtolower($c->name), 'quarterly') || str_contains(strtolower($c->name), 'exam') || str_contains(strtolower($c->name), 'assessment'));

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

        // Group assessments and compute scores for each category
        $categoryScores = [
            'written' => ['score' => 0.0, 'total' => 0, 'weight' => $weights['written_work']],
            'performance' => ['score' => 0.0, 'total' => 0, 'weight' => $weights['performance_task']],
            'quarterly' => ['score' => 0.0, 'total' => 0, 'weight' => $weights['quarterly_assessment']],
        ];

        foreach ($assessments as $assessment) {
            $scoreVal = $scores->has($assessment->id) ? (float)$scores[$assessment->id]->score : 0.0;
            $totalVal = (int)$assessment->total_items;

            if ($writtenCategory && $assessment->assessment_category_id === $writtenCategory->id) {
                $categoryScores['written']['score'] += $scoreVal;
                $categoryScores['written']['total'] += $totalVal;
            } elseif ($performanceCategory && $assessment->assessment_category_id === $performanceCategory->id) {
                $categoryScores['performance']['score'] += $scoreVal;
                $categoryScores['performance']['total'] += $totalVal;
            } elseif ($quarterlyCategory && $assessment->assessment_category_id === $quarterlyCategory->id) {
                $categoryScores['quarterly']['score'] += $scoreVal;
                $categoryScores['quarterly']['total'] += $totalVal;
            }
        }

        // Active weights normalization (mid-quarter grades scaling)
        $totalActiveWeight = 0.0;
        foreach ($categoryScores as $key => $data) {
            if ($data['total'] > 0) {
                $totalActiveWeight += $data['weight'];
            }
        }

        // If no assessments exist at all, we cannot compute any grade yet
        if ($totalActiveWeight <= 0.0) {
            return DB::transaction(function () use ($teachingAssignmentId, $enrollmentId, $gradingPeriodId) {
                return QuarterlyGrade::updateOrCreate(
                    [
                        'teaching_assignment_id' => $teachingAssignmentId,
                        'enrollment_id' => $enrollmentId,
                        'grading_period_id' => $gradingPeriodId,
                    ],
                    [
                        'written_work_grade' => null,
                        'performance_task_grade' => null,
                        'quarterly_assessment_grade' => null,
                        'initial_grade' => null,
                        'transmuted_grade' => null,
                    ]
                );
            });
        }

        // Calculate Weighted Scores (WS) using normalized weights
        $computedGrades = [];
        $initialGrade = 0.0;

        foreach ($categoryScores as $key => $data) {
            if ($data['total'] > 0) {
                $percentageScore = ($data['score'] / $data['total']) * 100.0;
                $normalizedWeight = $data['weight'] / $totalActiveWeight;
                $weightedScore = $percentageScore * $normalizedWeight;
                
                $computedGrades[$key] = round($weightedScore, 2);
                $initialGrade += $weightedScore;
            } else {
                $computedGrades[$key] = null;
            }
        }

        $initialGrade = round($initialGrade, 2);
        $transmutedGrade = $this->transmute($initialGrade);

        return DB::transaction(function () use ($teachingAssignmentId, $enrollmentId, $gradingPeriodId, $computedGrades, $initialGrade, $transmutedGrade) {
            return QuarterlyGrade::updateOrCreate(
                [
                    'teaching_assignment_id' => $teachingAssignmentId,
                    'enrollment_id' => $enrollmentId,
                    'grading_period_id' => $gradingPeriodId,
                ],
                [
                    'written_work_grade' => $computedGrades['written'],
                    'performance_task_grade' => $computedGrades['performance'],
                    'quarterly_assessment_grade' => $computedGrades['quarterly'],
                    'initial_grade' => $initialGrade,
                    'transmuted_grade' => $transmutedGrade,
                ]
            );
        });
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
