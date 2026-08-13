$t = App\Models\Teacher::whereHas("user", function($q) {
    $q->where("email", "arriane.estrada@example.com");
})->first();
echo "Teacher ID: " . ($t->id ?? "NOT FOUND") . PHP_EOL;
