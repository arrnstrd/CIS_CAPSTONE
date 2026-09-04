<?php

namespace App\Http\Controllers\Teacher\StudentManagement;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Services\QrSystem\QRCodeService;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function __construct(private QRCodeService $qrCodeService) {}

    /**
     * Return a lightweight teacher-scoped student summary for modal use.
     */
    public function summary(Request $request, Student $student)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this page.');

        $teachingSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $advisedSectionIds = \App\Models\Section::query()
            ->where('advisor_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('id');

        $teacherSectionIds = $teachingSectionIds
            ->concat($advisedSectionIds)
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
    public function show(Request $request, Student $student)
    {
        $teacher = $request->user()?->teacher;

        abort_unless($teacher, 403, 'Only teachers can access this page.');

        $teachingSectionIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $advisedSectionIds = \App\Models\Section::query()
            ->where('advisor_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('id');

        $teacherSectionIds = $teachingSectionIds
            ->concat($advisedSectionIds)
            ->unique()
            ->values()
            ->all();

        $isInTeacherClass = $student->enrollments()
            ->whereIn('section_id', $teacherSectionIds)
            ->where('status', 'active')
            ->exists();

        abort_unless($isInTeacherClass, 403, 'You can only view students in your own classes.');

        if ($request->ajax() || $request->boolean('fragment')) {
            return view('pov.teacher.student-management.student-profile-content',
                $this->loadProfileData($student)
            );
        }

        return view('pov.teacher.student-management.student-profile',
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
