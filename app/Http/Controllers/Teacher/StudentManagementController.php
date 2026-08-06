<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class StudentManagementController extends Controller
{
    /**
     * Show the students handled by the authenticated teacher.
     *
     * The scope is derived from the teacher's active teaching assignments, so
     * the teacher can never reach students from sections they do not handle —
     * regardless of which filter parameters or IDs they pass.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this page.');

        $activeSchoolYear = SchoolYear::query()->active()->first();

        // Sections/classes the authenticated teacher actually handles.
        $teacherSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique()
            ->values()
            ->all();

        // Filters
        $query = $request->input('query');
        $sectionId = $request->input('section_id');
        $gradeLevel = $request->input('grade_level');
        $schoolYearId = $request->input('school_year_id');

        $students = Student::query()
            ->with(['enrollments.sectionModel'])
            ->whereHas('enrollments', function ($q) use ($teacherSectionIds, $schoolYearId, $activeSchoolYear) {
                $q->whereIn('section_id', $teacherSectionIds)
                    ->where('status', 'active');

                if ($schoolYearId) {
                    $q->where('school_year_id', $schoolYearId);
                } elseif ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
            })
            ->when(filled($query), function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('lrn', 'like', "%{$query}%")
                        ->orWhere('student_number', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->when($sectionId, function ($q) use ($sectionId, $teacherSectionIds) {
                // Enforced within the teacher's own sections only.
                $q->whereHas('enrollments', function ($q) use ($sectionId, $teacherSectionIds) {
                    $q->whereIn('section_id', $teacherSectionIds)
                        ->where('section_id', $sectionId);
                });
            })
            ->when(filled($gradeLevel), function ($q) use ($gradeLevel) {
                $q->whereHas('enrollments.sectionModel', function ($q) use ($gradeLevel) {
                    $q->where('grade_level', $gradeLevel);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        // Attach display context (grade + section) from the relevant enrollment.
        $students->getCollection()->each(function (Student $student) use ($teacherSectionIds, $schoolYearId, $activeSchoolYear) {
            $teacherEnrollments = $student->enrollments
                ->filter(fn($enrollment) => in_array($enrollment->section_id, $teacherSectionIds));

            $enrollment = $teacherEnrollments
                ->filter(fn($enrollment) => $schoolYearId
                    ? $enrollment->school_year_id == $schoolYearId
                    : $enrollment->school_year_id === $activeSchoolYear?->id)
                ->first()
                ?? $teacherEnrollments->first();

            $student->setAttribute('grade_level', $enrollment?->sectionModel?->grade_level ?? $enrollment?->grade_level);
            $student->setAttribute('section_name', $enrollment?->sectionModel?->name);
        });

        // Filter dropdown data — always within the teacher's handled sections.
        $sections = Section::query()
            ->whereIn('id', $teacherSectionIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $gradeLevels = Section::query()
            ->whereIn('id', $teacherSectionIds)
            ->whereNotNull('grade_level')
            ->orderBy('grade_level')
            ->distinct()
            ->pluck('grade_level');

        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);

        return view('teacher-modules.student-management', compact(
            'students',
            'sections',
            'gradeLevels',
            'schoolYears',
            'activeSchoolYear'
        ));
    }
}
