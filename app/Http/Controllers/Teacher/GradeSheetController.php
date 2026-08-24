<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\StudentAssessmentScore;
use App\Models\TeachingAssignment;
use App\Services\Grading\GradingService;
use Illuminate\Http\Request;

class GradeSheetController extends Controller
{
    public function show(Request $request, int $teachingAssignmentId)
    {
        $teacher = $request->user()->teacher;

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        $gradingPeriods = GradingPeriod::orderBy('sequence')->where('sequence', '<=', 3)->get();
        $selectedPeriodId = (int) $request->input(
            'grading_period_id',
            $gradingPeriods->where('is_active', true)->sortByDesc('sequence')->first()?->id
                ?? $gradingPeriods->sortByDesc('sequence')->first()?->id
        );

        $categories = AssessmentCategory::all()->keyBy(function ($c) {
            $n = strtolower($c->name);
            if (str_contains($n, 'written')) return 'written';
            if (str_contains($n, 'performance')) return 'performance';
            return 'quarterly';
        });

        $assessmentsByCategory = ['written' => collect(), 'performance' => collect(), 'quarterly' => collect()];

        foreach ($assessmentsByCategory as $key => $_) {
            if (! isset($categories[$key])) {
                continue;
            }
            $assessmentsByCategory[$key] = Assessment::where('teaching_assignment_id', $ta->id)
                ->where('grading_period_id', $selectedPeriodId)
                ->where('assessment_category_id', $categories[$key]->id)
                ->where('status', 'active')
                ->orderBy('id')
                ->get();
        }

        $enrollments = Enrollment::where('section_id', $ta->section_id)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->filter(fn ($e) => $e->student !== null)
            ->sortBy(fn ($e) => $e->student->last_name . $e->student->first_name)
            ->values();

        $allAssessmentIds = collect($assessmentsByCategory)->flatten(1)->pluck('id');

        $scores = StudentAssessmentScore::whereIn('assessment_id', $allAssessmentIds)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->groupBy('enrollment_id');

        $quarterlyGrades = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
            ->where('grading_period_id', $selectedPeriodId)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get()
            ->keyBy('enrollment_id');

        $rows = $enrollments->map(function ($enrollment) use ($scores, $quarterlyGrades, $assessmentsByCategory) {
            $studentScores = $scores->get($enrollment->id, collect())->keyBy('assessment_id');

            $categoryTotals = [];
            foreach ($assessmentsByCategory as $key => $assessments) {
                $sumScore = 0;
                $sumTotal = 0;
                foreach ($assessments as $a) {
                    if ($studentScores->has($a->id)) {
                        $sumScore += (float) $studentScores[$a->id]->score;
                        $sumTotal += (int) $a->total_items;
                    }
                }
                $categoryTotals[$key] = ['ps' => $sumTotal > 0 ? round(($sumScore / $sumTotal) * 100, 2) : null];
            }

            $qg = $quarterlyGrades->get($enrollment->id);
            $mi = $enrollment->student->middle_name ? ' ' . mb_substr($enrollment->student->middle_name, 0, 1) . '.' : '';

            return (object) [
                'enrollment_id' => $enrollment->id,
                'student_name' => $enrollment->student->last_name . ', ' . $enrollment->student->first_name . $mi,
                'scores' => $studentScores,
                'category_totals' => $categoryTotals,
                'written_ws' => $qg->written_work_grade ?? null,
                'performance_ws' => $qg->performance_task_grade ?? null,
                'quarterly_ws' => $qg->quarterly_assessment_grade ?? null,
                'initial_grade' => $qg->initial_grade ?? null,
                'transmuted_grade' => $qg->transmuted_grade ?? null,
            ];
        });

        return view('teacher-modules.grading.grade-sheet', [
            'ta' => $ta,
            'gradingPeriods' => $gradingPeriods,
            'selectedPeriodId' => $selectedPeriodId,
            'assessmentsByCategory' => $assessmentsByCategory,
            'rows' => $rows,
        ]);
    }

    public function storeAssessment(Request $request)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'teaching_assignment_id' => 'required|integer',
            'grading_period_id' => 'required|integer',
            'category' => 'required|in:written,performance,quarterly',
            'title' => 'required|string|max:100',
            'total_items' => 'required|integer|min:1',
        ]);

        $ta = TeachingAssignment::where('id', $data['teaching_assignment_id'])
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $categoryNameMap = [
            'written' => 'Written Work',
            'performance' => 'Performance Task',
            'quarterly' => 'Quarterly Assessment',
        ];

        $category = AssessmentCategory::where('name', $categoryNameMap[$data['category']])->firstOrFail();

        $assessment = Assessment::create([
            'teaching_assignment_id' => $ta->id,
            'assessment_category_id' => $category->id,
            'grading_period_id' => $data['grading_period_id'],
            'title' => $data['title'],
            'total_items' => $data['total_items'],
            'assessment_date' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $assessment->id,
            'title' => $assessment->title,
            'total_items' => $assessment->total_items,
            'category' => $data['category'],
        ]);
    }

    public function storeScore(Request $request, GradingService $gradingService)
    {
        $teacher = $request->user()->teacher;

        $data = $request->validate([
            'assessment_id' => 'required|integer',
            'enrollment_id' => 'required|integer',
            'score' => 'required|numeric|min:0',
        ]);

        $assessment = Assessment::with('teachingAssignment')->findOrFail($data['assessment_id']);

        if (! $assessment->teachingAssignment || $assessment->teachingAssignment->teacher_id !== $teacher->id) {
            abort(403);
        }

        try {
            $gradingService->recordScore($data);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $qg = QuarterlyGrade::where('teaching_assignment_id', $assessment->teaching_assignment_id)
            ->where('enrollment_id', $data['enrollment_id'])
            ->where('grading_period_id', $assessment->grading_period_id)
            ->first();

        return response()->json([
            'written_work_grade' => $qg->written_work_grade,
            'performance_task_grade' => $qg->performance_task_grade,
            'quarterly_assessment_grade' => $qg->quarterly_assessment_grade,
            'initial_grade' => $qg->initial_grade,
            'transmuted_grade' => $qg->transmuted_grade,
        ]);
    }
}