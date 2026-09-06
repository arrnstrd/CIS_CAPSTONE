<?php

namespace Tests\Unit;

use App\Services\Grading\DepEdClassRecordParserService;
use App\Services\Grading\SchoolLevelDetector;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

class DepEdClassRecordParserServiceTest extends TestCase
{
    public function test_it_maps_term_assessment_headers_to_the_term_assessment_component(): void
    {
        $this->assertSame('term_assessment', $this->scanCategory('Term Assessment'));
    }

    public function test_it_preserves_legacy_quarterly_assessment_imports(): void
    {
        $this->assertSame('term_assessment', $this->scanCategory('Quarterly Assessment'));
    }

    private function scanCategory(string $header): string
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->setCellValue('F12', $header);
        $sheet->setCellValue('F14', 1);
        $sheet->setCellValue('F15', 20);

        $method = new \ReflectionMethod(DepEdClassRecordParserService::class, 'scanStructure');
        $structure = $method->invoke(
            new DepEdClassRecordParserService(new SchoolLevelDetector()),
            $sheet,
            'jhs'
        );

        return $structure['assessments'][0]['category'];
    }
}
