<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\QrSystem\QRCodeService;

class StudentProfileController extends Controller
{
    public function __construct(private QRCodeService $qrCodeService)
    {
    }

    public function show(Student $student)
    {
        // Load relationships needed for the profile
        $student->load([
            'guardian',
            'qrCode',
        ]);

        $schoolYearId = session('school_year_id')
            ?? SchoolYear::query()->active()->value('id');

        $enrollmentRelations = [
            'schoolYear',
            'sectionModel.advisor.user',
        ];

        $currentEnrollment = null;

        if ($schoolYearId) {
            $currentEnrollment = $student->enrollments()
                ->with($enrollmentRelations)
                ->where('school_year_id', $schoolYearId)
                ->orderByRaw("status = 'active' desc")
                ->latest('id')
                ->first();
        }

        $currentEnrollment ??= $student->enrollments()
            ->with($enrollmentRelations)
            ->orderByRaw("status = 'active' desc")
            ->latest('id')
            ->first();

        $enrollmentHistory = $student->enrollments()
            ->with($enrollmentRelations)
            ->latest('id')
            ->get();

        if ($student->qrCode) {
            $this->qrCodeService->ensureImage($student->qrCode);
            $student->load('qrCode');
        }

        $qrCodeUrl = $student->qrCode
            ? $this->qrCodeService->publicUrl($student->qrCode)
            : null;

        return view(
            'admin-modules.management.student-profile',
            compact(
                'student',
                'currentEnrollment',
                'enrollmentHistory',
                'qrCodeUrl'
            )
        );
    }
}
