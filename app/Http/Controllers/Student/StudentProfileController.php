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
     * Return a lightweight teacher-scoped student summary for modal use.
     */
    public function teacherSummary(Request $request, Student $student)
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

        $student->load('guardian');

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

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'lrn' => $student->lrn,
                'name' => trim(implode(' ', array_filter([
                    $student->first_name,
                    $student->middle_name,
                    $student->last_name,
                ])) . ($student->suffix ? ', ' . $student->suffix : '')),
            ],
            'guardian' => $student->guardian ? [
                'name' => $student->guardian->name,
                'relationship' => $student->guardian->relationship,
                'contact_number' => $student->guardian->contact_number,
                'email' => $student->guardian->email,
            ] : null,
            'enrollment' => $currentEnrollment ? [
                'status' => $currentEnrollment->status,
                'school_year' => $currentEnrollment->schoolYear?->school_year,
                'grade_level' => $currentEnrollment->sectionModel?->grade_level
                    ?? $currentEnrollment->grade_level,
                'section' => $currentEnrollment->sectionModel?->name
                    ?? $currentEnrollment->getAttribute('section'),
                'session_type' => $currentEnrollment->sectionModel?->session_type,
                'adviser' => $currentEnrollment->sectionModel?->advisor?->full_name,
            ] : null,
        ]);
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
