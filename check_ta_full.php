$assignments = App\Models\TeachingAssignment::where("teacher_id", 6)->with(["section", "subject", "schoolYear"])->get();
foreach ($assignments as $ta) {
    echo "TA ID: " . $ta->id
        . " | Section: " . ($ta->section->name ?? "NULL")
        . " | Grade Level: " . ($ta->section->grade_level ?? "NULL")
        . " | Subject: " . ($ta->subject->name ?? "NULL")
        . " | School Year ID: " . $ta->school_year_id
        . " | Status: " . $ta->status
        . PHP_EOL;
}
