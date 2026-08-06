<?php

namespace App\Services\Grading;

use App\Models\Assessment;
use App\Models\TeachingAssignment;
use App\Models\AssessmentCategory;
use App\Models\GradingPeriod;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    /**
     * Create a new assessment.
     *
     * @param array{
     *   teaching_assignment_id: int,
     *   assessment_category_id: int,
     *   grading_period_id: int,
     *   title: string,
     *   total_items: int,
     *   assessment_date: string,
     *   status?: string
     * } $data
     * @return Assessment
     */
    public function create(array $data): Assessment
    {
        return DB::transaction(function () use ($data) {
            return Assessment::create([
                'teaching_assignment_id' => $data['teaching_assignment_id'],
                'assessment_category_id' => $data['assessment_category_id'],
                'grading_period_id' => $data['grading_period_id'],
                'title' => $data['title'],
                'total_items' => $data['total_items'],
                'assessment_date' => $data['assessment_date'],
                'status' => $data['status'] ?? 'active',
            ]);
        });
    }

    /**
     * Update an existing assessment.
     *
     * @param Assessment $assessment
     * @param array{
     *   teaching_assignment_id?: int,
     *   assessment_category_id?: int,
     *   grading_period_id?: int,
     *   title?: string,
     *   total_items?: int,
     *   assessment_date?: string,
     *   status?: string
     * } $data
     * @return Assessment
     */
    public function update(Assessment $assessment, array $data): Assessment
    {
        return DB::transaction(function () use ($assessment, $data) {
            $assessment->update(array_filter($data, fn($value) => $value !== null));
            return $assessment->fresh();
        });
    }

    /**
     * Delete an assessment.
     *
     * @param Assessment $assessment
     * @return bool
     */
    public function delete(Assessment $assessment): bool
    {
        return DB::transaction(function () use ($assessment) {
            return $assessment->delete();
        });
    }

    /**
     * Get assessments for a specific teaching assignment.
     *
     * @param int $teachingAssignmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeachingAssignment(int $teachingAssignmentId)
    {
        return Assessment::with(['assessmentCategory', 'gradingPeriod'])
            ->where('teaching_assignment_id', $teachingAssignmentId)
            ->orderBy('assessment_date')
            ->get();
    }

    /**
     * Get assessments for a specific teaching assignment and grading period.
     *
     * @param int $teachingAssignmentId
     * @param int $gradingPeriodId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeachingAssignmentAndGradingPeriod(int $teachingAssignmentId, int $gradingPeriodId)
    {
        return Assessment::with(['assessmentCategory'])
            ->where('teaching_assignment_id', $teachingAssignmentId)
            ->where('grading_period_id', $gradingPeriodId)
            ->where('status', 'active')
            ->orderBy('assessment_date')
            ->get();
    }

    /**
     * Get assessments for a specific assessment category.
     *
     * @param int $assessmentCategoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCategory(int $assessmentCategoryId)
    {
        return Assessment::with(['teachingAssignment', 'gradingPeriod'])
            ->where('assessment_category_id', $assessmentCategoryId)
            ->orderBy('assessment_date')
            ->get();
    }
}
