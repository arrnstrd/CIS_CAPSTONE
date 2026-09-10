<?php

namespace App\Http\Controllers\Teacher\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use App\Models\TermGrade;
use App\Models\TeachingAssignment;
use App\Services\ScheduleResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildAnalyticsData($request);

        return view('pov.teacher.analytics.attendance-analytics-index', $data);
    }

    public function correlation(Request $request)
    {
        $data = $this->buildAnalyticsData($request);

        return view('pov.teacher.analytics.attendance-correlation-index', $data);
    }

    private function buildAnalyticsData(Request $request): array
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return [
                'studentPoints' => collect(),
                'sectionPoints' => collect(),
                'gradeLevels' => collect(),
                'sections' => collect(),
                'subjects' => collect(),
                'schoolYears' => collect(),
                'students' => collect(),
                'selectedSchoolYearId' => null,
                'selectedGradeLevel' => null,
                'selectedSectionId' => null,
                'selectedSubjectId' => null,
                'selectedTerm' => null,
                'attendanceSummary' => null,
                'sectionSummaries' => collect(),
                'studentSummaries' => collect(),
                'mostAbsentStudents' => collect(),
                'earlyArrivalStudents' => collect(),
                'mostPresentStudents' => collect(),
                'insights' => [
                    'relationship' => 'No active teaching assignments found.',
                    'absence_trend' => 'No active teaching assignments found.',
                    'term_pattern' => 'No active teaching assignments found.',
                ],
                'termTrend' => [
                    'labels' => [],
                    'gradeSeries' => [],
                    'attendanceSeries' => [],
                ],
            ];
        }

        /*
         * -------------------------------------------------------------
         * FILTERS
         * -------------------------------------------------------------
         */

        $selectedSchoolYearId = $request->input('school_year_id') ?: null;
        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedSectionId = $request->input('section_id') ?: null;
        $selectedSubjectId = $request->input('subject_id') ?: null;
        $selectedTerm = $request->input('term') ?: null;
        $selectedStudentId = $request->input('student_id') ?: null;

        /*
         * -------------------------------------------------------------
         * TEACHER-SCOPED ASSIGNMENTS
         * -------------------------------------------------------------
         */

        $allTeacherAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject', 'schoolYear'])
            ->get();

        /*
         * School years available to this teacher.
         */
        $schoolYears = $allTeacherAssignments
            ->pluck('schoolYear')
            ->filter()
            ->unique('id')
            ->sortByDesc('school_year')
            ->values();

        /*
         * If no school year was explicitly selected, use the active
         * school year when the teacher has assignments for it.
         */
        if (! $selectedSchoolYearId) {
            $activeSchoolYearId = SchoolYear::active()->value('id');

            if (
                $activeSchoolYearId &&
                $allTeacherAssignments->contains(
                    fn ($assignment) => (int) $assignment->school_year_id === (int) $activeSchoolYearId
                )
            ) {
                $selectedSchoolYearId = $activeSchoolYearId;
            }
        }

        /*
         * Apply school year before building dependent filter options.
         */
        $schoolYearAssignments = $allTeacherAssignments;

        if ($selectedSchoolYearId) {
            $schoolYearAssignments = $schoolYearAssignments->filter(
                fn ($ta) => (int) $ta->school_year_id === (int) $selectedSchoolYearId
            );
        }

        /*
         * Filter options are based on the teacher's available assignments
         * for the selected school year.
         */
        $gradeLevels = $schoolYearAssignments
            ->pluck('section.grade_level')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $sections = $schoolYearAssignments
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $subjects = $schoolYearAssignments
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        /*
         * Apply the remaining assignment filters.
         */
        $filteredAssignments = $schoolYearAssignments;

        if ($selectedGradeLevel) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => $ta->section &&
                    $ta->section->grade_level == $selectedGradeLevel
            );
        }

        if ($selectedSectionId) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => (int) $ta->section_id === (int) $selectedSectionId
            );
        }

        if ($selectedSubjectId) {
            $filteredAssignments = $filteredAssignments->filter(
                fn ($ta) => (int) $ta->subject_id === (int) $selectedSubjectId
            );
        }

        /*
         * -------------------------------------------------------------
         * STUDENT FILTER OPTIONS
         * -------------------------------------------------------------
         */

        $availableSectionIds = $filteredAssignments
            ->pluck('section_id')
            ->filter()
            ->unique()
            ->values();

        $students = Enrollment::whereIn('section_id', $availableSectionIds)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->sortBy('full_name')
            ->values();

        /*
         * -------------------------------------------------------------
         * GRADING PERIOD
         * -------------------------------------------------------------
         */

        $gradingPeriod = null;

        if ($selectedTerm) {
            $gradingPeriod = GradingPeriod::trimester()
                ->where('sequence', $selectedTerm)
                ->first();
        } else {
            $gradingPeriod = GradingPeriod::where('is_active', true)
                ->trimester()
                ->orderBy('sequence')
                ->first();
        }

        /*
         * -------------------------------------------------------------
         * ANALYTICS COLLECTIONS
         * -------------------------------------------------------------
         */

        $studentPoints = collect();
        $sectionPoints = collect();
        $sectionSummaries = collect();
        $studentSummaries = collect();

        $overallPresent = 0;
        $overallLate = 0;
        $overallAbsent = 0;
        $overallExcused = 0;
        $overallNotInClassroom = 0;
        $overallTotalStudents = 0;
        $totalPossibleStudentDays = 0;
        $totalAttendedStudentDays = 0;

        /*
         * -------------------------------------------------------------
         * SECTION / STUDENT PROCESSING
         * -------------------------------------------------------------
         */

        foreach ($filteredAssignments->groupBy('section_id') as $sectionId => $sectionAssignments) {
            $section = $sectionAssignments->first()->section;

            if (! $section) {
                continue;
            }

            $taIds = $sectionAssignments->pluck('id');

            $enrollments = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->with('student')
                ->get();

            /*
             * Apply student filter after enrollment resolution so the
             * attendance and academic calculations use the same student set.
             */
            if ($selectedStudentId) {
                $enrollments = $enrollments->filter(
                    fn ($enrollment) =>
                        $enrollment->student &&
                        (int) $enrollment->student->id === (int) $selectedStudentId
                )->values();
            }

            $enrollmentIds = $enrollments->pluck('id');
            $sectionStudentCount = $enrollmentIds->count();

            if ($sectionStudentCount === 0) {
                continue;
            }

            $overallTotalStudents += $sectionStudentCount;

            /*
             * Date window:
             * explicit date range has priority;
             * otherwise use the selected/current grading period.
             */
            [$startDate, $endDate, $schoolDaysCount] = $this->resolveSchoolDaysWindow(
                $enrollmentIds,
                $gradingPeriod
            );

            /*
             * ---------------------------------------------------------
             * TERM GRADES
             * ---------------------------------------------------------
             */

            $gradesQuery = TermGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade');

            if ($selectedTerm) {
                $gradesQuery->whereHas(
                    'gradingPeriod',
                    fn ($q) => $q->where('sequence', $selectedTerm)
                );
            }

            $gradesByEnrollment = $gradesQuery
                ->get()
                ->groupBy('enrollment_id');

            /*
             * ---------------------------------------------------------
             * QR IN LOGS
             * ---------------------------------------------------------
             */

            $qrLogsQuery = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN');

            if ($startDate && $endDate) {
                $qrLogsQuery->whereBetween('scan_time', [
                    $startDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay(),
                ]);
            }

            $qrLogs = $qrLogsQuery
                ->select('id', 'enrollment_id', 'session_type', 'scan_time')
                ->orderBy('scan_time')
                ->get()
                ->groupBy(
                    fn ($log) =>
                        $log->enrollment_id . '_' .
                        Carbon::parse($log->scan_time)->toDateString()
                );

            /*
             * ---------------------------------------------------------
             * TEACHER VERIFICATIONS
             * ---------------------------------------------------------
             */

            $verificationsQuery = AttendanceVerification::whereIn(
                'enrollment_id',
                $enrollmentIds
            );

            if ($startDate && $endDate) {
                $verificationsQuery->whereBetween('attendance_date', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]);
            }

            $verifications = $verificationsQuery
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(
                    fn ($v) =>
                        $v->enrollment_id . '_' .
                        Carbon::parse($v->attendance_date)->toDateString()
                )
                ->map(fn ($group) => $group->first());

            /*
             * ---------------------------------------------------------
             * SECTION COUNTERS
             * ---------------------------------------------------------
             */

            $sectionPresent = 0;
            $sectionLate = 0;
            $sectionAbsent = 0;
            $sectionExcused = 0;
            $sectionNotInClassroom = 0;
            $sectionAttendedDays = 0;
            $sectionGradesSum = 0;
            $sectionGradesCount = 0;

            foreach ($enrollments as $enrollment) {
                $grades = $gradesByEnrollment->get(
                    $enrollment->id,
                    collect()
                );

                $avgGrade = $grades->count()
                    ? round($grades->avg('transmuted_grade'), 1)
                    : null;

                $studentAttendedDays = 0;
                $studentAbsences = 0;
                $studentPresentDays = 0;
                $studentLateDays = 0;
                $studentExcusedDays = 0;
                $studentNotInClassroomDays = 0;
                $studentEarlyArrivals = 0;
                $studentScanMinutes = [];

                if ($schoolDaysCount > 0 && $startDate && $endDate) {
                    $cursor = $startDate->copy();

                    while ($cursor->lte($endDate)) {
                        if (! $cursor->isWeekend()) {
                            $dateKey = $enrollment->id . '_' . $cursor->toDateString();

                            $dayLogs = $qrLogs->get($dateKey, collect());
                            $hasQr = $dayLogs->isNotEmpty();
                            $verification = $verifications->get($dateKey);

                            /*
                             * Keep the existing attendance hierarchy:
                             *
                             * 1. Teacher verification is authoritative.
                             * 2. Otherwise valid QR IN means present.
                             * 3. Otherwise weekday with no data = absent.
                             */
                            if ($verification) {
                                switch ($verification->status) {
                                    case AttendanceVerification::STATUS_PRESENT:
                                        $sectionPresent++;
                                        $studentPresentDays++;
                                        $studentAttendedDays++;
                                        break;

                                    case AttendanceVerification::STATUS_LATE:
                                        $sectionLate++;
                                        $studentLateDays++;
                                        $studentAttendedDays++;
                                        break;

                                    case AttendanceVerification::STATUS_EXCUSED:
                                        $sectionExcused++;
                                        $studentExcusedDays++;
                                        $studentAttendedDays++;
                                        break;

                                    case AttendanceVerification::STATUS_ABSENT:
                                        $sectionAbsent++;
                                        $studentAbsences++;
                                        break;

                                    case AttendanceVerification::STATUS_NOT_IN_CLASSROOM:
                                        $sectionNotInClassroom++;
                                        $studentNotInClassroomDays++;
                                        $studentAbsences++;
                                        break;

                                    default:
                                        if ($hasQr) {
                                            $sectionPresent++;
                                            $studentPresentDays++;
                                            $studentAttendedDays++;
                                        } else {
                                            $sectionAbsent++;
                                            $studentAbsences++;
                                        }
                                        break;
                                }
                            } elseif ($hasQr) {
                                $sectionPresent++;
                                $studentPresentDays++;
                                $studentAttendedDays++;
                            } else {
                                $sectionAbsent++;
                                $studentAbsences++;
                            }

                            /*
                             * Early-arrival analytics uses the actual QR
                             * scan time and the same ScheduleConfig /
                             * late_threshold used by the QR system.
                             */
                            foreach ($dayLogs as $log) {
                                $scanTime = Carbon::parse($log->scan_time);

                                $schedule = $this->resolveScheduleForScan(
                                    $section,
                                    $log->session_type,
                                    $scanTime
                                );

                                if (! $schedule) {
                                    continue;
                                }

                                $lateThreshold = Carbon::parse(
                                    $scanTime->toDateString() . ' ' . $schedule->late_threshold
                                );

                                if ($scanTime->lt($lateThreshold)) {
                                    $studentEarlyArrivals++;
                                    $studentScanMinutes[] =
                                        ($scanTime->hour * 60) + $scanTime->minute;

                                    /*
                                     * Only count the earliest valid early
                                     * scan for a student's day.
                                     */
                                    break;
                                }
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
                        'student_id' => $enrollment->student?->id,
                        'section_id' => $section->id,
                        'section' => $section->name,
                        'grade_level' => $section->grade_level,
                    ]);

                    $sectionGradesSum += $avgGrade;
                    $sectionGradesCount++;
                }

                $averageCheckInTime = null;

                if (! empty($studentScanMinutes)) {
                    $averageMinutes = (int) round(
                        array_sum($studentScanMinutes) / count($studentScanMinutes)
                    );

                    $averageCheckInTime = sprintf(
                        '%02d:%02d',
                        intdiv($averageMinutes, 60),
                        $averageMinutes % 60
                    );
                }

                $studentSummaries->push([
                    'student_id' => $enrollment->student?->id,
                    'enrollment_id' => $enrollment->id,
                    'name' => $enrollment->student?->full_name ?? 'Student',
                    'student_number' => $enrollment->student?->student_number,
                    'section_id' => $section->id,
                    'section' => $section->name,
                    'grade_level' => $section->grade_level,
                    'present' => $studentPresentDays,
                    'late' => $studentLateDays,
                    'absent' => $studentAbsences,
                    'excused' => $studentExcusedDays,
                    'not_in_classroom' => $studentNotInClassroomDays,
                    'attended' => $studentAttendedDays,
                    'attendance_rate' => $schoolDaysCount > 0
                        ? round(($studentAttendedDays / $schoolDaysCount) * 100, 1)
                        : 0,
                    'early_arrivals' => $studentEarlyArrivals,
                    'avg_grade' => $avgGrade,
                    'average_check_in' => $averageCheckInTime,
                ]);
            }

            $sectionPossibleDays =
                $sectionStudentCount * $schoolDaysCount;

            $sectionRate = $sectionPossibleDays > 0
                ? round(
                    ($sectionAttendedDays / $sectionPossibleDays) * 100,
                    1
                )
                : 0;

            $sectionAvgGrade = $sectionGradesCount
                ? round(
                    $sectionGradesSum / $sectionGradesCount,
                    1
                )
                : null;

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

        /*
         * -------------------------------------------------------------
         * OVERALL SUMMARY
         * -------------------------------------------------------------
         */

        $overallAttendanceRate = $totalPossibleStudentDays > 0
            ? round(
                ($totalAttendedStudentDays / $totalPossibleStudentDays) * 100,
                1
            )
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

        /*
         * -------------------------------------------------------------
         * STUDENT RANKINGS
         * -------------------------------------------------------------
         */

        $mostAbsentStudents = $studentSummaries
            ->sortByDesc('absent')
            ->values();

        $earlyArrivalStudents = $studentSummaries
            ->sortByDesc('early_arrivals')
            ->values();

        $mostPresentStudents = $studentSummaries
            ->sortByDesc('attended')
            ->values();

        /*
         * -------------------------------------------------------------
         * INSIGHTS + TERM TREND
         * -------------------------------------------------------------
         */

        $insights = $this->computeInsights(
            $studentPoints,
            $sectionPoints
        );

        $termTrend = $this->computeTermTrend(
            $filteredAssignments,
            $selectedTerm,
            $selectedStudentId
        );

        return compact(
            'studentPoints',
            'sectionPoints',
            'gradeLevels',
            'sections',
            'subjects',
            'schoolYears',
            'students',
            'selectedSchoolYearId',
            'selectedGradeLevel',
            'selectedSectionId',
            'selectedSubjectId',
            'selectedTerm',
            'selectedStudentId',
            'attendanceSummary',
            'sectionSummaries',
            'studentSummaries',
            'mostAbsentStudents',
            'earlyArrivalStudents',
            'mostPresentStudents',
            'insights',
            'termTrend'
        );
    }

    /**
     * Resolve the date window and school day count.
     *
     * The selected grading period is used for the attendance window.
     */
    private function resolveSchoolDaysWindow(
        $enrollmentIds,
        ?GradingPeriod $gradingPeriod
    ): array {
        if (
            $gradingPeriod &&
            $gradingPeriod->start_date &&
            $gradingPeriod->end_date
        ) {
            $startDate = Carbon::parse(
                $gradingPeriod->start_date
            )->startOfDay();

            $endDate = Carbon::parse(
                $gradingPeriod->end_date
            )->startOfDay();

            if ($endDate->gt(now())) {
                $endDate = now()->startOfDay();
            }
        } else {
            $earliestScan = AttendanceLog::whereIn(
                'enrollment_id',
                $enrollmentIds
            )
                ->where('scan_type', 'IN')
                ->min('scan_time');

            $earliestVerification = AttendanceVerification::whereIn(
                'enrollment_id',
                $enrollmentIds
            )->min('attendance_date');

            $startDates = collect([
                $earliestScan,
                $earliestVerification,
            ])
                ->filter()
                ->map(
                    fn ($d) =>
                    Carbon::parse($d)->startOfDay()
                );

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

        return [
            $startDate,
            $endDate,
            $schoolDaysCount,
        ];
    }

    /**
     * Resolve the existing schedule used by the QR attendance system
     * for a particular section/session and scan time.
     */
    private function resolveScheduleForScan(
        $section,
        ?string $sessionType,
        Carbon $scanTime
    ) {
        if (! $section || ! $sessionType) {
            return null;
        }

        return (new ScheduleResolver())->resolve(
            $section->level,
            $sessionType,
            $scanTime->format('H:i')
        );
    }

    private function computeInsights(
        $studentPoints,
        $sectionPoints
    ): array {
        $minSampleSize = 5;

        /*
         * Insight 1: Pearson correlation between absences
         * and average grade.
         */
        $relationshipText =
            'Not enough data yet to observe a pattern.';

        if ($studentPoints->count() >= $minSampleSize) {
            $n = $studentPoints->count();

            $sumX = $studentPoints->sum('x');
            $sumY = $studentPoints->sum('y');

            $sumXY = $studentPoints->sum(
                fn ($p) => $p['x'] * $p['y']
            );

            $sumX2 = $studentPoints->sum(
                fn ($p) => $p['x'] * $p['x']
            );

            $sumY2 = $studentPoints->sum(
                fn ($p) => $p['y'] * $p['y']
            );

            $numerator =
                ($n * $sumXY) -
                ($sumX * $sumY);

            $denominator = sqrt(
                (($n * $sumX2) - ($sumX ** 2)) *
                (($n * $sumY2) - ($sumY ** 2))
            );

            $correlation =
                $denominator != 0
                    ? $numerator / $denominator
                    : 0;

            if ($correlation < -0.2) {
                $relationshipText =
                    'Students with more absences tend to have lower average grades in this data.';
            } elseif ($correlation > 0.2) {
                $relationshipText =
                    'No clear negative pattern between absences and grades was observed in this data.';
            } else {
                $relationshipText =
                    'No strong pattern between absences and grades was observed in this data.';
            }
        }

        /*
         * Insight 2: Absence-grade comparison.
         */
        $absenceTrendText =
            'Not enough data yet to compare absence groups.';

        if ($studentPoints->count() >= $minSampleSize) {
            $highAbsence = $studentPoints->where('x', '>=', 15);
            $lowAbsence = $studentPoints->where('x', '<', 15);

            if (
                $highAbsence->count() >= 2 &&
                $lowAbsence->count() >= 2
            ) {
                $highAvg = round($highAbsence->avg('y'), 1);
                $lowAvg = round($lowAbsence->avg('y'), 1);
                $diff = round($lowAvg - $highAvg, 1);

                $absenceTrendText = $diff > 0
                    ? "Students with 15+ absences average {$diff} points lower ({$highAvg}) than those with fewer absences ({$lowAvg})."
                    : "Students with 15+ absences do not show a lower average grade in this data ({$highAvg} vs {$lowAvg}).";
            } else {
                $absenceTrendText =
                    'Not enough students with 15+ absences yet to compare.';
            }
        }

        /*
         * Insight 3: Section-level attendance/grade pattern.
         */
        $termPatternText =
            'Not enough section data yet to observe a pattern.';

        if ($sectionPoints->count() >= 2) {
            $sorted = $sectionPoints
                ->sortBy('x')
                ->values();

            $lowest = $sorted->first();
            $highest = $sorted->last();

            if ($highest['x'] > $lowest['x']) {
                $termPatternText =
                    $highest['y'] >= $lowest['y']
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

    private function computeTermTrend(
        $filteredAssignments,
        $selectedTerm = null,
        $selectedStudentId = null
    ): array {
        $taIds = $filteredAssignments->pluck('id');

        $sectionIds = $filteredAssignments
            ->pluck('section_id')
            ->unique();

        $enrollmentQuery = Enrollment::whereIn(
            'section_id',
            $sectionIds
        )->where('status', 'active');

        if ($selectedStudentId) {
            $enrollmentQuery->whereHas(
                'student',
                fn ($q) => $q->where(
                    'students.id',
                    $selectedStudentId
                )
            );
        }

        $enrollmentIds = $enrollmentQuery->pluck('id');

        $gradingPeriods = GradingPeriod::orderBy('sequence')
            ->trimester()
            ->get();

        /*
         * If a specific term is selected, keep the trend focused
         * on that term rather than showing unrelated periods.
         */
        if ($selectedTerm) {
            $gradingPeriods = $gradingPeriods
                ->where('sequence', $selectedTerm)
                ->values();
        }

        $gradesByPeriod = TermGrade::whereIn(
            'teaching_assignment_id',
            $taIds
        )
            ->whereNotNull('transmuted_grade')
            ->get()
            ->groupBy('grading_period_id');

        $labels = [];
        $gradeSeries = [];
        $attendanceSeries = [];

        foreach ($gradingPeriods as $period) {
            $labels[] = 'Term ' . $period->sequence;

            $periodGrades = $gradesByPeriod->get(
                $period->id,
                collect()
            );

            $gradeSeries[] = $periodGrades->count()
                ? round($periodGrades->avg('transmuted_grade'), 1)
                : null;

            [$startDate, $endDate, $schoolDays] =
                $this->resolveSchoolDaysWindow(
                    $enrollmentIds,
                    $period,
                );

            if (
                $schoolDays > 0 &&
                $enrollmentIds->isNotEmpty() &&
                $startDate &&
                $endDate
            ) {
                $qrDates = AttendanceLog::whereIn(
                    'enrollment_id',
                    $enrollmentIds
                )
                    ->where('scan_type', 'IN')
                    ->whereBetween('scan_time', [
                        $startDate->copy()->startOfDay(),
                        $endDate->copy()->endOfDay(),
                    ])
                    ->selectRaw(
                        'enrollment_id, DATE(scan_time) as scan_date'
                    )
                    ->distinct()
                    ->get()
                    ->map(
                        fn ($r) =>
                        $r->enrollment_id . '_' . $r->scan_date
                    );

                $verifiedDates = AttendanceVerification::whereIn(
                    'enrollment_id',
                    $enrollmentIds
                )
                    ->whereBetween('attendance_date', [
                        $startDate->toDateString(),
                        $endDate->toDateString(),
                    ])
                    ->whereIn('status', [
                        AttendanceVerification::STATUS_PRESENT,
                        AttendanceVerification::STATUS_LATE,
                        AttendanceVerification::STATUS_EXCUSED,
                    ])
                    ->selectRaw(
                        'enrollment_id, attendance_date'
                    )
                    ->distinct()
                    ->get()
                    ->map(
                        fn ($r) =>
                        $r->enrollment_id . '_' .
                        Carbon::parse(
                            $r->attendance_date
                        )->toDateString()
                    );

                $totalAttended =
                    $qrDates
                        ->toBase()
                        ->merge($verifiedDates->toBase())
                        ->unique()
                        ->count();

                $totalPossible =
                    $enrollmentIds->count() *
                    $schoolDays;

                $attendanceSeries[] =
                    $totalPossible > 0
                        ? round(
                            ($totalAttended / $totalPossible) * 100,
                            1
                        )
                        : null;
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


