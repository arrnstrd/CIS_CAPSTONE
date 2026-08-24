<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\GradingPeriod;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class AttendanceAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.analytics.attendance-analytics-index', [
                'studentPoints' => collect(),
                'sectionPoints' => collect(),
                'gradeLevels' => collect(),
                'selectedGradeLevel' => null,
            ]);
        }

        $selectedGradeLevel = $request->input('grade_level') ?: null;

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

        $studentPoints = collect();
        $sectionPoints = collect();

        foreach ($teachingAssignments->groupBy('section_id') as $sectionAssignments) {
            $section = $sectionAssignments->first()->section;
            $taIds = $sectionAssignments->pluck('id');

            $enrollments = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->get();

            $enrollmentIds = $enrollments->pluck('id');
            $totalStudents = $enrollmentIds->count();

            if ($totalStudents === 0) {
                continue;
            }

            $gradesByEnrollment = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get()
                ->groupBy('enrollment_id');

            // Approximate school-day tracking window: from this section's earliest scan to today, weekdays only.
            $earliestScan = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)->min('scan_time');
            $schoolDaysCount = 0;

            if ($earliestScan) {
                $cursor = \Carbon\Carbon::parse($earliestScan)->startOfDay();
                $end = now()->startOfDay();
                while ($cursor->lte($end)) {
                    if (! $cursor->isWeekend()) {
                        $schoolDaysCount++;
                    }
                    $cursor->addDay();
                }
            }

            $presentDaysByEnrollment = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->selectRaw('enrollment_id, COUNT(DISTINCT DATE(scan_time)) as present_days')
                ->groupBy('enrollment_id')
                ->pluck('present_days', 'enrollment_id');

            $attendanceCountsByEnrollment = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->selectRaw('enrollment_id, COUNT(DISTINCT scan_time) as present_count')
                ->groupBy('enrollment_id')
                ->pluck('present_count', 'enrollment_id');

            $maxScans = $attendanceCountsByEnrollment->max() ?: 1;

            $sectionGradesSum = 0;
            $sectionGradesCount = 0;
            $sectionPresentTotal = 0;

            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;
                $presentDays = $presentDaysByEnrollment->get($enrollment->id, 0);
                $absences = max(0, $schoolDaysCount - $presentDays);

                if ($avgGrade !== null) {
                    $studentPoints->push(['x' => $absences, 'y' => $avgGrade]);
                    $sectionGradesSum += $avgGrade;
                    $sectionGradesCount++;
                }

                $sectionPresentTotal += $attendanceCountsByEnrollment->get($enrollment->id, 0);
            }

            $sectionAttendanceRate = $maxScans > 0 ? round(($sectionPresentTotal / ($totalStudents * $maxScans)) * 100, 1) : 0;
            $sectionAvgGrade = $sectionGradesCount ? round($sectionGradesSum / $sectionGradesCount, 1) : null;

            if ($sectionAvgGrade !== null) {
                $sectionPoints->push([
                    'x' => $sectionAttendanceRate,
                    'y' => $sectionAvgGrade,
                    'label' => $section->name,
                ]);
            }
        }

        $insights = $this->computeInsights($studentPoints, $sectionPoints);
        $termTrend = $this->computeTermTrend($teachingAssignments, $studentPoints);

        return view('teacher-modules.analytics.attendance-analytics-index', compact(
            'studentPoints', 'sectionPoints', 'gradeLevels', 'selectedGradeLevel', 'insights', 'termTrend'
        ));
    }

    private function computeInsights($studentPoints, $sectionPoints): array
    {
        $minSampleSize = 5;

        // Insight 1: Observed relationship (correlation direction between absences and grade)
        $relationshipText = 'Not enough data yet to observe a pattern.';
        if ($studentPoints->count() >= $minSampleSize) {
            $n = $studentPoints->count();
            $sumX = $studentPoints->sum('x');
            $sumY = $studentPoints->sum('y');
            $sumXY = $studentPoints->sum(fn ($p) => $p['x'] * $p['y']);
            $sumX2 = $studentPoints->sum(fn ($p) => $p['x'] * $p['x']);
            $sumY2 = $studentPoints->sum(fn ($p) => $p['y'] * $p['y']);

            $numerator = ($n * $sumXY) - ($sumX * $sumY);
            $denominator = sqrt((($n * $sumX2) - ($sumX ** 2)) * (($n * $sumY2) - ($sumY ** 2)));
            $correlation = $denominator != 0 ? $numerator / $denominator : 0;

            if ($correlation < -0.2) {
                $relationshipText = 'Students with more absences tend to have lower average grades in this data.';
            } elseif ($correlation > 0.2) {
                $relationshipText = 'No clear negative pattern between absences and grades was observed in this data.';
            } else {
                $relationshipText = 'No strong pattern between absences and grades was observed in this data.';
            }
        }

        // Insight 2: Absence-grade comparison (high vs low absence groups)
        $absenceTrendText = 'Not enough data yet to compare absence groups.';
        if ($studentPoints->count() >= $minSampleSize) {
            $highAbsence = $studentPoints->where('x', '>=', 15);
            $lowAbsence = $studentPoints->where('x', '<', 15);

            if ($highAbsence->count() >= 2 && $lowAbsence->count() >= 2) {
                $highAvg = round($highAbsence->avg('y'), 1);
                $lowAvg = round($lowAbsence->avg('y'), 1);
                $diff = round($lowAvg - $highAvg, 1);

                $absenceTrendText = $diff > 0
                    ? "Students with 15+ absences average {$diff} points lower ({$highAvg}) than those with fewer absences ({$lowAvg})."
                    : "Students with 15+ absences do not show a lower average grade in this data ({$highAvg} vs {$lowAvg}).";
            } else {
                $absenceTrendText = 'Not enough students with 15+ absences yet to compare.';
            }
        }

        // Insight 3: Section-level attendance/grade directional pattern
        $termPatternText = 'Not enough section data yet to observe a pattern.';
        if ($sectionPoints->count() >= 2) {
            $sorted = $sectionPoints->sortBy('x')->values();
            $lowest = $sorted->first();
            $highest = $sorted->last();

            if ($highest['x'] > $lowest['x']) {
                $termPatternText = $highest['y'] >= $lowest['y']
                    ? 'Sections with higher attendance rates tend to also have higher average grades.'
                    : 'Sections with higher attendance rates do not consistently have higher average grades in this data.';
            }
        }

        return [
            'relationship' => $relationshipText,
            'absence_trend' => $absenceTrendText,
            'term_pattern' => $termPatternText,
        ];
    }

    private function computeTermTrend($teachingAssignments, $studentPoints): array
    {
        $taIds = $teachingAssignments->pluck('id');
        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();

        $sectionIds = $teachingAssignments->pluck('section_id')->unique();
        $enrollmentIds = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->pluck('id');

        $totalStudents = $enrollmentIds->count();
        $presentEnrollmentCount = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->where('scan_type', 'IN')
            ->distinct('enrollment_id')
            ->count('enrollment_id');

        $overallAttendanceRate = $totalStudents ? round(($presentEnrollmentCount / $totalStudents) * 100, 1) : null;

        $gradesByPeriod = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->get()
            ->groupBy('grading_period_id');

        $labels = [];
        $gradeSeries = [];
        $attendanceSeries = [];

        foreach ($gradingPeriods as $period) {
            $labels[] = 'Term ' . $period->sequence;
            $periodGrades = $gradesByPeriod->get($period->id, collect());
            $gradeSeries[] = $periodGrades->count() ? round($periodGrades->avg('transmuted_grade'), 1) : null;
            $attendanceSeries[] = $overallAttendanceRate;
        }

        return [
            'labels' => $labels,
            'gradeSeries' => $gradeSeries,
            'attendanceSeries' => $attendanceSeries,
        ];
    }
}