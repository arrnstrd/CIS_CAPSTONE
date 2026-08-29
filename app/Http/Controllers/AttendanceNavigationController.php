<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Section;
use Illuminate\Http\Request;

class AttendanceNavigationController extends Controller
{
    public function gradeLevel()
    {
        $grades = [
            'Elementary' => [1, 2, 3, 4, 5, 6],
            'Junior High' => [7, 8, 9, 10],
            'Senior High' => [11, 12],
        ];

        $gradeCounts = Enrollment::selectRaw('grade_level, COUNT(DISTINCT student_id) as count')
            ->groupBy('grade_level')
            ->pluck('count', 'grade_level');

        return view('admin-modules.attendance.grade-level', compact('grades', 'gradeCounts'));
    }

    public function section($grade)
    {
        $sections = Section::where('grade_level', $grade)
            ->with('advisor')
            ->withCount('enrollments as student_count')
            ->get();

        return view('admin-modules.attendance.section-selection', compact('sections', 'grade'));
    }
}
