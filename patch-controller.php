<?php
$f = "app/Http/Controllers/Teacher/GradingRules/GradingFormulaImportController.php";
$c = file_get_contents($f);

$pattern = '/if \(!\$matches\)\s*\{.*?\],\s*422\);\s*\}\s*return response\(\)->json\(\[\s*\x27success\x27\s*=>\s*true,.*?\]\);\s*\}/s';

$count = preg_match_all($pattern, $c);
if ($count !== 1) { echo "ABORTED: expected 1 regex match, found $count\n"; exit(1); }

$replacement = "return response()->json([
            'success' => true,
            'matches_official_scheme' => \$matches,
            'message' => \$matches
                ? 'Formula matches the configured DepEd grading rules.'
                : 'This formula totals 100% but differs from the standard DepEd scheme for this subject. Review before applying.',
            'official_scheme' => \$officialScheme,
            'uploaded_scheme' => \$uploadedScheme,
            'differences' => \$matches ? [] : \$differences
        ]);
    }";

$c = preg_replace($pattern, $replacement, $c, 1);
file_put_contents($f, $c);
echo "PATCHED OK: $f\n";