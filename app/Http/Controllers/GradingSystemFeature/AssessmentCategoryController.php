<?php

namespace App\Http\Controllers\GradingSystemFeature;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\AssessmentCategory\StoreAssessmentCategoryRequest;
use App\Http\Requests\Grading\AssessmentCategory\UpdateAssessmentCategoryRequest;
use App\Models\AssessmentCategory;
use App\Services\Grading\AssessmentCategoryService;

class AssessmentCategoryController extends Controller
{
    protected AssessmentCategoryService $assessmentCategoryService;

    public function __construct(AssessmentCategoryService $assessmentCategoryService)
    {
        $this->assessmentCategoryService = $assessmentCategoryService;
    }

    /**
     * Display a listing of assessment categories.
     */
    public function index()
    {
        $assessmentCategories = $this->assessmentCategoryService->getAll();
        return view('grading.assessment-categories.index', compact('assessmentCategories'));
    }

    /**
     * Show the form for creating a new assessment category.
     */
    public function create()
    {
        return view('grading.assessment-categories.create');
    }

    /**
     * Store a newly created assessment category.
     */
    public function store(StoreAssessmentCategoryRequest $request)
    {
        $assessmentCategory = $this->assessmentCategoryService->create($request->validated());
        return redirect()->route('assessment-categories.index')
            ->with('success', 'Assessment category created successfully.');
    }

    /**
     * Display the specified assessment category.
     */
    public function show(AssessmentCategory $assessmentCategory)
    {
        $assessmentCategory->load('assessments');
        return view('grading.assessment-categories.show', compact('assessmentCategory'));
    }

    /**
     * Show the form for editing the specified assessment category.
     */
    public function edit(AssessmentCategory $assessmentCategory)
    {
        return view('grading.assessment-categories.edit', compact('assessmentCategory'));
    }

    /**
     * Update the specified assessment category.
     */
    public function update(UpdateAssessmentCategoryRequest $request, AssessmentCategory $assessmentCategory)
    {
        $assessmentCategory = $this->assessmentCategoryService->update($assessmentCategory, $request->validated());
        return redirect()->route('assessment-categories.index')
            ->with('success', 'Assessment category updated successfully.');
    }

    /**
     * Remove the specified assessment category.
     */
    public function destroy(AssessmentCategory $assessmentCategory)
    {
        $this->assessmentCategoryService->delete($assessmentCategory);
        return redirect()->route('assessment-categories.index')
            ->with('success', 'Assessment category deleted successfully.');
    }
}
