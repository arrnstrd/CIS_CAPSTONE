<?php

namespace App\Http\Controllers\SchoolAdmin\QrGeneration;

use App\Http\Controllers\Controller;
use App\Libraries\PDF\DomPdfWrapper;
use App\Models\QrCode;
use App\Models\Section;
use App\Models\Student;
use App\Services\SchoolAdmin\QRCodeService;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function __construct(
        private QRCodeService $qrCodeService,
        private readonly DomPdfWrapper $pdfWrapper = new DomPdfWrapper()
    ) {}

    public function index(Request $request)
    {
        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();

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
            ->paginate(15)
            ->withQueryString();
        return view('pov.school-admin.qr-generation.qr-generation',
            compact('sections')
        );
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
            'enrollments.section'
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
    public function downloadSection(Section $section)
    {
        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();

        $students = $section->students()
            ->when($activeSchoolYear, function ($query) use ($activeSchoolYear) {
                $query->where('enrollments.school_year_id', $activeSchoolYear->id);
            })
            ->with([
                'qrCode',
                'enrollments' => function ($query) use ($section, $activeSchoolYear) {
                    $query->where('section_id', $section->id);
                    if ($activeSchoolYear) {
                        $query->where('school_year_id', $activeSchoolYear->id);
                    }
                }
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        foreach ($students as $student) {
            if (!$student->qrCode) {
                continue;
            }

            // Generate PNG if missing
            $this->generateQrImage($student->qrCode);

            // Convert PNG to Base64 for DomPDF
            $student->qrImage = $this->getQrImageBase64($student->qrCode);
        }

        $pdf = $this->pdfWrapper->loadView('pdf.qr.section-qr', [
            'section' => $section,
            'students' => $students,
        ]);

        return $pdf->download("QR-{$section->grade_level}-{$section->name}.pdf");
    }

    // TODO: Add "Regenerate All QR Images" feature for administrators.
}
