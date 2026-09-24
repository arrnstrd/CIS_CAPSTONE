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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        // ── 2. Progressive Scope Filters ──────────────────────────────────
        $academicLevel = $this->normalizeAcademicLevel($request->input('academic_level'));
        $gradeLevels   = $this->normalizeArrayInput($request->input('grade_levels', $request->input('grade_level')));
        $sectionIds    = $this->normalizeArrayInput($request->input('section_ids', $request->input('section_id')));
        $subjectIds    = $this->normalizeArrayInput($request->input('subject_ids', $request->input('subject_id')));
        $studentId     = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $term          = $request->filled('term') ? (int) $request->input('term') : null;

        // ── 3. School Year Resolution ─────────────────────────────────────
        $activeSchoolYear = $request->filled('school_year_id')
            ? SchoolYear::find($request->input('school_year_id'))
            : SchoolYear::query()->active()->first();

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
            'activeSchoolYear'
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
}
