<?php

namespace App\Services\Grading;

class PerformanceDescriptorResolver
{
    /**
     * Resolve DepEd Performance Descriptors based on final grade.
     * 
     * Note: There's a gap in the official template between 79 and 88.
     * The 80-87 band uses "Progressing" as a reasonable placeholder label.
     * This gap needs confirmation from official DepEd documentation.
     *
     * @param float $finalGrade
     * @return array{description: string, remarks: string}
     */
    public static function resolve(float $finalGrade): array
    {
        if ($finalGrade >= 90 && $finalGrade <= 100) {
            return [
                'description' => 'Advancing',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 88 && $finalGrade <= 89) {
            return [
                'description' => 'Benchmarking',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 80 && $finalGrade <= 87) {
            return [
                'description' => 'Progressing',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 75 && $finalGrade <= 79) {
            return [
                'description' => 'Connecting',
                'remarks' => 'Passed'
            ];
        }
        
        if ($finalGrade >= 65 && $finalGrade <= 74) {
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