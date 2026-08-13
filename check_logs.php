$rows = App\Models\AttendanceLog::selectRaw("DATE(scan_time) as d, scan_type, count(*) as c")
    ->groupBy("d","scan_type")
    ->orderBy("d")
    ->get();
foreach ($rows as $r) {
    echo $r->d . " - " . $r->scan_type . " - " . $r->c . PHP_EOL;
}
