<?php
$f = "app/Services/Grading/GradingFormulaParserService.php";
$c = file_get_contents($f);
$pattern = '/(\$this->validateFormula\(\$components\);\s*\r?\n\s*\r?\n\s*)return \$components;/';
$count = preg_match_all($pattern, $c);
if ($count !== 1) { echo "ABORTED: expected 1 match, found $count\n"; exit(1); }
$replacement = '${1}return ["components" => $components];';
$c = preg_replace($pattern, $replacement, $c, 1);
file_put_contents($f, $c);
echo "PATCHED OK: $f\n";