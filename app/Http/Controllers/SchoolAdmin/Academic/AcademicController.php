<?php

namespace App\Http\Controllers\SchoolAdmin\Academic;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AcademicController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

        $allSubjects = Subject::orderBy('name')->get();
        $allSections = Section::where('status', 'active')->orderBy('grade_level')->orderBy('name')->get();
        $allSchoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        $sections = $this->sections($request);
        $subjects = $this->subjects($request);
        $teachingAssignments = $this->teachingAssignments($request);

        return view('pov.school-admin.academic.academic', compact(
            'teachers',
            'sections',
            'subjects',
            'teachingAssignments',
            'allSubjects',
            'allSections',
            'allSchoolYears'
        ));
    }

    private function sections(Request $request): LengthAwarePaginator
    {
        return SectionController::sectionIndexQuery(
            $request->query('section_search'),
            $request->query('section_status'),
            $request->query('section_grade_level')
        )
            ->paginate(15, ['*'], 'section_page')
            ->appends($request->only('section_search', 'section_status', 'section_grade_level'));
    }

    private function subjects(Request $request): LengthAwarePaginator
    {
        return SubjectController::subjectIndexQuery(
            $request->query('subject_search'),
            $request->query('subject_level')
        )
            ->paginate(15, ['*'], 'subject_page')
            ->appends($request->only('subject_search', 'subject_level'));
    }

    private function teachingAssignments(Request $request): LengthAwarePaginator
    {
        $search = $request->query('assignment_search');
        $status = $request->query('assignment_status');
        $gradeLevel = $request->query('assignment_grade_level');
        $sectionId = $request->query('assignment_section_id');

        return TeachingAssignment::with(['teacher.user', 'subject', 'section', 'schoolYear'])
            ->when(filled($gradeLevel), function ($q) use ($gradeLevel) {
                $q->whereHas('section', function ($secQ) use ($gradeLevel) {
                    $secQ->where('grade_level', $gradeLevel);
                });
            })
            ->when(filled($sectionId), function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            })
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->whereHas('teacher.user', function ($tq) use ($search) {
                        $tq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subject', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($secq) use ($search) {
                        $secq->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when(filled($status), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->join('sections', 'teaching_assignments.section_id', '=', 'sections.id')
            ->select('teaching_assignments.*')
            ->orderBy('sections.grade_level', 'asc')
            ->orderBy('sections.name', 'asc')
            ->paginate(15, ['*'], 'assignment_page')
            ->appends($request->only('assignment_search', 'assignment_status', 'assignment_grade_level', 'assignment_section_id'));
    }
}
