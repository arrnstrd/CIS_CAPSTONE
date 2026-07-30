<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeachingAssignmentRequest;
use App\Http\Requests\Teacher\UpdateTeachingAssignmentRequest;
use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Services\Teacher\TeachingAssignmentService;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function __construct(protected TeachingAssignmentService $teachingAssignmentService) {}

    /**
     * Display a listing of teaching assignments.
     */
    public function index()
    {
        $teachingAssignments = TeachingAssignment::with(['teacher', 'subject', 'section', 'schoolYear'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $subjects = Subject::orderBy('name')->get();
        $sections = Section::where('status', 'active')->orderBy('name')->get();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return view('teacher-modules.schedule-config', compact('teachingAssignments', 'teachers', 'subjects', 'sections', 'schoolYears'));
    }

    /**
     * Show the form for creating a new teaching assignment.
     */
    public function create()
    {
        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $subjects = Subject::orderBy('name')->get();
        $sections = Section::where('status', 'active')->orderBy('name')->get();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return view('teacher.teaching-assignments.create', compact('teachers', 'subjects', 'sections', 'schoolYears'));
    }

    /**
     * Store a newly created teaching assignment.
     */
    public function store(StoreTeachingAssignmentRequest $request)
    {
        try {
            $teachingAssignment = $this->teachingAssignmentService->create($request->validated());
            $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Teaching assignment created successfully.',
                    'data' => $teachingAssignment,
                ], 201);
            }

            return redirect()->route('teaching-assignments.index')
                ->with('success', 'Teaching assignment created successfully.');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified teaching assignment.
     */
    public function show(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);
        return view('teacher.teaching-assignments.show', compact('teachingAssignment'));
    }

    /**
     * Show the form for editing the specified teaching assignment.
     */
    public function edit(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);
        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $subjects = Subject::orderBy('name')->get();
        $sections = Section::where('status', 'active')->orderBy('name')->get();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();

        return view('teacher.teaching-assignments.edit', compact('teachingAssignment', 'teachers', 'subjects', 'sections', 'schoolYears'));
    }

    /**
     * Update the specified teaching assignment.
     */
    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment)
    {
        try {
            $teachingAssignment = $this->teachingAssignmentService->update($teachingAssignment, $request->validated());
            $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Teaching assignment updated successfully.',
                    'data' => $teachingAssignment,
                ]);
            }

            return redirect()->route('teaching-assignments.index')
                ->with('success', 'Teaching assignment updated successfully.');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified teaching assignment.
     */
    public function destroy(Request $request, TeachingAssignment $teachingAssignment)
    {
        $this->teachingAssignmentService->delete($teachingAssignment);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Teaching assignment deleted successfully.']);
        }

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Teaching assignment deleted successfully.');
    }

    /**
     * Display teaching assignments for a specific teacher.
     */
    public function byTeacher(Request $request, Teacher $teacher)
    {
        $schoolYearId = $request->query('school_year_id');

        if ($schoolYearId) {
            $teachingAssignments = $this->teachingAssignmentService->getByTeacherAndSchoolYear($teacher->id, $schoolYearId);
        } else {
            $teachingAssignments = TeachingAssignment::with(['subject', 'section', 'schoolYear'])
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('teacher.teaching-assignments.by-teacher', compact('teacher', 'teachingAssignments'));
    }

    /**
     * Display teaching assignments for a specific section.
     */
    public function bySection(Request $request, Section $section)
    {
        $schoolYearId = $request->query('school_year_id');

        if ($schoolYearId) {
            $teachingAssignments = $this->teachingAssignmentService->getBySectionAndSchoolYear($section->id, $schoolYearId);
        } else {
            $teachingAssignments = TeachingAssignment::with(['teacher', 'subject', 'schoolYear'])
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('teacher.teaching-assignments.by-section', compact('section', 'teachingAssignments'));
    }
}
