<?php

namespace App\Http\Controllers\SchoolAdmin\Students;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\SchoolAdmin\QRCodeService;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function __construct(private QRCodeService $qrCodeService) {}

    public function show(Request $request, Student $student)
    {
        if ($request->ajax() || $request->boolean('fragment')) {
            return view('pov.school-admin.students.student-profile-content',
                $this->loadProfileData($student)
            );
        }

        return view('pov.school-admin.students.student-profile',
            $this->loadProfileData($student)
        );
    }

    /**
     * Inline update of the student's personal information (admin only).
     * Updates only the fields sent by the Personal Information container.
     */
    public function updateInfo(Request $request, Student $student)
    {

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'middle_name'     => ['nullable', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'suffix'          => ['nullable', 'string', 'max:20'],
            'sex'             => ['required', 'in:female,male'],
            'age'             => ['nullable', 'integer', 'min:1', 'max:100'],
            'birthplace'      => ['nullable', 'string', 'max:255'],
            'mother_tongue'   => ['nullable', 'string', 'max:100'],
            'ip_ethnic_group' => ['nullable', 'string', 'max:100'],
            'religion'        => ['nullable', 'string', 'max:100'],
            'address'         => ['nullable', 'string'],
        ]);

        // students.address is NOT NULL — store an empty string when cleared.
        $student->update([...$validated, 'address' => $validated['address'] ?? '']);

        return response()->json([
            'message' => 'Personal information updated.',
            'student' => $student->fresh(),
        ]);
    }

    /**
     * Inline update of the guardian record (admin only).
     */
    public function updateGuardian(Request $request, Student $student)
    {

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'relationship'   => ['required', 'string', 'max:50'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
        ]);

        $student->guardian()->updateOrCreate([], $validated);

        return response()->json([
            'message' => 'Guardian information updated.',
            'student' => $student->fresh()->load('guardian'),
        ]);
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
