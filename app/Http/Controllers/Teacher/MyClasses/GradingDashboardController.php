<?php

namespace App\Http\Controllers\Teacher\MyClasses;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;

use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Services\Grading\GradingPeriodService;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

class GradingDashboardController extends Controller
{
    protected RiskScoreService $riskScoreService;
    protected GradingPeriodService $gradingPeriodService;

    public function __construct(
        RiskScoreService $riskScoreService,
        GradingPeriodService $gradingPeriodService
    ) {
        $this->riskScoreService = $riskScoreService;
        $this->gradingPeriodService = $gradingPeriodService;
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
            return view('pov.teacher.my-classes.grading-dashboard', [
                'classes' => collect(),
                'dashboardPreferences' => $dashboardPreferences,
                'recentActivities' => collect(),
            ]);
        }

        $validPeriodIds = GradingPeriod::trimester()->pluck('id');

        // Determine current period by request, teacher preference, or dynamic progression
        $currentPeriod = $this->gradingPeriodService->resolveSelectedPeriod(
            $request,
            $teacher,
            $dashboardPreferences
        );

        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject']);

        if ($activeSchoolYear) {
            $hasActiveYearAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->where('school_year_id', $activeSchoolYear->id)
                ->exists();
            if ($hasActiveYearAssignments) {
                $teachingAssignmentsQuery->where('school_year_id', $activeSchoolYear->id);
            }
        }

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

        $classes = $teachingAssignments->map(function ($ta) use ($validPeriodIds, $currentPeriod, $activeSchoolYear) {
            if (! $ta->section || ! $ta->subject) {
                return null;
            }

            $activeEnrollmentIds = Enrollment::where('section_id', $ta->section_id)
                ->where('status', 'active')
                ->when($activeSchoolYear, function ($q) use ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                })
                ->pluck('id');

            $learnerCount = $activeEnrollmentIds->count();

            $assessmentIds = Assessment::where('teaching_assignment_id', $ta->id)
                ->whereIn('grading_period_id', $validPeriodIds)
                ->pluck('id');

            $totalAssessments = $assessmentIds->count();
            $expectedScores = $totalAssessments * $learnerCount;

            $actualScores = 0;
            $encodedCount = 0;

            if ($expectedScores > 0) {
                $studentScores = StudentAssessmentScore::whereIn('assessment_id', $assessmentIds)
                    ->whereIn('enrollment_id', $activeEnrollmentIds)
                    ->whereNotNull('score')
                    ->select('enrollment_id')
                    ->get()
                    ->groupBy('enrollment_id');

                $actualScores = $studentScores->sum(fn($scores) => $scores->count());

                foreach ($studentScores as $enrollmentId => $scores) {
                    if ($scores->count() >= $totalAssessments) {
                        $encodedCount++;
                    }
                }
            }

            $completionPercent = $expectedScores > 0
                ? round(($actualScores / $expectedScores) * 100, 1)
                : null;

            // Get current grades for the period
            $currentGrades = $currentPeriod 
                ? \App\Models\TermGrade::where('teaching_assignment_id', $ta->id)
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
        
        $shouldCalculateRisk = ($dashboardPreferences?->show_at_risk ?? true);
        if ($currentPeriod && $shouldCalculateRisk) {
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

        return view('pov.teacher.my-classes.grading-dashboard', compact(
            'classes', 
            'totalClasses', 
            'totalStudents', 
            'currentPeriod',
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
        $currentPeriod = GradingPeriod::where('is_active', true)->trimester()->orderBy('sequence')->first() ?? GradingPeriod::trimester()->orderBy('sequence')->first();
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
            'periods_with_sequence_le_3' => GradingPeriod::trimester()->count(),
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

    /**
     * Dynamically resolve the active working term for the teacher based on
     * the progression and actual start of grading for their active teaching assignments.
     *
     * Progression Rules:
     * - If Term 1 is incomplete -> Term 1
     * - If Term 1 is 100% complete:
     *   - If Term 2 has NOT started yet -> remain on Term 1
     *   - If Term 2 has started:
     *     - If Term 2 is incomplete -> Term 2
     *     - If Term 2 is 100% complete:
     *       - If Term 3 has NOT started yet -> remain on Term 2
     *       - If Term 3 has started -> Term 3 (capped at Term 3)
     */
    protected function resolveTeacherCurrentTerm($teacher): ?GradingPeriod
    {
        $activeAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        $term1 = GradingPeriod::trimester()->where('sequence', 1)->first();
        $term2 = GradingPeriod::trimester()->where('sequence', 2)->first();
        $term3 = GradingPeriod::trimester()->where('sequence', 3)->first();

        // If teacher has no active assignments or Term 1 not found, return safest fallback
        if ($activeAssignments->isEmpty() || ! $term1) {
            return $term1 ?? GradingPeriod::trimester()->orderBy('sequence')->first();
        }

        // 1. Check if Term 1 is 100% complete across ALL active assignments
        $isTerm1Complete = $this->isTermCompleteForTeacher($activeAssignments, $term1);
        if (! $isTerm1Complete) {
            return $term1;
        }

        // 2. If Term 1 is complete, check if Term 2 has started
        if (! $term2 || ! $this->hasTermStartedForTeacher($activeAssignments, $term2)) {
            return $term1;
        }

        // 3. Term 2 has started -> check if Term 2 is 100% complete
        $isTerm2Complete = $this->isTermCompleteForTeacher($activeAssignments, $term2);
        if (! $isTerm2Complete) {
            return $term2;
        }

        // 4. Term 2 is 100% complete -> check if Term 3 has started
        if (! $term3 || ! $this->hasTermStartedForTeacher($activeAssignments, $term3)) {
            return $term2;
        }

        // 5. Term 3 has started -> Current Term is Term 3 (final term, stays on Term 3)
        return $term3;
    }

    /**
     * Check whether a grading period has started for the teacher's active assignments.
     *
     * A term counts as started if there is at least one assessment created OR
     * at least one term grade record with non-null component or transmuted scores
     * for the teacher's active assignments.
     *
     * @param \Illuminate\Support\Collection $activeAssignments
     * @param \App\Models\GradingPeriod $period
     * @return bool
     */
    protected function hasTermStartedForTeacher($activeAssignments, GradingPeriod $period): bool
    {
        $taIds = $activeAssignments->pluck('id');
        if ($taIds->isEmpty()) {
            return false;
        }

        // 1. Check if any assessment exists for this period in the teacher's active assignments
        $hasAssessments = \App\Models\Assessment::whereIn('teaching_assignment_id', $taIds)
            ->where('grading_period_id', $period->id)
            ->exists();

        if ($hasAssessments) {
            return true;
        }

        // 2. Check if any term grade record exists for this period
        $hasGrades = \App\Models\TermGrade::whereIn('teaching_assignment_id', $taIds)
            ->where('grading_period_id', $period->id)
            ->where(function ($query) {
                $query->whereNotNull('transmuted_grade')
                    ->orWhereNotNull('initial_grade')
                    ->orWhereNotNull('written_work_grade')
                    ->orWhereNotNull('performance_task_grade')
                    ->orWhereNotNull('term_assessment_grade');
            })
            ->exists();

        return $hasGrades;
    }

    /**
     * Check whether ALL active teaching assignments of the teacher have
     * 100% finalized grades (transmuted_grade !== null) for every active enrolled student.
     *
     * @param \Illuminate\Support\Collection $activeAssignments
     * @param \App\Models\GradingPeriod $period
     * @return bool
     */
    protected function isTermCompleteForTeacher($activeAssignments, GradingPeriod $period): bool
    {
        foreach ($activeAssignments as $ta) {
            $activeEnrollmentIds = Enrollment::where('section_id', $ta->section_id)
                ->where('school_year_id', $ta->school_year_id)
                ->where('status', 'active')
                ->pluck('id');

            $totalStudents = $activeEnrollmentIds->count();

            // If the section has active enrolled students
            if ($totalStudents > 0) {
                $finalizedCount = \App\Models\TermGrade::where('teaching_assignment_id', $ta->id)
                    ->where('grading_period_id', $period->id)
                    ->whereIn('enrollment_id', $activeEnrollmentIds)
                    ->whereNotNull('transmuted_grade')
                    ->count();

                if ($finalizedCount < $totalStudents) {
                    return false; // Incomplete: at least one class has not finished this term
                }
            }
        }

        return true; // All active assignments with students are 100% finalized
    }
}
