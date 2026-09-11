<?php

namespace App\Http\Controllers\SchoolAdmin\TeachingAssignments;

use App\Events\TeachingAssignmentUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\StoreTeachingAssignmentRequest;
use App\Http\Requests\SchoolAdmin\UpdateTeachingAssignmentRequest;
use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Services\SchoolAdmin\TeachingAssignmentService;
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

        return view('pov.school-admin.teaching-assignments.teaching-assignments', compact('teachingAssignments', 'teachers', 'subjects', 'sections', 'schoolYears'));
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

        return view('pov.teacher.teaching-assignments.create', compact('teachers', 'subjects', 'sections', 'schoolYears'));
    }

    /**
     * Get section context including primary grade status and eligible subjects with live assignment info.
     */
    public function sectionContext(Request $request)
    {
        $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'school_year_id' => ['nullable', 'exists:school_years,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $section = Section::findOrFail($request->query('section_id'));
        $gradeLevel = (int) $section->grade_level;
        $isPrimaryGrade = in_array($gradeLevel, [1, 2, 3], true);

        $schoolYearId = $request->query('school_year_id') ?: SchoolYear::where('is_active', true)->value('id');

        $level = Subject::normalizeLevel($section->level);
        $subjectsQuery = Subject::query();
        if ($isPrimaryGrade) {
            $subjectsQuery->where('level', 'elementary');
        } elseif ($level) {
            $subjectsQuery->where('level', $level);
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        $existingAssignments = collect();
        if ($schoolYearId) {
            $existingAssignments = TeachingAssignment::with('teacher.user')
                ->where('section_id', $section->id)
                ->where('school_year_id', $schoolYearId)
                ->get()
                ->keyBy('subject_id');
        }

        $currentTeacherId = $request->query('teacher_id');

        $subjectsData = $subjects->map(function ($subject) use ($existingAssignments, $currentTeacherId) {
            $existing = $existingAssignments->get($subject->id);
            $isAssigned = $existing !== null;
            $isCurrentTeacher = $isAssigned && $currentTeacherId && ((int) $existing->teacher_id === (int) $currentTeacherId);

            return [
                'id' => $subject->id,
                'code' => $subject->code,
                'name' => $subject->name,
                'level' => $subject->level,
                'level_label' => $subject->level_label,
                'is_assigned' => $isAssigned,
                'is_current_teacher' => $isCurrentTeacher,
                'assigned_teacher_id' => $existing?->teacher_id,
                'assigned_teacher_name' => $existing?->teacher?->full_name,
            ];
        });

        return response()->json([
            'section' => [
                'id' => $section->id,
                'name' => $section->name,
                'grade_level' => $section->grade_level,
                'level' => $section->level,
            ],
            'is_primary_grade' => $isPrimaryGrade,
            'subjects' => $subjectsData,
        ]);
    }

    /**
     * Store a newly created teaching assignment (supports single or mass subject assignment).
     */
    public function store(StoreTeachingAssignmentRequest $request)
    {
        try {
            $validated = $request->validated();

            if (!empty($validated['subject_ids']) && is_array($validated['subject_ids'])) {
                $assignments = $this->teachingAssignmentService->createBatch($validated);

                foreach ($assignments as $assignment) {
                    $assignment->load(['teacher', 'subject', 'section', 'schoolYear']);
                    try {
                        TeachingAssignmentUpdated::dispatch('created', $assignment->toArray());
                    } catch (\Throwable $e) {
                    }
                }

                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => count($assignments) . ' teaching assignment(s) created successfully.',
                        'data' => $assignments,
                    ], 201);
                }

                return redirect()->route('teaching-assignments.index')
                    ->with('success', count($assignments) . ' teaching assignment(s) created successfully.');
            }

            $teachingAssignment = $this->teachingAssignmentService->create($validated);
            $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);

            try {
                TeachingAssignmentUpdated::dispatch('created', $teachingAssignment->toArray());
            } catch (\Throwable $e) {
            }

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Teaching assignment created successfully.',
                    'data' => $teachingAssignment,
                ], 201);
            }

            return redirect()->route('teaching-assignments.index')
                ->with('success', 'Teaching assignment created successfully.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
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
        return view('pov.teacher.teaching-assignments.show', compact('teachingAssignment'));
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

        return view('pov.teacher.teaching-assignments.edit', compact('teachingAssignment', 'teachers', 'subjects', 'sections', 'schoolYears'));
    }

    /**
     * Update the specified teaching assignment.
     */
    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment)
    {
        try {
            $teachingAssignment = $this->teachingAssignmentService->update($teachingAssignment, $request->validated());
            $teachingAssignment->load(['teacher', 'subject', 'section', 'schoolYear']);

            try {
                TeachingAssignmentUpdated::dispatch('updated', $teachingAssignment->toArray());
            } catch (\Throwable $e) {
            }

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Teaching assignment updated successfully.',
                    'data' => $teachingAssignment,
                ]);
            }

            return redirect()->route('teaching-assignments.index')
                ->with('success', 'Teaching assignment updated successfully.');
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
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

        try {
            TeachingAssignmentUpdated::dispatch('deleted', ['id' => $teachingAssignment->id, 'section_id' => $teachingAssignment->section_id]);
        } catch (\Throwable $e) {
        }

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Teaching assignment deleted successfully.']);
        }

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Teaching assignment deleted successfully.');
    }

    /**
     * Bulk delete multiple teaching assignments.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:teaching_assignments,id'],
        ]);

        $ids = $request->input('ids', []);

        $assignments = TeachingAssignment::whereIn('id', $ids)->get();

        foreach ($assignments as $assignment) {
            $this->teachingAssignmentService->delete($assignment);
            try {
                TeachingAssignmentUpdated::dispatch('deleted', ['id' => $assignment->id, 'section_id' => $assignment->section_id]);
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'message' => count($assignments) . ' teaching assignment(s) deleted successfully.',
            'affected' => count($assignments),
        ]);
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

        return view('pov.teacher.teaching-assignments.by-teacher', compact('teacher', 'teachingAssignments'));
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

        return view('pov.teacher.teaching-assignments.by-section', compact('section', 'teachingAssignments'));
    }
}
