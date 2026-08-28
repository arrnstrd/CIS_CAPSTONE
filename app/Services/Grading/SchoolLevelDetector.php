<?php

namespace App\Services\Grading;

class SchoolLevelDetector
{
    /**
     * Detect school level based on grade level.
     *
     * @param int $gradeLevel
     * @return string
     */
    public function detect(int $gradeLevel): string
    {
        return match($gradeLevel) {
            1, 2, 3, 4, 5, 6 => 'elementary',
            7, 8, 9, 10 => 'jhs',
            11, 12 => 'shs',
            default => 'jhs' // fallback to JHS for unknown grades
        };
    }

    /**
     * Get level label for display.
     *
     * @param string $level
     * @return string
     */
    public function getLevelLabel(string $level): string
    {
        return match($level) {
            'elementary' => 'Elementary',
            'jhs' => 'Junior High School',
            'shs' => 'Senior High School',
            default => $level
        };
    }

    /**
     * Get assessment category labels for the level.
     *
     * @param string $level
     * @return array
     */
    public function getCategoryLabels(string $level): array
    {
        return match($level) {
            'elementary' => [
                'written' => 'Written / Oral Works (WWs)',
                'performance' => 'Product / Performance Tasks (PTs)',
                'exam' => 'Examinations (EXs)',
            ],
            'jhs' => [
                'written' => 'Written / Oral Works (WWs)',
                'performance' => 'Product / Performance Tasks (PTs)',
                'exam' => 'Examinations (EXs)',
            ],
            'shs' => [
                'written' => 'Written / Oral Works (WWs)',
                'performance' => 'Product / Performance Tasks (PTs)',
                'quarterly' => 'Quarterly Assessments (QAs)',
            ],
            default => [
                'written' => 'Written / Oral Works (WWs)',
                'performance' => 'Product / Performance Tasks (PTs)',
                'exam' => 'Examinations (EXs)',
            ]
        };
    }
}