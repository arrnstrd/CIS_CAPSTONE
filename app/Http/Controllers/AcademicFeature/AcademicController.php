<?php

namespace App\Http\Controllers\AcademicFeature;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
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

        $sections = $this->sections($request);
        $subjects = $this->subjects($request);

        return view('admin-modules.academic.academic', compact(
            'teachers',
            'sections',
            'subjects'
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
}
