<?php

namespace App\Http\Controllers\Teacher\GradingRules;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class CompRulesController extends Controller
{
    private const DEFAULTS = [
        'passing_grade' => '75',
        'gwa_formula' => 'Arithmetic mean of all subject final grades',
        'term_1_weight' => '33.33',
        'term_2_weight' => '33.33',
        'term_3_weight' => '33.34',
        'honors_highest' => 'GWA of 98-100, no grade below 90',
        'honors_high' => 'GWA of 95-97, no grade below 85',
        'honors_with' => 'GWA of 90-94, no grade below 85',
        'grading_scale' => 'Numeric (60-100)',
        'incomplete_policy' => 'Student must complete requirements within 30 days',
    ];

    public function index(Request $request)
    {
        $settings = $this->getSettings();

        return view('pov.teacher.grading-rules.comp-rules', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'passing_grade' => 'required|numeric|min:0|max:100',
            'gwa_formula' => 'required|string|max:500',
            'term_1_weight' => 'required|numeric|min:0|max:100',
            'term_2_weight' => 'required|numeric|min:0|max:100',
            'term_3_weight' => 'required|numeric|min:0|max:100',
            'honors_highest' => 'required|string|max:255',
            'honors_high' => 'required|string|max:255',
            'honors_with' => 'required|string|max:255',
            'grading_scale' => 'required|string|max:255',
            'incomplete_policy' => 'required|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()->route('teacher.grading-rules.comp-rules')->with('success', 'Computation rules updated.');
    }

    private function getSettings(): array
    {
        $stored = SystemSetting::whereIn('key', array_keys(self::DEFAULTS))->pluck('value', 'key')->toArray();

        return array_merge(self::DEFAULTS, $stored);
    }
}