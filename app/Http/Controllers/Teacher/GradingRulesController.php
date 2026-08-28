<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GradingRulesController extends Controller
{
    public function index(Request $request)
    {
        $policyName = "DepEd Standard K-12";
        $schoolYear = "2026-2027"; // Hardcoded for now
        $lastUpdated = "August 24, 2026";
        $configuredBy = "Admin Office";
        
        // Grading components with flat 30/50/20 distribution
        $gradingComponents = [
            'written_work' => ['name' => 'Written Work (WW)', 'weight' => 30, 'color' => '#4CAF50'],
            'performance_task' => ['name' => 'Performance Task (PT)', 'weight' => 50, 'color' => '#2196F3'],
            'quarterly_assessment' => ['name' => 'Teacher Assessment (TA)', 'weight' => 20, 'color' => '#FF9800'],
        ];
        
        $formula = "Final Grade = (WW Average × 0.30) + (PT Average × 0.50) + (TA Average × 0.20)";
        
        $workedExample = [
            'ww_score' => 85,
            'pt_score' => 90,
            'ta_score' => 85,
            'ww_contribution' => 25.5,
            'pt_contribution' => 45,
            'ta_contribution' => 17,
            'final_grade' => 87.5,
        ];
        
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