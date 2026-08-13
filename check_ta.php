$assignments = App\Models\TeachingAssignment::where("teacher_id", 6)->with("section")->get();
foreach ($assignments as $ta) {
    echo "TA ID: " . $ta->id
        . " | Section: " . ($ta->section->name ?? "NULL")
        . " | Grade Level: " . ($ta->section->grade_level ?? "NULL")
        . " | Status: " . $ta->status
        . PHP_EOL;
}
