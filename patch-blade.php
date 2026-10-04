<?php
$f = "resources/views/pov/teacher/grading-rules/grading-rules.blade.php";
$c = file_get_contents($f);
$q = chr(39);

$oldA = "                    displayValidationResults({ errors: [result.message] });";
$newA = "                    const errors = result.errors ? Object.values(result.errors).flat() : [result.message || {$q}Validation failed{$q}];\n                    displayValidationResults({ errors });";
$countA = substr_count($c, $oldA);
if ($countA !== 1) { echo "ABORTED on fix A: expected 1 match, found $countA\n"; exit(1); }
$c = str_replace($oldA, $newA, $c);

$oldB = "        function displayDepEdValidationResults(result) {\n            const validationResults = document.getElementById({$q}validationResults{$q});";
$newB = "        function displayDepEdValidationResults(result) {\n            const validationResults = document.getElementById({$q}depedValidationResults{$q});";
$countB = substr_count($c, $oldB);
if ($countB !== 1) { echo "ABORTED on fix B: expected 1 match, found $countB\n"; exit(1); }
$c = str_replace($oldB, $newB, $c);

file_put_contents($f, $c);
echo "PATCHED OK: $f (fix A + fix B)\n";