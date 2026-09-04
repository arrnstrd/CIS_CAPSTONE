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
        // Elementary and Junior High School: DepEd DO 8, s. 2015 standard weights
        // Written Work 20%, Performance Task 50%, Quarterly Assessment 30%
        if (in_array($subject->level, ['elementary', 'hs'])) {
            return [
                'written_work' => 0.20,
                'performance_task' => 0.50,
                'quarterly_assessment' => 0.30,
            ];
        }

        // Senior High School: DepEd DO 8, s. 2016 weights based on subject type
        if ($subject->level === 'shs') {
            // SHS subjects without subject_type set fall back to placeholder weights
            if ($subject->subject_type === null) {
                // TODO: Flag SHS subjects without subject_type - they need to be categorized
                // This fallback should be temporary until all SHS subjects are properly categorized
                return [
                    'written_work' => 0.30,
                    'performance_task' => 0.50,
                    'quarterly_assessment' => 0.20,
                ];
            }

            // Core and General Academic Elective: Written Work 20%, Performance Task 50%, Quarterly Assessment 30%
            if (in_array($subject->subject_type, ['core', 'academic_elective_general'])) {
                return [
                    'written_work' => 0.20,
                    'performance_task' => 0.50,
                    'quarterly_assessment' => 0.30,
                ];
            }

            // Special Academic Elective (Field Experience/Exposure, Sports and Arts): 
            // Written Work 15%, Performance Task 70%, Quarterly Assessment 15%
            if ($subject->subject_type === 'academic_elective_special') {
                return [
                    'written_work' => 0.15,
                    'performance_task' => 0.70,
                    'quarterly_assessment' => 0.15,
                ];
            }

            // TechPro Elective: Written Work 15%, Performance Task 65%, Quarterly Assessment 20%
            if ($subject->subject_type === 'techpro_elective') {
                return [
                    'written_work' => 0.15,
                    'performance_task' => 0.65,
                    'quarterly_assessment' => 0.20,
                ];
            }

            // TechPro Work Immersion: Written Work 20%, Performance Task 80%, no Quarterly Assessment
            if ($subject->subject_type === 'techpro_work_immersion') {
                return [
                    'written_work' => 0.20,
                    'performance_task' => 0.80,
                    'quarterly_assessment' => 0.00,
                ];
            }
        }

        // Fallback for any unexpected cases
        return [
            'written_work' => 0.30,
            'performance_task' => 0.50,
            'quarterly_assessment' => 0.20,
        ];
    }
}
