<?php

// Simple script to check database data
echo "Checking grading system data...\n\n";

// Check if we can connect to the database
try {
    $pdo = new PDO('pgsql:host=aws-0-ap-southeast-2.pooler.supabase.com;port=5432;dbname=postgres', 'postgres.drfsjlldyozndelgwrkl', 'CIS_CAPSTONE_');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n\n";
    
    // Check grading tables
    $tables = ['assessment_categories', 'assessments', 'quarterly_grades', 'grading_periods', 'student_assessment_scores'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "$table: {$result['count']} rows\n";
    }
    
    // Check school years
    $stmt = $pdo->prepare("SELECT * FROM school_years");
    $stmt->execute();
    $schoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSchool Years:\n";
    foreach ($schoolYears as $year) {
        echo "- {$year['school_year']} (active: " . ($year['is_active'] ? 'yes' : 'no') . ")\n";
    }
    
    // Check sections
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sections");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nSections: {$result['count']} rows\n";
    
    // Check students
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Students: {$result['count']} rows\n";
    
    // Check enrollments
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Enrollments: {$result['count']} rows\n";
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

?>