<?php

namespace App\Http\Controllers\SchoolAdmin\Academic;

use App\Events\SubjectUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\StoreSubjectRequest;
use App\Http\Requests\SchoolAdmin\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SchoolAdmin\SubjectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    protected SubjectService $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    /**
     * Display a listing of subjects.
     */
    public static function subjectIndexQuery(?string $search = null, ?string $level = null): Builder
    {
        $level = Subject::normalizeLevel($level);

        return Subject::query()
            ->when(filled($search), function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%");
                });
            })
            ->when(filled($level), fn(Builder $query) => $query->where('level', $level))
            ->orderBy('name');
    }

    public function index(Request $request)
    {
        $subjects = self::subjectIndexQuery(
            $request->query('subject_search'),
            $request->query('subject_level')
        )
            ->paginate(15, ['*'], 'subject_page')
            ->appends($request->only('subject_search', 'subject_level'));

        return view('pov.school-admin.academic.subjects', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return redirect()->route('academic.index');
    }

    /**
     * Store a newly created subject (single or mass via comma-separated names).
     */
    public function store(StoreSubjectRequest $request)
    {
        $validated = $request->validated();

        // Mass assignment: a comma-separated list of subject names.
        if (filled($validated['names'] ?? null)) {
            $names = collect(explode(',', $validated['names']))
                ->map(fn($name) => trim($name))
                ->filter(fn($name) => filled($name))
                ->unique()
                ->values();

            $subjects = $this->subjectService->createMany([
                'names' => $names,
                'level' => $validated['level'],
                'code_mode' => $validated['code_mode'] ?? null,
            ]);

            foreach ($subjects as $subject) {
                try {
                    SubjectUpdated::dispatch('created', $subject->toArray());
                } catch (\Throwable $e) {
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'message' => count($subjects) . ' subject(s) created successfully.',
                    'data' => $subjects,
                ]);
            }

            return redirect()->route('subjects.index')
                ->with('success', count($subjects) . ' subject(s) created successfully.');
        }

        $subject = $this->subjectService->create($validated);

        try {
            SubjectUpdated::dispatch('created', $subject->toArray());
        } catch (\Throwable $e) {
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Subject created successfully.',
                'data' => $subject,
            ]);
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        return response()->json($subject);
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return response()->json($subject);
    }

    /**
     * Update the specified subject.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject = $this->subjectService->update($subject, $request->validated());

        try {
            SubjectUpdated::dispatch('updated', $subject->toArray());
        } catch (\Throwable $e) {
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Subject updated successfully.',
                'data' => $subject,
            ]);
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        $this->subjectService->delete($subject);

        try {
            SubjectUpdated::dispatch('deleted', ['id' => $subject->id]);
        } catch (\Throwable $e) {
        }

        if (request()->ajax()) {
            return response()->json([
                'message' => 'Subject deleted successfully.',
                'data' => ['id' => $subject->id],
            ]);
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Bulk delete multiple subjects.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $ids = $request->input('ids', []);

        try {
            $count = Subject::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => $count . ' subject(s) deleted successfully.',
                'affected' => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete selected subjects. They may be referenced by other records (e.g., sections, schedules, or grades).',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
