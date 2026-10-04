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

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        $students = collect();

        if ($sectionId) {
            $students = Student::query()
                ->with(['enrollments.sectionModel'])
                ->whereHas('enrollments', function ($q) use ($teacherSectionIds, $activeSchoolYear) {
                    $q->whereIn('section_id', $teacherSectionIds)
                        ->where('status', 'active');
                    if ($activeSchoolYear) {
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
                ->whereHas('enrollments', function ($q) use ($sectionId, $teacherSectionIds, $activeSchoolYear) {
                    // Enforced within the teacher's own sections only.
                    $q->whereIn('section_id', $teacherSectionIds)
                        ->where('section_id', $sectionId)
                        ->where('status', 'active');
                    if ($activeSchoolYear) {
                        $q->where('school_year_id', $activeSchoolYear->id);
                    }
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate(15)
                ->withQueryString();

            // Attach display context (grade + section) from the selected section enrollment.
            $students->getCollection()->each(function (Student $student) use ($teacherSectionIds, $sectionId, $activeSchoolYear) {
                $teacherEnrollments = $student->enrollments
                    ->filter(fn($enrollment) => in_array($enrollment->section_id, $teacherSectionIds));

                if ($activeSchoolYear) {
                    $yearEnrollments = $teacherEnrollments->filter(fn($e) => (int) $e->school_year_id === (int) $activeSchoolYear->id);
                    if ($yearEnrollments->isNotEmpty()) {
                        $teacherEnrollments = $yearEnrollments;
                    }
                }

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
                'enrollments as student_count' => function ($q) use ($activeSchoolYear) {
                    $q->where('status', 'active');
                    if ($activeSchoolYear) {
                        $q->where('school_year_id', $activeSchoolYear->id);
                    }
                },
            ])
            ->get(['id', 'name', 'grade_level']);

        $selectedClass = $sectionId ? $classCards->firstWhere('id', $sectionId) : null;
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);
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

    public function export(Request $request, \App\Services\Export\StudentSf1ExportService $exportService)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this feature.');

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

        $query = $request->input('query');
        $sectionId = $request->input('section_id');

        if ($sectionId) {
            abort_unless(
                in_array((int) $sectionId, array_map('intval', $teacherSectionIds), true),
                403,
                'You do not have access to this section.'
            );
        }

        $section = $sectionId ? Section::find($sectionId) : null;

        $students = Student::query()
            ->with(['enrollments.sectionModel', 'guardian'])
            ->whereHas('enrollments', function ($q) use ($teacherSectionIds, $sectionId) {
                $q->whereIn('section_id', $teacherSectionIds)
                    ->where('status', 'active');

                if ($sectionId) {
                    $q->where('section_id', $sectionId);
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
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        $format = $request->input('format', 'sf1');
        $filePath = $exportService->export($students, $section, $activeSchoolYear, $section?->grade_level, $format);

        $sectionPart = $section ? 'Grade_' . $section->grade_level . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $section->name) : 'All_Students';
        $prefix = strtolower($format) === 'raw' ? 'Student_List_Raw' : 'SF1';
        $filename = "{$prefix}_{$sectionPart}_" . now()->format('Ymd_His') . ".xlsx";

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
