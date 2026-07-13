<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\Subject\StoreSubjectRequest;
use App\Http\Requests\Academic\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\Academic\SubjectService;
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
    public function index()
    {
        $subjects = Subject::orderBy('name')->get();
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
        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        $this->subjectService->delete($subject);
        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
