<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Services\QrSystem\QRCodeService;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function __construct(private QRCodeService $qrCodeService) {}

    public function show(Student $student)
    {
        return view(
            'admin-modules.management.student-profile',
            $this->loadProfileData($student)
        );
    }

    /**
     * Teacher-scoped profile view.
     *
     * A teacher may only open the profile of a student who is enrolled in one of
     * the sections/classes the teacher actually handles.
     */
    public function teacherShow(Request $request, Student $student)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this page.');

        $teacherSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique()
            ->values()
            ->all();

        $isInTeacherClass = $student->enrollments()
            ->whereIn('section_id', $teacherSectionIds)
            ->where('status', 'active')
            ->exists();

        abort_unless($isInTeacherClass, 403, 'You can only view students in your own classes.');

        return view(
            'admin-modules.management.student-profile',
            $this->loadProfileData($student)
        );
    }

    /**
     * Shared profile data loader.
     */
    private function loadProfileData(Student $student): array
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

        return compact(
            'student',
            'currentEnrollment',
            'enrollmentHistory',
            'qrCodeUrl'
        );
    }
}
