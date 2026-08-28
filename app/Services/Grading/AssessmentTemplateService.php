<?php

namespace App\Services\Grading;

class AssessmentTemplateService
{
    /**
     * Get fixed assessment slots for each category based on school level.
     *
     * @param string $level
     * @return array
     */
    public function getFixedSlots(string $level): array
    {
        return match($level) {
            'elementary' => [
                'written' => 5,    // WW1, WW2, WW3, WW4, WW5
                'performance' => 5, // PT1, PT2, PT3, PT4, PT5
                'exam' => 3        // EX1, EX2, EX3
            ],
            'jhs' => [
                'written' => 5,    // WW1, WW2, WW3, WW4, WW5
                'performance' => 5, // PT1, PT2, PT3, PT4, PT5
                'exam' => 3        // EX1, EX2, EX3
            ],
            'shs' => [
                'written' => 5,    // WW1, WW2, WW3, WW4, WW5
                'performance' => 5, // PT1, PT2, PT3, PT4, PT5
                'quarterly' => 3   // QA1, QA2, QA3
            ],
            default => [
                'written' => 5,
                'performance' => 5,
                'exam' => 3
            ]
        };
    }

    /**
     * Get assessment labels for fixed slots.
     *
     * @param string $level
     * @return array
     */
    public function getSlotLabels(string $level): array
    {
        $slots = $this->getFixedSlots($level);
        $labels = [];

        foreach ($slots as $category => $count) {
            $labels[$category] = [];
            for ($i = 1; $i <= $count; $i++) {
                if ($level === 'shs' && $category === 'quarterly') {
                    $labels[$category][] = "QA{$i}";
                } else {
                    $labels[$category][] = match($category) {
                        'written' => "WW{$i}",
                        'performance' => "PT{$i}",
                        'exam' => "EX{$i}",
                        default => "Item{$i}"
                    };
                }
            }
        }

        return $labels;
    }

    /**
     * Find next available slot for a category.
     *
     * @param \Illuminate\Database\Eloquent\Collection $existingAssessments
     * @param int $maxSlots
     * @return int
     */
    public function findNextAvailableSlot($existingAssessments, int $maxSlots): int
    {
        $usedSlots = $existingAssessments->pluck('slot_number')->filter()->sort()->values();
        
        for ($i = 1; $i <= $maxSlots; $i++) {
            if (!$usedSlots->contains($i)) {
                return $i;
            }
        }
        
        return $maxSlots + 1; // Return next slot beyond fixed slots if all are used
    }

    /**
     * Get category key for the level.
     *
     * @param string $level
     * @return string
     */
    public function getCategoryKey(string $level): string
    {
        return match($level) {
            'shs' => 'quarterly',
            default => 'exam'
        };
    }
}