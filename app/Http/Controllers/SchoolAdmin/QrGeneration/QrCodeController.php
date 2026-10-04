<?php

namespace App\Http\Controllers\SchoolAdmin\QrGeneration;

use App\Http\Controllers\Controller;
use App\Libraries\PDF\DomPdfWrapper;
use App\Models\QrCode;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Services\SchoolAdmin\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class QrCodeController extends Controller
{
    private const BASKET_KEY = 'qr_pass_basket';

    public function __construct(
        private QRCodeService $qrCodeService,
        private readonly DomPdfWrapper $pdfWrapper = new DomPdfWrapper()
    ) {}

    public function index(Request $request)
    {
        $activeSchoolYear = SchoolYear::query()->active()->first();

        // Metric Statistics
        $totalSections = Section::query()->count();
        $totalStudents = Student::query()
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->whereHas('enrollments', function ($q) use ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                });
            })->count();

        $totalQrCount = QrCode::query()->count();

        // Sections query with student count & adviser details
        $sections = Section::with('advisor.user')
            ->filterGradeLevel($request->grade_level)
            ->search($request->search)
            ->withCount(['students' => function ($query) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $query->where('enrollments.school_year_id', $activeSchoolYear->id);
                }
            }])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(12, ['*'], 'sections_page')
            ->withQueryString();

        // Students Directory Query (for Directory tab search & multi-select)
        $studentSearch  = $request->input('student_search');
        $studentSectionId = $request->input('section_id');

        $directoryStudents = Student::with(['qrCode', 'enrollments.section'])
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->whereHas('enrollments', function ($q) use ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                });
            })
            ->when($studentSearch, function ($query) use ($studentSearch) {
                $term = strtolower(trim($studentSearch));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw("LOWER(first_name) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(last_name) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(CONCAT(last_name, ' ', first_name)) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(CONCAT(last_name, ', ', first_name)) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(student_number) LIKE ?", ["%{$term}%"])
                        ->orWhereRaw("LOWER(lrn) LIKE ?", ["%{$term}%"]);
                });
            })
            ->when($studentSectionId, function ($query) use ($studentSectionId, $activeSchoolYear) {
                $query->whereHas('enrollments', function ($q) use ($studentSectionId, $activeSchoolYear) {
                    $q->where('section_id', $studentSectionId);
                    if ($activeSchoolYear) {
                        $q->where('school_year_id', $activeSchoolYear->id);
                    }
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15, ['*'], 'students_page')
            ->withQueryString();

        $allSections = Section::orderBy('grade_level')->orderBy('name')->get();

        // Load current basket from session and enrich with student data for display
        $basketIds   = session(self::BASKET_KEY, []);
        $basketItems = [];

        if (!empty($basketIds)) {
            $basketStudents = Student::with(['enrollments.section'])
                ->whereIn('id', $basketIds)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            foreach ($basketStudents as $student) {
                $section = $student->enrollments->first()?->section;
                $basketItems[$student->id] = [
                    'id'             => $student->id,
                    'full_name'      => $student->last_name . ', ' . $student->first_name,
                    'student_number' => $student->student_number,
                    'section_name'   => $section
                        ? 'Grade ' . $section->grade_level . ' - ' . $section->name
                        : 'Unassigned',
                ];
            }
        }

        return view('pov.school-admin.qr-generation.qr-generation', compact(
            'sections',
            'directoryStudents',
            'allSections',
            'totalSections',
            'totalStudents',
            'totalQrCount',
            'basketItems'
        ));
    }

    // -------------------------------------------------------------------------
    // Session Basket Endpoints
    // -------------------------------------------------------------------------

    /**
     * Add a student to the session basket.
     */
    public function basketAdd(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
        ]);

        $studentId = (int) $request->input('student_id');
        $basket    = session(self::BASKET_KEY, []);

        if (!in_array($studentId, $basket, true)) {
            $basket[] = $studentId;
            session([self::BASKET_KEY => $basket]);
        }

        return response()->json([
            'status' => 'added',
            'count'  => count($basket),
        ]);
    }

    /**
     * Remove a student from the session basket.
     */
    public function basketRemove(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer',
        ]);

        $studentId = (int) $request->input('student_id');
        $basket    = session(self::BASKET_KEY, []);
        $basket    = array_values(array_filter($basket, fn($id) => $id !== $studentId));

        session([self::BASKET_KEY => $basket]);

        return response()->json([
            'status' => 'removed',
            'count'  => count($basket),
        ]);
    }

    /**
     * Clear all students from the session basket.
     */
    public function basketClear()
    {
        session()->forget(self::BASKET_KEY);

        return response()->json([
            'status' => 'cleared',
            'count'  => 0,
        ]);
    }

    // -------------------------------------------------------------------------
    // QR Display / Download
    // -------------------------------------------------------------------------

    /**
     * Get JSON list of students in a specific section for inline drawer.
     */
    public function getSectionStudents(Section $section)
    {
        $activeSchoolYear = SchoolYear::query()->active()->first();

        $students = $section->students()
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('enrollments.school_year_id', $activeSchoolYear->id);
            })
            ->with('qrCode')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($student) {
                return [
                    'id'             => $student->id,
                    'full_name'      => $student->full_name,
                    'student_number' => $student->student_number,
                    'lrn'            => $student->lrn,
                    'has_qr'         => (bool) $student->qrCode,
                    'download_url'   => route('students.qr.download', $student->id),
                ];
            });

        return response()->json([
            'section' => [
                'id'          => $section->id,
                'name'        => $section->name,
                'grade_level' => $section->grade_level,
            ],
            'students' => $students,
        ]);
    }

    /**
     * Generate QR PNG if it doesn't already exist.
     */
    private function generateQrImage(QrCode $qrCode): void
    {
        $this->qrCodeService->ensureImage($qrCode);
    }

    /**
     * Read QR PNG and return Base64 string.
     */
    private function getQrImageBase64(QrCode $qrCode): string
    {
        return $this->qrCodeService->imageBase64($qrCode);
    }

    /**
     * Display QR page.
     */
    public function show($id)
    {
        $student = Student::with('qrCode')->findOrFail($id);

        if (!$student->qrCode) {
            abort(404, 'QR code not found.');
        }

        $this->generateQrImage($student->qrCode);

        return view('pov.school-admin.qr-generation.qr-show', ['student' => $student]);
    }

    /**
     * Download a single student's QR card.
     */
    public function download($id)
    {
        $student = Student::with([
            'qrCode',
            'enrollments.section',
        ])->findOrFail($id);

        if (!$student->qrCode) {
            abort(404, 'QR code not found.');
        }

        $this->generateQrImage($student->qrCode);
        $qrImage = $this->getQrImageBase64($student->qrCode);

        $pdf = $this->pdfWrapper->loadView('pdf.qr.student-qr', [
            'student' => $student,
            'qrImage' => $qrImage,
        ]);

        return $pdf->download('student-qr-' . $student->student_number . '.pdf');
    }

    /**
     * Download QR cards for all students in a section.
     */
    public function downloadSection(Request $request, Section $section)
    {
        $activeSchoolYear = SchoolYear::query()->active()->first();

        // Guard: no active school year
        if (!$activeSchoolYear) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'No active school year is configured. Cannot generate the section QR PDF.'
                ], 422);
            }
            return back()->with('error', 'No active school year is configured. Cannot generate the section QR PDF.');
        }

        $students = $section->students()
            ->where('enrollments.school_year_id', $activeSchoolYear->id)
            ->with([
                'qrCode',
                'enrollments' => function ($query) use ($section, $activeSchoolYear) {
                    $query->where('section_id', $section->id)
                          ->where('school_year_id', $activeSchoolYear->id);
                },
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $withQr = $students->filter(fn($s) => $s->qrCode);

        // Guard: empty PDF — no student in this section has a QR code yet
        if ($withQr->isEmpty()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => "No students in \"{$section->name}\" have a generated QR code yet. Generate QR codes first before downloading the section PDF."
                ], 422);
            }
            return back()->with('warning', "No students in \"{$section->name}\" have a generated QR code yet. Generate QR codes first before downloading the section PDF.");
        }

        try {
            foreach ($withQr as $student) {
                $this->generateQrImage($student->qrCode);
                $student->qrImage = $this->getQrImageBase64($student->qrCode);
            }

            $pdf = $this->pdfWrapper->loadView('pdf.qr.section-qr', [
                'section'  => $section,
                'students' => $withQr->values(),
            ]);

            return $pdf->download("QR-{$section->grade_level}-{$section->name}.pdf");
        } catch (Throwable $e) {
            Log::error('Section QR PDF generation failed', [
                'section_id' => $section->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'An unexpected error occurred while compiling the section PDF. Please try again.'
                ], 500);
            }
            return back()->with('error', 'An unexpected error occurred while compiling the section PDF. Please try again.');
        }
    }

    /**
     * Download a batch of selected student QR cards (driven by session basket).
     */
    public function downloadBatch(Request $request)
    {
        $activeSchoolYear = SchoolYear::query()->active()->first();

        // Guard: no active school year
        if (!$activeSchoolYear) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'No active school year is configured. Cannot generate the batch QR PDF.'
                ], 422);
            }
            return back()->with('error', 'No active school year is configured. Cannot generate the batch QR PDF.');
        }

        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $studentIds = array_unique(array_map('intval', $request->input('student_ids')));

        $students = Student::with(['qrCode', 'enrollments.section'])
            ->whereIn('id', $studentIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $withQr = $students->filter(fn($s) => $s->qrCode);

        // Guard: empty PDF — none of the selected students have a QR code
        if ($withQr->isEmpty()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'None of the selected students have a generated QR code. No PDF was produced.'
                ], 422);
            }
            return back()->with('warning', 'None of the selected students have a generated QR code. No PDF was produced.');
        }

        try {
            foreach ($withQr as $student) {
                $this->generateQrImage($student->qrCode);
                $student->qrImage = $this->getQrImageBase64($student->qrCode);
            }

            $pdf = $this->pdfWrapper->loadView('pdf.qr.batch-qr', [
                'students' => $withQr->values(),
            ]);

            // Clear the session basket after a successful batch download.
            session()->forget(self::BASKET_KEY);

            return $pdf->download('Batch-Selected-Students-QR.pdf');
        } catch (Throwable $e) {
            Log::error('Batch QR PDF generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'An unexpected error occurred while compiling the batch PDF. Please try again.'
                ], 500);
            }
            return back()->with('error', 'An unexpected error occurred while compiling the batch PDF. Please try again.');
        }
    }
}
