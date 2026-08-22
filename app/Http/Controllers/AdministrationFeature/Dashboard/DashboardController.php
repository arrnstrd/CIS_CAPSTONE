<?php

namespace App\Http\Controllers\AdministrationFeature\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user && $user->isProtectedAdmin()) {
            $totalUsers = \App\Models\User::count();
            $activeAccounts = \App\Models\User::where('status', 'active')->count();
            $pendingInvitations = \App\Models\User::where('status', 'pending')->count();
            $inactiveAccounts = \App\Models\User::where('status', 'inactive')->count();

            $pendingUsers = \App\Models\User::where('status', 'pending')->latest('created_at')->take(10)->get();

            $recentLoginLogs = \App\Models\LoginLog::with('user')
                ->latest('attempted_at')
                ->take(6)
                ->get();

            $recentActivities = \App\Models\AdminActivityLog::with('actor')
                ->latest('created_at')
                ->take(6)
                ->get();

            return view('admin-modules.super-admin-dashboard', compact(
                'totalUsers',
                'activeAccounts',
                'pendingInvitations',
                'inactiveAccounts',
                'pendingUsers',
                'recentLoginLogs',
                'recentActivities'
            ));
        }

        $today = Carbon::today();
        $now = now();
        $intervalMinutes = 15;

        $todayEnd = $today->copy()->setTime(18, 0);
        $chartEnd = $now->greaterThan($todayEnd) ? $now->copy() : $todayEnd;
        $chartStart = $today->copy()->setTime(6, 0);

        $todayLogs = AttendanceLog::query()
            ->with(['enrollment.student', 'enrollment.section', 'flagged_scans'])
            ->withoutExcessScanFlags()
            ->todayOnly()
            ->orderBy('scan_time')
            ->get();

        $weeklyWindowStart = $now->copy()->startOfWeek(Carbon::MONDAY)->subWeeks(5)->startOfDay();
        $weeklyWindowEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $weeklyLogs = AttendanceLog::query()
            ->with(['enrollment.student', 'enrollment.section', 'flagged_scans'])
            ->withoutExcessScanFlags()
            ->whereBetween('scan_time', [$weeklyWindowStart, $weeklyWindowEnd])
            ->orderBy('scan_time')
            ->get();

        $studentCount = Student::count();
        $teacherCount = Teacher::query()->where('status', 'active')->count();
        $activeSchoolYear = SchoolYear::query()->active()->first();
        $activeEnrollments = Enrollment::query()
            ->where('status', 'active')
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('school_year_id', $activeSchoolYear->id);
            })
            ->count();

        $activeEnrollmentRecords = Enrollment::query()
            ->where('status', 'active')
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('school_year_id', $activeSchoolYear->id);
            })
            ->with(['section:id,grade_level'])
            ->get(['id', 'section_id', 'school_year_id', 'status']);

        $totalScansToday = $todayLogs->count();
        $timeInToday = $todayLogs->where('scan_type', 'IN')->count();
        $timeOutToday = $todayLogs->where('scan_type', 'OUT')->count();
        $lateArrivalsToday = $todayLogs->filter(function (AttendanceLog $log) {
            return $log->flagged_scans->contains('flag_type', 'late_arrival');
        })->count();

        $gradePalette = [
            '#2563eb',
            '#22c55e',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#06b6d4',
            '#14b8a6',
            '#ec4899',
            '#f97316',
            '#84cc16',
            '#0ea5e9',
            '#6366f1',
        ];

        $gradeDistributionCounts = collect(range(1, 12))->mapWithKeys(fn ($grade) => [
            (string) $grade => 0,
        ])->all();
        $gradeDistributionCounts['unknown'] = 0;

        $activeEnrollmentRecords->each(function (Enrollment $enrollment) use (&$gradeDistributionCounts) {
            $grade = $enrollment->section?->grade_level;
            $key = filled($grade) && array_key_exists((string) $grade, $gradeDistributionCounts)
                ? (string) $grade
                : 'unknown';

            $gradeDistributionCounts[$key]++;
        });

        $gradeDistributionTotal = max(array_sum($gradeDistributionCounts), 1);
        $gradeDistribution = collect($gradeDistributionCounts)
            ->filter(fn ($count) => $count > 0)
            ->map(function ($count, $gradeKey) use ($gradeDistributionTotal, $gradePalette) {
                $label = $gradeKey === 'unknown' ? 'Unclassified' : 'Grade ' . $gradeKey;
                $index = $gradeKey === 'unknown' ? count($gradePalette) - 1 : ((int) $gradeKey - 1) % count($gradePalette);

                return [
                    'key' => $gradeKey,
                    'label' => $label,
                    'count' => $count,
                    'percentage' => round(($count / $gradeDistributionTotal) * 100, 1),
                    'color' => $gradePalette[$index],
                ];
            })
            ->values();

        $departmentLevels = [
            'elementary' => [
                'label' => 'Elementary',
                'color' => '#22c55e',
            ],
            'highschool' => [
                'label' => 'High School',
                'color' => '#2563eb',
            ],
            'senior_high_school' => [
                'label' => 'Senior High School',
                'color' => '#f59e0b',
            ],
            'unknown' => [
                'label' => 'Unclassified',
                'color' => '#6b7280',
            ],
        ];

        $levelTotals = array_fill_keys(array_keys($departmentLevels), 0);

        $weeklyGradeLevels = collect(range(1, 12))->mapWithKeys(function ($grade) use ($gradePalette) {
            return [
                (string) $grade => [
                    'label' => 'Grade ' . $grade,
                    'color' => $gradePalette[($grade - 1) % count($gradePalette)],
                ],
            ];
        })->all();
        $weeklyGradeLevels['unknown'] = [
            'label' => 'Unclassified',
            'color' => '#6b7280',
        ];

        $weeklyScanBuckets = collect(range(0, 5))->map(function ($offset) use ($weeklyWindowStart, $weeklyLogs, $weeklyGradeLevels) {
            $weekStart = $weeklyWindowStart->copy()->addWeeks($offset)->startOfWeek(Carbon::MONDAY)->startOfDay();
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            $bucketLogs = $weeklyLogs->filter(function (AttendanceLog $log) use ($weekStart, $weekEnd) {
                return $log->scan_time
                    && $log->scan_time->gte($weekStart)
                    && $log->scan_time->lte($weekEnd);
            });

            $gradeCounts = array_fill_keys(array_keys($weeklyGradeLevels), 0);

            $bucketLogs->each(function (AttendanceLog $log) use (&$gradeCounts, $weeklyGradeLevels) {
                $grade = $log->enrollment?->section?->grade_level;
                $key = filled($grade) && array_key_exists((string) $grade, $weeklyGradeLevels)
                    ? (string) $grade
                    : 'unknown';

                $gradeCounts[$key]++;
            });

            return [
                'label' => $weekStart->format('M j'),
                'range' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'total' => array_sum($gradeCounts),
                'grades' => $gradeCounts,
            ];
        })->values();

        $scanBuckets = [];
        $cursor = $chartStart->copy();

        while ($cursor->lt($chartEnd)) {
            $bucketEnd = $cursor->copy()->addMinutes($intervalMinutes);
            $bucketLogs = $todayLogs->filter(function (AttendanceLog $log) use ($cursor, $bucketEnd) {
                return $log->scan_time
                    && $log->scan_time->gte($cursor)
                    && $log->scan_time->lt($bucketEnd);
            });

            $timeIn = $bucketLogs->where('scan_type', 'IN')->count();
            $timeOut = $bucketLogs->where('scan_type', 'OUT')->count();
            $levelCounts = array_fill_keys(array_keys($departmentLevels), 0);

            $bucketLogs->each(function (AttendanceLog $log) use (&$levelCounts, &$levelTotals, $departmentLevels) {
                $level = $log->enrollment?->section?->level ?? 'unknown';
                if (! array_key_exists($level, $departmentLevels)) {
                    $level = 'unknown';
                }

                $levelCounts[$level]++;
                $levelTotals[$level]++;
            });

            $scanBuckets[] = [
                'label' => $cursor->format('g:i A'),
                'range' => $cursor->format('g:i A') . ' - ' . $bucketEnd->format('g:i A'),
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'total' => $timeIn + $timeOut,
                'levels' => $levelCounts,
            ];

            $cursor = $bucketEnd;
        }

        $maxBucketTotal = max(collect($scanBuckets)->pluck('total')->max() ?? 0, 1);
        $peakBucket = collect($scanBuckets)->sortByDesc('total')->first();
        $weeklyTotalScans = $weeklyScanBuckets->sum('total');
        $weeklyPeakBucket = $weeklyScanBuckets->sortByDesc('total')->first();
        $weeklyMaxBucketTotal = max($weeklyScanBuckets->pluck('total')->max() ?? 0, 1);
        $weeklyAverage = round($weeklyScanBuckets->avg('total') ?? 0, 1);

        $latestScans = $todayLogs
            ->sortByDesc('scan_time')
            ->take(5)
            ->map(function (AttendanceLog $log) {
                $student = $log->enrollment?->student;

                return [
                    'scan_date' => $log->scan_time?->format('Y-m-d'),
                    'student_name' => trim(
                        ($student?->first_name ?? '') . ' ' .
                            ($student?->last_name ?? '')
                    ) ?: 'Unknown',
                    'student_number' => $student?->student_number ?? '-',
                    'grade_level' => $log->enrollment?->section?->grade_level ?? '-',
                    'section_name' => $log->enrollment?->section?->name ?? '-',
                    'scan_type' => $log->scan_type,
                    'session_type' => $log->session_type ?? '-',
                    'scan_time' => $log->scan_time?->format('h:i A'),
                    'flag_types' => $log->flagged_scans
                        ->pluck('flag_type')
                        ->filter()
                        ->unique()
                        ->join(', '),
                ];
            })
            ->values();

        $dashboardCards = [
            [
                'label' => 'Students',
                'value' => $studentCount,
                'icon' => 'fas fa-user-graduate',
                'href' => route('student-management.index'),
                'tone' => 'primary',
                'hint' => 'Open student records',
            ],
            [
                'label' => 'Teachers',
                'value' => $teacherCount,
                'icon' => 'fas fa-chalkboard-teacher',
                'href' => route('teachers.index'),
                'tone' => 'success',
                'hint' => 'Open teacher records',
            ],
            [
                'label' => 'Active Enrollments',
                'value' => $activeEnrollments,
                'icon' => 'fas fa-file-signature',
                'href' => route('student-management.index'),
                'tone' => 'indigo',
                'hint' => 'Open enrollment records',
            ],
            [
                'label' => 'Scans Today',
                'value' => $totalScansToday,
                'icon' => 'fas fa-qrcode',
                'href' => route('time-in-time-out-history.index'),
                'tone' => 'dark',
                'hint' => 'Open attendance logs',
            ],
        ];

        $scanSplit = [
            'time_in' => $timeInToday,
            'time_out' => $timeOutToday,
            'total' => max($totalScansToday, 1),
        ];

        return view('admin-modules.dashboard', compact(
            'dashboardCards',
            'scanBuckets',
            'maxBucketTotal',
            'peakBucket',
            'latestScans',
            'scanSplit',
            'weeklyScanBuckets',
            'weeklyMaxBucketTotal',
            'weeklyPeakBucket',
            'weeklyTotalScans',
            'weeklyAverage',
            'activeEnrollments',
            'studentCount',
            'teacherCount',
            'totalScansToday',
            'timeInToday',
            'timeOutToday',
            'lateArrivalsToday',
            'departmentLevels',
            'levelTotals',
            'weeklyGradeLevels',
            'gradeDistribution',
            'gradeDistributionTotal'
        ));
    }
}
