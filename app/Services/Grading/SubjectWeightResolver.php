<?php

namespace App\Services\Grading;

use App\Models\Subject;

class SubjectWeightResolver
{
    /**
     * Resolve the default grading weight distribution.
     *
     * Default:
     * - Written Work: 20%
     * - Performance Task: 50%
     * - Term Assessment: 30%
     *
     * Teachers can manually override these defaults through Grading Rules.
     *
     * @param Subject $subject
     * @return array{written_work: float, performance_task: float, term_assessment: float}
     */
    public function resolve(Subject $subject): array
    {
        return [
            'written_work' => 0.20,
            'performance_task' => 0.50,
            'term_assessment' => 0.30,
        ];
    }
}