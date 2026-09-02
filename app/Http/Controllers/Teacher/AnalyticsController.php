<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\QuarterlyGrade;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.analytics.analytics-index', [
                'gradeLevels' => collect(),
                'sections' => collect(),
                'subjects' => collect(),
                'overview' => null,
                'gradeDistribution' => null,
                'termTrend' => null,
                'assessmentPerformance' => null,
                'topPerformers' => collect(),
                'sectionComparison' => collect(),
                'performanceInsights' => [],
                'performanceStatus' => ['label' => 'Not enough data', 'level' => 'unknown'],
                'classHealth' => null,
                'studentSnapshot' => null,
            ]);
        }

        $baseQuery = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section', 'subject');

        $allAssignments = $baseQuery->get();

        $gradeLevels = $allAssignments->pluck('section.grade_level')->unique()->sort()->values();
        $sections = $allAssignments->pluck('section')->unique('id')->sortBy('name')->values();
        $subjects = $allAssignments->pluck('subject')->unique('id')->sortBy('name')->values();

        $filtered = $allAssignments;

        if ($request->filled('grade_level')) {
            $filtered = $filtered->filter(fn ($ta) => $ta->section->grade_level == $request->grade_level);
        }

        if ($request->filled('section_id')) {
            $filtered = $filtered->filter(fn ($ta) => $ta->section_id == $request->section_id);
        }

        if ($request->filled('subject_id')) {
            $filtered = $filtered->filter(fn ($ta) => $ta->subject_id == $request->subject_id);
        }

        $taIds = $filtered->pluck('id');

        $gradesQuery = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade');

        if ($request->filled('term')) {
            $gradesQuery->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', $request->term));
        }

        $grades = $gradesQuery->get();

        $overview = [
            'class_average' => $grades->count() ? round($grades->avg('transmuted_grade'), 1) : null,
            'passing_rate' => $grades->count() ? round($grades->where('transmuted_grade', '>=', 75)->count() / $grades->count() * 100, 1) : null,
            'highest' => $grades->count() ? round($grades->max('transmuted_grade'), 1) : null,
            'lowest' => $grades->count() ? round($grades->min('transmuted_grade'), 1) : null,
            'students_assessed' => $grades->pluck('enrollment_id')->unique()->count(),
        ];
        $overview['completion_rate'] = $this->getCompletionRate($filtered, $overview);
        $studentSnapshot = $this->getStudentSnapshot($grades, $overview);

        $performanceStatus = $this->getPerformanceStatus($overview['class_average'] !== null ? (float) $overview['class_average'] : null);
        $gradeDistribution = $this->getGradeDistribution($grades);
        $termTrend = $this->getTermTrend($taIds);
        $assessmentPerformance = $this->getAssessmentPerformance($taIds, $request->input('term'));
        $classHealth = $this->getClassHealthIndicators($taIds, $request->input('term'));
        $topPerformers = $this->getTopPerformers($filtered, $request->input('term'));
        $sectionComparison = $this->getSectionComparison($filtered, $request->input('term'));
        $performanceInsights = $this->getPerformanceInsights($overview, $gradeDistribution, $termTrend, $assessmentPerformance);

        return view('teacher-modules.analytics.analytics-index', compact(
            'gradeLevels', 'sections', 'subjects', 'overview', 'performanceStatus', 'gradeDistribution', 'termTrend', 'assessmentPerformance', 'classHealth', 'studentSnapshot', 'topPerformers', 'sectionComparison', 'performanceInsights'
        ));
    }

    private function getGradeDistribution($grades): array
    {
        $buckets = [
            '90-100' => 0,
            '85-89' => 0,
            '80-84' => 0,
            '75-79' => 0,
            'Below 75' => 0,
        ];

        foreach ($grades as $grade) {
            $g = $grade->transmuted_grade;

            if ($g >= 90) {
                $buckets['90-100']++;
            } elseif ($g >= 85) {
                $buckets['85-89']++;
            } elseif ($g >= 80) {
                $buckets['80-84']++;
            } elseif ($g >= 75) {
                $buckets['75-79']++;
            } else {
                $buckets['Below 75']++;
            }
        }

        return $buckets;
    }

    private function getTermTrend($taIds): array
    {
        $allGrades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->with('gradingPeriod')
            ->get()
            ->filter(fn ($g) => ($g->gradingPeriod->sequence ?? 99) <= 3);

        $byTerm = $allGrades->groupBy(fn ($g) => $g->gradingPeriod->sequence);

        $trend = [];

        for ($t = 1; $t <= 3; $t++) {
            $termGrades = $byTerm->get($t, collect());
            $trend[$t] = $termGrades->count() ? round($termGrades->avg('transmuted_grade'), 1) : null;
        }

        return $trend;
    }

    private function getAssessmentPerformance($taIds, $term = null): array
    {
        $assessmentsQuery = Assessment::whereIn('teaching_assignment_id', $taIds)
            ->with('assessmentCategory', 'studentAssessmentScores');

        if ($term) {
            $assessmentsQuery->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', $term));
        }

        $assessments = $assessmentsQuery->get();

        $categoryLabels = ['Written Work', 'Performance Task', 'Quarterly Assessment'];
        $percentagesByCategory = array_fill_keys($categoryLabels, []);

        foreach ($assessments as $assessment) {
            $categoryName = $assessment->assessmentCategory->name ?? null;

            if (! in_array($categoryName, $categoryLabels) || ! $assessment->total_items) {
                continue;
            }

            foreach ($assessment->studentAssessmentScores as $score) {
                if ($score->score === null) {
                    continue;
                }

                $percentagesByCategory[$categoryName][] = ($score->score / $assessment->total_items) * 100;
            }
        }

        $result = [];

        foreach ($categoryLabels as $label) {
            $values = $percentagesByCategory[$label];
            $result[$label] = count($values) ? round(array_sum($values) / count($values), 1) : null;
        }

        return $result;
    }

    private function getTopPerformers($teachingAssignments, $term = null): \Illuminate\Support\Collection
    {
        $taIds = $teachingAssignments instanceof \Illuminate\Support\Collection && $teachingAssignments->first() instanceof TeachingAssignment
            ? $teachingAssignments->pluck('id')
            : collect($teachingAssignments);

        if ($term) {
            $termSequence = $term;
        } else {
            $currentPeriod = GradingPeriod::where('is_active', true)
                ->where('sequence', '<=', 3)
                ->orderBy('sequence')
                ->first()
                ?? GradingPeriod::where('sequence', '<=', 3)
                    ->orderBy('sequence')
                    ->first();
            $termSequence = $currentPeriod?->sequence ?? 1;
        }

        $gradesQuery = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', $termSequence))
            ->with(['enrollment.student', 'enrollment.section']);

        $grades = $gradesQuery->get();

        $validGrades = $grades->filter(fn ($g) => $g->enrollment && $g->enrollment->student);

        $groupedByGradeLevel = $validGrades->groupBy(function ($grade) {
            return (string) ($grade->enrollment->grade_level ?? $grade->enrollment->section->grade_level ?? 'Unassigned');
        });

        $result = collect();

        foreach ($groupedByGradeLevel->sortKeys() as $gradeLevel => $levelGrades) {
            $byStudent = $levelGrades->groupBy(fn ($g) => $g->enrollment->student_id);

            $studentPerformers = $byStudent->map(function ($studentGrades) {
                $student = $studentGrades->first()->enrollment->student;
                $section = $studentGrades->first()->enrollment->section;
                $avg = round($studentGrades->avg('transmuted_grade'), 1);

                return [
                    'name' => $student->full_name,
                    'average' => $avg,
                    'section' => $section ? $section->name : '—',
                ];
            });

            $topThree = $studentPerformers->sort(function ($a, $b) {
                if ($b['average'] == $a['average']) {
                    return strcmp($a['name'], $b['name']);
                }
                return $b['average'] <=> $a['average'];
            })->take(3)->values();

            $ranked = $topThree->map(function ($item, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $item['name'],
                    'average' => $item['average'],
                    'section' => $item['section'],
                ];
            });

            if ($ranked->isNotEmpty()) {
                $result->put($gradeLevel, $ranked);
            }
        }

        return $result;
    }

    private function getSectionComparison($teachingAssignments, $term = null): \Illuminate\Support\Collection
    {
        $assignments = $teachingAssignments instanceof \Illuminate\Support\Collection && $teachingAssignments->first() instanceof TeachingAssignment
            ? $teachingAssignments
            : TeachingAssignment::whereIn('id', collect($teachingAssignments))->with('section')->get();

        $bySection = $assignments->groupBy('section_id');

        if ($bySection->count() <= 1) {
            return collect();
        }

        if ($term) {
            $termSequence = $term;
        } else {
            $currentPeriod = GradingPeriod::where('is_active', true)
                ->where('sequence', '<=', 3)
                ->orderBy('sequence')
                ->first()
                ?? GradingPeriod::where('sequence', '<=', 3)
                    ->orderBy('sequence')
                    ->first();
            $termSequence = $currentPeriod?->sequence ?? 1;
        }

        $results = collect();

        foreach ($bySection as $sectionId => $sectionAssignments) {
            $section = $sectionAssignments->first()->section;
            $taIds = $sectionAssignments->pluck('id');

            $gradesQuery = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', $termSequence));

            $grades = $gradesQuery->get();
            $count = $grades->count();

            $classAverage = $count ? round($grades->avg('transmuted_grade'), 1) : null;
            $passingRate = $count ? round($grades->where('transmuted_grade', '>=', 75)->count() / $count * 100, 1) : null;
            $studentsAssessed = $grades->pluck('enrollment_id')->unique()->count();

            $results->push([
                'section' => $section ? $section->name : 'Section ' . $sectionId,
                'grade_level' => $section ? $section->grade_level : '—',
                'class_average' => $classAverage,
                'passing_rate' => $passingRate,
                'students_assessed' => $studentsAssessed,
            ]);
        }

        return $results->sortByDesc('class_average')->values();
    }

    private function getPerformanceInsights($overview, $gradeDistribution, $termTrend, $assessmentPerformance): array
    {
        $insights = [];

        $avg = $overview['class_average'] ?? null;
        $passingRate = $overview['passing_rate'] ?? null;
        $totalAssessed = $overview['students_assessed'] ?? 0;

        // 1. Overall class standing
        if ($avg !== null) {
            if ($avg >= 90) {
                $insights[] = [
                    'type' => 'positive',
                    'text' => "Outstanding class standing with an overall average of {$avg}.",
                ];
            } elseif ($avg < 75) {
                $insights[] = [
                    'type' => 'warning',
                    'text' => "Overall class average ({$avg}) is below the 75 passing threshold.",
                ];
            }
        }

        // 2. Passing rate flag
        if ($passingRate !== null) {
            if ($passingRate < 75) {
                $insights[] = [
                    'type' => 'warning',
                    'text' => "Passing rate is currently at {$passingRate}%, indicating a need for academic intervention.",
                ];
            } elseif ($passingRate >= 90) {
                $insights[] = [
                    'type' => 'positive',
                    'text' => "Strong academic retention with a high passing rate of {$passingRate}%.",
                ];
            }
        }

        // 3. Weakest assessment category (WW/PT/QA) if below 80%
        if ($assessmentPerformance) {
            $validAssess = array_filter($assessmentPerformance, fn ($v) => $v !== null);
            if (! empty($validAssess)) {
                asort($validAssess);
                $weakestCategory = array_key_first($validAssess);
                $lowestScore = $validAssess[$weakestCategory];

                if ($lowestScore < 80) {
                    $insights[] = [
                        'type' => 'warning',
                        'text' => "Students are experiencing the most difficulty in {$weakestCategory} (average score: {$lowestScore}%).",
                    ];
                } elseif ($lowestScore >= 85 && count($validAssess) === count($assessmentPerformance)) {
                    $insights[] = [
                        'type' => 'positive',
                        'text' => "Consistent mastery across all assessment components with scores above 85%.",
                    ];
                }
            }
        }

        // 4. Concentration of students Below 75 in grade distribution (>= 30%)
        if ($totalAssessed > 0 && isset($gradeDistribution['Below 75'])) {
            $below75Count = $gradeDistribution['Below 75'];
            $below75Pct = round(($below75Count / $totalAssessed) * 100, 1);

            if ($below75Pct >= 30 && $below75Count > 0) {
                $insights[] = [
                    'type' => 'warning',
                    'text' => "High concentration of struggling learners: {$below75Pct}% of assessed students ({$below75Count}) are below 75.",
                ];
            }
        }

        // 5. Term-over-term trend direction (Term 1 vs Term 2)
        if (isset($termTrend[1], $termTrend[2]) && $termTrend[1] !== null && $termTrend[2] !== null) {
            $t1 = $termTrend[1];
            $t2 = $termTrend[2];
            $diff = round($t2 - $t1, 1);

            if ($diff > 0) {
                $insights[] = [
                    'type' => 'positive',
                    'text' => "Positive term-over-term progression: class average increased by {$diff} points from Term 1 ({$t1}) to Term 2 ({$t2}).",
                ];
            } elseif ($diff < 0) {
                $absDiff = abs($diff);
                $insights[] = [
                    'type' => 'warning',
                    'text' => "Downward trend observed: class average decreased by {$absDiff} points from Term 1 ({$t1}) to Term 2 ({$t2}).",
                ];
            } else {
                $insights[] = [
                    'type' => 'info',
                    'text' => "Stable performance: class average remained constant at {$t1} between Term 1 and Term 2.",
                ];
            }
        } elseif ($avg !== null && count($insights) < 3) {
            $insights[] = [
                'type' => 'info',
                'text' => "Current active assessment data shows a class average of {$avg} with {$totalAssessed} students assessed.",
            ];
        }

        // 6. Fallback if empty
        if (empty($insights)) {
            return [
                [
                    'type' => 'info',
                    'text' => 'Not enough data yet to generate insights.',
                ],
            ];
        }

        return array_slice($insights, 0, 5);
    }

    private function getPerformanceStatus(?float $classAverage): array
    {
        if ($classAverage === null) {
            return ['label' => 'Not enough data', 'level' => 'unknown'];
        }

        return match (true) {
            $classAverage >= 90 => ['label' => 'Excellent', 'level' => 'excellent'],
            $classAverage >= 85 => ['label' => 'Good', 'level' => 'good'],
            $classAverage >= 75 => ['label' => 'Satisfactory', 'level' => 'satisfactory'],
            default => ['label' => 'Needs Improvement', 'level' => 'needs-improvement'],
        };
    }

    private function getCompletionRate($filtered, array $overview): ?float
    {
        $sectionIds = $filtered->pluck('section_id')->unique();

        $totalStudents = Enrollment::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->count();

        if ($totalStudents === 0) {
            return null;
        }

        return round(($overview['students_assessed'] / $totalStudents) * 100, 1);
    }

    private function getStudentSnapshot($grades, array $overview): array
    {
        $values = $grades->pluck('transmuted_grade')
            ->filter(fn ($v) => $v !== null)
            ->sort()
            ->values();

        $count = $values->count();
        $median = null;

        if ($count > 0) {
            $mid = intdiv($count, 2);
            $median = $count % 2 === 0
                ? round(($values[$mid - 1] + $values[$mid]) / 2, 1)
                : round($values[$mid], 1);
        }

        return [
            'highest' => $overview['highest'],
            'lowest' => $overview['lowest'],
            'class_average' => $overview['class_average'],
            'median' => $median,
        ];
    }

    private function getClassHealthIndicators($taIds, $term = null): array
    {
        if ($term) {
            $currentSequence = (int) $term;
        } else {
            $currentPeriod = GradingPeriod::where('is_active', true)
                ->where('sequence', '<=', 3)
                ->orderBy('sequence')
                ->first()
                ?? GradingPeriod::where('sequence', '<=', 3)
                    ->orderBy('sequence')
                    ->first();
            $currentSequence = $currentPeriod?->sequence ?? 1;
        }

        $result = [
            'improving' => 0,
            'stable' => 0,
            'declining' => 0,
            'insufficient_data' => 0,
            'current_term' => $currentSequence,
            'previous_term' => $currentSequence > 1 ? $currentSequence - 1 : null,
            'has_comparison' => $currentSequence > 1,
        ];

        if ($currentSequence <= 1) {
            $term1Count = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
                ->whereNotNull('transmuted_grade')
                ->whereHas('gradingPeriod', fn ($q) => $q->where('sequence', 1))
                ->pluck('enrollment_id')
                ->unique()
                ->count();
            $result['insufficient_data'] = $term1Count;

            return $result;
        }

        $prevSequence = $currentSequence - 1;

        $grades = QuarterlyGrade::whereIn('teaching_assignment_id', $taIds)
            ->whereNotNull('transmuted_grade')
            ->whereHas('gradingPeriod', fn ($q) => $q->whereIn('sequence', [$prevSequence, $currentSequence]))
            ->with('gradingPeriod')
            ->get();

        $byStudent = $grades->groupBy('enrollment_id');

        foreach ($byStudent as $enrollmentId => $studentGrades) {
            $currGrades = $studentGrades->filter(fn ($g) => $g->gradingPeriod && $g->gradingPeriod->sequence == $currentSequence);
            $prevGrades = $studentGrades->filter(fn ($g) => $g->gradingPeriod && $g->gradingPeriod->sequence == $prevSequence);

            if ($currGrades->isEmpty() || $prevGrades->isEmpty()) {
                $result['insufficient_data']++;
                continue;
            }

            $currAvg = (float) $currGrades->avg('transmuted_grade');
            $prevAvg = (float) $prevGrades->avg('transmuted_grade');
            $diff = round($currAvg - $prevAvg, 2);

            if ($diff >= 2.00) {
                $result['improving']++;
            } elseif ($diff <= -2.00) {
                $result['declining']++;
            } else {
                $result['stable']++;
            }
        }

        return $result;
    }
}