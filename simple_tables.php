<?php

// Simple script to check database tables
echo "Checking database tables...\n";

// Check specific grading tables
$gradingTables = ['assessment_categories', 'assessments', 'quarterly_grades', 'grading_periods', 'grading_configs', 'student_assessment_scores'];

echo "\nChecking grading tables:\n";
foreach ($gradingTables as $table) {
    echo "- $table: ";
    // Try to count rows
    try {
        $result = shell_exec("docker exec cis_app php artisan tinker --execute=\"echo DB::table('$table')->count();\"");
        echo trim($result) . " rows\n";
    } catch (Exception $e) {
        echo "Error checking table\n";
    }
}

?>