<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\GradingPeriod\StoreGradingPeriodRequest;
use App\Http\Requests\Grading\GradingPeriod\UpdateGradingPeriodRequest;
use App\Models\GradingPeriod;
use App\Services\Grading\GradingPeriodService;

class GradingPeriodController extends Controller
{
    protected GradingPeriodService $gradingPeriodService;

    public function __construct(GradingPeriodService $gradingPeriodService)
    {
        $this->gradingPeriodService = $gradingPeriodService;
    }

    /**
     * Display a listing of grading periods.
     */
    public function index()
    {
        $gradingPeriods = $this->gradingPeriodService->getAll();
        return view('grading.grading-periods.index', compact('gradingPeriods'));
    }

    /**
     * Show the form for creating a new grading period.
     */
    public function create()
    {
        return view('grading.grading-periods.create');
    }

    /**
     * Store a newly created grading period.
     */
    public function store(StoreGradingPeriodRequest $request)
    {
        $gradingPeriod = $this->gradingPeriodService->create($request->validated());
        return redirect()->route('grading-periods.index')
            ->with('success', 'Grading period created successfully.');
    }

    /**
     * Display the specified grading period.
     */
    public function show(GradingPeriod $gradingPeriod)
    {
        $gradingPeriod->load('assessments', 'quarterlyGrades');
        return view('grading.grading-periods.show', compact('gradingPeriod'));
    }

    /**
     * Show the form for editing the specified grading period.
     */
    public function edit(GradingPeriod $gradingPeriod)
    {
        return view('grading.grading-periods.edit', compact('gradingPeriod'));
    }

    /**
     * Update the specified grading period.
     */
    public function update(UpdateGradingPeriodRequest $request, GradingPeriod $gradingPeriod)
    {
        $gradingPeriod = $this->gradingPeriodService->update($gradingPeriod, $request->validated());
        return redirect()->route('grading-periods.index')
            ->with('success', 'Grading period updated successfully.');
    }

    /**
     * Remove the specified grading period.
     */
    public function destroy(GradingPeriod $gradingPeriod)
    {
        $this->gradingPeriodService->delete($gradingPeriod);
        return redirect()->route('grading-periods.index')
            ->with('success', 'Grading period deleted successfully.');
    }
}
