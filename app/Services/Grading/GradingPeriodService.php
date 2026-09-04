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

    /**
     * Resolve selected grading period based on:
     * 1. Explicit request input (grading_period_id)
     * 2. Teacher dashboard default_term preference
     * 3. Progression-based resolveTeacherCurrentTerm
     */
    public function resolveSelectedPeriod(\Illuminate\Http\Request $request, \App\Models\Teacher $teacher, $dashboardPreferences = null): ?GradingPeriod
    {
        $selectedPeriodId = $request->input('grading_period_id');
        if ($selectedPeriodId) {
            $period = GradingPeriod::where('id', $selectedPeriodId)->where('sequence', '<=', 3)->first();
            if ($period) {
                return $period;
            }
        }

        $preferences = $dashboardPreferences ?? $teacher->user?->dashboardPreference;
        if ($preferences?->default_term && $preferences->default_term !== 'current') {
            $targetSequence = match ($preferences->default_term) {
                'term_1' => 1,
                'term_2' => 2,
                'term_3' => 3,
                default => null,
            };
            if ($targetSequence) {
                $period = GradingPeriod::where('sequence', $targetSequence)->where('period_type', 'trimester')->first();
                if ($period) {
                    return $period;
                }
            }
        }

        return $this->resolveTeacherCurrentTerm($teacher);
    }

    /**
     * Dynamically resolve the active working term for the teacher based on
     * the progression and actual start of grading for their active teaching assignments.
     *
     * Progression Rules:
     * - If Term 1 is incomplete -> Term 1
     * - If Term 1 is 100% complete:
     *   - If Term 2 has NOT started yet -> remain on Term 1
     *   - If Term 2 has started:
     *     - If Term 2 is incomplete -> Term 2
     *     - If Term 2 is 100% complete:
     *       - If Term 3 has NOT started yet -> remain on Term 2
     *       - If Term 3 has started -> Term 3 (capped at Term 3)
     */
    public function resolveTeacherCurrentTerm(\App\Models\Teacher $teacher): ?GradingPeriod
    {
        $activeAssignments = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        $term1 = GradingPeriod::where('sequence', 1)->where('period_type', 'trimester')->first()
            ?? GradingPeriod::where('sequence', 1)->first();
        $term2 = GradingPeriod::where('sequence', 2)->where('period_type', 'trimester')->first()
            ?? GradingPeriod::where('sequence', 2)->first();
        $term3 = GradingPeriod::where('sequence', 3)->where('period_type', 'trimester')->first()
            ?? GradingPeriod::where('sequence', 3)->first();

        // If teacher has no active assignments or Term 1 not found, return safest fallback
        if ($activeAssignments->isEmpty() || ! $term1) {
            return $term1 ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();
        }

        // 1. Check if Term 1 is 100% complete across ALL active assignments
        $isTerm1Complete = $this->isTermCompleteForTeacher($activeAssignments, $term1);
        if (! $isTerm1Complete) {
            return $term1;
        }

        // 2. If Term 1 is complete, check if Term 2 has started
        if (! $term2 || ! $this->hasTermStartedForTeacher($activeAssignments, $term2)) {
            return $term1;
        }

        // 3. Term 2 has started -> check if Term 2 is 100% complete
        $isTerm2Complete = $this->isTermCompleteForTeacher($activeAssignments, $term2);
        if (! $isTerm2Complete) {
            return $term2;
        }

        // 4. Term 2 is 100% complete -> check if Term 3 has started
        if (! $term3 || ! $this->hasTermStartedForTeacher($activeAssignments, $term3)) {
            return $term2;
        }

        // 5. Term 3 has started -> Current Term is Term 3 (final term, stays on Term 3)
        return $term3;
    }

    /**
     * Check whether a grading period has started for the teacher's active assignments.
     */
    public function hasTermStartedForTeacher($activeAssignments, GradingPeriod $period): bool
    {
        $taIds = $activeAssignments->pluck('id');
        if ($taIds->isEmpty()) {
            return false;
        }

        // 1. Check if any assessment exists for this period in the teacher's active assignments
        $hasAssessments = \App\Models\Assessment::whereIn('teaching_assignment_id', $taIds)
            ->where('grading_period_id', $period->id)
            ->exists();

        if ($hasAssessments) {
            return true;
        }

        // 2. Check if any quarterly grade record exists for this period
        $hasGrades = \App\Models\QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->where('grading_period_id', $period->id)
            ->where(function ($query) {
                $query->whereNotNull('transmuted_grade')
                    ->orWhereNotNull('initial_grade')
                    ->orWhereNotNull('written_work_grade')
                    ->orWhereNotNull('performance_task_grade')
                    ->orWhereNotNull('quarterly_assessment_grade');
            })
            ->exists();

        return $hasGrades;
    }

    /**
     * Check whether ALL active teaching assignments of the teacher have
     * 100% finalized grades (transmuted_grade !== null) for every active enrolled student.
     */
    public function isTermCompleteForTeacher($activeAssignments, GradingPeriod $period): bool
    {
        foreach ($activeAssignments as $ta) {
            $activeEnrollmentIds = \App\Models\Enrollment::where('section_id', $ta->section_id)
                ->where('school_year_id', $ta->school_year_id)
                ->where('status', 'active')
                ->pluck('id');

            $totalStudents = $activeEnrollmentIds->count();

            if ($totalStudents > 0) {
                $finalizedCount = \App\Models\QuarterlyGrade::where('teaching_assignment_id', $ta->id)
                    ->where('grading_period_id', $period->id)
                    ->whereIn('enrollment_id', $activeEnrollmentIds)
                    ->whereNotNull('transmuted_grade')
                    ->count();

                if ($finalizedCount < $totalStudents) {
                    return false;
                }
            }
        }

        return true;
    }
}