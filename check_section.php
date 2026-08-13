$ids = App\Models\Enrollment::where("section_id",4)->where("status","active")->pluck("id");
echo "Section 4 active enrollment IDs: " . $ids->implode(",") . PHP_EOL;
echo "Logs matching those IDs (before today): " . App\Models\AttendanceLog::whereIn("enrollment_id",$ids)->whereDate("scan_time","<",now()->toDateString())->count() . PHP_EOL;
