<?php

namespace App\Http\Controllers\Teacher\ImportData;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\GradingPeriod;
use App\Models\TeachingAssignment;
use App\Services\Grading\DepEdClassRecordParserService;
use App\Services\Grading\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\Grading\SchoolLevelDetector;

class DepEdClassRecordImportController extends Controller
{
    protected DepEdClassRecordParserService $parserService;
    protected GradingService $gradingService;

    public function __construct(
        DepEdClassRecordParserService $parserService,
        GradingService $gradingService
    ) {
        $this->parserService = $parserService;
        $this->gradingService = $gradingService;
    }

    /**
     * Download the level-appropriate official DepEd E-Class Record template.
     */
    public function downloadTemplate(Request $request, int $teachingAssignmentId, SchoolLevelDetector $levelDetector)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $ta = TeachingAssignment::where('id', $teachingAssignmentId)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('section')
            ->firstOrFail();

        $level = $levelDetector->detect($ta->section?->grade_level ?? 1);

        $templateFileName = ($level === 'shs')
            ? 'SSHS-Three-Term-E-Class-Record-v2.xlsx'
            : 'Grades-2-10-3Term-E-Class-Record.xlsx';

        $filePath = storage_path("app/templates/teacher(pov)/{$templateFileName}");

        if (!file_exists($filePath)) {
            $fallbackPath = resource_path("templates/teacher(pov)/{$templateFileName}");
            if (file_exists($fallbackPath)) {
                $filePath = $fallbackPath;
            } else {
                abort(404, "Template file not found: {$templateFileName}");
            }
        }

        $downloadName = ($level === 'shs')
            ? "DepEd_SHS_Grade_{$ta->section->grade_level}_E-Class_Record_Template.xlsx"
            : "DepEd_Elem_JHS_Grade_{$ta->section->grade_level}_E-Class_Record_Template.xlsx";

        return response()->download($filePath, $downloadName);
    }

    /**
     * Inspect uploaded Excel file and return available sheet names, detected assessments, and roster reconciliation.
     */
    public function inspect(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:15360',
            'teaching_assignment_id' => 'required|integer|exists:teaching_assignments,id',
            'sheet_name' => 'nullable|string',
        ]);

        $ta = TeachingAssignment::where('id', $request->teaching_assignment_id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        $filePath = $request->file('excel_file')->getPathname();

        try {
            $sheetNames = $this->parserService->getWorksheetNames($filePath);

            $targetSheet = $request->input('sheet_name');
            if (!$targetSheet || !in_array($targetSheet, $sheetNames)) {
                $targetSheet = collect($sheetNames)->first(function ($name) {
                    $l = strtolower($name);
                    return str_contains($l, 'term 1') || str_contains($l, '1st') || str_contains($l, 't1');
                }) ?? ($sheetNames[0] ?? 'TERM 1');
            }

            $parsedData = $this->parserService->parseSheet($filePath, $targetSheet, $ta);

            // Compute category offsets for previewing target appended slot numbers
            $targetSequence = 1;
            if (str_contains(strtolower($targetSheet), 'term 2') || str_contains(strtolower($targetSheet), '2nd')) {
                $targetSequence = 2;
            } elseif (str_contains(strtolower($targetSheet), 'term 3') || str_contains(strtolower($targetSheet), '3rd')) {
                $targetSequence = 3;
            }

            $gradingPeriod = GradingPeriod::trimester()
                ->where('sequence', $targetSequence)
                ->first();

            if ($gradingPeriod) {
                $existingAssessments = Assessment::where('teaching_assignment_id', $ta->id)
                    ->where('grading_period_id', $gradingPeriod->id)
                    ->where('status', 'active')
                    ->get();

                $categories = AssessmentCategory::all();
                $catMap = [
                    'written_work' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'))?->id,
                    'performance_task' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'))?->id,
                    'term_assessment' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment') || str_contains(strtolower($c->name), 'exam'))?->id,
                ];

                $categoryOffsets = [];
                foreach ($catMap as $catKey => $catId) {
                    $categoryOffsets[$catKey] = $catId ? (int) ($existingAssessments->where('assessment_category_id', $catId)->max('slot_number') ?? 0) : 0;
                }

                foreach ($parsedData['assessments'] as &$asm) {
                    $catKey = $asm['category'];
                    $asm['target_slot_number'] = $asm['slot_number'] + ($categoryOffsets[$catKey] ?? 0);
                }
                unset($asm);
            }

            return response()->json([
                'success' => true,
                'available_sheets' => $sheetNames,
                'selected_sheet' => $targetSheet,
                'data' => $parsedData,
            ]);

        } catch (\Throwable $e) {
            Log::error('DepEd Class Record Inspection Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to process Excel file: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Confirm and process grade import after passing strict roster verification.
     */
    public function process(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:15360',
            'teaching_assignment_id' => 'required|integer|exists:teaching_assignments,id',
            'sheet_name' => 'required|string',
        ]);

        $ta = TeachingAssignment::where('id', $request->teaching_assignment_id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['section', 'subject'])
            ->firstOrFail();

        $filePath = $request->file('excel_file')->getPathname();
        $targetSheet = $request->input('sheet_name');

        $parsedData = $this->parserService->parseSheet($filePath, $targetSheet, $ta);

        if (!$parsedData['can_import']) {
            return response()->json([
                'success' => false,
                'error' => 'Import blocked due to roster mismatch or template errors.',
                'details' => $parsedData,
            ], 422);
        }

        // Determine target grading period sequence (Term 1 -> seq 1, Term 2 -> seq 2, Term 3 -> seq 3)
        $targetSequence = 1;
        if (str_contains(strtolower($targetSheet), 'term 2') || str_contains(strtolower($targetSheet), '2nd')) {
            $targetSequence = 2;
        } elseif (str_contains(strtolower($targetSheet), 'term 3') || str_contains(strtolower($targetSheet), '3rd')) {
            $targetSequence = 3;
        }

        $gradingPeriod = GradingPeriod::trimester()
            ->where('sequence', $targetSequence)
            ->firstOrFail();

        $categories = AssessmentCategory::all();
        $catMap = [
            'written_work' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'written'))?->id,
            'performance_task' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'performance'))?->id,
            'term_assessment' => $categories->first(fn($c) => str_contains(strtolower($c->name), 'term assessment') || str_contains(strtolower($c->name), 'exam'))?->id,
        ];

        // Fetch existing assessments to append imported assessments into empty slots
        $existingAssessments = Assessment::where('teaching_assignment_id', $ta->id)
            ->where('grading_period_id', $gradingPeriod->id)
            ->where('status', 'active')
            ->get();

        $categoryOffsets = [];
        foreach ($catMap as $catKey => $catId) {
            $categoryOffsets[$catKey] = $catId ? (int) ($existingAssessments->where('assessment_category_id', $catId)->max('slot_number') ?? 0) : 0;
        }

        try {
            DB::transaction(function () use ($ta, $gradingPeriod, $parsedData, $catMap, $categoryOffsets, $targetSheet) {
                // 1. Create or Update Assessment records, appending to next available empty slots
                $createdAssessments = [];

                foreach ($parsedData['assessments'] as $asmInfo) {
                    $catKey = $asmInfo['category'];
                    $catId = $catMap[$catKey] ?? null;
                    if (!$catId) continue;

                    $excelSlotNum = $asmInfo['slot_number'];
                    $offset = $categoryOffsets[$catKey] ?? 0;
                    $targetSlotNum = $excelSlotNum + $offset;
                    $hps = $asmInfo['hps'];

                    $titlePrefix = match($catKey) {
                        'written_work' => 'Written Work',
                        'performance_task' => 'Performance Task',
                        'term_assessment' => 'Exam',
                        default => 'Assessment',
                    };

                    $assessment = Assessment::updateOrCreate(
                        [
                            'teaching_assignment_id' => $ta->id,
                            'grading_period_id' => $gradingPeriod->id,
                            'assessment_category_id' => $catId,
                            'slot_number' => $targetSlotNum,
                        ],
                        [
                            'title' => "{$titlePrefix} {$targetSlotNum}",
                            'total_items' => $hps,
                            'assessment_date' => now(),
                            'description' => "Imported from DepEd Class Record ({$targetSheet})",
                            'status' => 'active',
                        ]
                    );

                    $createdAssessments["{$catKey}_{$excelSlotNum}"] = [
                        'id' => $assessment->id,
                        'total_items' => (float) $assessment->total_items,
                    ];
                }

                // 2. Save scores for each matched student
                foreach ($parsedData['matched_records'] as $studentRecord) {
                    $enrollmentId = $studentRecord['enrollment_id'];

                    foreach ($studentRecord['scores'] as $scoreItem) {
                        $key = "{$scoreItem['category']}_{$scoreItem['slot_number']}";
                        if (isset($createdAssessments[$key])) {
                            $asmData = $createdAssessments[$key];
                            $assessmentId = $asmData['id'];
                            $maxScore = $asmData['total_items'];
                            $safeScore = max(0, min((float) $scoreItem['score'], $maxScore));

                            $this->gradingService->recordScore([
                                'assessment_id' => $assessmentId,
                                'enrollment_id' => $enrollmentId,
                                'score' => $safeScore,
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Successfully imported grades for {$targetSheet}! Total matched students: " . count($parsedData['matched_records']),
                'redirect_url' => route('teacher.grading-system.grade-sheet', ['teachingAssignmentId' => $ta->id, 'grading_period_id' => $gradingPeriod->id]),
            ]);

        } catch (\Throwable $e) {
            Log::error('DepEd Class Record Import Process Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while saving grades: ' . $e->getMessage(),
            ], 500);
        }
    }
}
