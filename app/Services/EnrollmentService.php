<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class EnrollmentService
{
    public function createEnrollment(array $validatedData, int $schoolYearId): Enrollment
    {
        $section = Section::findOrFail($validatedData['section_id']);

        if ($section->status !== 'active') {
            throw new \RuntimeException('Selected section is inactive.');
        }

        if (! $this->sectionHasCapacity($section, $schoolYearId)) {
            throw new \RuntimeException('Selected section has reached its capacity.');
        }

        $validatedData['school_year_id'] = $schoolYearId;
        $validatedData = $this->applySectionSnapshot($validatedData, $section);

        try {
            return Enrollment::create($validatedData);
        } catch (QueryException $e) {
            if ($this->isDuplicateEnrollmentError($e)) {
                throw new \RuntimeException('Student is already enrolled for this school year');
            }

            throw $e;
        }
    }

    public function updateEnrollment(Enrollment $enrollment, array $validatedData): Enrollment
    {
        $currentSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $schoolYearId = $enrollment->school_year_id ?? $currentSchoolYear?->id;

        $section = Section::findOrFail($validatedData['section_id']);

        if ($section->status !== 'active') {
            throw new \RuntimeException('Selected section is inactive.');
        }

        if (! $this->sectionHasCapacity($section, $schoolYearId, $enrollment->id)) {
            throw new \RuntimeException('Selected section has reached its capacity.');
        }

        $validatedData = $this->applySectionSnapshot($validatedData, $section);

        $exists = Enrollment::where('student_id', $enrollment->student_id)
            ->where('school_year_id', $schoolYearId)
            ->where('id', '!=', $enrollment->id)
            ->exists();

        if ($exists) {
            throw new \RuntimeException('This student is already enrolled for this school year');
        }

        $enrollment->update($validatedData);

        return $enrollment;
    }

    public function isDuplicateEnrollmentError(QueryException $exception): bool
    {
        return $exception->getCode() === '23000'
            && str_contains($exception->getMessage(), 'enrollments_student_school_year_unique');
    }

    private function enrollmentLevelForSection(Section $section): string
    {
        return match ($section->level) {
            'elementary' => 'elementary',
            'highschool' => 'hs',
            'senior_high_school' => 'shs',
            default => $section->level,
        };
    }

    private function applySectionSnapshot(array $data, Section $section): array
    {
        $data['level'] = $this->enrollmentLevelForSection($section);

        if (Schema::hasColumn('enrollments', 'grade_level')) {
            $data['grade_level'] = (string) $section->grade_level;
        }

        if (Schema::hasColumn('enrollments', 'section')) {
            $data['section'] = $section->name;
        }

        return $data;
    }

    private function sectionHasCapacity(Section $section, int $schoolYearId, ?int $ignoreEnrollmentId = null): bool
    {
        $query = Enrollment::query()
            ->where('section_id', $section->id)
            ->where('school_year_id', $schoolYearId)
            ->where('status', 'active');

        if ($ignoreEnrollmentId !== null) {
            $query->where('id', '!=', $ignoreEnrollmentId);
        }

        return $query->count() < $section->capacity;
    }
}
