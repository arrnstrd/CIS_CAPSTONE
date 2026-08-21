<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignments = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->get()
            : collect();

        return view('teacher-modules.utilities.reports', compact('teachingAssignments'));
    }

    public function classRecordData(Request $request, int $teachingAssignmentId)
    {
        $teacher = $request->user()->teacher;

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->with(['section', 'subject'])
            ->firstOrFail();

        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();

        $enrollments = Enrollment::where('section_id', $ta->section_id)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->filter(fn ($e) => $e->student !== null)
            ->sortBy(fn ($e) => $e->student->last_name . $e->student->first_name)
            ->values();

        $grades = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->groupBy('enrollment_id');

        $rows = $enrollments->map(function ($e) use ($grades, $gradingPeriods) {
            $studentGrades = $grades->get($e->id, collect())->keyBy('grading_period_id');
            $termGrades = $gradingPeriods->map(fn ($gp) => $studentGrades->get($gp->id)?->transmuted_grade)->values();

            $validGrades = $termGrades->filter(fn ($g) => $g !== null);
            $final = $validGrades->count() ? round($validGrades->avg(), 1) : null;

            return [
                'name' => $e->student->last_name . ', ' . $e->student->first_name,
                'terms' => $termGrades,
                'final' => $final,
            ];
        });

        return response()->json([
            'section' => $ta->section->name,
            'grade_level' => $ta->section->grade_level,
            'subject' => $ta->subject->name,
            'terms' => $gradingPeriods->map(fn ($gp) => 'Term ' . $gp->sequence),
            'rows' => $rows,
        ]);
    }
}