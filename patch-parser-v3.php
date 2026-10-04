<?php
$f = "app/Services/Grading/GradingFormulaParserService.php";
$c = file_get_contents($f);
$pattern = "/return \$components;/";
$count = preg_match_all($pattern, $c);
if ($count !== 1) { echo "ABORTED: expected 1 match, found $count\n"; exit(1); }
$c = preg_replace($pattern, "return [\"components\" => \$components];", $c, 1);
file_put_contents($f, $c);
echo "PATCHED OK: $f\n";