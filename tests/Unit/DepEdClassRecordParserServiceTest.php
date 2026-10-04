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

    public function test_it_parses_exam_identifiers_correctly(): void
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->setCellValue('T12', 'EXAMINATIONS (EXs)');
        $sheet->setCellValue('T14', 'ST1');
        $sheet->setCellValue('T15', 30);
        $sheet->setCellValue('U14', 'ST2');
        $sheet->setCellValue('U15', 30);
        $sheet->setCellValue('V14', 'TE');
        $sheet->setCellValue('V15', 40);

        $method = new \ReflectionMethod(DepEdClassRecordParserService::class, 'scanStructure');
        $structure = $method->invoke(
            new DepEdClassRecordParserService(new SchoolLevelDetector()),
            $sheet,
            'elem_jhs'
        );

        $this->assertCount(3, $structure['assessments']);
        $this->assertSame('term_assessment', $structure['assessments'][0]['category']);
        $this->assertSame(1, $structure['assessments'][0]['slot_number']);
        $this->assertSame(30.0, $structure['assessments'][0]['hps']);

        $this->assertSame('term_assessment', $structure['assessments'][1]['category']);
        $this->assertSame(2, $structure['assessments'][1]['slot_number']);
        $this->assertSame(30.0, $structure['assessments'][1]['hps']);

        $this->assertSame('term_assessment', $structure['assessments'][2]['category']);
        $this->assertSame(3, $structure['assessments'][2]['slot_number']);
        $this->assertSame(40.0, $structure['assessments'][2]['hps']);
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

    public function test_it_parses_zero_scores_and_omits_blank_scores(): void
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        // Setup header
        $sheet->setCellValue('F12', 'WRITTEN WORK');
        $sheet->setCellValue('F14', 1);
        $sheet->setCellValue('F15', 20);
        $sheet->setCellValue('G14', 2);
        $sheet->setCellValue('G15', 20);

        // Student row (row 16)
        $sheet->setCellValue('A16', '1');
        $sheet->setCellValue('B16', 'DELA CRUZ, JUAN');
        $sheet->setCellValue('F16', 0); // zero score
        $sheet->setCellValue('G16', ''); // blank score

        $methodStructure = new \ReflectionMethod(DepEdClassRecordParserService::class, 'scanStructure');
        $service = new DepEdClassRecordParserService(new SchoolLevelDetector());
        $structure = $methodStructure->invoke($service, $sheet, 'jhs');

        $methodExtract = new \ReflectionMethod(DepEdClassRecordParserService::class, 'extractStudentsAndScores');
        $students = $methodExtract->invoke($service, $sheet, $structure);

        $this->assertCount(1, $students);
        $scores = $students[0]['scores'];

        // Should have 1 score (slot 1 with score 0), slot 2 should be omitted because it is blank
        $this->assertCount(1, $scores);
        $this->assertSame(1, $scores[0]['slot_number']);
        $this->assertSame(0.0, $scores[0]['score']);
    }
}
