<?php

namespace App\Http\Controllers\Teacher\StudentManagement;

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
     * The scope is derived from the teacher's active teaching assignments and advised sections,
     * so the teacher can access students they handle or within their teaching assignments.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this page.');

        // Sections/classes the authenticated teacher handles (teaching assignments + advised sections).
        $teachingSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $advisedSectionIds = Section::query()
            ->where('advisor_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('id');

        $teacherSectionIds = $teachingSectionIds
            ->concat($advisedSectionIds)
            ->unique()
            ->values()
            ->all();

        // Filters
        $query = $request->input('query');
        $sectionId = $request->input('section_id');

        if ($sectionId) {
            abort_unless(
                in_array((int) $sectionId, array_map('intval', $teacherSectionIds), true),
                403,
                'You do not have access to this section.'
            );
        }

        $students = collect();

        if ($sectionId) {
            $students = Student::query()
                ->with(['enrollments.sectionModel'])
                ->whereHas('enrollments', function ($q) use ($teacherSectionIds) {
                    $q->whereIn('section_id', $teacherSectionIds)
                        ->where('status', 'active');
                })
                ->when(filled($query), function ($q) use ($query) {
                    $q->where(function ($q) use ($query) {
                        $q->where('lrn', 'like', "%{$query}%")
                            ->orWhere('student_number', 'like', "%{$query}%")
                            ->orWhere('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%");
                    });
                })
                ->whereHas('enrollments', function ($q) use ($sectionId, $teacherSectionIds) {
                    // Enforced within the teacher's own sections only.
                    $q->whereIn('section_id', $teacherSectionIds)
                        ->where('section_id', $sectionId);
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(15)
                ->withQueryString();

            // Attach display context (grade + section) from the selected section enrollment.
            $students->getCollection()->each(function (Student $student) use ($teacherSectionIds, $sectionId) {
                $teacherEnrollments = $student->enrollments
                    ->filter(fn($enrollment) => in_array($enrollment->section_id, $teacherSectionIds));

                $enrollment = $teacherEnrollments
                    ->filter(fn($enrollment) => $enrollment->section_id == $sectionId)
                    ->first()
                    ?? $teacherEnrollments->first();

                $student->setAttribute('grade_level', $enrollment?->sectionModel?->grade_level ?? $enrollment?->grade_level);
                $student->setAttribute('section_name', $enrollment?->sectionModel?->name);
            });
        }

        // Filter dropdown data — always within the teacher's handled sections.
        $classCards = Section::query()
            ->whereIn('id', $teacherSectionIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->withCount([
                'enrollments as student_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->get(['id', 'name', 'grade_level']);

        $selectedClass = $sectionId ? $classCards->firstWhere('id', $sectionId) : null;
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);
        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $allSections = $classCards;
        $grade = $selectedClass?->grade_level ?? ($allSections->first()?->grade_level ?? 1);

        return view('pov.teacher.student-management.student-management', compact(
            'students',
            'classCards',
            'sectionId',
            'selectedClass',
            'schoolYears',
            'activeSchoolYear',
            'allSections',
            'grade'
        ));
    }
}
