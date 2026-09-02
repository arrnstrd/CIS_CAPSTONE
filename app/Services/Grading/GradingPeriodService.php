<?php

namespace App\Services\Grading;

use App\Models\GradingPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradingPeriodService
{
    /**
     * Create a new grading period.
     *
     * The grading system uses three terms only:
     * Term 1, Term 2, and Term 3.
     */
    public function create(array $data): GradingPeriod
    {
        return DB::transaction(function () use ($data) {
            $sequence = (int) $data['sequence'];

            if ($sequence < 1 || $sequence > 3) {
                throw ValidationException::withMessages([
                    'sequence' => 'The grading system only supports Term 1, Term 2, and Term 3.',
                ]);
            }

            if (GradingPeriod::where('sequence', $sequence)->exists()) {
                throw ValidationException::withMessages([
                    'sequence' => "Term {$sequence} already exists.",
                ]);
            }

            return GradingPeriod::create([
                'name' => 'Term ' . $sequence,
                'sequence' => $sequence,
                'period_type' => 'trimester',
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing grading period.
     *
     * The grading system uses three terms only:
     * Term 1, Term 2, and Term 3.
     */
    public function update(GradingPeriod $gradingPeriod, array $data): GradingPeriod
    {
        return DB::transaction(function () use ($gradingPeriod, $data) {
            $sequence = isset($data['sequence'])
                ? (int) $data['sequence']
                : (int) $gradingPeriod->sequence;

            if ($sequence < 1 || $sequence > 3) {
                throw ValidationException::withMessages([
                    'sequence' => 'The grading system only supports Term 1, Term 2, and Term 3.',
                ]);
            }

            $duplicateExists = GradingPeriod::where('sequence', $sequence)
                ->where('id', '!=', $gradingPeriod->id)
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'sequence' => "Term {$sequence} already exists.",
                ]);
            }

            $gradingPeriod->update([
                'name' => 'Term ' . $sequence,
                'sequence' => $sequence,
                'period_type' => 'trimester',
                'is_active' => $data['is_active'] ?? $gradingPeriod->is_active,
            ]);

            return $gradingPeriod->fresh();
        });
    }

    /**
     * Delete a grading period.
     *
     * @param GradingPeriod $gradingPeriod
     * @return bool
     */
    public function delete(GradingPeriod $gradingPeriod): bool
    {
        return DB::transaction(function () use ($gradingPeriod) {
            return $gradingPeriod->delete();
        });
    }

    /**
     * Get all grading periods ordered by sequence.
     *
     * Only the three active trimester terms are returned.
     */
    public function getAll()
    {
        return GradingPeriod::where('sequence', '<=', 3)
            ->orderBy('sequence')
            ->get();
    }

    /**
     * Get active grading periods.
     *
     * Only active trimester terms are returned.
     */
    public function getActive()
    {
        return GradingPeriod::where('is_active', true)
            ->where('sequence', '<=', 3)
            ->where('period_type', 'trimester')
            ->orderBy('sequence')
            ->get();
    }
}