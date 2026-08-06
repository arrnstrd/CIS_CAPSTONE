<?php

namespace App\Services\Grading;

use App\Models\GradingPeriod;
use Illuminate\Support\Facades\DB;

class GradingPeriodService
{
    /**
     * Create a new grading period.
     *
     * @param array{name: string, sequence: int, is_active?: bool} $data
     * @return GradingPeriod
     */
    public function create(array $data): GradingPeriod
    {
        return DB::transaction(function () use ($data) {
            return GradingPeriod::create([
                'name' => $data['name'],
                'sequence' => $data['sequence'],
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing grading period.
     *
     * @param GradingPeriod $gradingPeriod
     * @param array{name?: string, sequence?: int, is_active?: bool} $data
     * @return GradingPeriod
     */
    public function update(GradingPeriod $gradingPeriod, array $data): GradingPeriod
    {
        return DB::transaction(function () use ($gradingPeriod, $data) {
            $gradingPeriod->update(array_filter($data, fn($value) => $value !== null));
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return GradingPeriod::orderBy('sequence')->get();
    }

    /**
     * Get active grading periods.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActive()
    {
        return GradingPeriod::where('is_active', true)
            ->orderBy('sequence')
            ->get();
    }
}
