<?php

namespace App\Http\Controllers;
use App\Models\Student; 
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    //

    public function index(){
        
    }

      public function generate($id)
    {
        // 1. Load student + QR
        $student = Student::with('qrCode')->findOrFail($id);

        // 2. Generate QR image (base64 PNG)
        $qrImage = base64_encode(
            QrCode::format('svg')
                ->size(200)
                ->generate($student->qrCode->code)
        );

        // 3. Pass data to PDF view
        $pdf = Pdf::loadView('pdf.student-qr', [
            'student' => $student,
            'qrImage' => $qrImage
        ]);

        // 4. Download PDF
        return $pdf->download('student-qr-' . $student->student_number . '.pdf');
    }
}
