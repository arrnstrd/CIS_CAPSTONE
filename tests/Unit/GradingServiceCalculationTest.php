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
        $this->assertSame(86.0, $this->service->transmute(79.0));
        $this->assertSame('Progressing', PerformanceDescriptorResolver::resolve(86.0)['description']);
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

    private function assessment(int $id, int $categoryId, int $totalItems): Assessment
    {
        $assessment = new Assessment(['assessment_category_id' => $categoryId, 'total_items' => $totalItems]);
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
