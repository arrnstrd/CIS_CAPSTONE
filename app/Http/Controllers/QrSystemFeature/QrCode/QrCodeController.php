<?php

namespace App\Http\Controllers\QrSystemFeature\QrCode;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Section;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
           $sections = Section::with('advisor.user')
            ->filterGradeLevel($request->grade_level)
          
            ->search($request->search)
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        return view(
            'admin-modules.utilities.qr-generation',
            compact('sections')
        );
    }



    /**
     * Generate QR PNG if it doesn't already exist.
     */
    private function generateQrImage(QrCode $qrCode): void
    {
        if ($qrCode->image_path && Storage::disk('public')->exists($qrCode->image_path)) {
            return;
        }

        $image = QrGenerator::format('png')->size(300)->margin(1)->generate($qrCode->code);
        $path = "qr-codes/student-{$qrCode->student_id}.png";

        Storage::disk('public')->put($path, $image);
        $qrCode->update(['image_path' => $path]);
    }

    /**
     * Read QR PNG and return Base64 string.
     */
    private function getQrImageBase64(QrCode $qrCode): string
    {
        $path = storage_path('app/public/' . $qrCode->image_path);

        return base64_encode(file_get_contents($path));
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

        return view('qr.show', ['student' => $student]);
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

        $pdf = Pdf::loadView('pdf.student-qr', [
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
        $students = $section->students()
            ->with([
                'qrCode',
                'enrollments' => function ($query) use ($section) {
                    $query->where('section_id', $section->id);
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

        $pdf = Pdf::loadView('pdf.section-qr', [
            'section' => $section,
            'students' => $students,
        ]);

        return $pdf->download("QR-{$section->grade_level}-{$section->name}.pdf");
    }

    // TODO: Add "Regenerate All QR Images" feature for administrators.
}