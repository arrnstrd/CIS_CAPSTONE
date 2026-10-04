<?php

namespace App\Services\Grading;

use App\Models\Enrollment;
use App\Models\TermGrade;
use App\Models\GradingPeriod;
use App\Models\TeachingAssignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\StudentAssessmentScore;
use App\Models\Assessment;
use App\Services\Notification\NotificationService;

class RiskScoreService
{
    /**
     * Calculate risk score for a single student based on 4 weighted indicators.
     *
     * Indicators:
     * - Low Grade: +30
     * - Missing Grades: +25
     * - Low Attendance: +25
     * - Declining Performance: +20
     */
    public function calculateRiskScore(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): array {
        $indicators = [
            'low_grade' => $this->checkLowGrade(
                $enrollment,
                $teachingAssignment,
                $currentPeriod
            ),

            'missing_grades' => $this->checkMissingGrades(
                $enrollment,
                $teachingAssignment,
                $currentPeriod
            ),

            'low_attendance' => $this->checkLowAttendance(
                $enrollment,
                $teachingAssignment,
                $currentPeriod
            ),

            'declining_performance' => $this->checkDecliningPerformance(
                $enrollment,
                $teachingAssignment,
                $currentPeriod
            ),
        ];

        $riskScore = $this->calculateScoreFromIndicators($indicators);
        $riskLevel = $this->determineRiskLevel($riskScore);

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'indicators' => $indicators,
        ];
    }

    /**
     * Evaluate a student's risk level and notify the teacher if risk has entered Moderate or High,
     * while strictly avoiding duplicate notifications for the same risk level.
     */
    public function evaluateAndNotifyRiskChange(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $period
    ): ?array {
        $teacher = $teachingAssignment->teacher;
        $user = $teacher?->user;

        if (! $user) {
            return null;
        }

        $riskData = $this->calculateRiskScore($enrollment, $teachingAssignment, $period);
        $currentLevel = $riskData['risk_level'];

        // Only notify for Moderate or High risk
        if (! in_array($currentLevel, ['Moderate', 'High'], true)) {
            return $riskData;
        }

        // Check latest at-risk notification for this student, teaching assignment, and term
        $latestNotif = $user->notifications()
            ->latest()
            ->get()
            ->first(function ($n) use ($enrollment, $teachingAssignment, $period) {
                $data = $n->data;
                return ($data['category'] ?? null) === 'at_risk'
                    && (int) ($data['data']['enrollment_id'] ?? 0) === (int) $enrollment->id
                    && (int) ($data['data']['teaching_assignment_id'] ?? 0) === (int) $teachingAssignment->id
                    && (int) ($data['data']['grading_period_id'] ?? 0) === (int) $period->id;
            });

        $previousRiskLevel = $latestNotif
            ? ($latestNotif->data['data']['risk_level'] ?? null)
            : null;

        // Prevent duplicate alerts:
        // - If current level matches previous level, do not re-notify.
        // - If current is Moderate but previous was High, do not re-notify.
        if ($previousRiskLevel === $currentLevel) {
            return $riskData;
        }

        if ($currentLevel === 'Moderate' && $previousRiskLevel === 'High') {
            return $riskData;
        }

        $studentName = $enrollment->student
            ? trim($enrollment->student->first_name . ' ' . $enrollment->student->last_name)
            : 'Student';
        $sectionName = $teachingAssignment->section?->name ?? 'Class';

        NotificationService::send(
            $user,
            NotificationService::CATEGORY_AT_RISK,
            'Student At Risk',
            "{$studentName} is now classified as {$currentLevel} academic risk in Section {$sectionName}.",
            [
                'url' => route('teacher.grading-system.at-risk.show', ['enrollmentId' => $enrollment->id]),
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'teaching_assignment_id' => $teachingAssignment->id,
                'section_id' => $teachingAssignment->section_id,
                'grading_period_id' => $period->id,
                'risk_score' => $riskData['risk_score'],
                'risk_level' => $currentLevel,
                'indicators' => $riskData['indicators'],
            ]
        );

        return $riskData;
    }

    /**
     * Calculate risk scores for all active students in a teaching assignment.
     * Batched at the section level to eliminate N+1 database queries.
     */
    public function calculateRiskScoresForClass(
        TeachingAssignment $teachingAssignment,
        GradingPeriod $period
    ): array {
        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();

        $enrollments = Enrollment::where(
            'section_id',
            $teachingAssignment->section_id
        )
            ->where('status', 'active')
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->with('student')
            ->get();

        if ($enrollments->isEmpty()) {
            return [
                'risk_scores' => [],
                'stats' => ['total' => 0, 'high' => 0, 'moderate' => 0, 'low' => 0],
            ];
        }

        $enrollmentIds = $enrollments->pluck('id')->all();

        // 1. Batch fetch current term grades for all enrollments in this teaching assignment
        $currentGrades = TermGrade::whereIn('enrollment_id', $enrollmentIds)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $period->id)
            ->get()
            ->keyBy('enrollment_id');

        // 2. Batch fetch previous period & previous term grades
        $previousPeriod = $this->getPreviousPeriod($period);
        $previousGrades = $previousPeriod
            ? TermGrade::whereIn('enrollment_id', $enrollmentIds)
                ->where('teaching_assignment_id', $teachingAssignment->id)
                ->where('grading_period_id', $previousPeriod->id)
                ->get()
                ->keyBy('enrollment_id')
            : collect();

        // 3. Batch fetch active assessments and existing student scores
        $assessments = Assessment::where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $period->id)
            ->where('status', 'active')
            ->get();
        $assessmentIds = $assessments->pluck('id')->all();
        $totalAssessments = count($assessmentIds);

        $scoresByEnrollment = [];
        if ($totalAssessments > 0) {
            $scores = StudentAssessmentScore::whereIn('assessment_id', $assessmentIds)
                ->whereIn('enrollment_id', $enrollmentIds)
                ->whereNotNull('score')
                ->get(['enrollment_id', 'assessment_id', 'score']);

            foreach ($scores as $s) {
                $scoresByEnrollment[$s->enrollment_id][$s->assessment_id] = $s->score;
            }
        }

        // 4. Batch fetch attendance rates for all enrollments in this section
        $attendanceRates = $this->calculateBatchAttendanceRates($enrollmentIds, $period);

        $riskScores = [];
        $stats = [
            'total' => 0,
            'high' => 0,
            'moderate' => 0,
            'low' => 0,
        ];

        foreach ($enrollments as $enrollment) {
            $eId = $enrollment->id;

            // Indicator 1: Low Grade (< 75)
            $currGrade = $currentGrades->get($eId);
            $lowGrade = ($currGrade && $currGrade->transmuted_grade !== null)
                ? ((float) $currGrade->transmuted_grade < 75)
                : false;

            // Indicator 2: Missing Grades (any active assessment has no recorded score)
            $missingGrades = false;
            if ($totalAssessments > 0) {
                $studentScores = $scoresByEnrollment[$eId] ?? [];
                if (count($studentScores) < $totalAssessments) {
                    $missingGrades = true;
                }
            }

            // Indicator 3: Low Attendance (< 85%)
            $attRate = $attendanceRates[$eId] ?? 0.0;
            $lowAttendance = $attRate < 85.0;

            // Indicator 4: Declining Performance (current < previous)
            $decliningPerformance = false;
            if ($currGrade && $currGrade->transmuted_grade !== null && $previousPeriod) {
                $prevGrade = $previousGrades->get($eId);
                if ($prevGrade && $prevGrade->transmuted_grade !== null) {
                    $decliningPerformance = ((float) $currGrade->transmuted_grade < (float) $prevGrade->transmuted_grade);
                }
            }

            $indicators = [
                'low_grade' => $lowGrade,
                'missing_grades' => $missingGrades,
                'low_attendance' => $lowAttendance,
                'declining_performance' => $decliningPerformance,
            ];

            $riskScore = $this->calculateScoreFromIndicators($indicators);
            $riskLevel = $this->determineRiskLevel($riskScore);
            $riskLevelKey = strtolower($riskLevel);

            $riskScores[] = [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student?->full_name ?? 'Unknown Student',
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'indicators' => $indicators,
            ];

            $stats['total']++;

            if (isset($stats[$riskLevelKey])) {
                $stats[$riskLevelKey]++;
            }
        }

        return [
            'risk_scores' => $riskScores,
            'stats' => $stats,
        ];
    }

    /**
     * Batch calculate attendance rates for multiple enrollments in a grading period.
     */
    public function calculateBatchAttendanceRates(array $enrollmentIds, GradingPeriod $period): array
    {
        if (empty($enrollmentIds)) {
            return [];
        }

        if (!$period->start_date || !$period->end_date) {
            $earliestScans = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->selectRaw('enrollment_id, MIN(scan_time) as earliest_scan')
                ->groupBy('enrollment_id')
                ->pluck('earliest_scan', 'enrollment_id');

            $earliestVers = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                ->whereIn('status', [
                    AttendanceVerification::STATUS_PRESENT,
                    AttendanceVerification::STATUS_LATE,
                    AttendanceVerification::STATUS_EXCUSED,
                ])
                ->selectRaw('enrollment_id, MIN(attendance_date) as earliest_ver')
                ->groupBy('enrollment_id')
                ->pluck('earliest_ver', 'enrollment_id');

            $qrLogs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->where('scan_type', 'IN')
                ->selectRaw('enrollment_id, DATE(scan_time) as attendance_date')
                ->distinct()
                ->get()
                ->groupBy('enrollment_id');

            $verifiedLogs = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                ->whereIn('status', [
                    AttendanceVerification::STATUS_PRESENT,
                    AttendanceVerification::STATUS_LATE,
                    AttendanceVerification::STATUS_EXCUSED,
                ])
                ->select('enrollment_id', 'attendance_date')
                ->distinct()
                ->get()
                ->groupBy('enrollment_id');

            $end = now()->startOfDay();
            $rates = [];

            foreach ($enrollmentIds as $id) {
                $earliestScan = $earliestScans->get($id);
                $earliestVerification = $earliestVers->get($id);

                $startDates = collect([$earliestScan, $earliestVerification])
                    ->filter()
                    ->map(fn($date) => \Carbon\Carbon::parse($date)->startOfDay());

                if ($startDates->isEmpty()) {
                    $rates[$id] = 0.0;
                    continue;
                }

                $startDate = $startDates->sortBy(fn($d) => $d->timestamp)->first();

                $schoolDaysCount = 0;
                $cursor = $startDate->copy();
                while ($cursor->lte($end)) {
                    if (!$cursor->isWeekend()) {
                        $schoolDaysCount++;
                    }
                    $cursor->addDay();
                }

                if ($schoolDaysCount === 0) {
                    $rates[$id] = 0.0;
                    continue;
                }

                $qrDates = $qrLogs->get($id, collect())->pluck('attendance_date');
                $verDates = $verifiedLogs->get($id, collect())->pluck('attendance_date');

                $presentDaysCount = $qrDates
                    ->merge($verDates)
                    ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
                    ->unique()
                    ->count();

                $rates[$id] = round(($presentDaysCount / $schoolDaysCount) * 100, 1);
            }

            return $rates;
        }

        $startDate = \Carbon\Carbon::parse($period->start_date)->startOfDay();
        $endDate = \Carbon\Carbon::parse($period->end_date)->startOfDay();

        $schoolDaysCount = 0;
        $cursor = $startDate->copy();
        while ($cursor->lt($endDate)) {
            if (!$cursor->isWeekend()) {
                $schoolDaysCount++;
            }
            $cursor->addDay();
        }

        if ($schoolDaysCount === 0) {
            return array_fill_keys($enrollmentIds, 0.0);
        }

        // Batch QR attendance scans
        $qrLogs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->where('scan_type', 'IN')
            ->where('scan_time', '>=', $startDate)
            ->where('scan_time', '<', $endDate)
            ->selectRaw('enrollment_id, DATE(scan_time) as attendance_date')
            ->distinct()
            ->get();

        // Batch Teacher-verified attendance dates
        $verifiedLogs = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('attendance_date', [
                $startDate->toDateString(),
                $endDate->copy()->subDay()->toDateString(),
            ])
            ->whereIn('status', [
                AttendanceVerification::STATUS_PRESENT,
                AttendanceVerification::STATUS_LATE,
                AttendanceVerification::STATUS_EXCUSED,
            ])
            ->select('enrollment_id', 'attendance_date')
            ->distinct()
            ->get();

        $qrDatesByEnrollment = $qrLogs->groupBy('enrollment_id');
        $verifiedDatesByEnrollment = $verifiedLogs->groupBy('enrollment_id');

        $rates = [];
        foreach ($enrollmentIds as $id) {
            $qrDates = $qrDatesByEnrollment->get($id, collect())->pluck('attendance_date');
            $verDates = $verifiedDatesByEnrollment->get($id, collect())->pluck('attendance_date');

            $presentDaysCount = $qrDates
                ->merge($verDates)
                ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
                ->unique()
                ->count();

            $rates[$id] = round(($presentDaysCount / $schoolDaysCount) * 100, 1);
        }

        return $rates;
    }

    /**
     * Check if student has a low grade for the current term.
     */
    private function checkLowGrade(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): bool {
        $grade = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignment->id
            )
            ->where(
                'grading_period_id',
                $currentPeriod->id
            )
            ->first();

        if (!$grade || $grade->transmuted_grade === null) {
            return false;
        }

        return (float) $grade->transmuted_grade < 75;
    }

    /**
     * Check if student has missing assessment scores for the current term.
     */
    private function checkMissingGrades(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): bool {
        $assessments = Assessment::where(
            'teaching_assignment_id',
            $teachingAssignment->id
        )
            ->where(
                'grading_period_id',
                $currentPeriod->id
            )
            ->where(
                'status',
                'active'
            )
            ->get();

        if ($assessments->isEmpty()) {
            return false;
        }

        foreach ($assessments as $assessment) {
            $score = StudentAssessmentScore::where(
                'enrollment_id',
                $enrollment->id
            )
                ->where(
                    'assessment_id',
                    $assessment->id
                )
                ->first();

            if (!$score || $score->score === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if student's attendance rate is below 85%.
     */
    private function checkLowAttendance(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): bool {
        $attendanceRate = $this->calculateAttendanceRate(
            $enrollment->id,
            $currentPeriod->id
        );

        return $attendanceRate < 85.0;
    }

    /**
     * Check if the student's grade declined compared with the previous term.
     */
    private function checkDecliningPerformance(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): bool {
        $currentGrade = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignment->id
            )
            ->where(
                'grading_period_id',
                $currentPeriod->id
            )
            ->first();

        if (
            !$currentGrade ||
            $currentGrade->transmuted_grade === null
        ) {
            return false;
        }

        $previousPeriod = $this->getPreviousPeriod($currentPeriod);

        if (!$previousPeriod) {
            return false;
        }

        $previousGrade = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignment->id
            )
            ->where(
                'grading_period_id',
                $previousPeriod->id
            )
            ->first();

        if (
            !$previousGrade ||
            $previousGrade->transmuted_grade === null
        ) {
            return false;
        }

        return (float) $currentGrade->transmuted_grade
            < (float) $previousGrade->transmuted_grade;
    }

    /**
     * Get the previous active term.
     */
    private function getPreviousPeriod(
        GradingPeriod $currentPeriod
    ): ?GradingPeriod {
        static $previousPeriodCache = [];

        if (array_key_exists($currentPeriod->id, $previousPeriodCache)) {
            return $previousPeriodCache[$currentPeriod->id];
        }

        $previousPeriodCache[$currentPeriod->id] = GradingPeriod::where(
            'is_active',
            true
        )
            ->where(
                'period_type',
                'trimester'
            )
            ->where(
                'sequence',
                '<',
                $currentPeriod->sequence
            )
            ->where(
                'sequence',
                '<=',
                3
            )
            ->orderBy(
                'sequence',
                'desc'
            )
            ->first();

        return $previousPeriodCache[$currentPeriod->id];
    }

    /**
     * Calculate attendance rate for a student and term.
     *
     * Attendance is based on BOTH:
     * - QR IN scans from attendance_logs
     * - Teacher verification records from attendance_verifications
     *
     * Multiple records on the same date count as ONE attendance day.
     */
    private function calculateAttendanceRate(
        int $enrollmentId,
        int $gradingPeriodId
    ): float {
        $gradingPeriod = GradingPeriod::find($gradingPeriodId);

        if (!$gradingPeriod) {
            return 0.0;
        }

        if (
            $gradingPeriod->start_date &&
            $gradingPeriod->end_date
        ) {
            return $this->calculateTermAttendanceRate(
                $enrollmentId,
                $gradingPeriod
            );
        }

        return $this->calculateLifetimeAttendanceRate(
            $enrollmentId
        );
    }

    /**
     * Calculate attendance rate using the specific term date range.
     *
     * Weekdays are treated as school days.
     *
     * A day is considered present when either:
     * - there is a QR IN scan, OR
     * - there is a teacher verification with Present/Late/Excused status.
     */
    private function calculateTermAttendanceRate(
        int $enrollmentId,
        GradingPeriod $gradingPeriod
    ): float {
        $startDate = \Carbon\Carbon::parse(
            $gradingPeriod->start_date
        )->startOfDay();

        $endDate = \Carbon\Carbon::parse(
            $gradingPeriod->end_date
        )->startOfDay();

        $schoolDaysCount = 0;

        $cursor = $startDate->copy();

        while ($cursor->lt($endDate)) {
            if (!$cursor->isWeekend()) {
                $schoolDaysCount++;
            }

            $cursor->addDay();
        }

        if ($schoolDaysCount === 0) {
            return 0.0;
        }

        /*
         * QR attendance dates.
         */
        $qrDates = AttendanceLog::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'scan_type',
                'IN'
            )
            ->where(
                'scan_time',
                '>=',
                $startDate
            )
            ->where(
                'scan_time',
                '<',
                $endDate
            )
            ->selectRaw(
                'DATE(scan_time) as attendance_date'
            )
            ->distinct()
            ->pluck('attendance_date');

        /*
         * Teacher-verified attendance dates.
         *
         * Present, Late, and Excused are considered attended days.
         */
        $verifiedDates = AttendanceVerification::where(
            'enrollment_id',
            $enrollmentId
        )
            ->whereBetween(
                'attendance_date',
                [
                    $startDate->toDateString(),
                    $endDate->copy()->subDay()->toDateString(),
                ]
            )
            ->whereIn(
                'status',
                [
                    AttendanceVerification::STATUS_PRESENT,
                    AttendanceVerification::STATUS_LATE,
                    AttendanceVerification::STATUS_EXCUSED,
                ]
            )
            ->select('attendance_date')
            ->distinct()
            ->pluck('attendance_date');

        /*
         * Merge both sources and count each date only once.
         */
        $presentDaysCount = $qrDates
            ->merge($verifiedDates)
            ->map(
                fn ($date) => \Carbon\Carbon::parse($date)->toDateString()
            )
            ->unique()
            ->count();

        return round(
            ($presentDaysCount / $schoolDaysCount) * 100,
            1
        );
    }

    /**
     * Calculate lifetime-to-date attendance rate.
     *
     * Used when the active term does not have official
     * start/end dates configured.
     *
     * If there are no QR scans, teacher verification dates
     * are used as the attendance starting point.
     */
    private function calculateLifetimeAttendanceRate(
        int $enrollmentId
    ): float {
        /*
         * Find the earliest QR IN scan.
         */
        $earliestScan = AttendanceLog::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'scan_type',
                'IN'
            )
            ->min('scan_time');

        /*
         * Find the earliest teacher verification that represents
         * an attended day.
         */
        $earliestVerification = AttendanceVerification::where(
            'enrollment_id',
            $enrollmentId
        )
            ->whereIn(
                'status',
                [
                    AttendanceVerification::STATUS_PRESENT,
                    AttendanceVerification::STATUS_LATE,
                    AttendanceVerification::STATUS_EXCUSED,
                ]
            )
            ->min('attendance_date');

        /*
         * Use whichever valid attendance source started earlier.
         */
        $startDates = collect([
            $earliestScan,
            $earliestVerification,
        ])
            ->filter()
            ->map(
                fn ($date) => \Carbon\Carbon::parse($date)->startOfDay()
            );

        if ($startDates->isEmpty()) {
            return 0.0;
        }

        $startDate = $startDates
            ->sortBy(
                fn ($date) => $date->timestamp
            )
            ->first();

        $end = now()->startOfDay();

        $schoolDaysCount = 0;

        $cursor = $startDate->copy();

        while ($cursor->lte($end)) {
            if (!$cursor->isWeekend()) {
                $schoolDaysCount++;
            }

            $cursor->addDay();
        }

        if ($schoolDaysCount === 0) {
            return 0.0;
        }

        /*
         * QR attendance dates.
         */
        $qrDates = AttendanceLog::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'scan_type',
                'IN'
            )
            ->where(
                'scan_time',
                '>=',
                $startDate
            )
            ->selectRaw(
                'DATE(scan_time) as attendance_date'
            )
            ->distinct()
            ->pluck('attendance_date');

        /*
         * Teacher-verified attended dates.
         */
        $verifiedDates = AttendanceVerification::where(
            'enrollment_id',
            $enrollmentId
        )
            ->where(
                'attendance_date',
                '>=',
                $startDate->toDateString()
            )
            ->where(
                'attendance_date',
                '<=',
                $end->toDateString()
            )
            ->whereIn(
                'status',
                [
                    AttendanceVerification::STATUS_PRESENT,
                    AttendanceVerification::STATUS_LATE,
                    AttendanceVerification::STATUS_EXCUSED,
                ]
            )
            ->select('attendance_date')
            ->distinct()
            ->pluck('attendance_date');

        /*
         * Merge QR and teacher verification dates.
         *
         * Duplicate records for the same date count as ONE day.
         */
        $presentDaysCount = $qrDates
            ->merge($verifiedDates)
            ->map(
                fn ($date) => \Carbon\Carbon::parse($date)->toDateString()
            )
            ->unique()
            ->count();

        return round(
            ($presentDaysCount / $schoolDaysCount) * 100,
            1
        );
    }

    /**
     * Calculate total risk score from all indicators.
     */
    private function calculateScoreFromIndicators(
        array $indicators
    ): int {
        $score = 0;

        if ($indicators['low_grade'] ?? false) {
            $score += 30;
        }

        if ($indicators['missing_grades'] ?? false) {
            $score += 25;
        }

        if ($indicators['low_attendance'] ?? false) {
            $score += 25;
        }

        if ($indicators['declining_performance'] ?? false) {
            $score += 20;
        }

        return min($score, 100);
    }

    /**
     * Determine risk level based on total score.
     *
     * 0-24   = Low
     * 25-59  = Moderate
     * 60-100 = High
     */
    private function determineRiskLevel(int $score): string
    {
        if ($score <= 24) {
            return 'Low';
        }

        if ($score <= 59) {
            return 'Moderate';
        }

        return 'High';
    }

    /**
     * Get default risk score when no data is available.
     */
    public function getDefaultRiskScore(): array
    {
        return [
            'risk_score' => 0,
            'risk_level' => 'Low',
            'indicators' => [
                'low_grade' => false,
                'missing_grades' => false,
                'low_attendance' => false,
                'declining_performance' => false,
            ],
        ];
    }

    /**
     * Public wrapper for getting a student's attendance rate.
     */
    public function getAttendanceRate(
        int $enrollmentId,
        int $gradingPeriodId
    ): float {
        return $this->calculateAttendanceRate(
            $enrollmentId,
            $gradingPeriodId
        );
    }

    /**
     * Check if previous term grade data is available.
     */
    public function hasPreviousPeriodData(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): bool {
        $previousPeriod = $this->getPreviousPeriod(
            $currentPeriod
        );

        if (!$previousPeriod) {
            return false;
        }

        $previousGrade = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignment->id
            )
            ->where(
                'grading_period_id',
                $previousPeriod->id
            )
            ->first();

        return $previousGrade !== null
            && $previousGrade->transmuted_grade !== null;
    }

    /**
     * Get grade comparison between current and previous terms.
     */
    public function getGradeComparison(
        Enrollment $enrollment,
        TeachingAssignment $teachingAssignment,
        GradingPeriod $currentPeriod
    ): array {
        $currentGrade = TermGrade::where(
            'enrollment_id',
            $enrollment->id
        )
            ->where(
                'teaching_assignment_id',
                $teachingAssignment->id
            )
            ->where(
                'grading_period_id',
                $currentPeriod->id
            )
            ->first();

        $previousPeriod = $this->getPreviousPeriod(
            $currentPeriod
        );

        $previousGrade = null;

        if ($previousPeriod) {
            $previousGrade = TermGrade::where(
                'enrollment_id',
                $enrollment->id
            )
                ->where(
                    'teaching_assignment_id',
                    $teachingAssignment->id
                )
                ->where(
                    'grading_period_id',
                    $previousPeriod->id
                )
                ->first();
        }

        // Fallback for Term 3 if Term 2 has no grade but Term 1 exists
        if ((!$previousGrade || $previousGrade->transmuted_grade === null) && $currentPeriod->sequence > 2) {
            $earlierPeriod = GradingPeriod::where('period_type', 'trimester')
                ->where('sequence', '<', $previousPeriod?->sequence ?? $currentPeriod->sequence)
                ->where('sequence', '>=', 1)
                ->orderBy('sequence', 'desc')
                ->first();

            if ($earlierPeriod) {
                $earlierGrade = TermGrade::where(
                    'enrollment_id',
                    $enrollment->id
                )
                    ->where(
                        'teaching_assignment_id',
                        $teachingAssignment->id
                    )
                    ->where(
                        'grading_period_id',
                        $earlierPeriod->id
                    )
                    ->first();

                if ($earlierGrade && $earlierGrade->transmuted_grade !== null) {
                    $previousGrade = $earlierGrade;
                }
            }
        }

        $currVal = $currentGrade?->transmuted_grade !== null
            ? (float) $currentGrade->transmuted_grade
            : null;

        $prevVal = $previousGrade?->transmuted_grade !== null
            ? (float) $previousGrade->transmuted_grade
            : null;

        $hasPreviousData = ($currVal !== null && $prevVal !== null);

        $trend = 'N/A';
        if ($hasPreviousData) {
            if ($currVal > $prevVal) {
                $trend = 'Improving';
            } elseif ($currVal < $prevVal) {
                $trend = 'Declining';
            } else {
                $trend = 'Stable';
            }
        }

        return [
            'current_grade' => $currVal,
            'previous_grade' => $prevVal,
            'has_previous_data' => $hasPreviousData,
            'trend' => $trend,
        ];
    }
}