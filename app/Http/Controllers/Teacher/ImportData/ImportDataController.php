<?php

namespace App\Http\Controllers\Teacher\ImportData;

use App\Http\Controllers\Controller;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

use App\Models\GradingPeriod;

class ImportDataController extends Controller
{
    public function index(Request $request, ?int $teachingAssignmentId = null)
    {
        $teacher = $request->user()->teacher;

        abort_unless($teacher, 403);

        $selectedTaId = $teachingAssignmentId
            ?: (int) $request->input('teaching_assignment_id');

        $ta = null;
        if ($selectedTaId) {
            $ta = TeachingAssignment::where('id', $selectedTaId)
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->first();
        }

        if (!$ta) {
            $ta = TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->with(['section', 'subject'])
                ->first();
        }

        $gradingPeriods = GradingPeriod::where('sequence', '<=', 3)
            ->orderBy('sequence')
            ->get();

        $selectedPeriodId = $request->input('grading_period_id');

        return view('pov.teacher.import-data.import-data', compact('ta', 'gradingPeriods', 'selectedPeriodId'));
    }
}