<?php

namespace App\Services\Grading;

use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\DB;

class AssessmentCategoryService
{
    /**
     * Create a new assessment category.
     *
     * @param array{name: string} $data
     * @return AssessmentCategory
     */
    public function create(array $data): AssessmentCategory
    {
        return DB::transaction(function () use ($data) {
            return AssessmentCategory::create([
                'name' => $data['name'],
            ]);
        });
    }

    /**
     * Update an existing assessment category.
     *
     * @param AssessmentCategory $assessmentCategory
     * @param array{name?: string} $data
     * @return AssessmentCategory
     */
    public function update(AssessmentCategory $assessmentCategory, array $data): AssessmentCategory
    {
        return DB::transaction(function () use ($assessmentCategory, $data) {
            $assessmentCategory->update(array_filter($data, fn($value) => $value !== null));
            return $assessmentCategory->fresh();
        });
    }

    /**
     * Delete an assessment category.
     *
     * @param AssessmentCategory $assessmentCategory
     * @return bool
     */
    public function delete(AssessmentCategory $assessmentCategory): bool
    {
        return DB::transaction(function () use ($assessmentCategory) {
            return $assessmentCategory->delete();
        });
    }

    /**
     * Get all assessment categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return AssessmentCategory::orderBy('name')->get();
    }
}
