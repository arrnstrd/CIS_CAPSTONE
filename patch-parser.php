<?php
$f = "app/Services/Grading/GradingFormulaParserService.php";
$c = file_get_contents($f);
$q = chr(39);
$old = "            \$this->validateFormula(\$components);\n\n            return \$components;\n";
$new = "            \$this->validateFormula(\$components);\n\n            return [{$q}components{$q} => \$components];\n";
$count = substr_count($c, $old);
if ($count !== 1) { echo "ABORTED: expected 1 match, found $count\n"; exit(1); }
file_put_contents($f, str_replace($old, $new, $c));
echo "PATCHED OK: $f\n";