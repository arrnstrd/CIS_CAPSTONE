<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolYear;
use App\Services\Grading\SubjectWeightResolver;
use Illuminate\Http\Request;

class GradingRulesController extends Controller
{
    public function index(Request $request)
    {
        $policyName = "DepEd Standard K-12";
        $activeSchoolYear = SchoolYear::where('is_active', true)->first();
        $schoolYear = $activeSchoolYear?->school_year ?? 'No active school year';
        $lastUpdated = "August 24, 2026";
        $configuredBy = "Admin Office";
        
        // Same resolver fallback used by Grade Sheet calculations when no
        // complete per-assignment grading_configs override exists.
        $weights = app(SubjectWeightResolver::class)->resolve(new Subject());
        $gradingComponents = [
            'written_work' => ['name' => 'Written Work (WW)', 'weight' => $weights['written_work'] * 100, 'color' => '#4CAF50'],
            'performance_task' => ['name' => 'Performance Task (PT)', 'weight' => $weights['performance_task'] * 100, 'color' => '#2196F3'],
            'quarterly_assessment' => ['name' => 'Examination (EX)', 'weight' => $weights['quarterly_assessment'] * 100, 'color' => '#FF9800'],
        ];
        
        $formula = "Final Grade = (WW Average × 0.30) + (PT Average × 0.50) + (TA Average × 0.20)";
        
        $workedExample = [
            'ww_score' => 85,
            'pt_score' => 90,
            'ta_score' => 85,
            'ww_contribution' => 85 * $weights['written_work'],
            'pt_contribution' => 90 * $weights['performance_task'],
            'ta_contribution' => 85 * $weights['quarterly_assessment'],
            'final_grade' => (85 * $weights['written_work']) + (90 * $weights['performance_task']) + (85 * $weights['quarterly_assessment']),
        ];

        $formula = "Initial Grade = (WW PS × {$weights['written_work']}) + (PT PS × {$weights['performance_task']}) + (EX PS × {$weights['quarterly_assessment']})";
        
        $otherRules = [
            ['rule' => 'Passing Grade', 'value' => '75.00'],
            ['rule' => 'Rounding', 'value' => 'round to nearest whole number'],
            ['rule' => 'Decimal Handling', 'value' => '2 decimal places max'],
            ['rule' => 'Zero Score Handling', 'value' => 'counts as 0% in component average'],
            ['rule' => 'Attendance Threshold', 'value' => 'below 85% triggers Risk Indicator'],
            ['rule' => 'Incomplete Policy', 'value' => 'missing scores result in "Incomplete" status'],
            ['rule' => 'Finalization Policy', 'value' => 'locks editing, requires Admin request for changes'],
            ['rule' => 'Honors Criteria', 'value' => 'average ≥ 90 with no failing grades in any subject'],
        ];

        return view('teacher-modules.grading.grading-rules', compact(
            'policyName', 'schoolYear', 'lastUpdated', 'configuredBy',
            'gradingComponents', 'formula', 'workedExample', 'otherRules'
        ));
    }
}
