<?php

namespace App\Services\Grading;

use App\Models\Subject;

class SubjectWeightResolver
{
    /**
     * Resolve weight distribution for a given subject.
     *
     * Returns an array with weights for:
     * - 'written_work' (float, e.g. 0.30)
     * - 'performance_task' (float, e.g. 0.50)
     * - 'quarterly_assessment' (float, e.g. 0.20)
     *
     * @param Subject $subject
     * @return array{written_work: float, performance_task: float, quarterly_assessment: float}
     */
    public function resolve(Subject $subject): array
    {
        $name = strtolower($subject->name ?? '');
        $code = strtolower($subject->code ?? '');

        // 1. MAPEH / TLE / EPP / PE / Music / Arts / Health / Practical / Vocational / Home Economics
        if (
            str_contains($name, 'mapeh') ||
            str_contains($name, 'pe') ||
            str_contains($name, 'music') ||
            str_contains($name, 'art') ||
            str_contains($name, 'health') ||
            str_contains($name, 'physical education') ||
            str_contains($name, 'tle') ||
            str_contains($name, 'epp') ||
            str_contains($name, 'livelihood') ||
            str_contains($name, 'vocational') ||
            str_contains($code, 'mapeh') ||
            str_contains($code, 'pe') ||
            str_contains($code, 'music') ||
            str_contains($code, 'art') ||
            str_contains($code, 'health') ||
            str_contains($code, 'tle') ||
            str_contains($code, 'epp')
        ) {
            return [
                'written_work' => 0.20,
                'performance_task' => 0.60,
                'quarterly_assessment' => 0.20,
            ];
        }

        // 2. Science / Mathematics / Math / Science
        if (
            str_contains($name, 'science') ||
            str_contains($name, 'math') ||
            str_contains($name, 'algebra') ||
            str_contains($name, 'geometry') ||
            str_contains($name, 'calculus') ||
            str_contains($name, 'statistics') ||
            str_contains($name, 'biology') ||
            str_contains($name, 'chemistry') ||
            str_contains($name, 'physics') ||
            str_contains($code, 'sci') ||
            str_contains($code, 'math') ||
            str_contains($code, 'alg') ||
            str_contains($code, 'geom') ||
            str_contains($code, 'stat')
        ) {
            return [
                'written_work' => 0.40,
                'performance_task' => 0.40,
                'quarterly_assessment' => 0.20,
            ];
        }

        // 3. Default: Languages (English, Filipino, Mother Tongue), AP (Araling Panlipunan), EsP (Edukasyon sa Pagpapakatao)
        // Written Work = 30%, Performance Tasks = 50%, Quarterly Assessment = 20%
        return [
            'written_work' => 0.30,
            'performance_task' => 0.50,
            'quarterly_assessment' => 0.20,
        ];
    }
}
