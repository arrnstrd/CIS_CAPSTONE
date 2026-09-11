<?php
$f = "resources/views/pov/teacher/grading-rules/grading-rules.blade.php";
$c = file_get_contents($f);
$q = chr(39);

$oldA = "                    displayValidationResults({ errors: [result.message] });";
$newA = "                    const errors = result.errors ? Object.values(result.errors).flat() : [result.message || " . $q . "Validation failed" . $q . "];\n                    displayValidationResults({ errors });";
$countA = substr_count($c, $oldA);
if ($countA !== 1) { echo "ABORTED on fix A: expected 1 match, found $countA\n"; exit(1); }
$c = str_replace($oldA, $newA, $c);

$patternB = "/(function displayDepEdValidationResults\(result\)\s*\{\s*\r?\n\s*const validationResults = document\.getElementById\()" . $q . "validationResults" . $q . "(\);)/";
$countB = preg_match_all($patternB, $c);
if ($countB !== 1) { echo "ABORTED on fix B: expected 1 match, found $countB\n"; exit(1); }
$replacement = "\${1}" . $q . "depedValidationResults" . $q . "\${2}";
$c = preg_replace($patternB, $replacement, $c, 1);

file_put_contents($f, $c);
echo "PATCHED OK: $f (fix A + fix B)\n";