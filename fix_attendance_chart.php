<?php
/**
 * One-time fix script for the Attendance Analytics blade file.
 * Usage: php fix_attendance_chart.php path\to\attendance-analytics-index.blade.php
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php fix_attendance_chart.php <path-to-blade-file>\n");
    exit(1);
}

$path = $argv[1];

if (! file_exists($path)) {
    fwrite(STDERR, "File not found: {$path}\n");
    exit(1);
}

$content = file_get_contents($path);
$original = $content;
$applied = [];
$skipped = [];

function tryReplace(string $content, string $label, string $search, string $replace, array &$applied, array &$skipped): string {
    $count = substr_count($content, $search);

    if ($count === 0) {
        $skipped[] = "$label (pattern not found - may already be fixed, or whitespace differs)";
        return $content;
    }

    if ($count > 1) {
        $skipped[] = "$label (pattern found {$count} times - expected exactly 1, skipped for safety)";
        return $content;
    }

    $applied[] = $label;
    return str_replace($search, $replace, $content);
}

// --- Fix 1: correct condition + move $correlationChartData definition earlier ---
$content = tryReplace(
    $content,
    'Fix correlation @if condition (empty() -> isNotEmpty())',
    "    @if (!empty(\$correlationChartData))\n        <div style=\"position: relative; height: 320px; width: 100%;\">\n            <canvas id=\"attendanceGradeCorrelationChart\"></canvas>\n        </div>",
    "    @php\n        \$correlationChartData = collect(\$studentPoints ?? [])->map(function (\$point) {\n            return [\n                'x' => \$point['x'],\n                'y' => \$point['y'],\n                'name' => \$point['name'],\n            ];\n        })->values();\n    @endphp\n\n    @if (\$correlationChartData->isNotEmpty())\n        <div style=\"position: relative; height: 320px; width: 100%;\">\n            <canvas id=\"attendanceGradeCorrelationChart\"></canvas>\n        </div>",
    $applied,
    $skipped
);

// --- Fix 2: remove the now-duplicate @php block inside the <script> section ---
$content = tryReplace(
    $content,
    'Remove duplicate @php block in <script> (wrong $correlationPoints source)',
    "    // 4. Attendance vs Academic Performance\n    @php\n        \$correlationChartData = collect(\$correlationPoints ?? [])->map(function (\$point) {\n            return [\n                'x' => \$point['x'],\n                'y' => \$point['y'],\n                'name' => \$point['name'],\n            ];\n        })->values();\n    @endphp\n\n    @if (\$correlationChartData->isNotEmpty())",
    "    // 4. Attendance vs Academic Performance\n    @if (\$correlationChartData->isNotEmpty())",
    $applied,
    $skipped
);

// --- Fix 3: mojibake in insight label ---
$content = tryReplace(
    $content,
    'Fix mojibake in "Absence - Grade Trend" label',
    "AbsenceÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã¢â‚¬Å“Grade Trend",
    "Absence - Grade Trend",
    $applied,
    $skipped
);

// --- Fix 4-6: mojibake fallback dashes (may appear multiple times identically) ---
$mojibakeDash = "ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚Â";
$dashCount = substr_count($content, $mojibakeDash);

if ($dashCount > 0) {
    $content = str_replace($mojibakeDash, 'N/A', $content);
    $applied[] = "Replaced {$dashCount} mojibake fallback dash(es) with 'N/A'";
} else {
    $skipped[] = "Mojibake fallback dash (not found - may already be fixed)";
}

// --- Fix 7: strip a stray trailing markdown code fence, if present ---
$trimmedEnd = rtrim($content);
if (preg_match('/```[a-zA-Z]*\s*$/', $trimmedEnd)) {
    $content = preg_replace('/```[a-zA-Z]*\s*$/', '', $trimmedEnd) . "\n";
    $applied[] = "Removed trailing markdown code fence";
} else {
    $skipped[] = "Trailing markdown code fence (none found)";
}

if ($content === $original) {
    echo "No changes were made. See details below.\n\n";
} else {
    $backupPath = $path . '.bak';
    file_put_contents($backupPath, $original);
    file_put_contents($path, $content);
    echo "File updated: {$path}\n";
    echo "Backup saved to: {$backupPath}\n\n";
}

echo "Applied (" . count($applied) . "):\n";
foreach ($applied as $a) {
    echo "  [OK] {$a}\n";
}

echo "\nSkipped (" . count($skipped) . "):\n";
foreach ($skipped as $s) {
    echo "  [--] {$s}\n";
}
