<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\Assessment\StoreAssessmentRequest;
use App\Http\Requests\Grading\Assessment\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Models\TeachingAssignment;
use App\Models\AssessmentCategory;
use App\Models\GradingPeriod;
use App\Services\Grading\AssessmentService;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Display a listing of assessments.
     */
    public function index()
    {
        $assessments = Assessment::with(['teachingAssignment', 'assessmentCategory', 'gradingPeriod'])
            ->orderBy('assessment_date', 'desc')
            ->get();
        return view('grading.assessments.index', compact('assessments'));
    }

    /**
     * Show the form for creating a new assessment.
     */
    public function create()
    {
        $teachingAssignments = TeachingAssignment::with(['subject', 'section', 'schoolYear'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        $assessmentCategories = AssessmentCategory::orderBy('name')->get();
        $gradingPeriods = GradingPeriod::where('is_active', true)->orderBy('sequence')->get();
        
        return view('grading.assessments.create', compact('teachingAssignments', 'assessmentCategories', 'gradingPeriods'));
    }

    /**
     * Store a newly created assessment.
     */
    public function store(StoreAssessmentRequest $request)
    {
        $assessment = $this->assessmentService->create($request->validated());
        return redirect()->route('assessments.index')
            ->with('success', 'Assessment created successfully.');
    }

    /**
     * Display the specified assessment.
     */
    public function show(Assessment $assessment)
    {
        $assessment->load(['teachingAssignment', 'assessmentCategory', 'gradingPeriod', 'studentScores.enrollment.student']);
        return view('grading.assessments.show', compact('assessment'));
    }

    /**
     * Show the form for editing the specified assessment.
     */
    public function edit(Assessment $assessment)
    {
        $assessment->load(['teachingAssignment', 'assessmentCategory', 'gradingPeriod']);
        $teachingAssignments = TeachingAssignment::with(['subject', 'section', 'schoolYear'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        $assessmentCategories = AssessmentCategory::orderBy('name')->get();
        $gradingPeriods = GradingPeriod::where('is_active', true)->orderBy('sequence')->get();
        
        return view('grading.assessments.edit', compact('assessment', 'teachingAssignments', 'assessmentCategories', 'gradingPeriods'));
    }

    /**
     * Update the specified assessment.
     */
    public function update(UpdateAssessmentRequest $request, Assessment $assessment)
    {
        $assessment = $this->assessmentService->update($assessment, $request->validated());
        return redirect()->route('assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    /**
     * Remove the specified assessment.
     */
    public function destroy(Assessment $assessment)
    {
        $this->assessmentService->delete($assessment);
        return redirect()->route('assessments.index')
            ->with('success', 'Assessment deleted successfully.');
    }

    /**
     * Display assessments for a specific teaching assignment.
     */
    public function byTeachingAssignment(TeachingAssignment $teachingAssignment)
    {
        $assessments = $this->assessmentService->getByTeachingAssignment($teachingAssignment->id);
        return view('grading.assessments.by-assignment', compact('teachingAssignment', 'assessments'));
    }

    /**
     * Display assessments for a specific teaching assignment and grading period.
     */
    public function byTeachingAssignmentAndGradingPeriod(TeachingAssignment $teachingAssignment, GradingPeriod $gradingPeriod)
    {
        $assessments = $this->assessmentService->getByTeachingAssignmentAndGradingPeriod($teachingAssignment->id, $gradingPeriod->id);
        return view('grading.assessments.by-assignment-period', compact('teachingAssignment', 'gradingPeriod', 'assessments'));
    }
}
