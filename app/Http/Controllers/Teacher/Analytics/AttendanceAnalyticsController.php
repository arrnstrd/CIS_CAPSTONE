<?php

namespace App\Http\Controllers\Teacher\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\TermGrade;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('pov.teacher.analytics.attendance-analytics-index', [
                'studentPoints' => collect(),
                'sectionPoints' => collect(),
                'gradeLevels' => collect(),
                'sections' => collect(),
                'subjects' => collect(),
                'selectedGradeLevel' => null,
                'selectedSectionId' => null,
                'selectedSubjectId' => null,
                'selectedTerm' => null,
                'attendanceSummary' => null,
                'sectionSummaries' => collect(),
                'insights' => [
                    'relationship' => 'No active teaching assignments found.',
                    'absence_trend' => 'No active teaching assignments found.',
                    'term_pattern' => 'No active teaching assignments found.',
                ],
                'termTrend' => ['labels' => [], 'gradeSeries' => [], 'attendanceSeries' => []],
            ]);
        }

        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedSectionId = $request->input('section_id') ?: null;
        $selectedSubjectId = $request->input('subject_id') ?: null;
        $selectedTerm = $request->input('term') ?: null;

        // Base query for teacher's active assignments (matches AnalyticsController)
        $baseAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject']);

        $allTeacherAssignments = $baseAssignmentsQuery->get();

        $gradeLevels = $allTeacherAssignments->pluck('section.grade_level')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $sections = $allTeacherAssignments->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $subjects = $allTeacherAssignments->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        // Apply filters to active assignments
        $filteredAssignments = $allTeacherAssignments;

        if ($selectedGradeLevel) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => $ta->section && $ta->section->grade_level == $selectedGradeLevel
            );
        }

        if ($selectedSectionId) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => $ta->section_id == $selectedSectionId
            );
        }

        if ($selectedSubjectId) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => $ta->subject_id == $selectedSubjectId
            );
        }

        // Active grading period for term calculation
        $gradingPeriod = null;
        if ($selectedTerm) {
            $gradingPeriod = GradingPeriod::trimester()->where('sequence', $selectedTerm)->first();
        } else {
            $gradingPeriod = GradingPeriod::where('is_active', true)
                ->trimester()
                ->orderBy('sequence')
                ->first();
        }

        $studentPoints = collect();
        $sectionPoints = collect();
        $sectionSummaries = collect();

        $overallPresent = 0;
        $overallLate = 0;
        $overallAbsent = 0;
        $overallExcused = 0;
        $overallNotInClassroom = 0;
        $overallTotalStudents = 0;
        $totalPossibleStudentDays = 0;
        $totalAttendedStudentDays = 0;

        foreach ($filteredAssignments->groupBy('section_id') as $sectionId => $sectionAssignments) {
            $section = $sectionAssignments->first()->section;
            $taIds = $sectionAssignments->pluck('id');

            $enrollments = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->with('student')
                ->get();

            $enrollmentIds = $enrollments->pluck('id');
            $sectionStudentCount = $enrollmentIds->count();

            if ($sectionStudentCount === 0) {
                continue;
            }

            $overallTotalStudents += $sectionStudentCount;

            // Determine tracking date window (matching RiskScoreService)
            [$startDate, $endDate, $schoolDaysCount] = $this->resolveSchoolDaysWindow($enrollmentIds, $gradingPeriod);

            // Fetch term grades for this section's students
            $gradesQuery = TermGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade');

            if ($selectedTerm) {
                $gradesQuery->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', $selectedTerm));
            }

            $gradesByEnrollment = $gradesQuery->get()->groupBy('enrollment_id');

            // Fetch QR scans (IN) within the window
            $qrLogsQuery = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN');

            if ($startDate && $endDate) {
                $qrLogsQuery->whereBetween('scan_time', [
                    $startDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay(),
                ]);
            }

            $qrLogs = $qrLogsQuery->select('enrollment_id', 'scan_time')
                ->get()
                ->groupBy(fn ($log) => $log->enrollment_id . '_' . Carbon::parse($log->scan_time)->toDateString());

            // Fetch Teacher Verifications within the window
            $verificationsQuery = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds);

            if ($startDate && $endDate) {
                $verificationsQuery->whereBetween('attendance_date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]);
            }

            $verifications = $verificationsQuery->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(fn ($v) => $v->enrollment_id . '_' . Carbon::parse($v->attendance_date)->toDateString())
                ->map(fn ($group) => $group->first()); // latest verification per student per date

            // Section level counters
            $sectionPresent = 0;
            $sectionLate = 0;
            $sectionAbsent = 0;
            $sectionExcused = 0;
            $sectionNotInClassroom = 0;
            $sectionAttendedDays = 0;
            $sectionGradesSum = 0;
            $sectionGradesCount = 0;

            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get($enrollment->id, collect());
                $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;

                // Evaluate each school day in the tracking window for this student
                $studentAttendedDays = 0;
                $studentAbsences = 0;

                if ($schoolDaysCount > 0 && $startDate && $endDate) {
                    $cursor = $startDate->copy();
                    while ($cursor->lte($endDate)) {
                        if (! $cursor->isWeekend()) {
                            $dateKey = $enrollment->id . '_' . $cursor->toDateString();
                            $hasQr = $qrLogs->has($dateKey);
                            $verification = $verifications->get($dateKey);

                            if ($verification) {
                                switch ($verification->status) {
                                    case AttendanceVerification::STATUS_PRESENT:
                                        $sectionPresent++;
                                        $studentAttendedDays++;
                                        break;
                                    case AttendanceVerification::STATUS_LATE:
                                        $sectionLate++;
                                        $studentAttendedDays++;
                                        break;
                                    case AttendanceVerification::STATUS_EXCUSED:
                                        $sectionExcused++;
                                        $studentAttendedDays++;
                                        break;
                                    case AttendanceVerification::STATUS_ABSENT:
                                        $sectionAbsent++;
                                        $studentAbsences++;
                                        break;
                                    case AttendanceVerification::STATUS_NOT_IN_CLASSROOM:
                                        $sectionNotInClassroom++;
                                        $studentAbsences++;
                                        break;
                                    default:
                                        if ($hasQr) {
                                            $sectionPresent++;
                                            $studentAttendedDays++;
                                        } else {
                                            $sectionAbsent++;
                                            $studentAbsences++;
                                        }
                                        break;
                                }
                            } elseif ($hasQr) {
                                $sectionPresent++;
                                $studentAttendedDays++;
                            } else {
                                // Weekday passed with no QR scan and no verification -> Absent
                                $sectionAbsent++;
                                $studentAbsences++;
                            }
                        }
                        $cursor->addDay();
                    }
                }

                $sectionAttendedDays += $studentAttendedDays;

                if ($avgGrade !== null) {
                    $studentPoints->push([
                        'x' => $studentAbsences,
                        'y' => $avgGrade,
                        'name' => $enrollment->student?->full_name ?? 'Student',
                    ]);
                    $sectionGradesSum += $avgGrade;
                    $sectionGradesCount++;
                }
            }

            $sectionPossibleDays = $sectionStudentCount * max(1, $schoolDaysCount);
            $sectionRate = $sectionPossibleDays > 0 ? round(($sectionAttendedDays / $sectionPossibleDays) * 100, 1) : 0;
            $sectionAvgGrade = $sectionGradesCount ? round($sectionGradesSum / $sectionGradesCount, 1) : null;

            $overallPresent += $sectionPresent;
            $overallLate += $sectionLate;
            $overallAbsent += $sectionAbsent;
            $overallExcused += $sectionExcused;
            $overallNotInClassroom += $sectionNotInClassroom;
            $totalPossibleStudentDays += $sectionPossibleDays;
            $totalAttendedStudentDays += $sectionAttendedDays;

            if ($sectionAvgGrade !== null) {
                $sectionPoints->push([
                    'x' => $sectionRate,
                    'y' => $sectionAvgGrade,
                    'label' => $section->name,
                ]);
            }

            $sectionSummaries->push([
                'id' => $section->id,
                'name' => $section->name,
                'grade_level' => $section->grade_level,
                'student_count' => $sectionStudentCount,
                'attendance_rate' => $sectionRate,
                'avg_grade' => $sectionAvgGrade,
                'present' => $sectionPresent,
                'late' => $sectionLate,
                'absent' => $sectionAbsent,
                'excused' => $sectionExcused,
            ]);
        }

        $overallAttendanceRate = $totalPossibleStudentDays > 0
            ? round(($totalAttendedStudentDays / $totalPossibleStudentDays) * 100, 1)
            : 0;

        $attendanceSummary = [
            'overall_rate' => $overallAttendanceRate,
            'total_students' => $overallTotalStudents,
            'present' => $overallPresent,
            'late' => $overallLate,
            'absent' => $overallAbsent,
            'excused' => $overallExcused,
            'not_in_classroom' => $overallNotInClassroom,
        ];

        $insights = $this->computeInsights($studentPoints, $sectionPoints);
        $termTrend = $this->computeTermTrend($filteredAssignments);

        return view('pov.teacher.analytics.attendance-analytics-index', compact(
            'studentPoints',
            'sectionPoints',
            'gradeLevels',
            'sections',
            'subjects',
            'selectedGradeLevel',
            'selectedSectionId',
            'selectedSubjectId',
            'selectedTerm',
            'attendanceSummary',
            'sectionSummaries',
            'insights',
            'termTrend'
        ));
    }

    /**
     * Resolve the date window and school day count for a group of enrollments and optional grading period.
     */
    private function resolveSchoolDaysWindow($enrollmentIds, ?GradingPeriod $gradingPeriod): array
    {
        if ($gradingPeriod && $gradingPeriod->start_date && $gradingPeriod->end_date) {
            $startDate = Carbon::parse($gradingPeriod->start_date)->startOfDay();
            $endDate = Carbon::parse($gradingPeriod->end_date)->startOfDay();

            // Do not project future days past today
            if ($endDate->gt(now())) {
                $endDate = now()->startOfDay();
            }
        } else {
            // Find earliest QR scan or teacher verification
            $earliestScan = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->min('scan_time');

            $earliestVerification = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                ->min('attendance_date');

            $startDates = collect([$earliestScan, $earliestVerification])
                ->filter()
                ->map(fn ($d) => Carbon::parse($d)->startOfDay());

            if ($startDates->isEmpty()) {
                return [null, null, 0];
            }

            $startDate = $startDates->sort()->first();
            $endDate = now()->startOfDay();
        }

        if (! $startDate || $startDate->gt($endDate)) {
            return [null, null, 0];
        }

        $schoolDaysCount = 0;
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if (! $cursor->isWeekend()) {
                $schoolDaysCount++;
            }
            $cursor->addDay();
        }

        return [$startDate, $endDate, $schoolDaysCount];
    }

    private function computeInsights($studentPoints, $sectionPoints): array
    {
        $minSampleSize = 5;

        // Insight 1: Pearson correlation between absences and average grade
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

    private function computeTermTrend($filteredAssignments): array
    {
        $taIds = $filteredAssignments->pluck('id');
        $sectionIds = $filteredAssignments->pluck('section_id')->unique();
        $enrollmentIds = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->pluck('id');

        $gradingPeriods = GradingPeriod::orderBy('sequence')->trimester()->get();

        $gradesByPeriod = TermGrade::whereIn('teaching_assignment_id', $taIds)
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

            // Calculate term attendance rate for this specific grading period
            [$startDate, $endDate, $schoolDays] = $this->resolveSchoolDaysWindow($enrollmentIds, $period);

            if ($schoolDays > 0 && $enrollmentIds->isNotEmpty() && $startDate && $endDate) {
                // QR scan dates
                $qrDates = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                    ->where('scan_type', 'IN')
                    ->whereBetween('scan_time', [
                        $startDate->copy()->startOfDay(),
                        $endDate->copy()->endOfDay(),
                    ])
                    ->selectRaw('enrollment_id, DATE(scan_time) as scan_date')
                    ->distinct()
                    ->get()
                    ->map(fn ($r) => $r->enrollment_id . '_' . $r->scan_date);

                // Verification attended dates
                $verifiedDates = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                    ->whereBetween('attendance_date', [
                        $startDate->toDateString(),
                        $endDate->toDateString(),
                    ])
                    ->whereIn('status', [
                        AttendanceVerification::STATUS_PRESENT,
                        AttendanceVerification::STATUS_LATE,
                        AttendanceVerification::STATUS_EXCUSED,
                    ])
                    ->selectRaw('enrollment_id, attendance_date')
                    ->distinct()
                    ->get()
                    ->map(fn ($r) => $r->enrollment_id . '_' . Carbon::parse($r->attendance_date)->toDateString());

                $totalAttended = $qrDates->toBase()->merge($verifiedDates->toBase())->unique()->count();
                $totalPossible = $enrollmentIds->count() * $schoolDays;
                $attendanceSeries[] = $totalPossible > 0 ? round(($totalAttended / $totalPossible) * 100, 1) : null;
            } else {
                $attendanceSeries[] = null;
            }
        }

        return [
            'labels' => $labels,
            'gradeSeries' => $gradeSeries,
            'attendanceSeries' => $attendanceSeries,
        ];
    }
}
