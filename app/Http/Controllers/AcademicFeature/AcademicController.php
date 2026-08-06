<?php

namespace App\Http\Controllers\AcademicFeature;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AcademicController extends Controller
{
    public function index(Request $request)
    {
        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        $activeSections = Section::with('advisor')
            ->where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

        $notEnrolledStudents = $activeSchoolYear
            ? $this->notEnrolledStudents($request, $activeSchoolYear->id)
            : new LengthAwarePaginator([], 0, 10, 1, ['pageName' => 'enrollment_page']);

        $sections = $this->sections($request);
        $subjects = $this->subjects($request);

        return view('admin-modules.academic.academic', compact(
            'activeSchoolYear',
            'activeSections',
            'teachers',
            'notEnrolledStudents',
            'sections',
            'subjects'
        ));
    }

    private function notEnrolledStudents(Request $request, int $schoolYearId): LengthAwarePaginator
    {
        $search = $request->query('enrollment_search');

        return Student::withoutCurrentEnrollment($schoolYearId)
            ->when(filled($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('student_number', 'like', "%{$search}%")
                        ->orWhere('lrn', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15, ['*'], 'enrollment_page')
            ->appends($request->only('enrollment_search'));
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
}
