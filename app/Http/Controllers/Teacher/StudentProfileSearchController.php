<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use App\Models\GradingPeriod;
use App\Services\Grading\RiskScoreService;
use Illuminate\Http\Request;

class StudentProfileSearchController extends Controller
{
    protected RiskScoreService $riskScoreService;

    public function __construct(RiskScoreService $riskScoreService)
    {
        $this->riskScoreService = $riskScoreService;
    }

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $query = trim($request->input('q', ''));
        $results = collect();

        if ($teacher) {
            // Get current period using same logic as At-Risk page
            $currentPeriod = GradingPeriod::where('is_active', true)->where('sequence', '<=', 3)->orderBy('sequence')->first() ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();

            // Get all active teaching assignments
            $teachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with('section', 'subject')
                ->get();

            $sectionIds = $teachingAssignments->pluck('section_id')->unique();

            $enrollmentsQuery = Enrollment::whereIn('section_id', $sectionIds)
                ->where('status', 'active')
                ->with('student', 'section');

            $enrollments = $enrollmentsQuery->get();

            foreach ($enrollments as $enrollment) {
                // Apply search filter
                if ($query !== '') {
                    $student = $enrollment->student;
                    if (! $student) {
                        continue;
                    }
                    $fullName = strtolower($student->full_name);
                    $studentNumber = strtolower($student->student_number ?? '');
                    $needle = strtolower($query);

                    if (!str_contains($fullName, $needle) && !str_contains($studentNumber, $needle)) {
                        continue;
                    }
                }

                // Get grades for this specific enrollment
                $grades = QuarterlyGrade::where('enrollment_id', $enrollment->id)
                    ->whereHas('gradingPeriod', fn($q) => $q->where('sequence', '<=', 3))
                    ->get();

                $avgGrade = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;

                // Get attendance rate using RiskScoreService
                $attendanceRate = $currentPeriod ? $this->riskScoreService->getAttendanceRate($enrollment->id, $currentPeriod->id) : 0;

                // Get risk data using RiskScoreService
                $riskData = $currentPeriod ? $this->riskScoreService->calculateRiskScore($enrollment, $teachingAssignments->firstWhere('section_id', $enrollment->section_id), $currentPeriod) : $this->riskScoreService->getDefaultRiskScore();

                // Determine trend based on actual grade comparison
                $trend = 'N/A';
                if ($currentPeriod) {
                    $gradeComparison = $this->riskScoreService->getGradeComparison($enrollment, $teachingAssignments->firstWhere('section_id', $enrollment->section_id), $currentPeriod);
                    
                    if ($gradeComparison['has_previous_data']) {
                        $current = $gradeComparison['current_grade'];
                        $previous = $gradeComparison['previous_grade'];
                        
                        if ($current > $previous) {
                            $trend = 'Improving';
                        } elseif ($current < $previous) {
                            $trend = 'Declining';
                        } else {
                            $trend = 'Stable';
                        }
                    }
                }

                $subjectNames = $teachingAssignments->where('section_id', $enrollment->section_id)
                    ->pluck('subject.name')
                    ->unique()
                    ->implode(', ');

                $results->push((object) [
                    'enrollment_id' => $enrollment->id,
                    'name' => $enrollment->student->full_name,
                    'student_number' => $enrollment->student->student_number,
                    'lrn' => $enrollment->student->lrn,
                    'grade_level' => $enrollment->section->grade_level,
                    'section_name' => $enrollment->section->name,
                    'average' => $avgGrade,
                    'attendance' => $attendanceRate,
                    'trend' => $trend,
                    'risk_level' => $riskData['risk_level'],
                ]);
            }

            $results = $results->sortBy('name')->values();
        }

        return view('teacher-modules.students.student-profile-search', compact(
            'query', 'results'
        ));
    }

    public function show(Request $request, int $enrollmentId)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $enrollment = Enrollment::with('student', 'section')->findOrFail($enrollmentId);

        // Get all enrollments for this student across all the teacher's sections
        $allEnrollments = Enrollment::where('student_id', $enrollment->student_id)
            ->where('status', 'active')
            ->with('section')
            ->get();

        // Get ONLY teaching assignments for sections that this student is actually enrolled in
        $studentSectionIds = $allEnrollments->pluck('section_id')->unique();
        $allTeachingAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereIn('section_id', $studentSectionIds)
            ->with('subject', 'section')
            ->get();

        // Ensure we only work with the specific enrollment for grade queries
        $targetEnrollment = Enrollment::findOrFail($enrollmentId);
        
        $allTaIds = $allTeachingAssignments->pluck('id');

        // Get all grades for this specific student across all teaching assignments
        $allGrades = QuarterlyGrade::whereIn('teaching_assignment_id', $allTaIds)
            ->where('enrollment_id', $targetEnrollment->id)
            ->with('gradingPeriod')
            ->get()
            ->groupBy('teaching_assignment_id');

        // Build comprehensive subjects data across all teaching assignments
        $allSubjects = $allTeachingAssignments->map(function ($ta) use ($allGrades, $enrollmentId) {
            $subjectGrades = $allGrades->get($ta->id, collect())
                ->sortBy(fn ($g) => $g->gradingPeriod->sequence ?? 0)
                ->filter(fn ($g) => ($g->gradingPeriod->sequence ?? 99) <= 3)
                ->map(fn ($g) => (object) [
                    'term_label' => 'Term ' . ($g->gradingPeriod->sequence ?? '—'),
                    'grade' => $g->transmuted_grade,
                    'grading_period_id' => $g->grading_period_id,
                ])
                ->values();

            return (object) [
                'subject_name' => $ta->subject->name,
                'grade_level' => $ta->section->grade_level,
                'section_name' => $ta->section->name,
                'periods' => $subjectGrades,
                'average' => $subjectGrades->filter(fn ($g) => $g->grade !== null)->avg('grade'),
            ];
        });

        $overallAvg = $allSubjects->filter(fn ($s) => $s->average !== null)->avg('average');

        // Get current period for risk calculations
        $currentPeriod = GradingPeriod::where('is_active', true)->where('sequence', '<=', 3)->orderBy('sequence')->first() 
            ?? GradingPeriod::where('sequence', '<=', 3)->orderBy('sequence')->first();

        // Calculate comprehensive statistics using RiskScoreService for consistency
        $attendanceRate = $currentPeriod ? $this->riskScoreService->getAttendanceRate($enrollmentId, $currentPeriod->id) : 0;

        // Calculate present count for display (using same logic as RiskScoreService)
        if ($currentPeriod && $currentPeriod->start_date && $currentPeriod->end_date) {
            // Use term-specific date range
            $presentCount = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
                ->where('scan_type', 'IN')
                ->whereBetween('scan_time', [$currentPeriod->start_date, $currentPeriod->end_date])
                ->distinct('scan_time')
                ->count();
        } else {
            // Fallback to lifetime-to-date
            $presentCount = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
                ->where('scan_type', 'IN')
                ->distinct('scan_time')
                ->count();
        }

        // Count missing grades (null transmuted_grade)
        $missingGradesCount = $allGrades->flatten()->where('transmuted_grade', null)->count();

        // Count assessments below passing (grade < 75)
        $belowPassingCount = $allGrades->flatten()->filter(fn ($g) => $g->transmuted_grade !== null && $g->transmuted_grade < 75)->count();

        // Get risk data using RiskScoreService
        $riskData = $currentPeriod ? $this->riskScoreService->calculateRiskScore($enrollment, $allTeachingAssignments->firstWhere('section_id', $enrollment->section_id), $currentPeriod) : $this->riskScoreService->getDefaultRiskScore();

        // Get grade history for chart
        $gradeHistory = [];
        if ($currentPeriod) {
            foreach ([1, 2, 3] as $term) {
                $termGrades = $allGrades->flatten()->filter(fn ($g) => $g->gradingPeriod->sequence == $term && $g->transmuted_grade !== null);
                $termAverage = $termGrades->avg('transmuted_grade');
                $gradeHistory[] = [
                    'term' => 'Term ' . $term,
                    'average' => $termAverage ? round($termAverage, 1) : null,
                ];
            }
        }

        // Get recent scans
        $recentScans = \App\Models\AttendanceLog::where('enrollment_id', $enrollmentId)
            ->where('scan_type', 'IN')
            ->orderByDesc('scan_time')
            ->limit(5)
            ->get();

        return view('teacher-modules.students.student-profile-detail', [
            'enrollment' => $enrollment,
            'subjects' => $allSubjects,
            'overallAvg' => $overallAvg !== null ? round($overallAvg, 1) : null,
            'presentCount' => $presentCount,
            'attendanceRate' => $attendanceRate,
            'missingGradesCount' => $missingGradesCount,
            'belowPassingCount' => $belowPassingCount,
            'riskData' => $riskData,
            'gradeHistory' => $gradeHistory,
            'recentScans' => $recentScans,
            'currentPeriod' => $currentPeriod,
        ]);
    }
}