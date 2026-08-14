<?php

namespace App\Services\Teacher;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeachingAssignmentService
{
    /**
     * Create a new teaching assignment with composite unique constraint check.
     *
     * @param array{
     *   teacher_id: int,
     *   subject_id: int,
     *   section_id: int,
     *   school_year_id: int,
     *   status?: string
     * } $data
     * @return TeachingAssignment
     * @throws \InvalidArgumentException
     */
    public function create(array $data): TeachingAssignment
    {
        // Check composite unique constraint: (teacher_id, subject_id, section_id, school_year_id)
        $existing = TeachingAssignment::where('teacher_id', $data['teacher_id'])
            ->where('subject_id', $data['subject_id'])
            ->where('section_id', $data['section_id'])
            ->where('school_year_id', $data['school_year_id'])
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('A teaching assignment with this teacher, subject, section, and school year already exists.');
        }

        return DB::transaction(function () use ($data) {
            return TeachingAssignment::create([
                'teacher_id' => $data['teacher_id'],
                'subject_id' => $data['subject_id'],
                'section_id' => $data['section_id'],
                'school_year_id' => $data['school_year_id'],
                'status' => $data['status'] ?? 'active',
            ]);
        });
    }

    /**
     * Update an existing teaching assignment.
     *
     * @param TeachingAssignment $teachingAssignment
     * @param array{
     *   teacher_id?: int,
     *   subject_id?: int,
     *   section_id?: int,
     *   school_year_id?: int,
     *   status?: string
     * } $data
     * @return TeachingAssignment
     * @throws \InvalidArgumentException
     */
    public function update(TeachingAssignment $teachingAssignment, array $data): TeachingAssignment
    {
        return DB::transaction(function () use ($teachingAssignment, $data) {
            // If any of the composite key fields are being updated, check for duplicates
            $keyFields = ['teacher_id', 'subject_id', 'section_id', 'school_year_id'];
            $hasKeyChange = false;

            foreach ($keyFields as $field) {
                if (isset($data[$field]) && $data[$field] != $teachingAssignment->$field) {
                    $hasKeyChange = true;
                    break;
                }
            }

            if ($hasKeyChange) {
                $checkData = [
                    'teacher_id' => $data['teacher_id'] ?? $teachingAssignment->teacher_id,
                    'subject_id' => $data['subject_id'] ?? $teachingAssignment->subject_id,
                    'section_id' => $data['section_id'] ?? $teachingAssignment->section_id,
                    'school_year_id' => $data['school_year_id'] ?? $teachingAssignment->school_year_id,
                ];

                $existing = TeachingAssignment::where('teacher_id', $checkData['teacher_id'])
                    ->where('subject_id', $checkData['subject_id'])
                    ->where('section_id', $checkData['section_id'])
                    ->where('school_year_id', $checkData['school_year_id'])
                    ->where('id', '!=', $teachingAssignment->id)
                    ->first();

                if ($existing) {
                    throw new \InvalidArgumentException('A teaching assignment with this teacher, subject, section, and school year already exists.');
                }
            }

            $teachingAssignment->update(array_filter($data, fn($value) => $value !== null));
            return $teachingAssignment->fresh();
        });
    }

    /**
     * Delete a teaching assignment.
     *
     * @param TeachingAssignment $teachingAssignment
     * @return bool
     */
    public function delete(TeachingAssignment $teachingAssignment): bool
    {
        return DB::transaction(function () use ($teachingAssignment) {
            return $teachingAssignment->delete();
        });
    }

    /**
     * Get teaching assignments for a specific teacher in a school year.
     *
     * @param int $teacherId
     * @param int $schoolYearId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeacherAndSchoolYear(int $teacherId, int $schoolYearId)
    {
        return TeachingAssignment::with(['subject', 'section', 'schoolYear'])
            ->where('teacher_id', $teacherId)
            ->where('school_year_id', $schoolYearId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get teaching assignments for a specific section in a school year.
     *
     * @param int $sectionId
     * @param int $schoolYearId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBySectionAndSchoolYear(int $sectionId, int $schoolYearId)
    {
        return TeachingAssignment::with(['teacher', 'subject', 'schoolYear'])
            ->where('section_id', $sectionId)
            ->where('school_year_id', $schoolYearId)
            ->where('status', 'active')
            ->get();
    }
}
