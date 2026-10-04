<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\StudentAssessmentScore;
use App\Services\Grading\GradingService;
use App\Services\Grading\PerformanceDescriptorResolver;
use App\Services\Grading\SubjectWeightResolver;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class GradingServiceCalculationTest extends TestCase
{
    private GradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradingService(new SubjectWeightResolver());
    }

    public function test_it_calculates_class_record_totals_ps_ws_and_initial_grade(): void
    {
        $summaries = $this->service->calculateComponentSummaries(
            collect([
                $this->assessment(1, 1, 20),
                $this->assessment(2, 2, 50),
                $this->assessment(3, 3, 40),
            ]),
            $this->scores([1 => 18, 2 => 40, 3 => 28]),
            ['written_work' => 0.20, 'performance_task' => 0.50, 'term_assessment' => 0.30],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
        );

        $this->assertSame(18.0, $summaries['written']['total']);
        $this->assertSame(20.0, $summaries['written']['hps']);
        $this->assertSame(90.0, $summaries['written']['ps']);
        $this->assertSame(18.0, $summaries['written']['ws']);
        $this->assertSame(80.0, $summaries['performance']['ps']);
        $this->assertSame(40.0, $summaries['performance']['ws']);
        $this->assertSame(70.0, $summaries['term_assessment']['ps']);
        $this->assertSame(21.0, $summaries['term_assessment']['ws']);
        $this->assertSame(79.0, collect($summaries)->sum('ws'));
        $this->assertSame(82.0, $this->service->transmute(79.0));
        $this->assertSame('Benchmarking', PerformanceDescriptorResolver::resolve(82.0)['description']);
        $this->assertSame('Passed', PerformanceDescriptorResolver::resolve(82.0)['remarks']);
    }

    public function test_ecr_transmutation_and_descriptor_table(): void
    {
        // 70 -> 75 (Passing threshold)
        $this->assertSame(75.0, $this->service->transmute(70.0));
        $this->assertSame(75.0, $this->service->transmute(71.0));
        $this->assertSame('Connecting', PerformanceDescriptorResolver::resolve(75.0)['description']);
        $this->assertSame('Passed', PerformanceDescriptorResolver::resolve(75.0)['remarks']);

        // 69 -> 74 (Failing)
        $this->assertSame(74.0, $this->service->transmute(69.0));
        $this->assertSame('Developing', PerformanceDescriptorResolver::resolve(74.0)['description']);
        $this->assertSame('Failed', PerformanceDescriptorResolver::resolve(74.0)['remarks']);

        // 80 -> 83, 85 -> 87 (Benchmarking)
        $this->assertSame(83.0, $this->service->transmute(80.0));
        $this->assertSame('Benchmarking', PerformanceDescriptorResolver::resolve(83.0)['description']);
        $this->assertSame('Passed', PerformanceDescriptorResolver::resolve(83.0)['remarks']);

        // 90 -> 91, 100 -> 100 (Advancing)
        $this->assertSame(91.0, $this->service->transmute(90.0));
        $this->assertSame(100.0, $this->service->transmute(100.0));
        $this->assertSame('Advancing', PerformanceDescriptorResolver::resolve(91.0)['description']);

        // 0 -> 60 (Emerging)
        $this->assertSame(60.0, $this->service->transmute(0.0));
        $this->assertSame('Emerging', PerformanceDescriptorResolver::resolve(60.0)['description']);
        $this->assertSame('Failed', PerformanceDescriptorResolver::resolve(60.0)['remarks']);
    }

    public function test_initial_grade_whole_number_rounding_before_transmutation(): void
    {
        // 69.5 rounds to 70 before transmutation -> 75
        $this->assertSame(75.0, $this->service->transmute(69.5));
        // 69.4 rounds to 69 before transmutation -> 74
        $this->assertSame(74.0, $this->service->transmute(69.4));
    }

    public function test_missing_examination_does_not_normalize_ww_and_pt_weights(): void
    {
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(1, 1, 20), $this->assessment(2, 2, 50)]),
            $this->scores([1 => 18, 2 => 40]),
            ['written_work' => 0.20, 'performance_task' => 0.50, 'term_assessment' => 0.30],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
        );

        $this->assertSame(18.0, $summaries['written']['ws']);
        $this->assertSame(40.0, $summaries['performance']['ws']);
        $this->assertNull($summaries['term_assessment']['ps']);
        $this->assertNull($summaries['term_assessment']['ws']);
        $this->assertSame(58.0, collect($summaries)->sum('ws'));
    }

    public function test_missing_learner_score_counts_as_zero_while_hps_is_retained(): void
    {
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(1, 1, 20)]),
            collect(),
            ['written_work' => 0.20, 'performance_task' => 0.50, 'term_assessment' => 0.30],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
        );

        $this->assertSame(0.0, $summaries['written']['total']);
        $this->assertSame(20.0, $summaries['written']['hps']);
        $this->assertSame(0.0, $summaries['written']['ps']);
        $this->assertSame(0.0, $summaries['written']['ws']);
    }

    public function test_it_calculates_with_custom_configured_weights(): void
    {
        // Custom weights configured by teacher: WW 50%, PT 30%, EX 20%
        $customWeights = ['written_work' => 0.50, 'performance_task' => 0.30, 'term_assessment' => 0.20];

        $summaries = $this->service->calculateComponentSummaries(
            collect([
                $this->assessment(1, 1, 100), // WW: 80 / 100 = 80%
                $this->assessment(2, 2, 100), // PT: 90 / 100 = 90%
                $this->assessment(3, 3, 100), // EX: 70 / 100 = 70%
            ]),
            $this->scores([1 => 80, 2 => 90, 3 => 70]),
            $customWeights,
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
        );

        // WW WS: 80% * 0.50 = 40.00
        $this->assertSame(40.0, $summaries['written']['ws']);
        // PT WS: 90% * 0.30 = 27.00
        $this->assertSame(27.0, $summaries['performance']['ws']);
        // EX WS: 70% * 0.20 = 14.00
        $this->assertSame(14.0, $summaries['term_assessment']['ws']);

        // Initial Grade = 40 + 27 + 14 = 81.00
        $initialGrade = collect($summaries)->sum('ws');
        $this->assertSame(81.0, $initialGrade);
        // Transmuted Grade for 81.0 in ECR 3-Term table = 84.0
        $this->assertSame(84.0, $this->service->transmute($initialGrade));
    }

    public function test_subsequent_weight_configuration_updates_calculated_grades(): void
    {
        // Subsequent change: WW 40%, PT 40%, EX 20%
        $updatedWeights = ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20];

        $summaries = $this->service->calculateComponentSummaries(
            collect([
                $this->assessment(1, 1, 100),
                $this->assessment(2, 2, 100),
                $this->assessment(3, 3, 100),
            ]),
            $this->scores([1 => 80, 2 => 90, 3 => 70]),
            $updatedWeights,
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
        );

        // WW WS: 80% * 0.40 = 32.00
        $this->assertSame(32.0, $summaries['written']['ws']);
        // PT WS: 90% * 0.40 = 36.00
        $this->assertSame(36.0, $summaries['performance']['ws']);
        // EX WS: 70% * 0.20 = 14.00
        $this->assertSame(14.0, $summaries['term_assessment']['ws']);

        // Initial Grade = 32 + 36 + 14 = 82.00
        $initialGrade = collect($summaries)->sum('ws');
        $this->assertSame(82.0, $initialGrade);
        // Transmuted Grade for 82.0 in ECR 3-Term table = 85.0
        $this->assertSame(85.0, $this->service->transmute($initialGrade));
    }

    public function test_ecr_blank_component_completion_cases(): void
    {
        $weights = ['written_work' => 0.20, 'performance_task' => 0.50, 'term_assessment' => 0.30];
        $categoryIds = ['written' => 1, 'performance' => 2, 'term_assessment' => 3];

        // Case 1: WW only (18/20)
        $case1Summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(1, 1, 20)]),
            $this->scores([1 => 18]),
            $weights,
            $categoryIds
        );
        $this->assertSame(18.0, $case1Summaries['written']['ws']);
        $this->assertNull($case1Summaries['performance']['ws']);
        $this->assertNull($case1Summaries['term_assessment']['ws']);
        $this->assertFalse(collect($case1Summaries)->every(fn ($s) => $s['hps'] > 0 && $s['ws'] !== null));

        // Case 2: PT only (45/50)
        $case2Summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(2, 2, 50)]),
            $this->scores([2 => 45]),
            $weights,
            $categoryIds
        );
        $this->assertNull($case2Summaries['written']['ws']);
        $this->assertSame(45.0, $case2Summaries['performance']['ws']);
        $this->assertNull($case2Summaries['term_assessment']['ws']);
        $this->assertFalse(collect($case2Summaries)->every(fn ($s) => $s['hps'] > 0 && $s['ws'] !== null));

        // Case 3: EX only (ST1 25/100 weighted out of 30% exam component -> 25/100 * 30% = 7.50)
        $case3Summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(3, 3, 100)]),
            $this->scores([3 => 25]),
            $weights,
            $categoryIds
        );
        $this->assertNull($case3Summaries['written']['ws']);
        $this->assertNull($case3Summaries['performance']['ws']);
        $this->assertSame(7.5, $case3Summaries['term_assessment']['ws']);
        $this->assertFalse(collect($case3Summaries)->every(fn ($s) => $s['hps'] > 0 && $s['ws'] !== null));

        // Case 4: All 3 components entered (WW 18/20 = 18.00, PT 45/50 = 45.00, EX 85/100 = 25.50)
        $case4Summaries = $this->service->calculateComponentSummaries(
            collect([
                $this->assessment(1, 1, 20),
                $this->assessment(2, 2, 50),
                $this->assessment(3, 3, 100),
            ]),
            $this->scores([1 => 18, 2 => 45, 3 => 85]),
            $weights,
            $categoryIds
        );
        $this->assertSame(18.0, $case4Summaries['written']['ws']);
        $this->assertSame(45.0, $case4Summaries['performance']['ws']);
        $this->assertSame(25.5, $case4Summaries['term_assessment']['ws']);
        $this->assertTrue(collect($case4Summaries)->every(fn ($s) => $s['hps'] > 0 && $s['ws'] !== null));

        $initialGrade = round(collect($case4Summaries)->sum('ws'), 0);
        $this->assertSame(89.0, $initialGrade);
        $transmuted = $this->service->transmute($initialGrade);
        $this->assertSame(91.0, $transmuted);
        $descriptor = PerformanceDescriptorResolver::resolve($transmuted);
        $this->assertSame('Advancing', $descriptor['description']);
        $this->assertSame('Passed', $descriptor['remarks']);
    }

    public function test_mark_angeles_verified_ecr_calculation(): void
    {
        $weights = ['written_work' => 0.20, 'performance_task' => 0.50, 'term_assessment' => 0.30];
        $categoryIds = ['written' => 1, 'performance' => 2, 'term_assessment' => 3];

        $assessments = collect([
            $this->assessment(1, 1, 20), // WW1
            $this->assessment(2, 1, 20), // WW2
            $this->assessment(3, 1, 20), // WW3
            $this->assessment(4, 1, 20), // WW4
            $this->assessment(5, 1, 20), // WW5
            $this->assessment(6, 2, 20), // PT1
            $this->assessment(7, 2, 20), // PT2
            $this->assessment(8, 2, 60), // PT3
            $this->assessment(9, 3, 50), // EX1
        ]);

        $scores = $this->scores([
            1 => 10,
            2 => 20,
            3 => 15,
            4 => 15,
            5 => 14,
            6 => 12,
            7 => 15,
            8 => 59,
            9 => 35,
        ]);

        $summaries = $this->service->calculateComponentSummaries($assessments, $scores, $weights, $categoryIds);

        // WW: 74 / 100 -> PS 74.00%, WS 14.80
        $this->assertSame(74.0, $summaries['written']['total']);
        $this->assertSame(100.0, $summaries['written']['hps']);
        $this->assertSame(74.0, $summaries['written']['ps']);
        $this->assertSame(14.80, $summaries['written']['ws']);

        // PT: 86 / 100 -> PS 86.00%, WS 43.00
        $this->assertSame(86.0, $summaries['performance']['total']);
        $this->assertSame(100.0, $summaries['performance']['hps']);
        $this->assertSame(86.00, $summaries['performance']['ps']);
        $this->assertSame(43.00, $summaries['performance']['ws']);

        // EX: 35 / 50 -> PS 70.00%, WS 21.00
        $this->assertSame(35.0, $summaries['term_assessment']['total']);
        $this->assertSame(50.0, $summaries['term_assessment']['hps']);
        $this->assertSame(70.0, $summaries['term_assessment']['ps']);
        $this->assertSame(21.0, $summaries['term_assessment']['ws']);

        // Weighted Sum = 14.80 + 43.00 + 21.00 = 78.80
        $weightedSum = collect($summaries)->sum('ws');
        $this->assertSame(78.80, $weightedSum);

        // Initial Grade preserves 2 decimal places = 78.80
        $initialGrade = round($weightedSum, 2);
        $this->assertSame(78.80, $initialGrade);

        // Transmuted Grade = 82
        $transmuted = $this->service->transmute($initialGrade);
        $this->assertSame(82.0, $transmuted);

        // Descriptor = Benchmarking
        $descriptor = PerformanceDescriptorResolver::resolve($transmuted);
        $this->assertSame('Benchmarking', $descriptor['description']);
        $this->assertSame('Passed', $descriptor['remarks']);
    }

    public function test_rounding_boundaries_and_transmutation_coverage(): void
    {
        // Rounding boundaries around .50
        $this->assertSame(82.0, $this->service->transmute(79.49));
        $this->assertSame(83.0, $this->service->transmute(79.50));
        $this->assertSame(83.0, $this->service->transmute(79.51));

        // Exact official ECR HELPER table values
        $expectedMap = [
            0.0 => 60.0,
            23.0 => 64.0,
            60.0 => 72.0,
            65.0 => 73.0,
            69.0 => 74.0,
            70.0 => 75.0,
            71.0 => 75.0,
            75.0 => 79.0,
            79.0 => 82.0,
            80.0 => 83.0,
            85.0 => 87.0,
            87.0 => 89.0,
            88.0 => 90.0,
            90.0 => 91.0,
            95.0 => 96.0,
            100.0 => 100.0,
        ];

        foreach ($expectedMap as $initial => $expectedTransmuted) {
            $this->assertSame(
                $expectedTransmuted,
                $this->service->transmute($initial),
                "Failed asserting transmutation for Initial Grade {$initial}"
            );
        }
    }

    public function test_grades_1_to_10_st1_only_exam_calculation_with_official_ecr_weights(): void
    {
        // ST1: score 35 / HPS 50 -> (35 / 50) * 30% = 21.00% Exam PS
        // Category weight = 20% -> Exam WS = 21.00 * 0.20 = 4.20
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(1, 3, 50, 1)]),
            $this->scores([1 => 35]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'elementary'
        );

        $this->assertSame(50.0, $summaries['term_assessment']['hps']);
        $this->assertSame(35.0, $summaries['term_assessment']['total']);
        $this->assertSame(21.00, $summaries['term_assessment']['ps']);
        $this->assertSame(4.20, $summaries['term_assessment']['ws']);
    }

    public function test_grades_1_to_10_st2_only_30_percent_slot_weighting(): void
    {
        // ST2: score 24 / HPS 30 -> (24 / 30) * 30% = 24.00% Exam PS
        // Category weight = 20% -> Exam WS = 24.00 * 0.20 = 4.80
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(2, 3, 30, 2)]),
            $this->scores([2 => 24]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'jhs'
        );

        $this->assertSame(24.00, $summaries['term_assessment']['ps']);
        $this->assertSame(4.80, $summaries['term_assessment']['ws']);
    }

    public function test_grades_1_to_10_te_only_40_percent_slot_weighting(): void
    {
        // TE: score 36 / HPS 40 -> (36 / 40) * 40% = 36.00% Exam PS
        // Category weight = 20% -> Exam WS = 36.00 * 0.20 = 7.20
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(3, 3, 40, 3)]),
            $this->scores([3 => 36]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'elementary'
        );

        $this->assertSame(36.00, $summaries['term_assessment']['ps']);
        $this->assertSame(7.20, $summaries['term_assessment']['ws']);
    }

    public function test_grades_1_to_10_st1_st2_te_combined_independent_weighting(): void
    {
        // ST1: 27/30 (27.00), ST2: 24/30 (24.00), TE: 32/40 (32.00) -> PS = 83.00%
        // Category weight = 20% -> WS = 83.00 * 0.20 = 16.60
        $summaries = $this->service->calculateComponentSummaries(
            collect([
                $this->assessment(1, 3, 30, 1),
                $this->assessment(2, 3, 30, 2),
                $this->assessment(3, 3, 40, 3),
            ]),
            $this->scores([1 => 27, 2 => 24, 3 => 32]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'elementary'
        );

        $this->assertSame(83.00, $summaries['term_assessment']['ps']);
        $this->assertSame(16.60, $summaries['term_assessment']['ws']);
    }

    public function test_grades_1_to_10_missing_st2_and_te_contribute_zero(): void
    {
        // ST1: 35/50 (21.00), ST2: absent (0.00), TE: absent (0.00)
        $summaries = $this->service->calculateComponentSummaries(
            collect([$this->assessment(1, 3, 50, 1)]),
            $this->scores([1 => 35]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'elementary'
        );

        $this->assertSame(21.00, $summaries['term_assessment']['ps']);
        $this->assertSame(4.20, $summaries['term_assessment']['ws']);
    }

    public function test_grades_1_to_10_raw_hps_preservation(): void
    {
        $assessment = $this->assessment(1, 3, 50, 1);
        $this->assertSame(50, $assessment->total_items);

        $summaries = $this->service->calculateComponentSummaries(
            collect([$assessment]),
            $this->scores([1 => 35]),
            ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20],
            ['written' => 1, 'performance' => 2, 'term_assessment' => 3],
            'elementary'
        );

        $this->assertSame(50.0, $summaries['term_assessment']['hps']);
        $this->assertSame(50, $assessment->total_items);
    }

    public function test_grades_1_to_10_mark_angeles_official_ecr_full_flow_to_term_grade_74(): void
    {
        $weights = ['written_work' => 0.40, 'performance_task' => 0.40, 'term_assessment' => 0.20];
        $categoryIds = ['written' => 1, 'performance' => 2, 'term_assessment' => 3];

        $assessments = collect([
            $this->assessment(1, 1, 20), // WW1
            $this->assessment(2, 1, 20), // WW2
            $this->assessment(3, 1, 20), // WW3
            $this->assessment(4, 1, 20), // WW4
            $this->assessment(5, 1, 20), // WW5
            $this->assessment(6, 2, 20), // PT1
            $this->assessment(7, 2, 20), // PT2
            $this->assessment(8, 2, 60), // PT3
            $this->assessment(9, 3, 50, 1), // ST1 (slot 1)
        ]);

        $scores = $this->scores([
            1 => 10,
            2 => 20,
            3 => 15,
            4 => 15,
            5 => 14,
            6 => 12,
            7 => 15,
            8 => 59,
            9 => 35,
        ]);

        $summaries = $this->service->calculateComponentSummaries($assessments, $scores, $weights, $categoryIds, 'elementary');

        // WW: 74 / 100 -> PS 74.00%, WS 29.60 (40%)
        $this->assertSame(74.0, $summaries['written']['total']);
        $this->assertSame(100.0, $summaries['written']['hps']);
        $this->assertSame(74.0, $summaries['written']['ps']);
        $this->assertSame(29.60, $summaries['written']['ws']);

        // PT: 86 / 100 -> PS 86.00%, WS 34.40 (40%)
        $this->assertSame(86.0, $summaries['performance']['total']);
        $this->assertSame(100.0, $summaries['performance']['hps']);
        $this->assertSame(86.00, $summaries['performance']['ps']);
        $this->assertSame(34.40, $summaries['performance']['ws']);

        // EX: 35 / 50 -> ST1 PS 21.00%, WS 4.20 (20%)
        $this->assertSame(35.0, $summaries['term_assessment']['total']);
        $this->assertSame(50.0, $summaries['term_assessment']['hps']);
        $this->assertSame(21.00, $summaries['term_assessment']['ps']);
        $this->assertSame(4.20, $summaries['term_assessment']['ws']);

        // Initial Grade = ROUND(29.60 + 34.40 + 4.20, 0) = 68.00
        $initialGrade = $this->service->calculateInitialGrade($summaries, 'elementary');
        $this->assertSame(68.0, $initialGrade);

        // Transmuted Grade = 74
        $transmuted = $this->service->transmute($initialGrade);
        $this->assertSame(74.0, $transmuted);

        // Descriptor = Developing
        $descriptor = PerformanceDescriptorResolver::resolve($transmuted);
        $this->assertSame('Developing', $descriptor['description']);
        $this->assertSame('Failed', $descriptor['remarks']);
    }

    private function assessment(int $id, int $categoryId, int $totalItems, ?int $slotNumber = null): Assessment
    {
        $attributes = ['assessment_category_id' => $categoryId, 'total_items' => $totalItems];
        if ($slotNumber !== null) {
            $attributes['slot_number'] = $slotNumber;
        }
        $assessment = new Assessment($attributes);
        $assessment->setAttribute('id', $id);
        return $assessment;
    }

    private function scores(array $values): Collection
    {
        return collect($values)->mapWithKeys(fn (float $score, int $assessmentId) => [
            $assessmentId => new StudentAssessmentScore(['assessment_id' => $assessmentId, 'score' => $score]),
        ]);
    }
}
