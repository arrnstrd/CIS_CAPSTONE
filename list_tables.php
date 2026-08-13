<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Get all tables
$tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");

echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "- " . $table->tablename . "\n";
}

// Check specific grading tables
$gradingTables = ['assessment_categories', 'assessments', 'quarterly_grades', 'grading_periods', 'grading_configs', 'student_assessment_scores'];

echo "\nChecking grading tables:\n";
foreach ($gradingTables as $table) {
    $count = DB::table($table)->count();
    echo "- $table: $count rows\n";
}

?>