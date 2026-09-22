<?php

namespace App\Services\Grading;

class PerformanceDescriptorResolver
{
    /**
     * Resolve DepEd Performance Descriptors based on final grade.
     * Aligned with DepEd 3-Term Electronic Class Record (ECR).
     *
     * @param float $finalGrade
     * @return array{description: string, remarks: string}
     */
    public static function resolve(float $finalGrade): array
    {
        if ($finalGrade >= 90) {
            return [
                'description' => 'Advancing',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 80) {
            return [
                'description' => 'Benchmarking',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 75) {
            return [
                'description' => 'Connecting',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 65) {
            return [
                'description' => 'Developing',
                'remarks' => 'Failed'
            ];
        }
        
        // 0-64
        return [
            'description' => 'Emerging',
            'remarks' => 'Failed'
        ];
    }
}