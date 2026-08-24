<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class AtRiskController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.analytics.at-risk-index', [
                'students' => collect(),
                'gradeLevels' => collect(),
                'selectedGradeLevel' => null,
                'selectedRiskLevel' => null,
                'stats' => ['total' => 0, 'high' => 0, 'moderate' => 0],
            ]);
        }

        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedRiskLevel = $request->input('risk_level') ?: null;

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section');

        if ($selectedGradeLevel) {
            $teachingAssignmentsQuery->whereHas('section', fn ($q) => $q->where('grade_level', $selectedGradeLevel));
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        $gradeLevels = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->get()
            ->pluck('section.grade_level')
            ->unique()
            ->sort()
            ->values();

        $students = collect();

        foreach ($teachingAssignments->groupBy('section_id') as $sectionAssignments) {
            $section = $sectionAssignments->first()->section;
            $taIds = $sectionAssignments->pluck('id');

            $enrollments = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->with('student')
                ->get();

            $enrollmentIds = $enrollments->pluck('id');

            $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get()
                ->groupBy('enrollment_id');

            $attendanceCountsByEnrollment = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->selectRaw('enrollment_id, COUNT(DISTINCT scan_time) as present_count')
                ->groupBy('enrollment_id')
                ->pluck('present_count', 'enrollment_id');

            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;
                $riskLevel = AnalyticsController::riskLevel($avgGrade);

                if (! in_array($riskLevel, ['High', 'Moderate'])) {
                    continue;
                }

                $presentCount = $attendanceCountsByEnrollment->get($enrollment->id, 0);

                $students->push((object) [
                    'name' => trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                    'student_number' => $enrollment->student->student_number,
                    'grade_level' => $section->grade_level,
                    'section_name' => $section->name,
                    'avg_grade' => $avgGrade,
                    'present_count' => $presentCount,
                    'risk_level' => $riskLevel,
                ]);
            }
        }

        if ($selectedRiskLevel) {
            $students = $students->where('risk_level', $selectedRiskLevel);
        }

        $students = $students->sortBy('name')->values();

        $allAtRisk = $teachingAssignments->isNotEmpty() ? $students : collect();
        $stats = [
            'total' => $students->count(),
            'high' => $students->where('risk_level', 'High')->count(),
            'moderate' => $students->where('risk_level', 'Moderate')->count(),
        ];

        return view('teacher-modules.analytics.at-risk-index', compact(
            'students', 'gradeLevels', 'selectedGradeLevel', 'selectedRiskLevel', 'stats'
        ));
    }
}