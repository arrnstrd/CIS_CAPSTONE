<?php

// Use artisan tinker environment
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\TeachingAssignment;
use App\Models\Enrollment;
use App\Models\GradingPeriod;
use App\Models\AssessmentCategory;
use App\Models\Assessment;
use App\Models\StudentAssessmentScore;
use App\Models\QuarterlyGrade;
use App\Services\Grading\GradingService;
use Illuminate\Support\Facades\DB;

echo "=== Grading Pipeline Test ===\n";

// Get required records
$ta = TeachingAssignment::first();
echo "Teaching Assignment ID: " . $ta->id . "\n";

$enrollment = Enrollment::where('section_id', $ta->section_id)->where('status','active')->first();
echo "Enrollment ID: " . $enrollment->id . "\n";

$period = GradingPeriod::where('sequence', 1)->first();
echo "Period ID: " . $period->id . "\n";

$wwCat = AssessmentCategory::where('name', 'Written Work')->first();
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

echo "=== Test Complete ===\n";