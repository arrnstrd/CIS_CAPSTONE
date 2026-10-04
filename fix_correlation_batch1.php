<?php
$applied = [];
$skipped = [];

function tryReplace(string $content, string $label, string $search, string $replace, array &$applied, array &$skipped): string {
    $count = substr_count($content, $search);
    if ($count === 0) {
        $skipped[] = "$label (pattern not found - may already be applied)";
        return $content;
    }
    if ($count > 1) {
        $skipped[] = "$label (pattern found {$count} times - expected exactly 1, skipped for safety)";
        return $content;
    }
    $applied[] = $label;
    return str_replace($search, $replace, $content);
}

function processFile(string $path, array $replacements, array &$applied, array &$skipped): void {
    if (! file_exists($path)) {
        fwrite(STDERR, "File not found: {$path}\n");
        exit(1);
    }
    $content = file_get_contents($path);
    $original = $content;
    foreach ($replacements as [$label, $search, $replace]) {
        $content = tryReplace($content, $label, $search, $replace, $applied, $skipped);
    }
    if ($content !== $original) {
        file_put_contents($path . '.bak', $original);
        file_put_contents($path, $content);
    }
}

$routesPath = 'routes/teacher.php';
processFile($routesPath, [
    [
        'Add teacher.grading-system.correlation route',
        "    Route::get('/teacher/grading-system/attendance', [AttendanceAnalyticsController::class, 'index'])\n        ->name('teacher.grading-system.attendance');\n",
        "    Route::get('/teacher/grading-system/attendance', [AttendanceAnalyticsController::class, 'index'])\n        ->name('teacher.grading-system.attendance');\n    Route::get('/teacher/grading-system/correlation', [AttendanceAnalyticsController::class, 'correlation'])\n        ->name('teacher.grading-system.correlation');\n",
    ],
], $applied, $skipped);

$controllerPath = 'app/Http/Controllers/Teacher/Analytics/AttendanceAnalyticsController.php';
processFile($controllerPath, [
    [
        'Split index() into index()/correlation()/buildAnalyticsData()',
        "class AttendanceAnalyticsController extends Controller\n{\n    public function index(Request \$request)\n    {\n        \$teacher = \$request->user()->teacher;\n\n        if (! \$teacher) {\n            return view('pov.teacher.analytics.attendance-analytics-index', [",
        "class AttendanceAnalyticsController extends Controller\n{\n    public function index(Request \$request)\n    {\n        \$data = \$this->buildAnalyticsData(\$request);\n\n        return view('pov.teacher.analytics.attendance-analytics-index', \$data);\n    }\n\n    public function correlation(Request \$request)\n    {\n        \$data = \$this->buildAnalyticsData(\$request);\n\n        return view('pov.teacher.analytics.attendance-correlation-index', \$data);\n    }\n\n    private function buildAnalyticsData(Request \$request): array\n    {\n        \$teacher = \$request->user()->teacher;\n\n        if (! \$teacher) {\n            return [",
    ],
    [
        'Close early-return array (no-teacher case)',
        "                'termTrend' => [\n                    'labels' => [],\n                    'gradeSeries' => [],\n                    'attendanceSeries' => [],\n                ],\n            ]);\n        }",
        "                'termTrend' => [\n                    'labels' => [],\n                    'gradeSeries' => [],\n                    'attendanceSeries' => [],\n                ],\n            ];\n        }",
    ],
    [
        'Change final return view() to return compact() (closes buildAnalyticsData)',
        "        return view(\n            'pov.teacher.analytics.attendance-analytics-index',\n            compact(\n                'studentPoints',\n                'sectionPoints',\n                'gradeLevels',\n                'sections',\n                'subjects',\n                'schoolYears',\n                'students',\n                'selectedSchoolYearId',\n                'selectedGradeLevel',\n                'selectedSectionId',\n                'selectedSubjectId',\n                'selectedTerm',\n                'selectedStudentId',\n                'selectedDateFrom',\n                'selectedDateTo',\n                'attendanceSummary',\n                'sectionSummaries',\n                'studentSummaries',\n                'mostAbsentStudents',\n                'earlyArrivalStudents',\n                'mostPresentStudents',\n                'insights',\n                'termTrend'\n            )\n        );\n    }",
        "        return compact(\n            'studentPoints',\n            'sectionPoints',\n            'gradeLevels',\n            'sections',\n            'subjects',\n            'schoolYears',\n            'students',\n            'selectedSchoolYearId',\n            'selectedGradeLevel',\n            'selectedSectionId',\n            'selectedSubjectId',\n            'selectedTerm',\n            'selectedStudentId',\n            'selectedDateFrom',\n            'selectedDateTo',\n            'attendanceSummary',\n            'sectionSummaries',\n            'studentSummaries',\n            'mostAbsentStudents',\n            'earlyArrivalStudents',\n            'mostPresentStudents',\n            'insights',\n            'termTrend'\n        );\n    }",
    ],
], $applied, $skipped);

echo "Applied (" . count($applied) . "):\n";
foreach ($applied as $a) { echo "  [OK] {$a}\n"; }
echo "\nSkipped (" . count($skipped) . "):\n";
foreach ($skipped as $s) { echo "  [--] {$s}\n"; }
