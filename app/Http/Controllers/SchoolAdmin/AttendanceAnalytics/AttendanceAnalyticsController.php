<?php

namespace App\Http\Controllers\SchoolAdmin\AttendanceAnalytics;

use App\Http\Controllers\SchoolAdmin\InOutMonitoring\AttendanceLogController;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Mail\AdminInOutInterventionMail;
use App\Models\RiskFollowUp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AttendanceAnalyticsController extends AttendanceLogController
{
    /**
     * Dedicated School Admin Analytics Page with Configurable Progressive Scope Filtering.
     */
    public function analytics(Request $request)
    {
        $t0 = microtime(true);

        // ── 1. Basic In/Out and Date Filters ──────────────────────────────
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $session_type = $request->input('session_type');
        $flag_type = $request->input('flag_type');

        // ── 2. School Year Resolution ─────────────────────────────────────
        $activeSchoolYear = $request->filled('school_year_id')
            ? SchoolYear::find($request->input('school_year_id'))
            : SchoolYear::query()->active()->first();

        // ── 3. Time-Based Analytics Default Hierarchy ─────────────────────
        // Default: 3-month overview when sufficient data exists.
        // Fallback: Full school year if recent 3 months contain insufficient/no usable attendance data.
        $dateFilter = $request->input('date_filter');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        if (empty($dateFilter)) {
            $threeMonthsAgo = now()->subMonths(3)->startOfDay();
            $recentCount = AttendanceLog::where('scan_time', '>=', $threeMonthsAgo)->count();

            if ($recentCount > 0) {
                $dateFilter = 'last_3_months';
            } else {
                $dateFilter = 'school_year';
            }
        }

        // ── 4. Progressive Scope Filters ──────────────────────────────────
        $academicLevel = $this->normalizeAcademicLevel($request->input('academic_level'));
        $gradeLevels   = $this->normalizeArrayInput($request->input('grade_levels', $request->input('grade_level')));
        $sectionIds    = $this->normalizeArrayInput($request->input('section_ids', $request->input('section_id')));
        $subjectIds    = $this->normalizeArrayInput($request->input('subject_ids', $request->input('subject_id')));
        $studentId     = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $term          = $request->filled('term') ? (int) $request->input('term') : null;

        // ── 4. Build Hierarchy Options for Dynamic Cascading in View ──────
        $hierarchy = $this->buildHierarchyOptions($activeSchoolYear);

        // ── 5. Resolve Target Scoped Enrollments ──────────────────────────
        $enrollmentQuery = Enrollment::query()
            ->where('status', 'active')
            ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id));

        // Academic Level constraint
        if ($academicLevel) {
            $levelGrades = $this->getGradesForAcademicLevel($academicLevel);
            if (!empty($levelGrades)) {
                $enrollmentQuery->whereIn('grade_level', array_map('strval', $levelGrades));
            }
        }

        // Specific Grade Level(s)
        if (!empty($gradeLevels)) {
            $enrollmentQuery->whereIn('grade_level', array_map('strval', $gradeLevels));
        }

        // Specific Section(s)
        if (!empty($sectionIds)) {
            $enrollmentQuery->whereIn('section_id', $sectionIds);
        }

        // Specific Subject(s) -> filter sections offering those subjects
        if (!empty($subjectIds)) {
            $sectionIdsForSubjects = TeachingAssignment::query()
                ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id))
                ->where('status', 'active')
                ->whereIn('subject_id', $subjectIds)
                ->pluck('section_id')
                ->unique();

            $enrollmentQuery->whereIn('section_id', $sectionIdsForSubjects);
        }

        // Specific Student
        if ($studentId) {
            $enrollmentQuery->where('student_id', $studentId);
        }

        $scopedEnrollmentIds = $enrollmentQuery->pluck('id');
        $cohortStudentCount = $scopedEnrollmentIds->count();
        $cohortSectionCount = (clone $enrollmentQuery)->distinct('section_id')->count('section_id');

        // ── 6. Query Scoped Attendance Logs ───────────────────────────────
        $analyticsLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans',
        ])
            ->whereIn('enrollment_id', $scopedEnrollmentIds)
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

        // ── 7. Scope Context & Human-Readable Labels ("What am I analyzing?") ──
        $scopeContext = $this->buildScopeContext(
            $request,
            $academicLevel,
            $gradeLevels,
            $sectionIds,
            $subjectIds,
            $studentId,
            $term,
            $cohortStudentCount,
            $cohortSectionCount,
            $hierarchy
        );

        // ── 8. Available School Year Months for Optional Chart Filter ────
        $startYear = 2026;
        if ($activeSchoolYear && preg_match('/^(\d{4})/', $activeSchoolYear->school_year, $m)) {
            $startYear = (int) $m[1];
        }

        $cursor = Carbon::create($startYear, 7, 1)->startOfMonth();
        $nowEnd = now()->endOfMonth();
        $availableMonths = [];

        while ($cursor->lessThanOrEqualTo($nowEnd)) {
            $nextMonth = $cursor->copy()->addMonth();
            $rangeLabel = $cursor->format('M 1') . ' – ' . ($cursor->isCurrentMonth() ? 'Present' : $nextMonth->format('M 1'));
            $availableMonths[] = [
                'key'           => $cursor->format('Y-m'),
                'label'         => $cursor->format('F Y'),
                'short_label'   => $cursor->format('M Y'),
                'range_label'   => $rangeLabel,
                'month_num'     => (int) $cursor->format('m'),
                'year'          => (int) $cursor->format('Y'),
                'days_in_month' => $cursor->daysInMonth,
            ];
            $cursor->addMonth();
        }

        \Illuminate\Support\Facades\Log::debug('[PROFILE-CTRL:school-admin-analytics] ms=' . round((microtime(true) - $t0) * 1000, 1));

        return view('pov.school-admin.attendance-analytics.attendance-analytics', compact(
            'scan_type',
            'session_type',
            'flag_type',
            'statusCounts',
            'dateFilter',
            'customStartDate',
            'customEndDate',
            'query',
            'analytics',
            'hierarchy',
            'academicLevel',
            'gradeLevels',
            'sectionIds',
            'subjectIds',
            'studentId',
            'term',
            'scopeContext',
            'activeSchoolYear',
            'availableMonths'
        ));
    }

    /**
     * Download Analytics PDF with full scope preservation.
     */
    public function downloadAnalyticsPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $dateFilter = 'custom';
        $dateRangeLabel = Carbon::parse($startDate)->format('M d, Y') . ' – ' . Carbon::parse($endDate)->format('M d, Y');

        $activeSchoolYear = $request->filled('school_year_id')
            ? SchoolYear::find($request->input('school_year_id'))
            : SchoolYear::query()->active()->first();

        // Scope filters
        $academicLevel = $this->normalizeAcademicLevel($request->input('academic_level'));
        $gradeLevels   = $this->normalizeArrayInput($request->input('grade_levels', $request->input('grade_level')));
        $sectionIds    = $this->normalizeArrayInput($request->input('section_ids', $request->input('section_id')));
        $subjectIds    = $this->normalizeArrayInput($request->input('subject_ids', $request->input('subject_id')));
        $studentId     = $request->filled('student_id') ? (int) $request->input('student_id') : null;

        $enrollmentQuery = Enrollment::query()
            ->where('status', 'active')
            ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id));

        if ($academicLevel) {
            $levelGrades = $this->getGradesForAcademicLevel($academicLevel);
            if (!empty($levelGrades)) {
                $enrollmentQuery->whereIn('grade_level', array_map('strval', $levelGrades));
            }
        }
        if (!empty($gradeLevels)) {
            $enrollmentQuery->whereIn('grade_level', array_map('strval', $gradeLevels));
        }
        if (!empty($sectionIds)) {
            $enrollmentQuery->whereIn('section_id', $sectionIds);
        }
        if (!empty($subjectIds)) {
            $sectionIdsForSubjects = TeachingAssignment::query()
                ->when($activeSchoolYear, fn ($q) => $q->where('school_year_id', $activeSchoolYear->id))
                ->where('status', 'active')
                ->whereIn('subject_id', $subjectIds)
                ->pluck('section_id')
                ->unique();
            $enrollmentQuery->whereIn('section_id', $sectionIdsForSubjects);
        }
        if ($studentId) {
            $enrollmentQuery->where('student_id', $studentId);
        }

        $scopedEnrollmentIds = $enrollmentQuery->pluck('id');

        $analyticsLogsQuery = AttendanceLog::with([
            'enrollment.student',
            'enrollment.section',
            'flagged_scans',
        ])
            ->whereIn('enrollment_id', $scopedEnrollmentIds)
            ->withoutExcessScanFlags()
            ->filterByDateRange($startDate, $endDate)
            ->search($request->input('query'))
            ->filterByScanType($request->input('scan_type'))
            ->filterBySessionType($request->input('session_type'))
            ->filterByFlagType($request->input('flag_type'));

        $statusCounts = $analyticsLogsQuery->getStatistics();
        $analyticsLogs = $analyticsLogsQuery->orderBy('scan_time')->get();
        $analytics = $this->buildAnalytics($analyticsLogs, $dateFilter);

        $pdf = app(\App\Libraries\PDF\DomPdfWrapper::class)->loadView('pdf.attendance.attendance-analytics-report', compact(
            'analytics',
            'statusCounts',
            'dateRangeLabel',
            'dateFilter'
        ));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Attendance-Analytics-Report-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Normalize Academic Level to canonical key (elementary, jhs, shs).
     */
    private function normalizeAcademicLevel(?string $level): ?string
    {
        if (!$level) {
            return null;
        }

        return match (strtolower(trim($level))) {
            'elementary', 'elem' => 'elementary',
            'jhs', 'hs', 'highschool', 'junior_high_school', 'junior_high' => 'jhs',
            'shs', 'senior_high_school', 'senior_high' => 'shs',
            default => strtolower(trim($level)),
        };
    }

    /**
     * Map Academic Level to corresponding Grade Levels.
     */
    private function getGradesForAcademicLevel(?string $level): array
    {
        $norm = $this->normalizeAcademicLevel($level);

        return match ($norm) {
            'elementary' => range(1, 6),
            'jhs' => range(7, 10),
            'shs' => range(11, 12),
            default => [],
        };
    }

    /**
     * Normalize request input to array of integers/strings.
     */
    private function normalizeArrayInput(mixed $input): array
    {
        if (empty($input)) {
            return [];
        }

        if (is_array($input)) {
            return array_values(array_filter(array_map('intval', $input)));
        }

        if (is_string($input) && str_contains($input, ',')) {
            return array_values(array_filter(array_map('intval', explode(',', $input))));
        }

        return [(int) $input];
    }

    /**
     * Build the hierarchy dataset for client-side progressive cascading.
     */
    private function buildHierarchyOptions(?SchoolYear $activeSchoolYear): array
    {
        $yearId = $activeSchoolYear?->id;

        // 1. Academic Levels
        $levels = [
            'elementary' => [
                'key' => 'elementary',
                'label' => 'Elementary',
                'grades' => range(1, 6),
            ],
            'jhs' => [
                'key' => 'jhs',
                'label' => 'Junior High School',
                'grades' => range(7, 10),
            ],
            'shs' => [
                'key' => 'shs',
                'label' => 'Senior High School',
                'grades' => range(11, 12),
            ],
        ];

        // 2. Sections
        $sections = Section::query()
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'grade_level'])
            ->map(fn ($s) => [
                'id' => (int) $s->id,
                'name' => $s->name,
                'grade_level' => (int) $s->grade_level,
                'level' => $s->level,
                'display' => 'G' . $s->grade_level . ' — ' . $s->name,
            ])
            ->values();

        // 3. Subjects with linked sections via TeachingAssignments
        $assignments = TeachingAssignment::query()
            ->when($yearId, fn ($q) => $q->where('school_year_id', $yearId))
            ->where('status', 'active')
            ->get(['section_id', 'subject_id']);

        $sectionSubjectMap = [];
        foreach ($assignments as $ta) {
            $sectionSubjectMap[$ta->section_id][] = (int) $ta->subject_id;
        }

        $subjects = Subject::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'level', 'subject_type'])
            ->map(fn ($sub) => [
                'id' => (int) $sub->id,
                'code' => $sub->code,
                'name' => $sub->name,
                'level' => $sub->level,
                'display' => $sub->name . ($sub->code ? ' (' . $sub->code . ')' : ''),
            ])
            ->values();

        // 4. Students enrolled in active school year
        $students = Enrollment::query()
            ->where('status', 'active')
            ->when($yearId, fn ($q) => $q->where('school_year_id', $yearId))
            ->with('student:id,student_number,first_name,last_name,middle_name')
            ->get()
            ->filter(fn ($e) => $e->student !== null)
            ->map(fn ($e) => [
                'enrollment_id' => (int) $e->id,
                'student_id' => (int) $e->student_id,
                'section_id' => (int) $e->section_id,
                'grade_level' => (int) $e->grade_level,
                'student_number' => $e->student->student_number ?? '—',
                'name' => $e->student->full_name,
            ])
            ->sortBy('name')
            ->values();

        // 5. Grading periods
        $gradingPeriods = GradingPeriod::trimester()
            ->orderBy('sequence')
            ->get(['id', 'name', 'sequence', 'is_active'])
            ->map(fn ($gp) => [
                'id' => (int) $gp->id,
                'name' => $gp->name,
                'sequence' => (int) $gp->sequence,
                'is_active' => (bool) $gp->is_active,
            ])
            ->values();

        return [
            'levels' => $levels,
            'sections' => $sections,
            'subjects' => $subjects,
            'sectionSubjectMap' => $sectionSubjectMap,
            'students' => $students,
            'gradingPeriods' => $gradingPeriods,
        ];
    }

    /**
     * Build scope context, breadcrumbs, and active filter chips for the UI.
     */
    private function buildScopeContext(
        Request $request,
        ?string $academicLevel,
        array $gradeLevels,
        array $sectionIds,
        array $subjectIds,
        ?int $studentId,
        ?int $term,
        int $cohortStudentCount,
        int $cohortSectionCount,
        array $hierarchy
    ): array {
        $breadcrumbs = [['label' => 'School-wide', 'url' => route('school_admin.time-in-time-out-history.analytics')]];
        $activeChips = [];
        $scopeTitle = 'School-wide Analytics';

        // 1. Academic Level
        if ($academicLevel && isset($hierarchy['levels'][$academicLevel])) {
            $levelName = $hierarchy['levels'][$academicLevel]['label'];
            $breadcrumbs[] = ['label' => $levelName, 'url' => null];
            $scopeTitle = $levelName;
            $activeChips[] = [
                'key' => 'academic_level',
                'label' => 'Level: ' . $levelName,
                'remove_url' => $this->buildRemoveFilterUrl($request, 'academic_level'),
            ];
        }

        // 2. Grade Level(s)
        if (!empty($gradeLevels)) {
            $gradeLabels = array_map(fn ($g) => 'Grade ' . $g, $gradeLevels);
            $gradeText = implode(', ', $gradeLabels);
            $breadcrumbs[] = ['label' => $gradeText, 'url' => null];
            $scopeTitle = $gradeText;
            $activeChips[] = [
                'key' => 'grade_levels',
                'label' => $gradeText,
                'remove_url' => $this->buildRemoveFilterUrl($request, ['grade_level', 'grade_levels']),
            ];
        }

        // 3. Section(s)
        if (!empty($sectionIds)) {
            $sectionNames = collect($hierarchy['sections'])
                ->whereIn('id', $sectionIds)
                ->pluck('name')
                ->all();
            $secText = !empty($sectionNames) ? 'Section ' . implode(', ', $sectionNames) : 'Selected Sections';
            $breadcrumbs[] = ['label' => $secText, 'url' => null];
            $scopeTitle = $secText;
            $activeChips[] = [
                'key' => 'section_ids',
                'label' => $secText,
                'remove_url' => $this->buildRemoveFilterUrl($request, ['section_id', 'section_ids']),
            ];
        }

        // 4. Subject(s)
        if (!empty($subjectIds)) {
            $subjectNames = collect($hierarchy['subjects'])
                ->whereIn('id', $subjectIds)
                ->pluck('name')
                ->all();
            $subText = !empty($subjectNames) ? 'Subject: ' . implode(', ', $subjectNames) : 'Selected Subjects';
            $breadcrumbs[] = ['label' => $subText, 'url' => null];
            $scopeTitle = $subText;
            $activeChips[] = [
                'key' => 'subject_ids',
                'label' => $subText,
                'remove_url' => $this->buildRemoveFilterUrl($request, ['subject_id', 'subject_ids']),
            ];
        }

        // 5. Specific Student
        if ($studentId) {
            $matchedStudent = collect($hierarchy['students'])->firstWhere('student_id', $studentId);
            $stName = $matchedStudent['name'] ?? 'Student #' . $studentId;
            $breadcrumbs[] = ['label' => $stName, 'url' => null];
            $scopeTitle = $stName;
            $activeChips[] = [
                'key' => 'student_id',
                'label' => 'Student: ' . $stName,
                'remove_url' => $this->buildRemoveFilterUrl($request, 'student_id'),
            ];
        }

        // 6. Term / Grading Period
        if ($term) {
            $termLabel = 'Term ' . $term;
            $activeChips[] = [
                'key' => 'term',
                'label' => $termLabel,
                'remove_url' => $this->buildRemoveFilterUrl($request, 'term'),
            ];
        }

        // Clear All URL (resets all scope filters back to base URL)
        $clearAllUrl = route('school_admin.time-in-time-out-history.analytics');

        return [
            'title' => $scopeTitle,
            'breadcrumbs' => $breadcrumbs,
            'activeChips' => $activeChips,
            'isSchoolWide' => empty($activeChips),
            'cohortStudentCount' => $cohortStudentCount,
            'cohortSectionCount' => $cohortSectionCount,
            'clearAllUrl' => $clearAllUrl,
        ];
    }

    /**
     * Generate URL with specific filter keys removed.
     */
    private function buildRemoveFilterUrl(Request $request, string|array $keysToRemove): string
    {
        $keys = (array) $keysToRemove;
        $params = $request->except(array_merge($keys, ['page']));

        return route('school_admin.time-in-time-out-history.analytics', $params);
    }

    /**
     * Override applyDateFilter to implement 3-month default and school-year fallback.
     */
    protected function applyDateFilter(
        $query,
        $dateFilter,
        $customStartDate = null,
        $customEndDate = null
    ) {
        switch ($dateFilter) {
            case 'last_3_months':
                return $query->filterByDateRange(
                    now()->subMonths(3)->startOfDay(),
                    now()->endOfDay()
                );

            case 'school_year':
                $activeSy = SchoolYear::query()->active()->first();
                if ($activeSy && !empty($activeSy->school_year)) {
                    $years = explode('-', $activeSy->school_year);
                    if (count($years) === 2 && is_numeric($years[0]) && is_numeric($years[1])) {
                        return $query->filterByDateRange(
                            Carbon::create((int) $years[0], 6, 1)->startOfDay(),
                            Carbon::create((int) $years[1], 6, 30)->endOfDay()
                        );
                    }
                }
                return $query->filterByDateRange(
                    now()->subMonths(12)->startOfDay(),
                    now()->endOfDay()
                );

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
                return $query->filterByDateRange(
                    now()->subMonths(3)->startOfDay(),
                    now()->endOfDay()
                );

            default:
                return $query->filterByDateRange(
                    now()->subMonths(3)->startOfDay(),
                    now()->endOfDay()
                );
        }
    }

    /**
     * Enhance buildAnalytics with explicit in/out scan totals, 4-metric daily trend series,
     * and dual pattern-detected monitoring registries (Multiple Late Arrivals & Multiple Missing Time Outs).
     */
    protected function buildAnalytics(Collection $logs, string $dateFilter): array
    {
        $data = parent::buildAnalytics($logs, $dateFilter);
        $data['totalInScans'] = $logs->where('scan_type', 'IN')->count();
        $data['totalOutScans'] = $logs->where('scan_type', 'OUT')->count();

        // Enrich dailyTrend with 4 core metrics (in, out, late, missing_out) per day
        $inLogs = $logs->where('scan_type', 'IN');
        $outLogs = $logs->where('scan_type', 'OUT');

        $enhancedDailyTrend = collect();
        if ($logs->isNotEmpty()) {
            $allDates = $logs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString());
            foreach ($allDates as $dateStr => $dayLogs) {
                $cDate = Carbon::parse($dateStr);
                $dayIn = $dayLogs->where('scan_type', 'IN');
                $dayOut = $dayLogs->where('scan_type', 'OUT');

                $dayLate = $dayIn->filter(fn (AttendanceLog $l) => $l->flagged_scans->contains('flag_type', 'late_arrival'))->count();

                $dayInEids = $dayIn->pluck('enrollment_id')->unique();
                $dayOutEids = $dayOut->pluck('enrollment_id')->unique();
                $dayMissing = $dayInEids->diff($dayOutEids)->count();

                $enhancedDailyTrend->push([
                    'date'            => $dateStr,
                    'label'           => $cDate->format('M j'),
                    'day_name'        => $cDate->format('l'),
                    'month_key'       => $cDate->format('Y-m'),
                    'day_of_month'    => (int) $cDate->format('j'),
                    'in'              => $dayIn->count(),
                    'out'             => $dayOut->count(),
                    'late'            => $dayLate,
                    'missing_out'     => $dayMissing,
                    'total'           => $dayLogs->count(),
                    'unique_students' => $dayLogs->pluck('enrollment_id')->unique()->count(),
                ]);
            }
            $enhancedDailyTrend = $enhancedDailyTrend->sortBy('date')->values();
        }

        $data['dailyTrend'] = $enhancedDailyTrend;
        $data['dayCount']   = $enhancedDailyTrend->count();

        // ── Phase 3: Enhanced Multiple Late Arrivals Registry with Pattern Detection ──
        $lateInLogs = $inLogs->filter(fn (AttendanceLog $l) => $l->flagged_scans->contains('flag_type', 'late_arrival'));
        $frequentLateStudents = collect();

        foreach ($lateInLogs->groupBy('enrollment_id') as $eid => $sLateLogs) {
            $firstLog = $sLateLogs->first();
            $student = $firstLog->enrollment?->student;
            $section = $firstLog->enrollment?->section;
            $lateOccurrences = $sLateLogs->count();
            $totalStudentIn = $inLogs->where('enrollment_id', $eid)->count();
            $personalLateRate = $totalStudentIn > 0 ? round(($lateOccurrences / $totalStudentIn) * 100, 1) : 100;

            $dates = $sLateLogs->map(fn (AttendanceLog $l) => $l->scan_time->toDateString());
            $pattern = $this->detectAttendancePattern($dates, 'late');

            $scanDetails = $sLateLogs->sortByDesc('scan_time')->map(function ($l) {
                return [
                    'date'         => $l->scan_time?->format('M d, Y'),
                    'time'         => $l->scan_time?->format('h:i A'),
                    'session_type' => $l->session_type ? Str::headline(str_replace('_', ' ', $l->session_type)) : '-',
                ];
            })->values()->all();

            // Include students with >= 2 late arrivals
            if ($lateOccurrences >= 2) {
                $frequentLateStudents->push([
                    'enrollment_id'      => $eid,
                    'student_name'       => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: 'Unknown',
                    'student_number'     => $student?->student_number ?? '-',
                    'grade_level'        => $section?->grade_level ?? '-',
                    'section_name'       => $section?->name ?? '-',
                    'late_count'         => $lateOccurrences,
                    'personal_late_rate' => $personalLateRate,
                    'badge_label'        => $pattern['badge_label'],
                    'badge_tone'         => $pattern['badge_tone'],
                    'pattern_type'       => $pattern['pattern_type'],
                    'pattern_desc'       => $pattern['pattern_desc'],
                    'scan_details'       => $scanDetails,
                ]);
            }
        }
        $data['frequentLateStudents'] = $frequentLateStudents->sortByDesc(function ($s) {
            $pScore = $s['pattern_type'] === 'consecutive' ? 1000 : ($s['pattern_type'] === 'weekly_cluster' ? 500 : 0);
            return $pScore + $s['late_count'];
        })->values();

        // ── Phase 3: Multiple Missing Time Out Follow-Up Registry with Pattern Detection ──
        $inByDateEnrollment = $inLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);
        $outByDateEnrollment = $outLogs->groupBy(fn (AttendanceLog $l) => $l->scan_time->toDateString() . '|' . $l->enrollment_id);

        $missingKeys = $inByDateEnrollment->keys()->diff($outByDateEnrollment->keys());
        $missingByEid = collect();

        foreach ($missingKeys as $key) {
            $inLog = $inByDateEnrollment->get($key)->first();
            $eid = $inLog->enrollment_id;
            if (!$missingByEid->has($eid)) {
                $missingByEid->put($eid, collect());
            }
            $missingByEid->get($eid)->push($inLog);
        }

        $frequentMissingOutStudents = collect();
        foreach ($missingByEid as $eid => $sMissingLogs) {
            $firstLog = $sMissingLogs->first();
            $student = $firstLog->enrollment?->student;
            $section = $firstLog->enrollment?->section;
            $missingCount = $sMissingLogs->count();

            $dates = $sMissingLogs->map(fn (AttendanceLog $l) => $l->scan_time->toDateString());
            $pattern = $this->detectAttendancePattern($dates, 'missing');

            $scanDetails = $sMissingLogs->sortByDesc('scan_time')->map(function ($l) {
                return [
                    'date'         => $l->scan_time?->format('M d, Y'),
                    'time'         => $l->scan_time?->format('h:i A'),
                    'session_type' => $l->session_type ? Str::headline(str_replace('_', ' ', $l->session_type)) : '-',
                ];
            })->values()->all();

            $frequentMissingOutStudents->push([
                'enrollment_id'   => $eid,
                'student_name'    => trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: 'Unknown',
                'student_number'  => $student?->student_number ?? '-',
                'grade_level'     => $section?->grade_level ?? '-',
                'section_name'    => $section?->name ?? '-',
                'missing_count'   => $missingCount,
                'badge_label'     => $pattern['badge_label'],
                'badge_tone'      => $pattern['badge_tone'],
                'pattern_type'    => $pattern['pattern_type'],
                'pattern_desc'    => $pattern['pattern_desc'],
                'scan_details'    => $scanDetails,
            ]);
        }
        $data['frequentMissingOutStudents'] = $frequentMissingOutStudents->sortByDesc(function ($s) {
            $pScore = $s['pattern_type'] === 'consecutive' ? 1000 : ($s['pattern_type'] === 'weekly_cluster' ? 500 : 0);
            return $pScore + $s['missing_count'];
        })->values();

        // ── Phase 3: Enhance Action Directives to Highlight Pattern Registries ──
        if ($data['prescriptiveActions'] instanceof Collection) {
            $actions = $data['prescriptiveActions']->map(function ($action) use ($data) {
                if ($action['action_key'] === 'late_guidance') {
                    $action['title'] = 'Multiple Late Arrivals Follow-Up';
                    $action['action_label'] = 'Review Late Registry';
                    $action['modal_target'] = '#lateArrivalsRegistry';
                } elseif ($action['action_key'] === 'missing_checkout') {
                    $action['title'] = 'Multiple Missing Time Out Follow-Up';
                    $action['action_label'] = 'Review Missing OUT Registry';
                    $action['modal_target'] = '#missingOutRegistry';
                }
                return $action;
            });
            $data['prescriptiveActions'] = $actions;
        }

        return $data;
    }

    /**
     * Detect operational attendance patterns (consecutive days, weekly clusters, repeated occurrences).
     */
    protected function detectAttendancePattern($dates, string $metricType = 'late'): array
    {
        $uniqueDates = collect($dates)->unique()->sort()->values();
        $totalOccurrences = $uniqueDates->count();

        // 1. Check Consecutive School Days
        $maxConsecutive = 1;
        $currConsecutive = 1;
        for ($i = 1; $i < $uniqueDates->count(); $i++) {
            $dPrev = Carbon::parse($uniqueDates[$i - 1])->startOfDay();
            $dCurr = Carbon::parse($uniqueDates[$i])->startOfDay();
            $diffDays = (int) $dPrev->diffInDays($dCurr);
            // Consecutive calendar days, or consecutive school days across weekends (Friday to Monday)
            $isConsecutive = ($diffDays === 1) || ($diffDays === 3 && $dPrev->isFriday() && $dCurr->isMonday());
            if ($isConsecutive) {
                $currConsecutive++;
                if ($currConsecutive > $maxConsecutive) {
                    $maxConsecutive = $currConsecutive;
                }
            } else {
                $currConsecutive = 1;
            }
        }
        $hasConsecutive = $maxConsecutive >= 2;

        // 2. Check Weekly Clusters (in any single ISO calendar week)
        $weeks = $uniqueDates->groupBy(fn ($d) => Carbon::parse($d)->format('o-W'));
        $maxWeekly = $weeks->map->count()->max() ?? 0;
        $hasWeeklyCluster = $maxWeekly >= 3;

        $noun = $metricType === 'late' ? 'late arrival' : 'unrecorded departure';
        $nounPlural = $metricType === 'late' ? 'late arrivals' : 'unrecorded departures';
        $nounShort = $metricType === 'late' ? 'Late' : 'Missing OUT';

        if ($hasConsecutive) {
            return [
                'pattern_type'    => 'consecutive',
                'badge_label'     => "Consecutive {$nounShort} ({$maxConsecutive}d)",
                'badge_tone'      => 'danger',
                'pattern_desc'    => "Recorded {$nounPlural} on {$maxConsecutive} consecutive school days.",
                'max_consecutive' => $maxConsecutive,
                'max_weekly'      => $maxWeekly,
            ];
        }

        if ($hasWeeklyCluster) {
            return [
                'pattern_type'    => 'weekly_cluster',
                'badge_label'     => "{$maxWeekly} {$nounShort} in Week",
                'badge_tone'      => 'danger',
                'pattern_desc'    => "Recorded {$maxWeekly} {$nounPlural} within a single 5-day school week.",
                'max_consecutive' => $maxConsecutive,
                'max_weekly'      => $maxWeekly,
            ];
        }

        if ($totalOccurrences >= 4) {
            return [
                'pattern_type'    => 'repeated',
                'badge_label'     => "{$totalOccurrences} {$nounShort} Records",
                'badge_tone'      => 'danger',
                'pattern_desc'    => "High-frequency: {$totalOccurrences} {$nounPlural} across this analytical period.",
                'max_consecutive' => $maxConsecutive,
                'max_weekly'      => $maxWeekly,
            ];
        }

        return [
            'pattern_type'    => 'repeated',
            'badge_label'     => "{$totalOccurrences} {$nounShort} " . ($totalOccurrences > 1 ? 'Entries' : 'Entry'),
            'badge_tone'      => $totalOccurrences >= 2 ? 'warning' : 'info',
            'pattern_desc'    => "{$totalOccurrences} {$noun} record" . ($totalOccurrences > 1 ? 's' : '') . " in this period.",
            'max_consecutive' => $maxConsecutive,
            'max_weekly'      => $maxWeekly,
        ];
    }

    /**
     * Prepare student IN/OUT intervention details, recipient profiles, and message templates.
     */
    public function prepareIntervention(Request $request)
    {
        $enrollmentId = (int) $request->input('enrollment_id');
        $reasonType   = $request->input('reason_type', 'late') === 'missing_out' ? 'missing_out' : 'late';

        if (!$enrollmentId) {
            return response()->json([
                'success' => false,
                'message' => 'No student enrollment specified for intervention.',
            ], 422);
        }

        $enrollment = Enrollment::with([
            'student.guardian',
            'section.advisor.user',
        ])->find($enrollmentId);

        if (!$enrollment || !$enrollment->student) {
            return response()->json([
                'success' => false,
                'message' => 'Student enrollment record could not be found.',
            ], 404);
        }

        $student = $enrollment->student;
        $section = $enrollment->section;
        $advisor = $section?->advisor?->user;
        $guardian = $student->guardian;

        // Fetch recent matching attendance logs to build evidence and detect pattern
        if ($reasonType === 'late') {
            $logs = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'IN')
                ->whereHas('flagged_scans', fn ($q) => $q->where('flag_type', 'late_arrival'))
                ->orderByDesc('scan_time')
                ->take(10)
                ->get();
        } else {
            $inLogs = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'IN')
                ->orderByDesc('scan_time')
                ->take(30)
                ->get();
            $outDates = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'OUT')
                ->pluck('scan_time')
                ->map(fn($t) => Carbon::parse($t)->toDateString())
                ->unique()
                ->all();
            $logs = $inLogs->filter(fn($l) => !in_array(Carbon::parse($l->scan_time)->toDateString(), $outDates, true))->take(10)->values();
        }

        $dates = $logs->pluck('scan_time')->map(function ($t) {
            return Carbon::parse($t)->toDateString();
        })->values()->all();

        $pattern = $this->detectAttendancePattern($dates, $reasonType === 'missing_out' ? 'missing' : 'late');

        $logDatesFormatted = $logs->map(function ($log) use ($reasonType) {
            $dt = Carbon::parse($log->scan_time);
            if ($reasonType === 'late') {
                return $dt->format('M d, Y') . " (Time In: " . $dt->format('h:i A') . ")";
            } else {
                return $dt->format('M d, Y') . " (Time In: " . $dt->format('h:i A') . " — Missing OUT)";
            }
        })->values()->all();

        // Concern title & situation categorization
        $count = max(count($dates), (int) $request->input('count', 1));
        if ($reasonType === 'late') {
            if ($pattern['pattern_type'] === 'consecutive') {
                $concernTitle = "Consecutive Late Arrivals ({$pattern['max_consecutive']} Days)";
            } elseif ($pattern['pattern_type'] === 'weekly_cluster') {
                $concernTitle = "Weekly Cluster of Late Arrivals ({$pattern['max_weekly']} in a Week)";
            } else {
                $concernTitle = "Repeated Late Arrivals ({$count} Records)";
            }
        } else {
            if ($pattern['pattern_type'] === 'consecutive') {
                $concernTitle = "Consecutive Missing Time Outs ({$pattern['max_consecutive']} Days)";
            } elseif ($pattern['pattern_type'] === 'weekly_cluster') {
                $concernTitle = "Weekly Cluster of Missing Time Outs ({$pattern['max_weekly']} in a Week)";
            } else {
                $concernTitle = "Repeated Missing Time Outs ({$count} Records)";
            }
        }

        $studentName = $student->full_name;
        $studentNumber = $student->student_number ?? 'STU-' . $student->id;
        $gradeSection = $section ? "Grade {$section->grade_level} — {$section->name}" : 'Unassigned';

        $advisorName = $advisor?->name ?? 'Class Adviser';
        $advisorEmail = $advisor?->email;
        $hasAdvisorEmail = !empty($advisorEmail) && filter_var($advisorEmail, FILTER_VALIDATE_EMAIL);

        $guardianName = $guardian?->name ?? 'Parent / Guardian';
        $guardianEmail = $guardian?->email;
        $hasGuardianEmail = !empty($guardianEmail) && filter_var($guardianEmail, FILTER_VALIDATE_EMAIL);

        // Date strings for template display
        $evidenceSummary = !empty($logDatesFormatted)
            ? implode("\n• ", array_slice($logDatesFormatted, 0, 5))
            : "Recent session attendance logs";

        // Build Adviser Template (Internal Memo)
        $advisorSubject = "MEMORANDUM: IN/OUT Attendance Monitoring - {$studentName} ({$studentNumber})";
        $advisorSalutation = "Dear {$advisorName},";
        $advisorBody = "MEMORANDUM FOR CLASS ADVISER\n"
            . "TO: {$advisorName}, Class Adviser ({$gradeSection})\n"
            . "FROM: Office of the School Administrator\n"
            . "DATE: " . now()->format('F d, Y') . "\n"
            . "SUBJECT: Administrative Attendance Monitoring — {$studentName}\n\n"
            . "Please be informed that the automated School IN and OUT Analytics system has flagged an attendance pattern requiring your advisory monitoring:\n\n"
            . "• Concern: {$concernTitle}\n"
            . "• Pattern Observed: {$pattern['pattern_desc']}\n"
            . "• Recorded Incidents:\n• {$evidenceSummary}\n\n"
            . "Directive / Recommended Action:\n"
            . "As the class adviser, please conduct a brief, supportive check-in with the student during morning advisory period to determine if recurring transportation or schedule constraints are contributing factors. Kindly monitor their daily gate check-in/departure records for the next two weeks and coordinate with School Administration if further parental conference is needed.";

        // Build Parent Template (English - Advisory, Not Disciplinary)
        $parentEnSubject = "Concepcion Integrated School - Student Attendance Advisory ({$studentName})";
        $parentEnSalutation = "Dear {$guardianName},";
        if ($reasonType === 'late') {
            $parentEnBody = "We hope this message finds you in good health.\n\n"
                . "The Office of the School Administrator is reaching out regarding the daily attendance records of your child, {$studentName} ({$gradeSection}).\n\n"
                . "Our automated IN and OUT monitoring system has noted a recurring pattern of late arrivals at the school gate:\n\n"
                . "• Concern: {$concernTitle}\n"
                . "• Summary: {$pattern['pattern_desc']}\n"
                . "• Recent Dates Recorded:\n• {$evidenceSummary}\n\n"
                . "We share this notice as a proactive measure to support your child's academic routine and safety. Arriving at school on time ensures that students do not miss essential morning instructions, flag ceremonies, and announcements.\n\n"
                . "We kindly ask for your cooperation in reminding and encouraging {$student->first_name} to arrive on campus before the morning assembly. If there are particular transportation issues or circumstances you would like to share, please feel free to reach out to us or their class adviser.\n\n"
                . "Thank you for your continuous support and partnership in your child's education.";
        } else {
            $parentEnBody = "We hope this message finds you in good health.\n\n"
                . "The Office of the School Administrator is reaching out regarding the daily attendance records of your child, {$studentName} ({$gradeSection}).\n\n"
                . "Our automated IN and OUT monitoring system has recorded instances where your child had a valid morning entry (Time In) but did not record a corresponding departure scan (Time Out):\n\n"
                . "• Concern: {$concernTitle}\n"
                . "• Summary: {$pattern['pattern_desc']}\n"
                . "• Recent Dates Recorded:\n• {$evidenceSummary}\n\n"
                . "Our school utilizes gate scanning to ensure student campus safety and provide peace of mind to families. Scanning their school ID upon leaving ensures our system properly logs their safe departure from the premises.\n\n"
                . "We kindly ask for your assistance in reminding {$student->first_name} to always scan their ID at the gate terminal before heading home at dismissal. If you have any inquiries, please coordinate with our office or their class adviser.\n\n"
                . "Thank you for partnering with us to ensure student safety and accurate records.";
        }

        // Build Parent Template (Tagalog / Filipino - Advisory & Respectful)
        $parentTlSubject = "Paabiso sa Attendance ng Mag-aaral - Concepcion Integrated School ({$studentName})";
        $parentTlSalutation = "Magandang araw, {$guardianName},";
        if ($reasonType === 'late') {
            $parentTlBody = "Nais po naming ipaabot ang liham na ito mula sa Tanggapan ng School Administrator patungkol sa pang-araw-araw na attendance record ng inyong anak na si {$studentName} ({$gradeSection}).\n\n"
                . "Ayon sa aming automated IN/OUT attendance monitoring system, napansin po ang sumusunod na talaan ng pagdating sa paaralan:\n\n"
                . "• Uri ng Concern: {$concernTitle}\n"
                . "• Buod ng Obserbasyon: {$pattern['pattern_desc']}\n"
                . "• Mga Naitalang Araw:\n• {$evidenceSummary}\n\n"
                . "Ipinapaalam po namin ito bilang bahagi ng pagtutulungan ng paaralan at ng tahanan upang mapanatili ang kaligtasan at regular na pagpasok ng mga mag-aaral. Ang maagang pagdating ay napakahalaga upang hindi mahuli sa klase at mga paunang gawain sa umaga.\n\n"
                . "Magalang po naming hinihiling ang inyong tulong sa pagpapaalala kay {$student->first_name} sa inyong tahanan. Kung mayroon pong mga suliranin sa transportasyon o kalusugan na nais ninyong isangguni, mangyaring makipag-ugnayan po sa aming tanggapan o sa kanilang Class Adviser.\n\n"
                . "Maraming salamat po sa inyong patuloy na suporta at pakikipagtulungan para sa kinabukasan ng inyong anak.";
        } else {
            $parentTlBody = "Nais po naming ipaabot ang liham na ito mula sa Tanggapan ng School Administrator patungkol sa pang-araw-araw na attendance record ng inyong anak na si {$studentName} ({$gradeSection}).\n\n"
                . "Ayon sa aming automated IN/OUT attendance monitoring system, naitala po na nakapag-Time In ang inyong anak sa umaga ngunit hindi nakapag-scan ng Time Out sa paglabas ng paaralan:\n\n"
                . "• Uri ng Concern: {$concernTitle}\n"
                . "• Buod ng Obserbasyon: {$pattern['pattern_desc']}\n"
                . "• Mga Naitalang Araw:\n• {$evidenceSummary}\n\n"
                . "Mahalaga po ang gate scanner system upang masubaybayan ang kaligtasan ng mga bata at masigurong ligtas silang nakalabas ng campus patungo sa kanilang tahanan pagkatapos ng klase.\n\n"
                . "Magalang po naming hinihiling ang inyong tulong sa pagpapaalala kay {$student->first_name} na huwag kalimutang mag-scan ng kanilang ID sa gate bago umuwi. Kung mayroon po kayong katanungan, malugod po kayong makipag-ugnayan sa aming tanggapan o sa kanilang Class Adviser.\n\n"
                . "Maraming salamat po sa inyong patuloy na pagmamalasakit at pakikipagtulungan.";
        }

        return response()->json([
            'success' => true,
            'student' => [
                'enrollment_id' => $enrollment->id,
                'name' => $studentName,
                'first_name' => $student->first_name,
                'student_number' => $studentNumber,
                'grade_level' => $section?->grade_level ?? 'N/A',
                'section_name' => $section?->name ?? 'Unassigned',
                'grade_section' => $gradeSection,
            ],
            'advisor' => [
                'name' => $advisorName,
                'email' => $advisorEmail,
                'has_email' => $hasAdvisorEmail,
            ],
            'guardian' => [
                'name' => $guardianName,
                'relationship' => $guardian?->relationship ?? 'Guardian',
                'email' => $guardianEmail,
                'contact_number' => $guardian?->contact_number ?? 'N/A',
                'has_email' => $hasGuardianEmail,
            ],
            'concern' => [
                'reason_type' => $reasonType,
                'title' => $concernTitle,
                'pattern_type' => $pattern['pattern_type'],
                'pattern_desc' => $pattern['pattern_desc'],
                'badge_tone' => $pattern['badge_tone'],
                'count' => $count,
                'log_dates' => $logDatesFormatted,
            ],
            'templates' => [
                'advisor' => [
                    'subject' => $advisorSubject,
                    'salutation' => $advisorSalutation,
                    'message_body' => $advisorBody,
                ],
                'parent_en' => [
                    'subject' => $parentEnSubject,
                    'salutation' => $parentEnSalutation,
                    'message_body' => $parentEnBody,
                ],
                'parent_tl' => [
                    'subject' => $parentTlSubject,
                    'salutation' => $parentTlSalutation,
                    'message_body' => $parentTlBody,
                ],
            ],
        ]);
    }

    /**
     * Dispatch IN/OUT intervention notice(s) to Class Adviser, Parent/Guardian, or both.
     */
    public function sendIntervention(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id'          => ['required', 'integer', 'exists:enrollments,id'],
            'recipients'             => ['required', 'array', 'min:1'],
            'recipients.*'           => ['required', 'string', 'in:advisor,parent'],
            'reason_type'            => ['required', 'string', 'in:late,missing_out'],
            'parent_language'        => ['nullable', 'string', 'in:en,tl'],
            'admin_notes'            => ['nullable', 'string', 'max:2000'],
            'custom_advisor_message' => ['nullable', 'string', 'max:5000'],
            'custom_parent_message'  => ['nullable', 'string', 'max:5000'],
        ]);

        $enrollment = Enrollment::with([
            'student.guardian',
            'section.advisor.user',
        ])->findOrFail($validated['enrollment_id']);

        $student = $enrollment->student;
        $section = $enrollment->section;
        $advisorUser = $section?->advisor?->user;
        $guardian = $student->guardian;

        $reasonType = $validated['reason_type'];
        $parentLang = $validated['parent_language'] ?? 'en';
        $adminNotes = !empty($validated['admin_notes']) ? trim($validated['admin_notes']) : null;

        // Fetch recent matching attendance logs to build evidence and detect pattern
        if ($reasonType === 'late') {
            $logs = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'IN')
                ->whereHas('flagged_scans', fn ($q) => $q->where('flag_type', 'late_arrival'))
                ->orderByDesc('scan_time')
                ->take(10)
                ->get();
        } else {
            $inLogs = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'IN')
                ->orderByDesc('scan_time')
                ->take(30)
                ->get();
            $outDates = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->where('scan_type', 'OUT')
                ->pluck('scan_time')
                ->map(fn($t) => Carbon::parse($t)->toDateString())
                ->unique()
                ->all();
            $logs = $inLogs->filter(fn($l) => !in_array(Carbon::parse($l->scan_time)->toDateString(), $outDates, true))->take(10)->values();
        }

        $dates = $logs->pluck('scan_time')->map(fn($t) => Carbon::parse($t)->toDateString())->all();
        $pattern = $this->detectAttendancePattern($dates, $reasonType === 'missing_out' ? 'missing' : 'late');

        $logDatesFormatted = $logs->map(function ($log) use ($reasonType) {
            $dt = Carbon::parse($log->scan_time);
            return $reasonType === 'late'
                ? $dt->format('M d, Y') . " (Time In: " . $dt->format('h:i A') . ")"
                : $dt->format('M d, Y') . " (Time In: " . $dt->format('h:i A') . " — Missing OUT)";
        })->values()->all();

        $concernTitle = $reasonType === 'late' ? 'Late Arrival Monitoring' : 'Missing Time Out Departure Monitoring';

        $sentCount = 0;
        $sentRecipients = [];
        $skippedRecipients = [];

        // 1. Dispatch to Class Adviser if selected
        if (in_array('advisor', $validated['recipients'], true)) {
            $advisorEmail = $advisorUser?->email;
            if (empty($advisorEmail) || !filter_var($advisorEmail, FILTER_VALIDATE_EMAIL)) {
                $skippedRecipients[] = [
                    'recipient' => 'Class Adviser',
                    'name' => $advisorUser?->name ?? 'Unassigned Adviser',
                    'reason' => 'Class Adviser has no valid email address registered in the system.',
                ];
            } else {
                $advisorData = [
                    'recipient_type' => 'advisor',
                    'student_name'   => $student->full_name,
                    'student_number' => $student->student_number ?? 'STU-' . $student->id,
                    'grade_level'    => $section?->grade_level ?? 'N/A',
                    'section_name'   => $section?->name ?? 'Unassigned',
                    'advisor_name'   => $advisorUser->name,
                    'guardian_name'  => $guardian?->name ?? 'Parent / Guardian',
                    'reason_type'    => $reasonType,
                    'concern_title'  => $concernTitle,
                    'pattern_desc'   => $pattern['pattern_desc'],
                    'log_dates'      => $logDatesFormatted,
                    'language'       => 'en',
                    'subject'        => "MEMORANDUM: IN/OUT Attendance Monitoring - {$student->full_name}",
                    'salutation'     => "Dear {$advisorUser->name},",
                    'message_body'   => !empty($validated['custom_advisor_message'])
                        ? $validated['custom_advisor_message']
                        : "Please conduct a morning advisory check-in with {$student->full_name} regarding their {$concernTitle} ({$pattern['pattern_desc']}).",
                ];

                try {
                    Mail::to($advisorEmail)->send(new AdminInOutInterventionMail($advisorData, 'advisor', $adminNotes));
                    $sentCount++;
                    $sentRecipients[] = [
                        'recipient' => 'Class Adviser',
                        'name'      => $advisorUser->name,
                        'email'     => $advisorEmail,
                    ];
                } catch (\Throwable $e) {
                    Log::error("Failed sending IN/OUT intervention to Class Adviser ({$advisorEmail}): " . $e->getMessage());
                    $skippedRecipients[] = [
                        'recipient' => 'Class Adviser',
                        'name'      => $advisorUser->name,
                        'reason'    => 'Mail server error while sending to adviser: ' . $e->getMessage(),
                    ];
                }
            }
        }

        // 2. Dispatch to Parent / Guardian if selected
        if (in_array('parent', $validated['recipients'], true)) {
            $guardianEmail = $guardian?->email;
            if (empty($guardianEmail) || !filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
                $skippedRecipients[] = [
                    'recipient' => 'Parent / Guardian',
                    'name' => $guardian?->name ?? 'Parent / Guardian',
                    'reason' => 'Parent/Guardian has no valid email address registered on file.',
                ];
            } else {
                $parentData = [
                    'recipient_type' => 'parent',
                    'student_name'   => $student->full_name,
                    'student_number' => $student->student_number ?? 'STU-' . $student->id,
                    'grade_level'    => $section?->grade_level ?? 'N/A',
                    'section_name'   => $section?->name ?? 'Unassigned',
                    'advisor_name'   => $advisorUser?->name ?? 'Class Adviser',
                    'guardian_name'  => $guardian->name,
                    'reason_type'    => $reasonType,
                    'concern_title'  => $concernTitle,
                    'pattern_desc'   => $pattern['pattern_desc'],
                    'log_dates'      => $logDatesFormatted,
                    'language'       => $parentLang,
                    'subject'        => $parentLang === 'tl'
                        ? "Paabiso sa Attendance ng Mag-aaral - Concepcion Integrated School ({$student->full_name})"
                        : "Concepcion Integrated School - Student Attendance Advisory ({$student->full_name})",
                    'salutation'     => ($parentLang === 'tl' ? "Magandang araw, " : "Dear ") . $guardian->name . ",",
                    'message_body'   => !empty($validated['custom_parent_message'])
                        ? $validated['custom_parent_message']
                        : "Notice regarding attendance pattern for {$student->full_name}.",
                ];

                try {
                    Mail::to($guardianEmail)->send(new AdminInOutInterventionMail($parentData, 'parent', $adminNotes));
                    $sentCount++;
                    $sentRecipients[] = [
                        'recipient' => 'Parent / Guardian',
                        'name'      => $guardian->name,
                        'email'     => $guardianEmail,
                    ];
                } catch (\Throwable $e) {
                    Log::error("Failed sending IN/OUT intervention to Parent/Guardian ({$guardianEmail}): " . $e->getMessage());
                    $skippedRecipients[] = [
                        'recipient' => 'Parent / Guardian',
                        'name'      => $guardian->name,
                        'reason'    => 'Mail server error while sending to parent: ' . $e->getMessage(),
                    ];
                }
            }
        }

        // Record follow-up in RiskFollowUp if advisor teacher profile exists
        if ($sentCount > 0 && $section && $section->advisor_id) {
            try {
                $recipSummary = implode(' & ', array_column($sentRecipients, 'recipient'));
                RiskFollowUp::create([
                    'enrollment_id'  => $enrollment->id,
                    'teacher_id'     => $section->advisor_id,
                    'follow_up_date' => now()->toDateString(),
                    'intervention'   => "School Admin IN/OUT Intervention ({$concernTitle})",
                    'notes'          => "Administrative notice dispatched to: {$recipSummary}.\nConcern: {$concernTitle} ({$pattern['pattern_desc']})"
                        . ($adminNotes ? "\nAdmin Remarks: {$adminNotes}" : ''),
                    'status'         => 'Completed',
                ]);
            } catch (\Throwable $e) {
                Log::warning("Could not create RiskFollowUp entry for IN/OUT intervention: " . $e->getMessage());
            }
        }

        if ($sentCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No interventions could be dispatched. Review recipient status.',
                'sent_count' => 0,
                'skipped_count' => count($skippedRecipients),
                'sent_recipients' => [],
                'skipped_recipients' => $skippedRecipients,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully dispatched IN/OUT intervention to {$sentCount} recipient(s).",
            'sent_count' => $sentCount,
            'skipped_count' => count($skippedRecipients),
            'sent_recipients' => $sentRecipients,
            'skipped_recipients' => $skippedRecipients,
        ]);
    }
}

