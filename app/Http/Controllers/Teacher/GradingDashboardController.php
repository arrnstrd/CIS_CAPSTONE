<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;

use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

class GradingDashboardController extends Controller
{
    protected RiskScoreService $riskScoreService;

    public function __construct(RiskScoreService $riskScoreService)
    {
        $this->riskScoreService = $riskScoreService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user?->teacher;
        $dashboardPreferences = $user?->getOrCreateDashboardPreference();

        // Handle Default View preference (overview, my_classes, analytics)
        if (! $request->has('view') && ! $request->has('no_redirect') && ! $request->has('teaching_assignment_id') && ! $request->has('grading_period_id')) {
            $defaultView = $dashboardPreferences?->default_view ?? 'overview';

            if ($defaultView === 'my_classes') {
                return redirect()->route('teacher.grading-system.grades');
            }

            if ($defaultView === 'analytics') {
                return redirect()->route('teacher.grading-system.analytics');
            }
        }

        if (! $teacher) {
            return view('teacher-modules.grading.grading-dashboard', [
                'classes' => collect(),
                'dashboardPreferences' => $dashboardPreferences,
                'recentActivities' => collect(),
            ]);
        }

        $validPeriodIds = GradingPeriod::where('sequence', '<=', 3)->pluck('id');

        // Determine current period by request or teacher default term preference
        $selectedPeriodId = $request->input('grading_period_id');
        if ($selectedPeriodId) {
            $currentPeriod = GradingPeriod::where('id', $selectedPeriodId)->where('sequence', '<=', 3)->first();
        } elseif ($dashboardPreferences?->default_term && $dashboardPreferences->default_term !== 'current') {
            $targetSequence = match ($dashboardPreferences->default_term) {
                'term_1' => 1,
                'term_2' => 2,
                'term_3' => 3,
                default => null,
            };
            $currentPeriod = $targetSequence
                ? GradingPeriod::where('sequence', $targetSequence)->where('period_type', 'trimester')->first()
                : null;
        }

        if (! isset($currentPeriod) || ! $currentPeriod) {
            $currentPeriod = GradingPeriod::where('is_active', true)->where('sequence', '<=', 3)->orderBy('sequence')->first()
                ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();
        }

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject']);

        // Default class filtering if requested or configured in teacher dashboard preferences
        $selectedClassId = $request->input('teaching_assignment_id', $dashboardPreferences?->default_class_id);
        if ($selectedClassId) {
            $hasAssignment = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('id', $selectedClassId)
                ->where('status', 'active')
                ->exists();
            if ($hasAssignment) {
                $teachingAssignmentsQuery->where('id', $selectedClassId);
            }
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        $classes = $teachingAssignments->map(function ($ta) use ($validPeriodIds, $currentPeriod) {
            if (! $ta->section || ! $ta->subject) {
                return null;
            }

            $activeEnrollmentIds = Enrollment::where('section_id', $ta->section_id)
                ->where('school_year_id', $ta->school_year_id)
                ->where('status', 'active')
                ->pluck('id');

            $learnerCount = $activeEnrollmentIds->count();

            $assessmentIds = Assessment::where('teaching_assignment_id', $ta->id)
                ->whereIn('grading_period_id', $validPeriodIds)
                ->pluck('id');

            $totalAssessments = $assessmentIds->count();
            $expectedScores = $totalAssessments * $learnerCount;

            $actualScores = $expectedScores > 0
                ? StudentAssessmentScore::whereIn('assessment_id', $assessmentIds)
                    ->whereIn('enrollment_id', $activeEnrollmentIds)
                    ->whereNotNull('score')
                    ->count()
                : 0;

            $completionPercent = $expectedScores > 0
                ? round(($actualScores / $expectedScores) * 100, 1)
                : null;

            // Calculate encoded count (students with all assessments graded)
            $encodedCount = 0;
            $studentScores = StudentAssessmentScore::whereIn('assessment_id', $assessmentIds)
                ->whereIn('enrollment_id', $activeEnrollmentIds)
                ->whereNotNull('score')
                ->get()
                ->groupBy('enrollment_id');
            
            foreach ($studentScores as $enrollmentId => $scores) {
                if ($scores->count() >= $totalAssessments) {
                    $encodedCount++;
                }
            }

            // Get current grades for the period
            $currentGrades = $currentPeriod 
                ? \App\Models\QuarterlyGrade::where('teaching_assignment_id', $ta->id)
                    ->where('grading_period_id', $currentPeriod->id)
                    ->whereNotNull('transmuted_grade')
                    ->get()
                : collect();
            
            $avgGrade = $currentGrades->isNotEmpty() 
                ? round($currentGrades->avg('transmuted_grade'), 1) 
                : null;
            
            $passingRate = $currentGrades->isNotEmpty() 
                ? round(($currentGrades->where('transmuted_grade', '>=', 75)->count() / $currentGrades->count()) * 100, 1) 
                : null;

            // Determine status
            $status = $completionPercent === null ? 'Not Started' : ($completionPercent >= 100 ? 'Complete' : 'In Progress');

            // Only "senior_high_school" (not yet present in data) gets the SHS badge;
            // both current values (elementary, highschool) default to K-10 Format.
            $formatBadge = $ta->section->level === 'senior_high_school' ? 'SHS Format' : 'K-10 Format';

            return (object) [
                'teaching_assignment_id' => $ta->id,
                'section_name' => $ta->section->name,
                'grade_level' => $ta->section->grade_level,
                'subject_name' => $ta->subject->name,
                'learner_count' => $learnerCount,
                'completion_percent' => $completionPercent,
                'format_badge' => $formatBadge,
                'encoded_count' => $encodedCount,
                'avg_grade' => $avgGrade,
                'passing_rate' => $passingRate,
                'status' => $status,
            ];
        })->filter()->values();

    $totalClasses = $classes->count();
    $totalStudents = $classes->sum('learner_count');
    $currentTermLabel = $currentPeriod ? 'Term ' . $currentPeriod->sequence : '—';

        // Calculate total at-risk students across all classes
        $totalAtRisk = 0;
        $classAtRiskCounts = [];
        $classRiskReasons = [];
        
        if ($currentPeriod) {
            foreach ($teachingAssignments as $ta) {
                if ($ta->section && $ta->subject) {
                    $riskData = $this->riskScoreService->calculateRiskScoresForClass($ta, $currentPeriod);
                    $atRiskCount = $riskData['stats']['high'] + $riskData['stats']['moderate'];
                    $totalAtRisk += $atRiskCount;
                    $classAtRiskCounts[$ta->id] = $atRiskCount;
                    
                    // Find the most common cause among at-risk students
                    $mostCommonCause = $this->findMostCommonRiskCause($riskData['risk_scores']);
                    $classRiskReasons[$ta->id] = $mostCommonCause;
                }
            }
        }

        $recentActivities = $user->notifications()->latest()->take(5)->get();

        return view('teacher-modules.grading.grading-dashboard', compact(
            'classes', 
            'totalClasses', 
            'totalStudents', 
            'currentTermLabel',
            'totalAtRisk',
            'classAtRiskCounts',
            'classRiskReasons',
            'dashboardPreferences',
            'recentActivities'
        ));
    }

    /**
     * Debug method to investigate the "At-Risk" issue
     */
    public function debugRiskScores(Request $request)
    {
        $teacher = $request->user()->teacher;
        
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        // Get current period (this is the first issue to investigate)
        // TODO: Once "Finalize Term" is implemented, this should pick the lowest sequence <=3 
        // whose status is NOT "Finalized" (so it naturally advances as terms get locked)
        $currentPeriod = GradingPeriod::where('is_active', true)->where('sequence', '<=', 3)->orderBy('sequence')->first() ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();
        $allPeriods = GradingPeriod::orderBy('sequence')->get();
        
        $debugInfo = [
            'current_period' => $currentPeriod ? [
                'id' => $currentPeriod->id,
                'name' => $currentPeriod->name,
                'sequence' => $currentPeriod->sequence,
                'is_active' => $currentPeriod->is_active,
            ] : null,
            'all_periods' => $allPeriods->map(function ($period) {
                return [
                    'id' => $period->id,
                    'name' => $period->name,
                    'sequence' => $period->sequence,
                    'is_active' => $period->is_active,
                ];
            })->values(),
            'periods_with_sequence_le_3' => GradingPeriod::where('sequence', '<=', 3)->count(),
        ];

        // Get teaching assignments
        $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $riskAnalysis = [];
        
        if ($currentPeriod) {
            foreach ($teachingAssignments as $ta) {
                if ($ta->section && $ta->subject) {
                    $enrollments = Enrollment::where('section_id', $ta->section_id)
                        ->where('school_year_id', $ta->school_year_id)
                        ->where('status', 'active')
                        ->get();

                    $classRiskData = [
                        'teaching_assignment_id' => $ta->id,
                        'section_name' => $ta->section->name,
                        'subject_name' => $ta->subject->name,
                        'total_students' => $enrollments->count(),
                        'at_risk_students' => 0,
                        'student_details' => [],
                    ];

                    foreach ($enrollments as $enrollment) {
                        $riskData = $this->riskScoreService->calculateRiskScore($enrollment, $ta, $currentPeriod);
                        
                        $classRiskData['student_details'][] = [
                            'student_name' => $enrollment->student->full_name ?? 'Unknown Student',
                            'enrollment_id' => $enrollment->id,
                            'risk_score' => $riskData['risk_score'],
                            'risk_level' => $riskData['risk_level'],
                            'indicators' => $riskData['indicators'],
                            'attendance_rate' => $this->riskScoreService->calculateAttendanceRate($enrollment->id, $currentPeriod->id),
                        ];

                        if ($riskData['risk_level'] !== 'Low') {
                            $classRiskData['at_risk_students']++;
                        }
                    }

                    $riskAnalysis[] = $classRiskData;
                }
            }
        }

        return response()->json([
            'debug_info' => $debugInfo,
            'risk_analysis' => $riskAnalysis,
        ]);
    }

    /**
     * Find the most common risk cause among at-risk students.
     *
     * @param array $riskScores
     * @return string|null
     */
    private function findMostCommonRiskCause(array $riskScores): ?string
    {
        $indicatorCounts = [
            'low_grade' => 0,
            'missing_grades' => 0,
            'low_attendance' => 0,
            'declining_performance' => 0,
        ];

        // Count indicators for at-risk students (Moderate/High risk levels)
        foreach ($riskScores as $studentRisk) {
            if (in_array($studentRisk['risk_level'], ['Moderate', 'High'])) {
                foreach ($studentRisk['indicators'] as $indicator => $isTrue) {
                    if ($isTrue) {
                        $indicatorCounts[$indicator]++;
                    }
                }
            }
        }

        // Find the indicator with the highest count
        $maxCount = 0;
        $mostCommonCause = null;
        
        // Priority order: low_grade, missing_grades, low_attendance, declining_performance
        $priorityOrder = ['low_grade', 'missing_grades', 'low_attendance', 'declining_performance'];
        
        foreach ($priorityOrder as $indicator) {
            if ($indicatorCounts[$indicator] > $maxCount) {
                $maxCount = $indicatorCounts[$indicator];
                $mostCommonCause = $indicator;
            }
        }

        // Return human-readable label if there's at least one at-risk student with this cause
        return $maxCount > 0 ? $this->getRiskIndicatorLabel($mostCommonCause) : null;
    }

    /**
     * Get human-readable label for risk indicator.
     *
     * @param string $indicator
     * @return string
     */
    private function getRiskIndicatorLabel(string $indicator): string
    {
        $labels = [
            'low_grade' => 'Low Grades',
            'missing_grades' => 'Missing Grades',
            'low_attendance' => 'Low Attendance',
            'declining_performance' => 'Declining Performance',
        ];

        return $labels[$indicator] ?? $indicator;
    }
}