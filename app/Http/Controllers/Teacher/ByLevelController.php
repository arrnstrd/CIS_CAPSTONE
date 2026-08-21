<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class ByLevelController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.analytics.by-level-index', [
                'gradeData' => ['labels' => [], 'avgGrade' => [], 'passingRate' => [], 'atRisk' => []],
                'sectionRows' => collect(),
                'subjectRows' => collect(),
                'termData' => ['labels' => [], 'avgGrade' => [], 'passingRate' => []],
            ]);
        }

        $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        // ----- GRADE LEVEL VIEW -----
        $gradeGroups = $teachingAssignments->groupBy(fn ($ta) => $ta->section->grade_level)->sortKeys();
        $gradeLabels = [];
        $gradeAvg = [];
        $gradePassing = [];
        $gradeAtRisk = [];

        foreach ($gradeGroups as $gradeLevel => $assignments) {
            $taIds = $assignments->pluck('id');
            $sectionIds = $assignments->pluck('section_id')->unique();

            $enrollments = Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'active')
                ->get();

            $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get()
                ->groupBy('enrollment_id');

            $studentAverages = collect();
            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                if ($grades->count()) {
                    $studentAverages->push(round($grades->avg('transmuted_grade'), 1));
                }
            }

            $atRiskCount = 0;
            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                $avg = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;
                if (in_array(AnalyticsController::riskLevel($avg), ['High', 'Moderate'])) {
                    $atRiskCount++;
                }
            }

            $gradeLabels[] = 'Grade ' . $gradeLevel;
            $gradeAvg[] = $studentAverages->isNotEmpty() ? round($studentAverages->avg(), 1) : 0;
            $gradePassing[] = $studentAverages->isNotEmpty()
                ? round(($studentAverages->filter(fn ($g) => $g >= 75)->count() / $studentAverages->count()) * 100, 1)
                : 0;
            $gradeAtRisk[] = $atRiskCount;
        }

        // ----- SECTION VIEW -----
        $sectionRows = collect();
        foreach ($teachingAssignments->groupBy('section_id') as $assignments) {
            $section = $assignments->first()->section;
            $taIds = $assignments->pluck('id');

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

            $sectionRows->push((object) [
                'grade_level' => $section->grade_level,
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
        $sectionRows = $sectionRows->sortBy(['grade_level', 'section_name'])->values();

        // ----- SUBJECT VIEW -----
        $subjectRows = collect();
        foreach ($teachingAssignments->groupBy(fn ($ta) => $ta->subject->name) as $subjectName => $assignments) {
            $taIds = $assignments->pluck('id');
            $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get();

            $gradedCount = $grades->count();
            $passingCount = $grades->where('transmuted_grade', '>=', 75)->count();

            $subjectRows->push((object) [
                'subject_name' => $subjectName,
                'avg_grade' => $gradedCount ? round($grades->avg('transmuted_grade'), 1) : null,
                'passing_rate' => $gradedCount ? round(($passingCount / $gradedCount) * 100, 1) : null,
                'failing_rate' => $gradedCount ? round((($gradedCount - $passingCount) / $gradedCount) * 100, 1) : null,
            ]);
        }
        $subjectRows = $subjectRows->sortBy('subject_name')->values();

        // ----- TERM VIEW -----
        $taIds = $teachingAssignments->pluck('id');
        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();
        $termLabels = [];
        $termAvg = [];
        $termPassing = [];

        foreach ($gradingPeriods as $period) {
            $periodGrades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->where('grading_period_id', $period->id)
                ->whereNotNull('transmuted_grade')
                ->get();

            $termLabels[] = 'Term ' . $period->sequence;
            $termAvg[] = $periodGrades->count() ? round($periodGrades->avg('transmuted_grade'), 1) : 0;
            $termPassing[] = $periodGrades->count()
                ? round(($periodGrades->where('transmuted_grade', '>=', 75)->count() / $periodGrades->count()) * 100, 1)
                : 0;
        }

        return view('teacher-modules.analytics.by-level-index', [
            'gradeData' => ['labels' => $gradeLabels, 'avgGrade' => $gradeAvg, 'passingRate' => $gradePassing, 'atRisk' => $gradeAtRisk],
            'sectionRows' => $sectionRows,
            'subjectRows' => $subjectRows,
            'termData' => ['labels' => $termLabels, 'avgGrade' => $termAvg, 'passingRate' => $termPassing],
        ]);
    }
}