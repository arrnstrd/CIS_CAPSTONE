<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\SchoolYear;
use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class GradingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        $schoolYears = SchoolYear::orderByDesc('school_year')->get();

        if (! $teacher) {
            return view('teacher-modules.grading.grading-dashboard', [
                'classes' => collect(),
                'schoolYears' => $schoolYears,
            ]);
        }

        $validPeriodIds = GradingPeriod::where('sequence', '<=', 3)->pluck('id');
        $currentPeriod = GradingPeriod::where('is_active', true)->orderByDesc('sequence')->first() ?? GradingPeriod::orderByDesc('sequence')->first();

        $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $classes = $teachingAssignments->map(function ($ta) use ($validPeriodIds, $currentPeriod) {
            if (! $ta->section || ! $ta->subject) {
                return null;
            }

            $activeEnrollmentIds = Enrollment::where('section_id', $ta->section_id)
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

        return view('teacher-modules.grading.grading-dashboard', compact('classes', 'schoolYears'));
    }
}