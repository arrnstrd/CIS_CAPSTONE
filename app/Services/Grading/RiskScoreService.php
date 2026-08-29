<?php

namespace App\Services\Grading;

use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\GradingPeriod;
use App\Models\TeachingAssignment;
use App\Models\AttendanceLog;
use App\Models\StudentAssessmentScore;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

class RiskScoreService
{
    /**
     * Calculate risk score for a single student based on 4 weighted indicators.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return array
     */
    public function calculateRiskScore(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): array
    {
        $indicators = [
            'low_grade' => $this->checkLowGrade($enrollment, $teachingAssignment, $currentPeriod),
            'missing_grades' => $this->checkMissingGrades($enrollment, $teachingAssignment, $currentPeriod),
            'low_attendance' => $this->checkLowAttendance($enrollment, $teachingAssignment, $currentPeriod),
            'declining_performance' => $this->checkDecliningPerformance($enrollment, $teachingAssignment, $currentPeriod),
        ];

        $indicators = [
            'low_grade' => $this->checkLowGrade($enrollment, $teachingAssignment, $currentPeriod),
            'missing_grades' => $this->checkMissingGrades($enrollment, $teachingAssignment, $currentPeriod),
            'low_attendance' => $this->checkLowAttendance($enrollment, $teachingAssignment, $currentPeriod),
            'declining_performance' => $this->checkDecliningPerformance($enrollment, $teachingAssignment, $currentPeriod),
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
     * Calculate risk scores for all students in a teaching assignment.
     *
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $period
     * @return array
     */
    public function calculateRiskScoresForClass(TeachingAssignment $teachingAssignment, GradingPeriod $period): array
    {
        $enrollments = Enrollment::where('section_id', $teachingAssignment->section_id)
            ->where('status', 'active')
            ->get();

        $riskScores = [];
        $stats = ['total' => 0, 'high' => 0, 'moderate' => 0, 'low' => 0];

        foreach ($enrollments as $enrollment) {
            $riskData = $this->calculateRiskScore($enrollment, $teachingAssignment, $period);
            $riskScores[] = [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student->full_name ?? 'Unknown Student',
                'risk_score' => $riskData['risk_score'],
                'risk_level' => $riskData['risk_level'],
                'indicators' => $riskData['indicators'],
            ];

            $stats['total']++;
            $stats[strtolower($riskData['risk_level'])]++;
        }

        return [
            'risk_scores' => $riskScores,
            'stats' => $stats,
        ];
    }

    /**
     * Check if student has low grade for current period.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return bool
     */
    private function checkLowGrade(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): bool
    {
        $quarterlyGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $currentPeriod->id)
            ->first();

        if (!$quarterlyGrade) {
            return false;
        }

        // Check if transmuted grade is < 75 OR status is "Failing"
        if ($quarterlyGrade->transmuted_grade < 75) {
            return true;
        }

        // Check if there's any failing status (if applicable)
        // This might need to be adjusted based on your failing status implementation
        return false;
    }

    /**
     * Check if student has missing assessment scores.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return bool
     */
    private function checkMissingGrades(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): bool
    {
        $assessments = Assessment::where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $currentPeriod->id)
            ->where('status', 'active')
            ->get();

        if ($assessments->isEmpty()) {
            return false;
        }

        foreach ($assessments as $assessment) {
            $score = StudentAssessmentScore::where('enrollment_id', $enrollment->id)
                ->where('assessment_id', $assessment->id)
                ->first();

            // If any assessment score is missing/absent, return true
            if (!$score || $score->score === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if student has low attendance rate.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return bool
     */
    private function checkLowAttendance(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): bool
    {
        $attendanceRate = $this->calculateAttendanceRate($enrollment->id, $currentPeriod->id);
        
        // Low attendance if rate < 85%
        return $attendanceRate < 85.0;
    }

    /**
     * Check if student has declining performance.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return bool
     */
    private function checkDecliningPerformance(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): bool
    {
        // Get current period grade
        $currentGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $currentPeriod->id)
            ->first();

        if (!$currentGrade || !$currentGrade->transmuted_grade) {
            return false;
        }

        // Get previous grading period
        $previousPeriod = GradingPeriod::where('sequence', '<', $currentPeriod->sequence)
            ->where('period_type', $currentPeriod->period_type)
            ->orderBy('sequence', 'desc')
            ->first();

        if (!$previousPeriod) {
            return false; // No previous period to compare with
        }

        $previousGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $previousPeriod->id)
            ->first();

        if (!$previousGrade || !$previousGrade->transmuted_grade) {
            return false; // No previous grade data
        }

        // Check if current grade is lower than previous grade
        return $currentGrade->transmuted_grade < $previousGrade->transmuted_grade;
    }

    /**
     * Calculate attendance rate for a student.
     *
     * @param int $enrollmentId
     * @param int $gradingPeriodId
     * @return float
     */
    private function calculateAttendanceRate(int $enrollmentId, int $gradingPeriodId): float
    {
        $gradingPeriod = GradingPeriod::find($gradingPeriodId);
        if (!$gradingPeriod) {
            return 0.0;
        }

        // Check if grading period has specific date range set
        if ($gradingPeriod->start_date && $gradingPeriod->end_date) {
            // Use term-specific date range
            return $this->calculateTermAttendanceRate($enrollmentId, $gradingPeriod);
        } else {
            // Fallback to lifetime-to-date attendance (known limitation)
            // TODO: Once real term dates are established with team/Ariana,
            // populate start_date/end_date in grading_periods and remove this fallback
            return $this->calculateLifetimeAttendanceRate($enrollmentId);
        }
    }

    /**
     * Calculate attendance rate for a specific term date range.
     *
     * @param int $enrollmentId
     * @param GradingPeriod $gradingPeriod
     * @return float
     */
    private function calculateTermAttendanceRate(int $enrollmentId, GradingPeriod $gradingPeriod): float
    {
        $startDate = $gradingPeriod->start_date;
        $endDate = $gradingPeriod->end_date;

        // Count school days within this specific term period
        $schoolDaysCount = 0;
        $cursor = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->startOfDay();
        
        while ($cursor->lt($end)) {
            if (!$cursor->isWeekend()) {
                $schoolDaysCount++;
            }
            $cursor->addDay();
        }

        if ($schoolDaysCount === 0) {
            return 0.0;
        }

        // Count present days within this term period
        $presentDaysByDate = AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->where('scan_time', '>=', $startDate)
            ->where('scan_time', '<', $endDate)
            ->selectRaw('DATE(scan_time) as date')
            ->distinct()
            ->pluck('date');

        $presentDaysCount = $presentDaysByDate->count();

        return round(($presentDaysCount / $schoolDaysCount) * 100, 1);
    }

    /**
     * Calculate lifetime-to-date attendance rate (fallback when term dates not set).
     *
     * @param int $enrollmentId
     * @return float
     */
    private function calculateLifetimeAttendanceRate(int $enrollmentId): float
    {
        // Use earliest attendance log for this enrollment as the start date
        // and current date as the end date, similar to AttendanceAnalyticsController approach
        $earliestScan = AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->min('scan_time');

        if (!$earliestScan) {
            return 0.0;
        }

        // Calculate school days count (weekdays only) from earliest scan to today
        $schoolDaysCount = 0;
        $cursor = \Carbon\Carbon::parse($earliestScan)->startOfDay();
        $end = now()->startOfDay();
        
        while ($cursor->lte($end)) {
            if (!$cursor->isWeekend()) {
                $schoolDaysCount++;
            }
            $cursor->addDay();
        }

        if ($schoolDaysCount === 0) {
            return 0.0;
        }

        // Count present days within this lifetime date range
        $presentDaysByDate = AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->where('scan_time', '>=', $earliestScan)
            ->selectRaw('DATE(scan_time) as date')
            ->distinct()
            ->pluck('date');

        $presentDaysCount = $presentDaysByDate->count();

        return round(($presentDaysCount / $schoolDaysCount) * 100, 1);
    }

    /**
     * Calculate total risk score from indicators.
     *
     * @param array $indicators
     * @return int
     */
    private function calculateScoreFromIndicators(array $indicators): int
    {
        $score = 0;

        // Low Grade: +30 if true
        if ($indicators['low_grade']) {
            $score += 30;
        }

        // Missing Grades: +25 if true
        if ($indicators['missing_grades']) {
            $score += 25;
        }

        // Low Attendance: +25 if true
        if ($indicators['low_attendance']) {
            $score += 25;
        }

        // Declining Performance: +20 if true
        if ($indicators['declining_performance']) {
            $score += 20;
        }

        return min($score, 100); // Ensure score doesn't exceed 100
    }

    /**
     * Determine risk level based on score.
     *
     * @param int $score
     * @return string
     */
    private function determineRiskLevel(int $score): string
    {
        if ($score <= 24) {
            return 'Low';
        } elseif ($score <= 59) {
            return 'Moderate';
        } else {
            return 'High';
        }
    }

    /**
     * Get default risk score when no data is available.
     *
     * @return array
     */
    private function getDefaultRiskScore(): array
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
     * Get attendance rate for a student (public wrapper for private method).
     *
     * @param int $enrollmentId
     * @param int $gradingPeriodId
     * @return float
     */
    public function getAttendanceRate(int $enrollmentId, int $gradingPeriodId): float
    {
        return $this->calculateAttendanceRate($enrollmentId, $gradingPeriodId);
    }

    /**
     * Check if student has previous grading period data available.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return bool
     */
    public function hasPreviousPeriodData(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): bool
    {
        // Get previous grading period
        $previousPeriod = GradingPeriod::where('sequence', '<', $currentPeriod->sequence)
            ->where('period_type', $currentPeriod->period_type)
            ->orderBy('sequence', 'desc')
            ->first();

        if (!$previousPeriod) {
            return false; // No previous period to compare with
        }

        $previousGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $previousPeriod->id)
            ->first();

        return $previousGrade && $previousGrade->transmuted_grade !== null;
    }

    /**
     * Get grade comparison data between current and previous periods.
     *
     * @param Enrollment $enrollment
     * @param TeachingAssignment $teachingAssignment
     * @param GradingPeriod $currentPeriod
     * @return array
     */
    public function getGradeComparison(Enrollment $enrollment, TeachingAssignment $teachingAssignment, GradingPeriod $currentPeriod): array
    {
        // Get current period grade
        $currentGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->where('grading_period_id', $currentPeriod->id)
            ->first();

        // Get previous grading period
        $previousPeriod = GradingPeriod::where('sequence', '<', $currentPeriod->sequence)
            ->where('period_type', $currentPeriod->period_type)
            ->orderBy('sequence', 'desc')
            ->first();

        $previousGrade = null;
        if ($previousPeriod) {
            $previousGrade = QuarterlyGrade::where('enrollment_id', $enrollment->id)
                ->where('teaching_assignment_id', $teachingAssignment->id)
                ->where('grading_period_id', $previousPeriod->id)
                ->first();
        }

        return [
            'current_grade' => $currentGrade?->transmuted_grade,
            'previous_grade' => $previousGrade?->transmuted_grade,
            'has_previous_data' => $previousGrade && $previousGrade->transmuted_grade !== null,
            'trend' => 'N/A', // Will be calculated by the controller
        ];
    }
}