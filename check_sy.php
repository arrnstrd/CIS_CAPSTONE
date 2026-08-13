App\Models\SchoolYear::all()->each(function($sy) {
    echo "ID: " . $sy->id . " | Name: " . ($sy->name ?? $sy->year ?? "N/A") . " | Is Current: " . ($sy->is_current ?? "N/A") . PHP_EOL;
});
