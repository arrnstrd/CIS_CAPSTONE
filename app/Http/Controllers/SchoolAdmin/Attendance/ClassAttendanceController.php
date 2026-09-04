<?php

namespace App\Http\Controllers\SchoolAdmin\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Http\Request;

class ClassAttendanceController extends Controller
{
    public function index()
    {
        return $this->gradeLevel();
    }
    
    public function gradeLevel()
    {
        $grades = [
            'Elementary' => range(1, 6),
            'Junior High School' => range(7, 10),
            'Senior High School' => range(11, 12),
        ];

        $activeSchoolYear = SchoolYear::active()->first();

        $gradeCounts = Enrollment::query()
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->selectRaw('grade_level, COUNT(DISTINCT student_id) as total')
            ->groupBy('grade_level')
            ->pluck('total', 'grade_level');

        return view('pov.school-admin.attendance.grade-level', compact('grades', 'gradeCounts'));
    }
    
    public function section($grade)
    {
        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::active()->first();

        $sections = Section::query()
            ->where('grade_level', $grade)
            ->with('advisor.user')
            ->withCount(['enrollments as student_count' => function ($q) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
            }])
            ->orderBy('name')
            ->get();

        return view('pov.school-admin.attendance.section-selection', compact('grade', 'sections'));
    }
}