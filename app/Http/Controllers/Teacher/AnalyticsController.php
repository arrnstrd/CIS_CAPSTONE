<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    const RISK_HIGH_THRESHOLD = 75;
    const RISK_MODERATE_THRESHOLD = 85;

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.analytics.analytics-index', ['gradeLevels' => collect(), 'schoolYears' => collect(), 'selectedSchoolYearId' => null]);
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

        return view('teacher-modules.analytics.analytics-index', compact('gradeLevels', 'schoolYears', 'selectedSchoolYearId'));
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
                'total_students' => $totalStudents,
                'avg_grade' => $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null,
            ];
        })->values();

        $schoolYears = SchoolYear::orderByDesc('school_year')->get();

        return view('teacher-modules.analytics.analytics-sections-index', compact(
            'sections', 'gradeLevel', 'schoolYears', 'selectedSchoolYearId'
        ));
    }

    public static function riskLevel(?float $avgGrade): ?string
    {
        if ($avgGrade === null) {
            return null;
        }

        if ($avgGrade < self::RISK_HIGH_THRESHOLD) {
            return 'High';
        }

        if ($avgGrade < self::RISK_MODERATE_THRESHOLD) {
            return 'Moderate';
        }

        return 'Low';
    }

    public function students(Request $request, int $sectionId)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $section = Section::findOrFail($sectionId);

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

        $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->get()
            ->groupBy('enrollment_id');

        $students = $enrollments->map(function ($enrollment) use ($gradesByEnrollment) {
            $grades = $gradesByEnrollment->get($enrollment->id, collect());
            $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;

            return (object) [
                'enrollment_id' => $enrollment->id,
                'name' => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                'student_number' => $enrollment->student->student_number,
                'avg_grade' => $avgGrade,
                'risk_level' => self::riskLevel($avgGrade),
            ];
        })->sortBy('name')->values();

        return view('teacher-modules.analytics.analytics-students-index', compact('students', 'section'));
    }

    public function studentSubjects(Request $request, int $enrollmentId)
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
                ->filter(fn ($g) => ($g->gradingPeriod->sequence ?? 99) <= 3)
                ->map(fn ($g) => (object) [
                    'term_label' => 'Term ' . ($g->gradingPeriod->sequence ?? '—'),
                    'grade' => $g->transmuted_grade,
                ])
                ->values();

            return (object) [
                'teaching_assignment_id' => $ta->id,
                'subject_name' => $ta->subject->name,
                'trend' => $subjectGrades,
                'average' => $subjectGrades->filter(fn ($g) => $g->grade !== null)->avg('grade'),
            ];
        });

        return view('teacher-modules.analytics.analytics-student-subjects', compact('enrollment', 'subjects'));
    }
}