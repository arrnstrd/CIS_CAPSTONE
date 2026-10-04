<?php

namespace App\Http\Controllers\SchoolAdmin\InOutMonitoring;

use App\Http\Controllers\Controller;
use App\Libraries\PDF\DomPdfWrapper;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceLogController extends Controller
{
    public function __construct(
        private readonly DomPdfWrapper $pdfWrapper = new DomPdfWrapper()
    ) {}

    // Attendance logs module

    public function index(Request $request)
    {
        $t0 = microtime(true); // TEMP
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $attendanceLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans'
        ])
        ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
            $q->whereHas('enrollment', function ($enrollmentQuery) use ($activeSchoolYear) {
                $enrollmentQuery->where('school_year_id', $activeSchoolYear->id);
            });
        })
        ->withoutExcessScanFlags();

        $attendanceLogsQuery = $this->applyDateFilter(
            $attendanceLogsQuery,
            $dateFilter,
            $customStartDate,
            $customEndDate
        );

        $attendanceLogsQuery = $attendanceLogsQuery
            ->search($query)
            ->filterByScanType($scan_type)
            ->filterBySessionType($session_type)
            ->filterByFlagType($flag_type);

        $statusCounts = $attendanceLogsQuery->getStatistics();

        $attendance_logs = $attendanceLogsQuery
            ->orderBy('scan_time', 'desc')
            ->paginate(15)
            ->withQueryString();

        \Illuminate\Support\Facades\Log::debug('[PROFILE-CTRL:time-in-time-out-history] ms=' . round((microtime(true) - $t0) * 1000, 1)); // TEMP

        return view('pov.school-admin.in-out-monitoring.in-out-monitoring',
            compact(
                'attendance_logs',
                'scan_type',
                'session_type',
                'flag_type',
                'statusCounts',
                'dateFilter',
                'customStartDate',
                'customEndDate',
                'query'
            )
        );
    }

    /**
     * Dedicated Analytics Page
     */
    public function analytics(Request $request)
    {
        $t0 = microtime(true);
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $analyticsLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans'
        ])
        ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
            $q->whereHas('enrollment', function ($enrollmentQuery) use ($activeSchoolYear) {
                $enrollmentQuery->where('school_year_id', $activeSchoolYear->id);
            });
        })
        ->withoutExcessScanFlags();

        $analyticsLogsQuery = $this->applyDateFilter(
            $analyticsLogsQuery,
            $dateFilter,
            $customStartDate,
            $customEndDate
        );

        $analyticsLogsQuery = $analyticsLogsQuery
            ->search($query)
            ->filterByScanType($scan_type)
            ->filterBySessionType($session_type)
            ->filterByFlagType($flag_type);

        $statusCounts = $analyticsLogsQuery->getStatistics();

        $analyticsLogs = $analyticsLogsQuery->orderBy('scan_time')->get();
        $analytics = $this->buildAnalytics($analyticsLogs, $dateFilter);

        \Illuminate\Support\Facades\Log::debug('[PROFILE-CTRL:time-in-time-out-analytics] ms=' . round((microtime(true) - $t0) * 1000, 1));

        return view('pov.school-admin.attendance-analytics.attendance-analytics',
            compact(
                'scan_type',
                'session_type',
                'flag_type',
                'statusCounts',
                'dateFilter',
                'customStartDate',
                'customEndDate',
                'query',
                'analytics'
            )
        );
    }

    /**
     * Build comprehensive, decision-support analytics (Descriptive, Diagnostic, Predictive, Prescriptive).
     *
     * @param  Collection  $logs  Eagerly loaded logs with enrollment.student, enrollment.section, flagged_scans
     * @param  string      $dateFilter  The active date-filter key (today, week, month, etc.)
     * @return array
     */
    protected function buildAnalytics(Collection $logs, string $dateFilter): array
    {
        // ── 1. Separate IN and OUT logs ────────────────────────────────────
        $inLogs  = $logs->where('scan_type', 'IN');
        $outLogs = $logs->where('scan_type', 'OUT');

        // ── 2. Unique student counts ───────────────────────────────────────
        $uniqueStudentsIn  = $inLogs->pluck('enrollment_id')->unique()->count();
        $uniqueStudentsOut = $outLogs->pluck('enrollment_id')->unique()->count();
        $uniqueStudentsTotal = $logs->pluck('enrollment_id')->unique()->count();

        // ── 3. Late arrivals ────────────────────────────────────────────────
        $lateInLogs = $inLogs->filter(function (AttendanceLog $log) {
            return $log->flagged_scans->contains('flag_type', 'late_arrival');
        });
        $lateEnrollmentIds = $lateInLogs->pluck('enrollment_id')->unique();
        $lateCount = $lateEnrollmentIds->count();
        $totalLateScans = $lateInLogs->count();

        // ── 4. Rates & Integrity ────────────────────────────────────────────
        $onTimeCount = max($uniqueStudentsIn - $lateCount, 0);
        $onTimeRate  = $uniqueStudentsIn > 0 ? round(($onTimeCount / $uniqueStudentsIn) * 100, 1) : null;
        $lateRate    = $uniqueStudentsIn > 0 ? round(($lateCount / $uniqueStudentsIn) * 100, 1) : null;

        $activeSchoolYear = SchoolYear::active()->first();
        $expectedStudents = Enrollment::where('status', 'active')
            ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->count();
        $attendanceRate = $expectedStudents > 0
            ? round(($uniqueStudentsIn / $expectedStudents) * 100, 1)
            : null;

        // ── 5. Average arrival / departure times ───────────────────────────
        $avgArrival   = null;
        $avgDeparture = null;
        if ($inLogs->isNotEmpty()) {
            $avgArrivalMinutes = $inLogs->avg(fn (AttendanceLog $l) => $l->scan_time->hour * 60 + $l->scan_time->minute);
            $avgArrival = Carbon::today()->addMinutes((int) round($avgArrivalMinutes))->format('g:i A');
        }
        if ($outLogs->isNotEmpty()) {
            $avgDepartureMinutes = $outLogs->avg(fn (AttendanceLog $l) => $l->scan_time->hour * 60 + $l->scan_time->minute);
            $avgDeparture = Carbon::today()->addMinutes((int) round($avgDepartureMinutes))->format('g:i A');
        }

        // ── 6. Missing OUT: students with IN but no OUT on the same date ───
        $inByDateEnrollment  = $inLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
        $outByDateEnrollment = $outLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);

        $missingOutRecords = collect();
        foreach ($inByDateEnrollment as $key => $inGroup) {
            if (!$outByDateEnrollment->has($key)) {
                $log = $inGroup->first();
                $missingOutRecords->push([
                    'enrollment_id' => $log->enrollment_id,
                    'student_name' => trim(($log->enrollment?->student?->first_name ?? '') . ' ' . ($log->enrollment?->student?->last_name ?? '')) ?: 'Unknown',
                    'student_number' => $log->enrollment?->student?->student_number ?? '-',
                    'grade_level' => $log->enrollment?->section?->grade_level ?? '-',
                    'section_name' => $log->enrollment?->section?->name ?? '-',
                    'in_time' => $log->scan_time?->format('g:i A') ?? '-',
                    'date' => $log->scan_time?->format('M d, Y') ?? '-',
                    'session_type' => $log->session_type ? Str::headline(str_replace('_', ' ', $log->session_type)) : '-',
                ]);
            }
        }
        $missingOutCount = $missingOutRecords->count();
        $missingOutSample = $missingOutRecords->sortByDesc('date')->take(20)->values();

        $checkoutIntegrityRate = $inByDateEnrollment->count() > 0
            ? round((($inByDateEnrollment->count() - $missingOutCount) / $inByDateEnrollment->count()) * 100, 1)
            : 100;

        // ── 7. Session Breakdown & Punctuality Donut Composition ───────────
        $sessionMorning = $logs->where('session_type', 'morning')->count();
        $sessionAfternoon = $logs->where('session_type', 'afternoon')->count();
        $sessionWholeDay = $logs->where('session_type', 'whole_day')->count();
        $totalSessions = max($sessionMorning + $sessionAfternoon + $sessionWholeDay, 1);

        $sessionDistribution = [
            'morning' => ['count' => $sessionMorning, 'pct' => round(($sessionMorning / $totalSessions) * 100, 1)],
            'afternoon' => ['count' => $sessionAfternoon, 'pct' => round(($sessionAfternoon / $totalSessions) * 100, 1)],
            'whole_day' => ['count' => $sessionWholeDay, 'pct' => round(($sessionWholeDay / $totalSessions) * 100, 1)],
        ];

        // Punctuality Donut Composition (On-Time vs Late)
        $donutTotalStudents = max($uniqueStudentsIn, 1);
        $donutOnTimePct = round(($onTimeCount / $donutTotalStudents) * 100, 1);
        $donutLatePct = round(($lateCount / $donutTotalStudents) * 100, 1);

        // ── 8. Daily IN/OUT trend ──────────────────────────────────────────
        $dailyTrend = collect();
        if ($logs->isNotEmpty()) {
            $allDates = $logs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString());
            foreach ($allDates as $dateStr => $dayLogs) {
                $dailyTrend->push([
                    'date'  => $dateStr,
                    'label' => Carbon::parse($dateStr)->format('M j'),
                    'day_name' => Carbon::parse($dateStr)->format('l'),
                    'in'    => $dayLogs->where('scan_type', 'IN')->count(),
                    'out'   => $dayLogs->where('scan_type', 'OUT')->count(),
                    'total' => $dayLogs->count(),
                    'unique_students' => $dayLogs->pluck('enrollment_id')->unique()->count(),
                ]);
            }
            $dailyTrend = $dailyTrend->sortBy('date')->values();
        }

        // ── 9. Hourly scan distribution & Peak Rush Hour ───────────────────
        $hourlyDistribution = collect(range(5, 18))->mapWithKeys(fn ($h) => [
            $h => ['hour' => $h, 'label' => Carbon::today()->setHour($h)->format('g A'), 'in' => 0, 'out' => 0, 'total' => 0],
        ]);
        $logs->each(function (AttendanceLog $log) use (&$hourlyDistribution) {
            $h = $log->scan_time->hour;
            if ($hourlyDistribution->has($h)) {
                $entry = $hourlyDistribution[$h];
                $entry['total']++;
                if ($log->scan_type === 'IN') {
                    $entry['in']++;
                } else {
                    $entry['out']++;
                }
                $hourlyDistribution[$h] = $entry;
            }
        });
        $hourlyDistribution = $hourlyDistribution->values();
        $maxHourlyTotal = max($hourlyDistribution->max('total'), 1);

        $peakEntryHour = $hourlyDistribution->sortByDesc('in')->first();
        $peakExitHour  = $hourlyDistribution->sortByDesc('out')->first();

        // Calculate peak 15-minute rush window and entry velocity
        $rushWindow = null;
        $rushVelocity = null;
        if ($inLogs->isNotEmpty()) {
            $fifteenMinBuckets = $inLogs->groupBy(function (AttendanceLog $l) {
                $m = floor($l->scan_time->minute / 15) * 15;
                return sprintf('%02d:%02d', $l->scan_time->hour, $m);
            });
            $peak15 = $fifteenMinBuckets->sortByDesc(fn ($group) => $group->count())->first();
            if ($peak15 && $peak15->isNotEmpty()) {
                $startMin = floor($peak15->first()->scan_time->minute / 15) * 15;
                $startH = $peak15->first()->scan_time->hour;
                $startC = Carbon::today()->setHour($startH)->setMinute($startMin);
                $endC = $startC->copy()->addMinutes(15);
                $rushWindow = $startC->format('g:i A') . ' – ' . $endC->format('g:i A');
                $rushVelocity = round($peak15->count() / 15, 1); // scans per minute
            }
        }

        // ── 10. Late arrival daily trend ────────────────────────────────────
        $lateDailyTrend = collect();
        if ($inLogs->isNotEmpty()) {
            $inByDate = $inLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString());
            foreach ($inByDate as $dateStr => $dayInLogs) {
                $dayLateCount = $dayInLogs->filter(fn (AttendanceLog $l) => $l->flagged_scans->contains('flag_type', 'late_arrival'))
                    ->pluck('enrollment_id')->unique()->count();
                $dayStudentCount = $dayInLogs->pluck('enrollment_id')->unique()->count();
                $lateDailyTrend->push([
                    'date' => $dateStr,
                    'label' => Carbon::parse($dateStr)->format('M j'),
                    'late' => $dayLateCount,
                    'on_time' => $dayStudentCount - $dayLateCount,
                    'total_students' => $dayStudentCount,
                    'late_rate' => $dayStudentCount > 0 ? round(($dayLateCount / $dayStudentCount) * 100, 1) : 0,
                ]);
            }
            $lateDailyTrend = $lateDailyTrend->sortBy('date')->values();
        }

        // ── 11. Grade-level comparison & Diagnostics ────────────────────────
        $gradeLevelData = collect();
        $logsByGrade = $logs->groupBy(fn (AttendanceLog $l) => $l->enrollment?->section?->grade_level ?? 'unknown');
        foreach ($logsByGrade as $grade => $gradeLogs) {
            if ($grade === 'unknown') continue;
            $gradeInLogs = $gradeLogs->where('scan_type', 'IN');
            $gradeStudentCount = $gradeInLogs->pluck('enrollment_id')->unique()->count();
            $gradeLateCount = $gradeInLogs->filter(fn (AttendanceLog $l) => $l->flagged_scans->contains('flag_type', 'late_arrival'))
                ->pluck('enrollment_id')->unique()->count();

            $gradeInKeys = $gradeInLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
            $gradeOutKeys = $gradeLogs->where('scan_type', 'OUT')->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
            $gradeMissingOut = $gradeInKeys->keys()->diff($gradeOutKeys->keys())->count();

            $gradeLevelData->push([
                'grade' => (int) $grade,
                'label' => 'Grade ' . $grade,
                'students' => $gradeStudentCount,
                'total_scans' => $gradeLogs->count(),
                'late_count' => $gradeLateCount,
                'late_rate' => $gradeStudentCount > 0 ? round(($gradeLateCount / $gradeStudentCount) * 100, 1) : 0,
                'missing_out' => $gradeMissingOut,
            ]);
        }
        $gradeLevelData = $gradeLevelData->sortBy('grade')->values();

        // ── 12. Section-level breakdown & Disparity Diagnostics ─────────────
        $sectionData = collect();
        $logsBySection = $logs->groupBy(fn (AttendanceLog $l) => $l->enrollment?->section_id ?? 0);
        foreach ($logsBySection as $sectionId => $sectionLogs) {
            if ($sectionId === 0) continue;
            $section = $sectionLogs->first()->enrollment?->section;
            if (!$section) continue;

            $sectionInLogs = $sectionLogs->where('scan_type', 'IN');
            $sectionStudentCount = $sectionInLogs->pluck('enrollment_id')->unique()->count();
            $sectionLateCount = $sectionInLogs->filter(fn (AttendanceLog $l) => $l->flagged_scans->contains('flag_type', 'late_arrival'))
                ->pluck('enrollment_id')->unique()->count();

            $sectionInKeys = $sectionInLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
            $sectionOutKeys = $sectionLogs->where('scan_type', 'OUT')->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
            $sectionMissingOut = $sectionInKeys->keys()->diff($sectionOutKeys->keys())->count();

            $sectionData->push([
                'section_id' => $sectionId,
                'name' => 'G' . ($section->grade_level ?? '?') . ' — ' . ($section->name ?? '?'),
                'grade_level' => $section->grade_level ?? '-',
                'students' => $sectionStudentCount,
                'total_scans' => $sectionLogs->count(),
                'late_count' => $sectionLateCount,
                'late_rate' => $sectionStudentCount > 0 ? round(($sectionLateCount / $sectionStudentCount) * 100, 1) : 0,
                'missing_out' => $sectionMissingOut,
            ]);
        }
        $sectionData = $sectionData->sortByDesc('total_scans')->take(15)->values();

        // ── 13. Students with Multiple Late Arrivals (Follow-up Registry) ──
        $frequentLateStudents = collect();
        $lateLogsByEnrollment = $lateInLogs->groupBy('enrollment_id');
        foreach ($lateLogsByEnrollment as $eid => $sLateLogs) {
            $student = $sLateLogs->first()->enrollment?->student;
            $section = $sLateLogs->first()->enrollment?->section;
            $lateOccurrences = $sLateLogs->count();
            $totalStudentIn = $inLogs->where('enrollment_id', $eid)->count();
            $personalLateRate = $totalStudentIn > 0 ? round(($lateOccurrences / $totalStudentIn) * 100, 1) : 100;

            $scanDetails = $sLateLogs->map(function ($l) {
                return [
                    'date' => $l->scan_time?->format('M d, Y'),
                    'time' => $l->scan_time?->format('h:i A'),
                    'session_type' => $l->session_type ? Str::headline(str_replace('_', ' ', $l->session_type)) : '-',
                ];
            })->values()->all();

            if ($lateOccurrences >= 2) {
                $frequentLateStudents->push([
                    'enrollment_id' => $eid,
                    'student_name' => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: 'Unknown',
                    'student_number' => $student?->student_number ?? '-',
                    'grade_level' => $section?->grade_level ?? '-',
                    'section_name' => $section?->name ?? '-',
                    'late_count' => $lateOccurrences,
                    'personal_late_rate' => $personalLateRate,
                    'badge_label' => $lateOccurrences >= 4 ? '4+ Late Entries' : '2–3 Late Entries',
                    'badge_tone' => $lateOccurrences >= 4 ? 'danger' : 'warning',
                    'scan_details' => $scanDetails,
                ]);
            }
        }
        $frequentLateStudents = $frequentLateStudents->sortByDesc('late_count')->values();

        // ── 14. Prescriptive Action Items (Dynamic Recommendations) ────────
        $prescriptiveActions = collect();

        // Action 1: High Late Rate Section Follow-up
        $worstLateSection = $sectionData->where('late_count', '>', 0)->sortByDesc('late_rate')->first();
        if ($worstLateSection && $worstLateSection['late_rate'] >= 15) {
            $sectionLateLogs = $inLogs->filter(function ($l) use ($worstLateSection) {
                return ($l->enrollment?->section_id === $worstLateSection['section_id'])
                    && $l->flagged_scans->contains('flag_type', 'late_arrival');
            });
            $sectionLateList = $sectionLateLogs->map(function ($l) {
                $st = $l->enrollment?->student;
                return [
                    'name' => trim(($st?->first_name ?? '') . ' ' . ($st?->last_name ?? '')) ?: 'Student',
                    'student_number' => $st?->student_number ?? '-',
                    'date' => $l->scan_time?->format('M d, Y'),
                    'time' => $l->scan_time?->format('h:i A'),
                    'session' => $l->session_type ? Str::headline(str_replace('_', ' ', $l->session_type)) : '-',
                ];
            })->values()->all();

            $prescriptiveActions->push([
                'action_key' => 'section_advisory',
                'modal_target' => '#modalAction_section_advisory',
                'category' => 'Section Advisory',
                'priority' => 'High Priority',
                'priority_tone' => 'danger',
                'icon' => 'fas fa-chalkboard-user',
                'title' => "Advisor Follow-Up: {$worstLateSection['name']}",
                'description' => "This section recorded a {$worstLateSection['late_rate']}% late arrival rate ({$worstLateSection['late_count']} late entries). Recommend having the section adviser check for morning commute factors with students.",
                'action_label' => 'Review Section Details',
                'section_name' => $worstLateSection['name'],
                'section_late_count' => $worstLateSection['late_count'],
                'section_late_rate' => $worstLateSection['late_rate'],
                'late_students' => $sectionLateList,
            ]);
        }

        // Action 2: Gate Flow & Scanner Lane Optimization
        if ($rushVelocity && $rushVelocity > 15) {
            $prescriptiveActions->push([
                'action_key' => 'gate_timing',
                'modal_target' => '#modalAction_gate_timing',
                'category' => 'Gate Operations',
                'priority' => 'Operational Alert',
                'priority_tone' => 'warning',
                'icon' => 'fas fa-door-open',
                'title' => "Peak Gate Entry Density During {$rushWindow}",
                'description' => "Gate scan velocity reaches {$rushVelocity} scans/minute during this rush period. Assigning a secondary scanning assistant 10 minutes prior can help smooth student arrival queues.",
                'action_label' => 'Inspect Gate Timing',
                'rush_window' => $rushWindow,
                'rush_velocity' => $rushVelocity,
            ]);
        }

        // Action 3: Missing Checkout Accountability
        if ($missingOutCount > 0) {
            $prescriptiveActions->push([
                'action_key' => 'missing_checkout',
                'modal_target' => '#modalAction_missing_checkout',
                'category' => 'Campus Safety',
                'priority' => 'Daily Protocol',
                'priority_tone' => 'danger',
                'icon' => 'fas fa-user-shield',
                'title' => "Unrecorded Departures ({$missingOutCount} missing OUTs)",
                'description' => "{$missingOutCount} student(s) clocked IN without a recorded exit timestamp. Classroom advisers may verify if these students departed during dismissal without scanning.",
                'action_label' => 'View Incomplete Scans',
                'missing_count' => $missingOutCount,
                'missing_sample' => $missingOutSample,
            ]);
        }

        // Action 4: Frequent Late Comers Attendance Guidance
        if ($frequentLateStudents->isNotEmpty()) {
            $topFrequent = $frequentLateStudents->first();
            $prescriptiveActions->push([
                'action_key' => 'late_guidance',
                'modal_target' => '#modalAction_late_guidance',
                'category' => 'Student Guidance',
                'priority' => 'Advisory Follow-Up',
                'priority_tone' => 'info',
                'icon' => 'fas fa-user-clock',
                'title' => "Multiple Late Arrivals ({$frequentLateStudents->count()} students recorded)",
                'description' => "{$frequentLateStudents->count()} student(s) recorded 2 or more late arrivals during this period (e.g. {$topFrequent['student_name']} with {$topFrequent['late_count']} late entries). Recommend advisor check-in.",
                'action_label' => 'View Student Details',
                'student_count' => $frequentLateStudents->count(),
            ]);
        }

        // Fallback affirmative action if all metrics are optimal
        if ($prescriptiveActions->isEmpty()) {
            $prescriptiveActions->push([
                'action_key' => 'optimal_status',
                'modal_target' => '#downloadAnalyticsModal',
                'category' => 'Operations Status',
                'priority' => 'All Systems Normal',
                'priority_tone' => 'success',
                'icon' => 'fas fa-circle-check',
                'title' => 'Punctual & Smooth Gate Operations',
                'description' => 'Student arrival flow and attendance rates are within normal expectations. No operational interventions required for this selected period.',
                'action_label' => 'Export Executive Summary',
            ]);
        }

        // ── 15. SVG path builders for line charts ───────────────────────────
        $toSmoothPath = function (array $pts): string {
            $n = count($pts);
            if ($n === 0) return '';
            if ($n === 1) return 'M ' . $pts[0]['x'] . ',' . $pts[0]['y'];
            $d = 'M ' . $pts[0]['x'] . ',' . $pts[0]['y'];
            for ($i = 0; $i < $n - 1; $i++) {
                $p0 = $pts[$i - 1] ?? $pts[$i];
                $p1 = $pts[$i];
                $p2 = $pts[$i + 1];
                $p3 = $pts[$i + 2] ?? $p2;
                $c1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                $c1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                $c2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                $c2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;
                $d .= ' C ' . round($c1x, 2) . ',' . round($c1y, 2)
                    . ' ' . round($c2x, 2) . ',' . round($c2y, 2)
                    . ' ' . $p2['x'] . ',' . $p2['y'];
            }
            return $d;
        };

        // Daily trend SVG paths
        $trendMaxTotal = max($dailyTrend->max('in') ?? 0, $dailyTrend->max('out') ?? 0, 1);
        $trendPointCount = max($dailyTrend->count(), 1);
        $trendSpan = max($trendPointCount - 1, 1);

        $inPoints = $dailyTrend->map(function ($d, $i) use ($trendSpan, $trendMaxTotal) {
            return ['x' => round(4 + ($i / $trendSpan) * 92, 2), 'y' => round(8 + (1 - ($d['in'] / $trendMaxTotal)) * 82, 2)];
        })->values()->all();

        $outPoints = $dailyTrend->map(function ($d, $i) use ($trendSpan, $trendMaxTotal) {
            return ['x' => round(4 + ($i / $trendSpan) * 92, 2), 'y' => round(8 + (1 - ($d['out'] / $trendMaxTotal)) * 82, 2)];
        })->values()->all();

        $inPath  = $toSmoothPath($inPoints);
        $outPath = $toSmoothPath($outPoints);
        $inArea  = $inPath !== '' ? $inPath . ' L 96 90 L 4 90 Z' : '';
        $outArea = $outPath !== '' ? $outPath . ' L 96 90 L 4 90 Z' : '';

        // Late trend SVG path
        $lateMax = max($lateDailyTrend->max('late') ?? 0, 1);
        $lateSpan = max(max($lateDailyTrend->count(), 1) - 1, 1);
        $latePoints = $lateDailyTrend->map(function ($d, $i) use ($lateSpan, $lateMax) {
            return ['x' => round(4 + ($i / $lateSpan) * 92, 2), 'y' => round(8 + (1 - ($d['late'] / $lateMax)) * 82, 2)];
        })->values()->all();
        $latePath = $toSmoothPath($latePoints);
        $lateArea = $latePath !== '' ? $latePath . ' L 96 90 L 4 90 Z' : '';

        // Tick labels for daily trend (evenly spaced, max 6)
        $trendTicks = collect();
        if ($dailyTrend->count() > 1) {
            $count = $dailyTrend->count();
            $maxTicks = min($count, 6);
            $step = max(floor(($count - 1) / ($maxTicks - 1)), 1);
            for ($i = 0; $i < $count; $i += $step) {
                $trendTicks->push($dailyTrend[$i]['label']);
            }
            if ($trendTicks->last() !== $dailyTrend->last()['label']) {
                $trendTicks->push($dailyTrend->last()['label']);
            }
        } elseif ($dailyTrend->count() === 1) {
            $trendTicks->push($dailyTrend->first()['label']);
        }

        return [
            // 1. Descriptive Analytics (KPIs)
            'uniqueStudents'   => $uniqueStudentsTotal,
            'uniqueStudentsIn' => $uniqueStudentsIn,
            'attendanceRate'   => $attendanceRate,
            'expectedStudents' => $expectedStudents,
            'onTimeRate'       => $onTimeRate,
            'lateRate'         => $lateRate,
            'lateCount'        => $lateCount,
            'totalLateScans'   => $totalLateScans,
            'onTimeCount'      => $onTimeCount,
            'avgArrival'       => $avgArrival,
            'avgDeparture'     => $avgDeparture,
            'missingOutCount'  => $missingOutCount,
            'checkoutIntegrityRate' => $checkoutIntegrityRate,
            'totalScans'       => $logs->count(),

            // 2. Diagnostic Analytics (Root Causes)
            'sessionDistribution' => $sessionDistribution,
            'donutOnTimePct'   => $donutOnTimePct,
            'donutLatePct'     => $donutLatePct,
            'rushWindow'       => $rushWindow,
            'rushVelocity'     => $rushVelocity,
            'peakEntryHour'    => $peakEntryHour,
            'peakExitHour'     => $peakExitHour,
            'hourlyDistribution' => $hourlyDistribution,
            'maxHourlyTotal'   => $maxHourlyTotal,
            'gradeLevelData'   => $gradeLevelData,
            'sectionData'      => $sectionData,

            // 3. Predictive Analytics (Risk Registry & Trends)
            'frequentLateStudents' => $frequentLateStudents,
            'chronicLateStudents'  => $frequentLateStudents,
            'dailyTrend'       => $dailyTrend,
            'trendMaxTotal'    => $trendMaxTotal,
            'inPath'           => $inPath,
            'outPath'          => $outPath,
            'inArea'           => $inArea,
            'outArea'          => $outArea,
            'trendTicks'       => $trendTicks,
            'lateDailyTrend'   => $lateDailyTrend,
            'lateMax'          => $lateMax,
            'latePath'         => $latePath,
            'lateArea'         => $lateArea,

            // 4. Prescriptive Analytics (Action Items)
            'prescriptiveActions' => $prescriptiveActions,
            'missingOutSample' => $missingOutSample,

            // Meta
            'dateFilter'       => $dateFilter,
            'hasData'          => $logs->isNotEmpty(),
            'dayCount'         => $dailyTrend->count(),
        ];
    }

    /**
     * Download Attendance Analytics Executive Report as PDF.
     */
    public function downloadAnalyticsPdf(Request $request)
    {
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $analyticsLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans'
        ])
        ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
            $q->whereHas('enrollment', function ($enrollmentQuery) use ($activeSchoolYear) {
                $enrollmentQuery->where('school_year_id', $activeSchoolYear->id);
            });
        })
        ->withoutExcessScanFlags();

        if ($startDate && $endDate) {
            $analyticsLogsQuery = $analyticsLogsQuery->filterByDateRange($startDate, $endDate);
            $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' – ' . Carbon::parse($endDate)->format('M d, Y');
            $dateFilter = 'custom';
        } else {
            $dateFilter = $request->input('date_filter', 'today');
            $customStartDate = $request->input('custom_start_date');
            $customEndDate = $request->input('custom_end_date');
            $analyticsLogsQuery = $this->applyDateFilter(
                $analyticsLogsQuery,
                $dateFilter,
                $customStartDate,
                $customEndDate
            );
            $dateRangeLabel = match ($dateFilter) {
                'today' => 'Today (' . now()->format('M d, Y') . ')',
                'yesterday' => 'Yesterday (' . now()->subDay()->format('M d, Y') . ')',
                'last_7_days' => 'Last 7 Days (' . now()->subDays(6)->format('M d') . ' – ' . now()->format('M d, Y') . ')',
                'month' => 'This Month (' . now()->format('F Y') . ')',
                'custom' => ($customStartDate && $customEndDate) ? ($customStartDate . ' to ' . $customEndDate) : 'Custom Range',
                default => 'All Records',
            };
        }

        $analyticsLogsQuery = $analyticsLogsQuery
            ->search($query)
            ->filterByScanType($scan_type)
            ->filterBySessionType($session_type)
            ->filterByFlagType($flag_type);

        $statusCounts = $analyticsLogsQuery->getStatistics();
        $analyticsLogs = $analyticsLogsQuery->orderBy('scan_time')->get();
        $analytics = $this->buildAnalytics($analyticsLogs, $dateFilter);

        $pdf = $this->pdfWrapper->loadView('pdf.attendance.attendance-analytics-report', compact('analytics', 'statusCounts', 'dateRangeLabel', 'dateFilter'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Attendance-Analytics-Report-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Download Filtered Attendance Logs as PDF Table.
     */
    public function downloadPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $logs = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans',
        ])
            ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
                $q->whereHas('enrollment', function ($enrollmentQuery) use ($activeSchoolYear) {
                    $enrollmentQuery->where('school_year_id', $activeSchoolYear->id);
                });
            })
            ->withoutExcessScanFlags()
            ->filterByDateRange($startDate, $endDate)
            ->search($request->input('query'))
            ->filterByScanType($request->input('scan_type'))
            ->filterBySessionType($request->input('session_type'))
            ->filterByFlagType($request->input('flag_type'))
            ->orderBy('scan_time', 'desc')
            ->get();

        $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' – ' . Carbon::parse($endDate)->format('M d, Y');

        $pdf = $this->pdfWrapper->loadView('pdf.attendance.attendance-logs-report', compact('logs', 'dateRangeLabel'));
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Attendance-Logs-' . Carbon::parse($startDate)->format('Ymd') . '-' . Carbon::parse($endDate)->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export the filtered time in / time out logs to an Excel file.
     * Date range is supplied by the modal (max one month = 31 days).
     */
    public function download(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        // Enforce a maximum of one month (31 days) on the exported range.
        if ($start->diffInDays($end) > 31) {
            return back()
                ->with('error', 'The selected date range cannot exceed one month (31 days).')
                ->withInput();
        }

        $activeSchoolYear = SchoolYear::query()->active()->first();

        $logs = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans',
        ])
            ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
                $q->whereHas('enrollment', function ($enrollmentQuery) use ($activeSchoolYear) {
                    $enrollmentQuery->where('school_year_id', $activeSchoolYear->id);
                });
            })
            ->withoutExcessScanFlags()
            ->filterByDateRange($validated['start_date'], $validated['end_date'])
            ->search($request->input('query'))
            ->filterByScanType($request->input('scan_type'))
            ->filterBySessionType($request->input('session_type'))
            ->filterByFlagType($request->input('flag_type'))
            ->orderBy('scan_time', 'desc')
            ->get();

        // Build the spreadsheet following the same pattern as BulkImportController::exportErrors.
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $columns = ['Student', 'Date', 'Grade & Section', 'Scan Type', 'Session', 'Gate Time', 'Remarks'];

        foreach ($columns as $colIndex => $header) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $rowNum = 2;

        foreach ($logs as $log) {
            $student     = $log->enrollment?->student;
            $section     = $log->enrollment?->section;
            $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '-';
            $gradeSection = 'Grade ' . ($section?->grade_level ?? '-') . ' — ' . ($section?->name ?? '-');

            $flagTypes = $log->flagged_scans->pluck('flag_type')->filter()->unique();

            $flagText = $flagTypes->isNotEmpty()
                ? $flagTypes->map(fn($flagType) => match ($flagType) {
                    'late_arrival' => 'Late arrival',
                    'invalid_checkout' => 'Checkout issue',
                    default => 'Needs review',
                })->join(', ')
                : '-';

            $sheet->setCellValue('A' . $rowNum, $studentName . ' (' . ($student?->student_number ?? '-') . ')');
            $sheet->setCellValue('B' . $rowNum, $log->scan_time?->format('Y-m-d') ?? '-');
            $sheet->setCellValue('C' . $rowNum, $gradeSection);
            $sheet->setCellValue('D' . $rowNum, $log->scan_type === 'IN' ? 'Time In' : ($log->scan_type === 'OUT' ? 'Time Out' : 'Unknown'));
            $sheet->setCellValue('E' . $rowNum, $log->session_type ? Str::headline(str_replace('_', ' ', $log->session_type)) : '-');
            $sheet->setCellValue('F' . $rowNum, $log->scan_time?->format('h:i A') ?? '-');
            $sheet->setCellValue('G' . $rowNum, $flagText);
            $rowNum++;
        }

        $headerRange = 'A1:' . Coordinate::stringFromColumnIndex(count($columns)) . '1';
        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E8E8E8');

        $sheet->freezePane('A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'attendance_export_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $filename = 'attendance-logs-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.xlsx';

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Teacher index method for time in time out history
     */
    public function teacherIndex(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Apply date filtering
     */
    protected function applyDateFilter(
        $query,
        $dateFilter,
        $customStartDate = null,
        $customEndDate = null
    ) {
        switch ($dateFilter) {
            case 'today':
                return $query->todayOnly();

            case 'yesterday':
                return $query->yesterdayOnly();

            case 'week':
                return $query->thisWeekOnly();

            case 'last_7_days':
                return $query->last7Days();

            case 'month':
                return $query->filterByDateRange(now()->subMonth(), now());

            case 'custom':
                if ($customStartDate && $customEndDate) {
                    return $query->filterByDateRange(
                        $customStartDate,
                        $customEndDate
                    );
                }

                return $query->todayOnly();

            default:
                return $query->todayOnly();
        }
    }
}
