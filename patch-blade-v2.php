<?php
$f = "resources/views/pov/teacher/grading-rules/grading-rules.blade.php";
$c = file_get_contents($f);

$patternB = "/(function displayDepEdValidationResults\(result\)\s*\{\s*\r?\n\s*const validationResults = document\.getElementById\()'"'"'validationResults'"'"'(\);)/";
$countB = preg_match_all($patternB, $c);
if ($countB !== 1) { echo "ABORTED on fix B: expected 1 match, found $countB\n"; exit(1); }
$replacement = "\${1}'"'"'depedValidationResults'"'"'\${2}";
$c = preg_replace($patternB, $replacement, $c, 1);

file_put_contents($f, $c);
echo "PATCHED OK: $f (fix B)\n";