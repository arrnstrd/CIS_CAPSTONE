<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\GradingPeriod;
use App\Models\TeachingAssignment;
use App\Models\Enrollment;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

class AtRiskController extends Controller
{
    protected RiskScoreService $riskScoreService;

    public function __construct(RiskScoreService $riskScoreService)
    {
        $this->riskScoreService = $riskScoreService;
    }

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return view('teacher-modules.grading.at-risk-index', [
                'students' => collect(),
                'gradeLevels' => collect(),
                'selectedGradeLevel' => null,
                'selectedRiskLevel' => null,
                'stats' => ['low' => 0, 'moderate' => 0, 'high' => 0],
            ]);
        }

        // Get current period using same logic as GradingDashboardController
        // TODO: Once "Finalize Term" is implemented, this should pick the lowest sequence <=3 
        // whose status is NOT "Finalized" (so it naturally advances as terms get locked)
        $currentPeriod = GradingPeriod::where('is_active', true)->where('sequence', '<=', 3)->orderBy('sequence')->first() ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();

        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedRiskLevel = $request->input('risk_level') ?: null;

        $teachingAssignmentsQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject']);

        if ($selectedGradeLevel) {
            $teachingAssignmentsQuery->whereHas('section', fn ($q) => $q->where('grade_level', $selectedGradeLevel));
        }

        $teachingAssignments = $teachingAssignmentsQuery->get();

        $gradeLevels = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->get()
            ->pluck('section.grade_level')
            ->unique()
            ->sort()
            ->values();

        $allStudents = collect();

        if ($currentPeriod) {
            foreach ($teachingAssignments as $ta) {
                if ($ta->section && $ta->subject) {
                    $riskData = $this->riskScoreService->calculateRiskScoresForClass($ta, $currentPeriod);
                    
                    foreach ($riskData['risk_scores'] as $studentRisk) {
                        // Only include at-risk students (Moderate/High)
                        if (in_array($studentRisk['risk_level'], ['Moderate', 'High'])) {
                            $attendanceRate = $this->riskScoreService->getAttendanceRate($studentRisk['enrollment_id'], $currentPeriod->id);
                            
                            // Get the enrollment to access student details directly
                            $enrollment = \App\Models\Enrollment::find($studentRisk['enrollment_id']);
                            
                            $allStudents->push((object) [
                                'enrollment_id' => $studentRisk['enrollment_id'],
                                'name' => $enrollment->student->full_name ?? 'Unknown Student',
                                'student_number' => $enrollment->student->student_number ?? 'N/A',
                                'grade_level' => $ta->section->grade_level,
                                'section_name' => $ta->section->name,
                                'subject_name' => $ta->subject->name,
                                'avg_grade' => $this->getStudentAverageGrade($studentRisk['enrollment_id'], $ta->id, $currentPeriod->id),
                                'attendance_rate' => $attendanceRate,
                                'risk_score' => $studentRisk['risk_score'],
                                'risk_level' => $studentRisk['risk_level'],
                                'indicators' => $studentRisk['indicators'],
                                'teaching_assignment_id' => $ta->id,
                            ]);
                        }
                    }
                }
            }
        }

        // Apply filters
        if ($selectedRiskLevel) {
            $allStudents = $allStudents->where('risk_level', $selectedRiskLevel);
        }

        $students = $allStudents->sortBy('name')->values();

        $stats = [
            'low' => $students->where('risk_level', 'Low')->count(),
            'moderate' => $students->where('risk_level', 'Moderate')->count(),
            'high' => $students->where('risk_level', 'High')->count(),
        ];

        return view('teacher-modules.grading.at-risk-index', compact(
            'students', 'gradeLevels', 'selectedGradeLevel', 'selectedRiskLevel', 'stats', 'currentPeriod'
        ));
    }

    /**
     * Get student's average grade for a specific teaching assignment and period.
     */
    private function getStudentAverageGrade(int $enrollmentId, int $teachingAssignmentId, int $gradingPeriodId): ?float
    {
        $grades = \App\Models\QuarterlyGrade::where('enrollment_id', $enrollmentId)
            ->where('teaching_assignment_id', $teachingAssignmentId)
            ->where('grading_period_id', $gradingPeriodId)
            ->whereNotNull('transmuted_grade')
            ->get();

        return $grades->isNotEmpty() ? round($grades->avg('transmuted_grade'), 1) : null;
    }
}