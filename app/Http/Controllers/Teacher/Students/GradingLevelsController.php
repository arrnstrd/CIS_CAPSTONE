<?php

namespace App\Http\Controllers\Teacher\Students;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class GradingLevelsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('pov.teacher.students.grading-levels-index', ['gradeLevels' => collect()]);
        }

        $selectedSchoolYearId = $request->input('school_year_id') ?: null;

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section');

        if ($selectedSchoolYearId) {
            $teachingAssignmentsQuery->where('school_year_id', $selectedSchoolYearId);
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        $gradeLevels = $teachingAssignments
            ->groupBy(fn ($ta) => $ta->section->grade_level)
            ->map(function ($assignments, $gradeLevel) use ($selectedSchoolYearId) {
                $sectionIds = $assignments->pluck('section_id')->unique();

                $enrollmentsQuery = Enrollment::whereIn('section_id', $sectionIds)
                    ->where('status', 'active');

                if ($selectedSchoolYearId) {
                    $enrollmentsQuery->where('school_year_id', $selectedSchoolYearId);
                }

                $totalStudents = $enrollmentsQuery->count();

                $taIds = $assignments->pluck('id');
                $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                    ->whereNotNull('transmuted_grade')
                    ->get();

                return (object) [
                    'grade_level' => $gradeLevel,
                    'section_count' => $sectionIds->count(),
                    'total_students' => $totalStudents,
                    'avg_grade' => $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null,
                ];
            })
            ->sortBy('grade_level')
            ->values();

        $schoolYears = SchoolYear::orderByDesc('school_year')->get();

        return view('pov.teacher.students.grading-levels-index', compact('gradeLevels', 'schoolYears', 'selectedSchoolYearId'));
    }

    public function show(Request $request, int $gradeLevel)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $selectedSchoolYearId = $request->input('school_year_id') ?: null;

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereHas('section', fn ($q) => $q->where('grade_level', $gradeLevel))
            ->with(['section', 'subject']);

        if ($selectedSchoolYearId) {
            $teachingAssignmentsQuery->where('school_year_id', $selectedSchoolYearId);
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        abort_if($teachingAssignments->isEmpty(), 404, 'No sections found for this grade level.');

        $sections = $teachingAssignments->groupBy('section_id')->map(function ($assignments) use ($selectedSchoolYearId) {
            $ta = $assignments->first();
            $section = $ta->section;

            $enrollmentsQuery = Enrollment::where('section_id', $section->id)
                ->where('status', 'active');

            if ($selectedSchoolYearId) {
                $enrollmentsQuery->where('school_year_id', $selectedSchoolYearId);
            }

            $totalStudents = $enrollmentsQuery->count();

            $taIds = $assignments->pluck('id');
            $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get();

            return (object) [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'advisor' => $section->advisor?->full_name,
                'total_students' => $totalStudents,
                'avg_grade' => $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null,
                'subjects' => $assignments->pluck('subject.name')->unique()->values(),
            ];
        })->values();

        $schoolYears = SchoolYear::orderByDesc('school_year')->get();

        return view('pov.teacher.students.grading-sections-index', compact(
            'sections', 'gradeLevel', 'schoolYears', 'selectedSchoolYearId'
        ));
    }

    public function students(Request $request, int $sectionId)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $section = \App\Models\Section::findOrFail($sectionId);

        $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->with('subject')
            ->get();

        abort_if($teachingAssignments->isEmpty(), 403, 'You do not have an active teaching assignment for this section.');

        $enrollments = Enrollment::where('section_id', $sectionId)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $taIds = $teachingAssignments->pluck('id');
        $enrollmentIds = $enrollments->pluck('id');

        $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->get()
            ->groupBy('enrollment_id');

        $attendanceCountsByEnrollment = \App\Models\AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->where('scan_type', 'IN')
            ->selectRaw('enrollment_id, COUNT(DISTINCT scan_time) as present_count')
            ->groupBy('enrollment_id')
            ->pluck('present_count', 'enrollment_id');

        $students = $enrollments->map(function ($enrollment) use ($gradesByEnrollment, $attendanceCountsByEnrollment) {
            $grades = $gradesByEnrollment->get($enrollment->id, collect());

            return (object) [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student->id,
                'name' => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                'student_number' => $enrollment->student->student_number,
                'avg_grade' => $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null,
                'present_count' => $attendanceCountsByEnrollment->get($enrollment->id, 0),
            ];
        })->sortBy('name')->values();

        $sectionAvg = $students->filter(fn ($s) => $s->avg_grade !== null)->avg('avg_grade');

        return view('pov.teacher.students.grading-students-index', [
            'section' => $section,
            'students' => $students,
            'sectionAvg' => $sectionAvg !== null ? round($sectionAvg, 1) : null,
        ]);
    }

    public function studentDetail(Request $request, int $enrollmentId)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $enrollment = Enrollment::with('student', 'section')->findOrFail($enrollmentId);

        $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $enrollment->section_id)
            ->where('status', 'active')
            ->with('subject')
            ->get();

        abort_if($teachingAssignments->isEmpty(), 403, 'You do not have an active teaching assignment for this student\'s section.');

        $taIds = $teachingAssignments->pluck('id');

        $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->where('enrollment_id', $enrollmentId)
            ->with('gradingPeriod')
            ->get()
            ->groupBy('teaching_assignment_id');

        $subjects = $teachingAssignments->map(function ($ta) use ($grades) {
            $subjectGrades = $grades->get($ta->id, collect())
                ->sortBy(fn ($g) => $g->gradingPeriod->sequence ?? 0)
                ->map(fn ($g) => (object) [
                    'period_name' => 'Term ' . ($g->gradingPeriod->sequence ?? '—'),
                    'grade' => $g->transmuted_grade,
                ])
                ->values();

            return (object) [
                'subject_name' => $ta->subject->name,
                'periods' => $subjectGrades,
                'average' => $subjectGrades->filter(fn ($g) => $g->grade !== null)->avg('grade'),
            ];
        });

        $presentCount = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->distinct('scan_time')
            ->count();

        return view('pov.teacher.students.grading-student-detail', compact('enrollment', 'subjects', 'presentCount'));
    }
}