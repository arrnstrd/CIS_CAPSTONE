<?php

namespace App\Services\QrSystem;

use App\Models\AttendanceVerification;
use App\Models\AttendanceVerificationHistory;
use App\Models\Enrollment;
use App\Models\QrAttendance;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Validation\ValidationException;

class ClassroomVerificationService
{
    public function getRoster(int $teachingAssignmentId): array
    {
        $assignment = TeachingAssignment::findOrFail($teachingAssignmentId);
        $today = now()->toDateString();

        $enrollments = Enrollment::with('student')
            ->where('section_id', $assignment->section_id)
            ->where('status', 'active')
            ->get();

        return $enrollments->map(function (Enrollment $enrollment) use ($today, $assignment) {
            $qrAttendance = QrAttendance::where('enrollment_id', $enrollment->id)
                ->where('attendance_date', $today)
                ->first();

            $verification = AttendanceVerification::where('enrollment_id', $enrollment->id)
                ->where('teaching_assignment_id', $assignment->id)
                ->where('attendance_date', $today)
                ->first();

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'student_name' => trim(
                    ($enrollment->student->first_name ?? '') . ' ' .
                        ($enrollment->student->last_name ?? '')
                ),
                'lrn' => $enrollment->student->lrn ?? null,
                'time_in' => $qrAttendance?->time_in_log_id !== null,
                'classroom_status' => $verification?->status,
                'remarks' => $verification?->remarks,
            ];
        })->values()->all();
    }

    public function verify(array $data, int $userId): AttendanceVerification
    {
        $teacher = Teacher::where('user_id', $userId)->first();

        if (!$teacher) {
            throw ValidationException::withMessages([
                'teacher_id' => 'No teacher record found for the authenticated user.',
            ]);
        }

        $today = now()->toDateString();

        $verification = AttendanceVerification::firstOrNew([
            'enrollment_id' => $data['enrollment_id'],
            'teaching_assignment_id' => $data['teaching_assignment_id'],
            'attendance_date' => $today,
        ]);

        $previousStatus = $verification->exists ? $verification->status : null;

        $verification->fill([
            'teacher_id' => $teacher->id,
            'resolved_by' => 'teacher',
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'verified_at' => now(),
        ]);

        $verification->save();

        AttendanceVerificationHistory::create([
            'attendance_verification_id' => $verification->id,
            'previous_status' => $previousStatus,
            'new_status' => $verification->status,
            'changed_by' => $userId,
            'remarks' => $verification->remarks,
        ]);

        return $verification;
    }
}
