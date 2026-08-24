<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use App\Models\GradingPeriod;
use Illuminate\Http\Request;

class StudentProfileSearchController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $query = trim($request->input('q', ''));
        $classFilter = $request->input('class');
        $gradeLevelFilter = $request->input('grade_level');
        $subjectFilter = $request->input('subject');
        $termFilter = $request->input('term');

        $results = collect();
        $classes = collect();
        $gradeLevels = collect();
        $subjects = collect();

        if ($teacher) {
            // First get the full, unfiltered list of teaching assignments for filter options
            $allTeachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with('section', 'subject')
                ->get();

            // Build filter options from the full list (always showing all options)
            $classes = $allTeachingAssignments->map(function ($ta) {
                return (object) [
                    'id' => $ta->id,
                    'name' => $ta->section->name . ' - ' . $ta->subject->name
                ];
            })->unique('id')->values();

            $gradeLevels = $allTeachingAssignments->pluck('section.grade_level')->unique()->sort()->values();
            $subjects = $allTeachingAssignments->pluck('subject.name')->unique()->sort()->values();

            // Separately, get filtered teaching assignments for determining which students to show
            $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with('section', 'subject');

            // Apply class filter to the query used for determining results
            if ($classFilter) {
                if (is_numeric($classFilter)) {
                    $teachingAssignmentsQuery->where('id', $classFilter);
                } else {
                    $teachingAssignmentsQuery->whereHas('section', function ($q) use ($classFilter) {
                        $q->where('name', 'like', '%' . $classFilter . '%');
                    });
                }
            }

            $teachingAssignments = $teachingAssignmentsQuery->get();

            $sectionIds = $teachingAssignments->pluck('section_id')->unique();

            $enrollmentsQuery = Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'active')
                ->with('student', 'section');

            $enrollments = $enrollmentsQuery->get();

            foreach ($enrollments as $enrollment) {
                // Apply grade level filter
                if ($gradeLevelFilter && $enrollment->section->grade_level != $gradeLevelFilter) {
                    continue;
                }

                // Apply subject filter
                if ($subjectFilter) {
                    $taSubjects = $teachingAssignments->where('section_id', $enrollment->section_id)
                        ->pluck('subject.name');
                    if (!$taSubjects->contains($subjectFilter)) {
                        continue;
                    }
                }

                $sectionTaIds = $teachingAssignments->where('section_id', $enrollment->section_id)->pluck('id');

                // Apply term filter
                $gradesQuery = QuarterlyGrade::whereIn('teaching_assignment_id', $sectionTaIds)
                    ->where('enrollment_id', $enrollment->id);

                if ($termFilter) {
                    $gradingPeriod = GradingPeriod::where('sequence', $termFilter)->first();
                    if ($gradingPeriod) {
                        $gradesQuery->where('grading_period_id', $gradingPeriod->id);
                    }
                }

                $grades = $gradesQuery->whereNotNull('transmuted_grade')->get();

                // Apply search filter
                if ($query !== '') {
                    $student = $enrollment->student;
                    if (! $student) {
                        continue;
                    }
                    $fullName = strtolower(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
                    $studentNumber = strtolower($student->student_number ?? '');
                    $needle = strtolower($query);

                    if (!str_contains($fullName, $needle) && !str_contains($studentNumber, $needle)) {
                        continue;
                    }
                }

                $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;

                $presentCount = \App\Models\AttendanceLog::where('enrollment_id', $enrollment->id)
                    ->where('scan_type', 'IN')
                    ->distinct('scan_time')
                    ->count();

                $subjectNames = $teachingAssignments->where('section_id', $enrollment->section_id)
                    ->pluck('subject.name')
                    ->unique()
                    ->implode(', ');

                $results->push((object) [
                    'enrollment_id' => $enrollment->id,
                    'name' => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                    'student_number' => $enrollment->student->student_number,
                    'grade_level' => $enrollment->section->grade_level,
                    'section_name' => $enrollment->section->name,
                    'subjects' => $subjectNames,
                    'avg_grade' => $avgGrade,
                    'present_count' => $presentCount,
                    'risk_level' => AnalyticsController::riskLevel($avgGrade),
                ]);
            }

            $results = $results->sortBy('name')->values();
        }

        return view('teacher-modules.students.student-profile-search', compact(
            'query', 'results', 'classes', 'gradeLevels', 'subjects', 'classFilter', 
            'gradeLevelFilter', 'subjectFilter', 'termFilter'
        ));
    }

    public function show(Request $request, int $enrollmentId)
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
                'subject_name' => $ta->subject->name,
                'periods' => $subjectGrades,
                'average' => $subjectGrades->filter(fn ($g) => $g->grade !== null)->avg('grade'),
            ];
        });

        $overallAvg = $subjects->filter(fn ($s) => $s->average !== null)->avg('average');

        $presentCount = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->distinct('scan_time')
            ->count();

        $recentScans = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->orderByDesc('scan_time')
            ->limit(5)
            ->get();

        $riskLevel = AnalyticsController::riskLevel($overallAvg !== null ? round($overallAvg, 1) : null);

        return view('teacher-modules.students.student-profile-detail', [
            'enrollment' => $enrollment,
            'subjects' => $subjects,
            'overallAvg' => $overallAvg !== null ? round($overallAvg, 1) : null,
            'presentCount' => $presentCount,
            'recentScans' => $recentScans,
            'riskLevel' => $riskLevel,
        ]);
    }
}