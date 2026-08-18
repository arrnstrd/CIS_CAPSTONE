<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class StudentProfileSearchController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $query = trim($request->input('q', ''));
        $results = collect();

        if ($teacher && $query !== '') {
            $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with('section', 'subject')
                ->get();

            $sectionIds = $teachingAssignments->pluck('section_id')->unique();

            $enrollments = Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'active')
                ->with('student', 'section')
                ->get()
                ->filter(function ($enrollment) use ($query) {
                    $student = $enrollment->student;
                    if (! $student) {
                        return false;
                    }
                    $fullName = strtolower(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
                    $studentNumber = strtolower($student->student_number ?? '');
                    $needle = strtolower($query);

                    return str_contains($fullName, $needle) || str_contains($studentNumber, $needle);
                });

            foreach ($enrollments as $enrollment) {
                $sectionTaIds = $teachingAssignments->where('section_id', $enrollment->section_id)->pluck('id');

                $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $sectionTaIds)
                    ->where('enrollment_id', $enrollment->id)
                    ->whereNotNull('transmuted_grade')
                    ->get();

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

        return view('teacher-modules.student-profile-search', compact('query', 'results'));
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

        return view('teacher-modules.student-profile-detail', [
            'enrollment' => $enrollment,
            'subjects' => $subjects,
            'overallAvg' => $overallAvg !== null ? round($overallAvg, 1) : null,
            'presentCount' => $presentCount,
            'recentScans' => $recentScans,
            'riskLevel' => $riskLevel,
        ]);
    }
}