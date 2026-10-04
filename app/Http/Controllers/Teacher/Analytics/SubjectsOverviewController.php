<?php

namespace App\Http\Controllers\Teacher\Analytics;

use App\Http\Controllers\Controller;
use App\Models\GradingPeriod;
use App\Models\TermGrade;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class SubjectsOverviewController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('pov.teacher.analytics.subjects-overview-index', [
                'gradeLevels' => collect(),
                'sections' => collect(),
                'selectedGradeLevel' => null,
                'selectedSectionId' => null,
                'subjectRows' => collect(),
                'trendData' => ['labels' => [], 'datasets' => []],
            ]);
        }

        $allAssignments = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->get();

        $gradeLevels = $allAssignments->pluck('section.grade_level')->unique()->sort()->values();
        $selectedGradeLevel = $request->input('grade_level') ?: null;
        $selectedSectionId = $request->input('section_id') ?: null;

        $filtered = $allAssignments;
        if ($selectedGradeLevel) {
            $filtered = $filtered->filter(fn ($ta) => $ta->section->grade_level == $selectedGradeLevel);
        }

        $sections = $filtered->pluck('section')->unique('id')->values();

        if ($selectedSectionId) {
            $filtered = $filtered->filter(fn ($ta) => $ta->section_id == $selectedSectionId);
        }

        $subjectRows = collect();
        foreach ($filtered->groupBy(fn ($ta) => $ta->subject->name) as $subjectName => $assignments) {
            $taIds = $assignments->pluck('id');
            $grades = TermGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->get();

            $gradedCount = $grades->count();
            $passingCount = $grades->where('transmuted_grade', '>=', 75)->count();

            $subjectRows->push((object) [
                'subject_name' => $subjectName,
                'avg_grade' => $gradedCount ? round($grades->avg('transmuted_grade'), 1) : null,
                'passing_rate' => $gradedCount ? round(($passingCount / $gradedCount) * 100, 1) : null,
                'failing_rate' => $gradedCount ? round((($gradedCount - $passingCount) / $gradedCount) * 100, 1) : null,
                'highest' => $gradedCount ? $grades->max('transmuted_grade') : null,
                'lowest' => $gradedCount ? $grades->min('transmuted_grade') : null,
            ]);
        }
        $subjectRows = $subjectRows->sortBy('subject_name')->values();

        $gradingPeriods = GradingPeriod::orderBy('sequence')->trimester()->get();
        $trendLabels = $gradingPeriods->map(fn ($p) => 'Term ' . $p->sequence)->values();
        $colors = ['#2438b9', '#f5a623', '#0f9d58', '#6c63ff', '#e05d5d'];

        $datasets = [];
        $colorIndex = 0;
        foreach ($filtered->groupBy(fn ($ta) => $ta->subject->name) as $subjectName => $assignments) {
            $taIds = $assignments->pluck('id');
            $series = [];
            foreach ($gradingPeriods as $period) {
                $periodGrades = TermGrade::whereIn('teaching_assignment_id', $taIds)
                    ->where('grading_period_id', $period->id)
                    ->whereNotNull('transmuted_grade')
                    ->get();
                $series[] = $periodGrades->count() ? round($periodGrades->avg('transmuted_grade'), 1) : null;
            }
            $datasets[] = [
                'label' => $subjectName,
                'data' => $series,
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => $colors[$colorIndex % count($colors)],
                'borderWidth' => 2,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
                'tension' => 0.35,
            ];
            $colorIndex++;
        }

        $divisionChart = $this->buildDivisionChart($allAssignments);

        return view('pov.teacher.analytics.subjects-overview-index', [
            'gradeLevels' => $gradeLevels,
            'sections' => $sections,
            'selectedGradeLevel' => $selectedGradeLevel,
            'selectedSectionId' => $selectedSectionId,
            'subjectRows' => $subjectRows,
            'trendData' => ['labels' => $trendLabels, 'datasets' => $datasets],
            'divisionChart' => $divisionChart,
        ]);
    }

    private function buildDivisionChart($allAssignments): array
    {
        $colors = ['#0f9d58', '#6c63ff', '#2438b9', '#e05d5d', '#f5a623'];

        $gradeLevelsSorted = $allAssignments->pluck('section.grade_level')->unique()->sort()->values();
        $subjectNames = $allAssignments->pluck('subject.name')->unique()->sort()->values();

        $gradeLabels = $gradeLevelsSorted->map(fn ($g) => 'Grade ' . $g)->values();

        $datasets = [];
        foreach ($subjectNames as $i => $subjectName) {
            $data = [];
            foreach ($gradeLevelsSorted as $gradeLevel) {
                $taIds = $allAssignments
                    ->filter(fn ($ta) => $ta->section->grade_level == $gradeLevel && $ta->subject->name === $subjectName)
                    ->pluck('id');

                if ($taIds->isEmpty()) {
                    $data[] = null;
                    continue;
                }

                $grades = TermGrade::whereIn('teaching_assignment_id', $taIds)
                    ->whereNotNull('transmuted_grade')
                    ->get();

                $data[] = $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null;
            }

            $datasets[] = [
                'label' => $subjectName,
                'data' => $data,
                'backgroundColor' => $colors[$i % count($colors)],
                'borderRadius' => 4,
            ];
        }

        return ['labels' => $gradeLabels, 'datasets' => $datasets];
    }
}