<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class SectionsOverviewController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.sections-overview-index', [
                'gradeLevels' => collect(),
                'selectedGradeLevel' => null,
                'sections' => collect(),
            ]);
        }

        $allAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->get();

        $gradeLevels = $allAssignments->pluck('section.grade_level')->unique()->sort()->values();

        $selectedGradeLevel = $request->input('grade_level') ?: $gradeLevels->first();

        $assignments = $allAssignments->filter(fn ($ta) => $ta->section->grade_level == $selectedGradeLevel);

        $sections = collect();
        foreach ($assignments->groupBy('section_id') as $sectionAssignments) {
            $section = $sectionAssignments->first()->section;
            $taIds = $sectionAssignments->pluck('id');

            $enrollments = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->get();
            $enrollmentIds = $enrollments->pluck('id');
            $totalStudents = $enrollmentIds->count();

            $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get()
                ->groupBy('enrollment_id');

            $studentAverages = collect();
            $atRiskCount = 0;
            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                $avg = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;
                if ($avg !== null) {
                    $studentAverages->push($avg);
                }
                if (in_array(AnalyticsController::riskLevel($avg), ['High', 'Moderate'])) {
                    $atRiskCount++;
                }
            }

            $presentEnrollmentCount = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->distinct('enrollment_id')
                ->count('enrollment_id');
            $attendanceRate = $totalStudents ? round(($presentEnrollmentCount / $totalStudents) * 100, 1) : null;

            $sections->push((object) [
                'section_name' => $section->name,
                'total_students' => $totalStudents,
                'avg_grade' => $studentAverages->isNotEmpty() ? round($studentAverages->avg(), 1) : null,
                'passing_rate' => $studentAverages->isNotEmpty()
                    ? round(($studentAverages->filter(fn ($g) => $g >= 75)->count() / $studentAverages->count()) * 100, 1)
                    : null,
                'attendance_rate' => $attendanceRate,
                'at_risk_count' => $atRiskCount,
            ]);
        }
        $sections = $sections->sortBy('section_name')->values();

        return view('teacher-modules.sections-overview-index', compact('gradeLevels', 'selectedGradeLevel', 'sections'));
    }
}