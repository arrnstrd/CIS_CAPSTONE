<?php

namespace App\Http\Controllers\AcademicFeature;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Subject\StoreSubjectRequest;
use App\Http\Requests\Academic\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\Academic\SubjectService;
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

        return view('academic.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return view('academic.subjects.create');
    }

    /**
     * Store a newly created subject.
     */
    public function store(StoreSubjectRequest $request)
    {
        $subject = $this->subjectService->create($request->validated());

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
        return view('academic.subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return view('academic.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject = $this->subjectService->update($subject, $request->validated());

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

        if (request()->ajax()) {
            return response()->json([
                'message' => 'Subject deleted successfully.',
                'data' => ['id' => $subject->id],
            ]);
        }

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
