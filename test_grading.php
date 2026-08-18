<?php

require_once 'vendor/autoload.php';

use App\Models\TeachingAssignment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\AssessmentCategory;
use App\Models\Assessment;
use App\Services\Grading\GradingService;
use Illuminate\Support\Facades\DB;

// Get required records
$ta = TeachingAssignment::first();
$enrollment = Enrollment::where('section_id', $ta->section_id)->where('status','active')->first();
$period = GradingPeriod::where('sequence', 1)->first();
$wwCat = AssessmentCategory::where('name', 'Written Work')->first();

echo "Teaching Assignment ID: " . $ta->id . "\n";
echo "Enrollment ID: " . $enrollment->id . "\n";
echo "Period ID: " . $period->id . "\n";
echo "WW Category ID: " . $wwCat->id . "\n";

// Create assessment
$assessment = Assessment::create([
    'teaching_assignment_id' => $ta->id,
    'assessment_category_id' => $wwCat->id,
    'grading_period_id' => $period->id,
    'title' => 'TEST WW1',
    'total_items' => 20,
    'assessment_date' => now(),
    'status' => 'active',
]);

echo "Assessment ID: " . $assessment->id . "\n";

// Record score
$service = app(GradingService::class);
$score = $service->recordScore([
    'assessment_id' => $assessment->id,
    'enrollment_id' => $enrollment->id,
    'score' => 18,
]);

echo "Score recorded: " . $score->score . "\n";

// Get quarterly grade
$qg = QuarterlyGrade::where('teaching_assignment_id', $ta->id)
    ->where('enrollment_id', $enrollment->id)
    ->where('grading_period_id', $period->id)
    ->first();

echo "Quarterly Grade:\n";
echo json_encode($qg ? $qg->toArray() : null) . "\n";