<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use App\Services\Grading\SchoolLevelDetector;
use App\Services\Grading\GradingService;
use App\Services\Grading\SubjectWeightResolver;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    protected SchoolLevelDetector $levelDetector;
    protected GradingService $gradingService;
    protected SubjectWeightResolver $weightResolver;

    public function __construct(SchoolLevelDetector $levelDetector, GradingService $gradingService, SubjectWeightResolver $weightResolver)
    {
        $this->levelDetector = $levelDetector;
        $this->gradingService = $gradingService;
        $this->weightResolver = $weightResolver;
    }

    /**
     * Show the Report Card for a specific student.
     */
    public function show(Request $request, int $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $student = $enrollment->student;
        $section = $enrollment->section;
        
        if (!$student) {
            abort(404, 'Student not found');
        }

        // Verify teacher has access to this student
        $teacher = $request->user()->teacher;
        if (!$teacher || !$this->hasTeacherAccess($teacher, $enrollment)) {
            abort(403, 'Access denied');
        }

        // Detect school level
        $schoolLevel = $this->levelDetector->detect($section->grade_level);
        
        // Get all teaching assignments for this student's section
        $teachingAssignments = TeachingAssignment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with(['subject'])
            ->get();
        
        // Get all grading periods
        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();
        
        // Get all grades for this student using the same logic as Grade Sheet
        $grades = $this->getStudentGrades($enrollmentId, $teachingAssignments, $gradingPeriods);
        
        // Calculate general average and standing
        $generalAverage = $this->calculateGeneralAverage($grades);
        $standing = $this->getStanding($generalAverage);
        
        // Ensure grades array has all expected subjects to prevent undefined key errors
        $expectedSubjects = ['Filipino', 'English', 'Mathematics', 'Science', 'Araling Panlipunan', 'GMRC / Values Education', 'EPP / TLE', 'MAPEH'];
        foreach ($expectedSubjects as $subject) {
            if (!isset($grades[$subject])) {
                $grades[$subject] = [
                    'term1' => null,
                    'term2' => null,
                    'term3' => null,
                    'final' => null
                ];
            }
        }
        
        return view('teacher-modules.grading.report-card', [
            'student' => $student,
            'section' => $section,
            'schoolYear' => $teachingAssignments->first()?->schoolYear,
            'schoolLevel' => $schoolLevel,
            'grades' => $grades,
            'gradingPeriods' => $gradingPeriods,
            'generalAverage' => $generalAverage,
            'standing' => $standing,
            'currentDate' => now(),
            'enrollmentId' => $enrollmentId,
        ]);
    }

    /**
     * Check if teacher has access to this student's data.
     */
    private function hasTeacherAccess($teacher, $enrollment)
    {
        return TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $enrollment->section_id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Get all grades for a student across all subjects and terms.
     * Uses the same logic as Grade Sheet to ensure consistency.
     */
    private function getStudentGrades(int $enrollmentId, $teachingAssignments, $gradingPeriods)
    {
        $grades = [];
        
        foreach ($teachingAssignments as $ta) {
            $subjectName = $ta->subject->name;
            $subjectGrades = [];
            
            // Get grades for each grading period using existing QuarterlyGrade records
            foreach ($gradingPeriods as $period) {
                $quarterlyGrade = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
                    ->where('enrollment_id', $enrollmentId)
                    ->where('grading_period_id', $period->id)
                    ->first();
                
                $subjectGrades["term{$period->sequence}"] = $quarterlyGrade ? $quarterlyGrade->transmuted_grade : null;
            }
            
            // Calculate final grade using the same logic as Grade Sheet
            $termGrades = collect($subjectGrades)->filter()->values();
            $finalGrade = $termGrades->count() > 0 ? round($termGrades->avg(), 1) : null;
            
            $subjectGrades['final'] = $finalGrade;
            $grades[$subjectName] = $subjectGrades;
        }
        
        return $grades;
    }

    /**
     * Generate PDF export of the Report Card.
     */
    public function generatePdf(Request $request, int $enrollmentId)
    {
        // Get the same data as the show method
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $student = $enrollment->student;
        $section = $enrollment->section;
        
        if (!$student) {
            abort(404, 'Student not found');
        }

        // Verify teacher has access to this student
        $teacher = $request->user()->teacher;
        if (!$teacher || !$this->hasTeacherAccess($teacher, $enrollment)) {
            abort(403, 'Access denied');
        }

        $schoolLevel = $this->levelDetector->detect($section->grade_level);
        $teachingAssignments = TeachingAssignment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with(['subject'])
            ->get();
        
        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();
        $grades = $this->getStudentGrades($enrollmentId, $teachingAssignments, $gradingPeriods);
        
        // Calculate general average and standing
        $generalAverage = $this->calculateGeneralAverage($grades);
        
        // Return PDF view
        $pdf = view('teacher-modules.grading.report-card-pdf', [
            'student' => $student,
            'section' => $section,
            'schoolLevel' => $schoolLevel,
            'grades' => $grades,
            'gradingPeriods' => $gradingPeriods,
            'generalAverage' => $generalAverage,
            'currentDate' => now(),
        ]);
        
        // Generate PDF using dompdf
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdf)
            ->setPaper('A4', 'portrait')
            ->setWarnings(false);
        
        return $pdf->stream('report-card-' . $student->lrn . '.pdf');
    }

    /**
     * Calculate general average for all subjects.
     */
    private function calculateGeneralAverage($grades)
    {
        $allGrades = collect();
        
        foreach ($grades as $subjectGrades) {
            if ($subjectGrades['final'] !== null) {
                $allGrades->push($subjectGrades['final']);
            }
        }
        
        return $allGrades->count() > 0 ? round($allGrades->avg(), 1) : null;
    }

    /**
     * Get student standing based on general average.
     */
    private function getStanding($average)
    {
        if ($average === null) return '—';
        
        if ($average >= 75) return 'Passed';
        if ($average >= 70) return 'Conditionally Passed';
        return 'Failed';
    }
}