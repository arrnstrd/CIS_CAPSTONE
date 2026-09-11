<?php

namespace Tests\Unit;

use App\Http\Controllers\Teacher\Reports\ReportsController;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class ReportsIndicatorRenderingTest extends TestCase
{
    private ReportsController $controller;
    private ReflectionMethod $formatIndicatorLabelsMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ReportsController();

        $refClass = new ReflectionClass(ReportsController::class);
        $this->formatIndicatorLabelsMethod = $refClass->getMethod('formatIndicatorLabels');
        $this->formatIndicatorLabelsMethod->setAccessible(true);
    }

    /**
     * Test 1: Single active indicator formats to actual label and not [object Object].
     */
    public function test_single_active_indicator_formats_to_human_readable_label(): void
    {
        $rawIndicators = [
            'low_grade' => true,
            'missing_grades' => false,
            'low_attendance' => false,
            'declining_performance' => false,
        ];

        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $rawIndicators);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(['Low Grade'], $result);
        $this->assertNotContains('[object Object]', $result);
    }

    /**
     * Test 2: No active indicators returns empty array without [object Object].
     */
    public function test_no_active_indicators_returns_empty_array(): void
    {
        $rawIndicators = [
            'low_grade' => false,
            'missing_grades' => false,
            'low_attendance' => false,
            'declining_performance' => false,
        ];

        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $rawIndicators);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
        $this->assertSame([], $result);
    }

    /**
     * Test 3: Multiple active indicators format correctly in established application order.
     */
    public function test_multiple_active_indicators_format_to_expected_labels(): void
    {
        $rawIndicators = [
            'low_grade' => true,
            'missing_grades' => false,
            'low_attendance' => true,
            'declining_performance' => true,
        ];

        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $rawIndicators);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame(['Low Grade', 'Low Attendance', 'Declining Performance'], $result);
    }

    /**
     * Test 4: All 4 active indicators format correctly.
     */
    public function test_all_four_active_indicators_format_correctly(): void
    {
        $rawIndicators = [
            'low_grade' => true,
            'missing_grades' => true,
            'low_attendance' => true,
            'declining_performance' => true,
        ];

        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $rawIndicators);

        $this->assertSame([
            'Low Grade',
            'Missing Grades',
            'Low Attendance',
            'Declining Performance',
        ], $result);
    }

    /**
     * Test 5: Unknown keys or false flags are safely ignored.
     */
    public function test_unknown_indicator_keys_are_ignored(): void
    {
        $rawIndicators = [
            'unknown_indicator' => true,
            'missing_grades' => true,
            'low_grade' => false,
        ];

        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $rawIndicators);

        $this->assertSame(['Missing Grades'], $result);
    }

    /**
     * Test 6: PDF HTML generation renders badges for indicators and does not contain [object Object] or raw booleans.
     */
    public function test_generate_at_risk_html_renders_indicator_badges(): void
    {
        $refClass = new ReflectionClass(ReportsController::class);
        $generateAtRiskHtml = $refClass->getMethod('generateAtRiskHtml');
        $generateAtRiskHtml->setAccessible(true);

        // Dummy teaching assignment mock
        $ta = (object) [
            'section' => (object) ['grade_level' => 10, 'name' => 'Emerald'],
            'subject' => (object) ['name' => 'Mathematics'],
        ];

        $dataWithIndicators = [
            'rows' => [
                [
                    'name' => 'Dela Cruz, Juan',
                    'risk_score' => 55,
                    'risk_level' => 'Moderate',
                    'attendance_rate' => 80.0,
                    'indicators' => ['Low Grade', 'Low Attendance'],
                ],
                [
                    'name' => 'Santos, Maria',
                    'risk_score' => 75,
                    'risk_level' => 'High',
                    'attendance_rate' => 60.0,
                    'indicators' => [],
                ],
            ],
        ];

        $html = $generateAtRiskHtml->invoke($this->controller, $dataWithIndicators, $ta);

        $this->assertStringContainsString('Low Grade', $html);
        $this->assertStringContainsString('Low Attendance', $html);
        $this->assertStringNotContainsString('[object Object]', $html);
        $this->assertStringContainsString('<span class="badge badge-neutral me-1">Low Grade</span>', $html);
        $this->assertStringContainsString('<span class="badge badge-neutral me-1">Low Attendance</span>', $html);
        $this->assertStringContainsString('&#8212;', $html);
    }

    /**
     * Test 7: Excel generation formats indicators with delimiter and em-dash fallback.
     */
    public function test_generate_at_risk_excel_formats_indicators(): void
    {
        $refClass = new ReflectionClass(ReportsController::class);
        $generateAtRiskExcel = $refClass->getMethod('generateAtRiskExcel');
        $generateAtRiskExcel->setAccessible(true);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $ta = (object) [
            'section' => (object) ['grade_level' => 10, 'name' => 'Emerald'],
            'subject' => (object) ['name' => 'Mathematics'],
        ];

        $dataWithIndicators = [
            'rows' => [
                [
                    'name' => 'Dela Cruz, Juan',
                    'risk_score' => 55,
                    'risk_level' => 'Moderate',
                    'attendance_rate' => 80.0,
                    'indicators' => ['Low Grade', 'Low Attendance'],
                ],
                [
                    'name' => 'Santos, Maria',
                    'risk_score' => 75,
                    'risk_level' => 'High',
                    'attendance_rate' => 60.0,
                    'indicators' => [],
                ],
            ],
        ];

        $generateAtRiskExcel->invoke($this->controller, $sheet, $dataWithIndicators, $ta);

        // Row 2 is Dela Cruz, Juan: column E (index 5) should have "Low Grade; Low Attendance"
        $this->assertSame('Low Grade; Low Attendance', $sheet->getCell('E2')->getValue());
        // Row 3 is Santos, Maria: column E should have "—"
        $this->assertSame('—', $sheet->getCell('E3')->getValue());
    }

    /**
     * Test 8: Pre-formatted labels pass through formatIndicatorLabels cleanly.
     */
    public function test_already_formatted_labels_pass_through_cleanly(): void
    {
        $input = ['Low Grade', 'Declining Performance'];
        $result = $this->formatIndicatorLabelsMethod->invoke($this->controller, $input);

        $this->assertSame(['Low Grade', 'Declining Performance'], $result);
    }

    /**
     * Test 9: Excel and PDF generation safely handle raw boolean indicator maps.
     */
    public function test_excel_and_pdf_generation_handle_raw_boolean_indicator_maps(): void
    {
        $refClass = new ReflectionClass(ReportsController::class);
        $generateAtRiskHtml = $refClass->getMethod('generateAtRiskHtml');
        $generateAtRiskHtml->setAccessible(true);
        $generateAtRiskExcel = $refClass->getMethod('generateAtRiskExcel');
        $generateAtRiskExcel->setAccessible(true);

        $ta = (object) [
            'section' => (object) ['grade_level' => 10, 'name' => 'Emerald'],
            'subject' => (object) ['name' => 'Mathematics'],
        ];

        $dataWithRawIndicators = [
            'rows' => [
                [
                    'name' => 'Reyes, Pedro',
                    'risk_score' => 60,
                    'risk_level' => 'Moderate',
                    'attendance_rate' => 75.0,
                    'indicators' => [
                        'low_grade' => true,
                        'missing_grades' => false,
                        'low_attendance' => true,
                        'declining_performance' => false,
                    ],
                ],
            ],
        ];

        // Verify HTML / PDF output
        $html = $generateAtRiskHtml->invoke($this->controller, $dataWithRawIndicators, $ta);
        $this->assertStringContainsString('Low Grade', $html);
        $this->assertStringContainsString('Low Attendance', $html);
        $this->assertStringNotContainsString('1</span>', $html);
        $this->assertStringNotContainsString('[object Object]', $html);

        // Verify Excel output
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $generateAtRiskExcel->invoke($this->controller, $sheet, $dataWithRawIndicators, $ta);
        $this->assertSame('Low Grade; Low Attendance', $sheet->getCell('E2')->getValue());
    }
}

