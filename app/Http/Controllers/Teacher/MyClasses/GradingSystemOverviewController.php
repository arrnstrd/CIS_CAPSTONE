<?php

namespace App\Http\Controllers\Teacher\MyClasses;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class GradingSystemOverviewController extends Controller
{
    const DEFAULT_PASSING_GRADE = 75;

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $schoolYears = SchoolYear::orderByDesc('school_year')->get();
        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();

        if (! $teacher) {
            return view('pov.teacher.my-classes.grading-system', [
                'schoolYears' => $schoolYears,
                'gradingPeriods' => $gradingPeriods,
                'selectedSchoolYearId' => $request->input('school_year_id'),
                'selectedGradingPeriodId' => null,
                'stats' => $this->emptyStats(),
                'sectionBreakdown' => collect(),
                'chartData' => $this->emptyChartData(),
            ]);
        }

        // Default: null means "All School Years" — avoids hiding sections due to
        // ambiguous is_active flags on school_years while that data gets cleaned up.
        $selectedSchoolYearId = $request->input('school_year_id') ?: null;
        $selectedGradingPeriodId = $request->input(
            'grading_period_id',
            $gradingPeriods->where('is_active', true)->sortByDesc('sequence')->first()?->id
                ?? $gradingPeriods->sortByDesc('sequence')->first()?->id
        );

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject']);

        if ($selectedSchoolYearId) {
            $teachingAssignmentsQuery->where('school_year_id', $selectedSchoolYearId);
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        $teachingAssignmentIds = $teachingAssignments->pluck('id');
        $sectionIds = $teachingAssignments->pluck('section_id')->unique();

        $enrollmentsQuery = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'active');

        if ($selectedSchoolYearId) {
            $enrollmentsQuery->where('school_year_id', $selectedSchoolYearId);
        }

        $enrollmentIds = $enrollmentsQuery->pluck('id');

        $totalStudents = $enrollmentIds->count();

        $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $teachingAssignmentIds)
            ->where('grading_period_id', $selectedGradingPeriodId)
            ->whereNotNull('transmuted_grade')
            ->get();

        $gradedCount = $grades->count();
        $passingCount = $grades->where('transmuted_grade', '>=', self::DEFAULT_PASSING_GRADE)->count();
        $failingCount = $grades->where('transmuted_grade', '<', self::DEFAULT_PASSING_GRADE)->count();

        $avgGrade = $gradedCount ? round($grades->avg('transmuted_grade'), 1) : null;
        $passingRate = $gradedCount ? round(($passingCount / $gradedCount) * 100, 1) : null;
        $failingRate = $gradedCount ? round(($failingCount / $gradedCount) * 100, 1) : null;

        $presentEnrollmentCount = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->where('scan_type', 'IN')
            ->distinct('enrollment_id')
            ->count('enrollment_id');
        $avgAttendance = $totalStudents ? round(($presentEnrollmentCount / $totalStudents) * 100, 1) : null;

        $stats = [
            'avg_grade' => $avgGrade,
            'passing_rate' => $passingRate,
            'failing_rate' => $failingRate,
            'avg_attendance' => $avgAttendance,
            'total_students' => $totalStudents,
            'failing_students' => $failingCount,
        ];

        $sectionBreakdown = $teachingAssignments->map(function ($ta) use ($selectedGradingPeriodId) {
            $sectionEnrollmentIds = Enrollment::where('section_id', $ta->section_id)
                ->where('status', 'active')
                ->pluck('id');

            $sectionGrades = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
                ->where('grading_period_id', $selectedGradingPeriodId)
                ->whereNotNull('transmuted_grade')
                ->get();

            $sectionGradedCount = $sectionGrades->count();
            $sectionPassingCount = $sectionGrades->where('transmuted_grade', '>=', self::DEFAULT_PASSING_GRADE)->count();
            $sectionFailingCount = $sectionGradedCount - $sectionPassingCount;

            $sectionPresentCount = AttendanceLog::whereIn('enrollment_id', $sectionEnrollmentIds)
                ->where('scan_type', 'IN')
                ->distinct('enrollment_id')
                ->count('enrollment_id');
            $sectionTotal = $sectionEnrollmentIds->count();

            return (object) [
                'section_name' => $ta->section->name,
                'grade_level' => $ta->section->grade_level,
                'subject_name' => $ta->subject->name,
                'total_students' => $sectionTotal,
                'avg_grade' => $sectionGradedCount ? round($sectionGrades->avg('transmuted_grade'), 1) : null,
                'passing_rate' => $sectionGradedCount ? round(($sectionPassingCount / $sectionGradedCount) * 100, 1) : null,
                'passing_count' => $sectionPassingCount,
                'failing_count' => $sectionFailingCount,
                'attendance_rate' => $sectionTotal ? round(($sectionPresentCount / $sectionTotal) * 100, 1) : 0,
            ];
        })->values();

        $trendData = $gradingPeriods->map(function ($period) use ($teachingAssignmentIds) {
            $periodGrades = QuarterlyGrade::whereIn('teaching_assignment_id', $teachingAssignmentIds)
                ->where('grading_period_id', $period->id)
                ->whereNotNull('transmuted_grade')
                ->get();

            return $periodGrades->count() ? round($periodGrades->avg('transmuted_grade'), 1) : null;
        })->values();

        $chartData = [
            'sectionLabels' => $sectionBreakdown->map(fn ($s) => $s->section_name . ' (Grade ' . $s->grade_level . ')')->values(),
            'sectionAvgGrades' => $sectionBreakdown->map(fn ($s) => $s->avg_grade ?? 0)->values(),
            'trendLabels' => $gradingPeriods->map(fn ($p) => 'Term ' . $p->sequence)->values(),
            'trendData' => $trendData,
            'totalPassing' => $sectionBreakdown->sum('passing_count'),
            'totalFailing' => $sectionBreakdown->sum('failing_count'),
            'attendanceLabels' => $sectionBreakdown->map(fn ($s) => $s->section_name . ' (Grade ' . $s->grade_level . ')')->values(),
            'attendanceRates' => $sectionBreakdown->map(fn ($s) => $s->attendance_rate)->values(),
        ];

        return view('pov.teacher.my-classes.grading-system', compact(
            'schoolYears', 'gradingPeriods', 'selectedSchoolYearId', 'selectedGradingPeriodId',
            'stats', 'sectionBreakdown', 'chartData'
        ));
    }

    private function emptyStats(): array
    {
        return [
            'avg_grade' => null,
            'passing_rate' => null,
            'failing_rate' => null,
            'avg_attendance' => null,
            'total_students' => 0,
            'failing_students' => 0,
        ];
    }

    private function emptyChartData(): array
    {
        return [
            'sectionLabels' => collect(),
            'sectionAvgGrades' => collect(),
            'trendLabels' => collect(),
            'trendData' => collect(),
            'totalPassing' => 0,
            'totalFailing' => 0,
            'attendanceLabels' => collect(),
            'attendanceRates' => collect(),
        ];
    }
}