foreach (App\Models\AssessmentCategory::all() as $c) {
    echo $c->id . " " . $c->name . PHP_EOL;
}
