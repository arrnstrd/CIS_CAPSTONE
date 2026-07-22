<?php

namespace App\Services;

use App\Exceptions\ClassroomScanRejectedException;
use App\Models\Enrollment;
use App\Models\QrCode;
use App\Models\RoomAttendance;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassroomScanService
{
    /**
     * Handle a classroom QR scan for the given authenticated user.
     *
     * Flow (per confirmed spec):
     *  1. Resolve + verify the TeachingAssignment belongs to the teacher and is active
     *  2. Validate the QR code and resolve the student
     *  3. Validate the student's active enrollment matches the assignment's section + school year
     *  4. Determine attendance status against in_start / late_threshold / out_end
     *  5. Create the RoomAttendance record (time_in) or update it (time_out) if one already
     *     exists for this student + assignment + date
     *
     * @param  array{qr_code: string, teaching_assignment_id: int}  $data
     * @param  int  $authUserId
     * @return array  response payload for the controller to return as JSON
     *
     * @throws ClassroomScanRejectedException
     */
    public function scan(array $data, int $authUserId): array
    {
        $now = Carbon::now();

        $teacher = $this->resolveTeacher($authUserId);
        $assignment = $this->resolveOwnedAssignment($data['teaching_assignment_id'], $teacher->id);
        $student = $this->resolveStudentFromQrCode($data['qr_code']);
        $enrollment = $this->resolveMatchingEnrollment($student->id, $assignment);
        $remarks = $this->resolveAttendanceStatus($assignment, $now);

        $attendance = DB::transaction(
            fn () => $this->storeOrUpdateAttendance($assignment, $enrollment, $now, $remarks)
        );

        return [
            'message' => $attendance['action'] === 'time_in'
                ? 'Scan successful'
                : 'Time-out recorded',
            'student' => [
                'id' => $student->id,
                'name' => trim("{$student->first_name} {$student->last_name}"),
            ],
            'teaching_assignment' => [
                'id' => $assignment->id,
                'subject_id' => $assignment->subject_id,
                'section_id' => $assignment->section_id,
                'session_type' => $assignment->session_type,
            ],
            'room_attendance' => [
                'id' => $attendance['record']->id,
                'attendance_date' => $attendance['record']->attendance_date->toDateString(),
                'time_in' => optional($attendance['record']->time_in)->toDateTimeString(),
                'time_out' => optional($attendance['record']->time_out)->toDateTimeString(),
                'remarks' => $attendance['record']->remarks,
            ],
        ];
    }

    private function resolveTeacher(int $authUserId): Teacher
    {
        $teacher = Teacher::where('user_id', $authUserId)->first();

        if (!$teacher) {
            throw new ClassroomScanRejectedException('No teacher record found for the authenticated user', 403);
        }

        return $teacher;
    }

    /**
     * Load the TeachingAssignment and verify it belongs to the authenticated
     * teacher. The submitted teaching_assignment_id is never trusted blindly.
     */
    private function resolveOwnedAssignment(int $teachingAssignmentId, int $teacherId): TeachingAssignment
    {
        $assignment = TeachingAssignment::find($teachingAssignmentId);

        if (!$assignment) {
            throw new ClassroomScanRejectedException('Teaching assignment not found', 404);
        }

        if ($assignment->teacher_id !== $teacherId) {
            throw new ClassroomScanRejectedException('This teaching assignment does not belong to you', 403);
        }

        if ($assignment->status !== 'active') {
            throw new ClassroomScanRejectedException('This teaching assignment is not active', 400);
        }

        return $assignment;
    }

    private function resolveStudentFromQrCode(string $code)
    {
        $qrCode = QrCode::with('student')
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$qrCode || !$qrCode->student) {
            throw new ClassroomScanRejectedException('Invalid QR code or student not found', 404);
        }

        return $qrCode->student;
    }

    /**
     * The student's active enrollment must match the Teaching Assignment's
     * section AND school year — a student from another section (or an
     * enrollment from a different school year) must never be recorded
     * against this assignment.
     */
    private function resolveMatchingEnrollment(int $studentId, TeachingAssignment $assignment): Enrollment
    {
        $enrollment = Enrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->where('section_id', $assignment->section_id)
            ->where('school_year_id', $assignment->school_year_id)
            ->first();

        if (!$enrollment) {
            throw new ClassroomScanRejectedException(
                'Student is not actively enrolled in this teaching assignment\'s section for this school year',
                404
            );
        }

        return $enrollment;
    }

    /**
     * Time-window logic (hard cutoff on both ends):
     *   now < in_start               -> reject, class hasn't started
     *   in_start <= now <= threshold -> present
     *   threshold < now <= out_end   -> late
     *   now > out_end                -> reject, attendance period ended
     */
    private function resolveAttendanceStatus(TeachingAssignment $assignment, Carbon $now): string
    {
        $today = $now->toDateString();

        $inStart = Carbon::parse("{$today} {$assignment->in_start}");
        $lateThreshold = Carbon::parse("{$today} {$assignment->late_threshold}");
        $outEnd = Carbon::parse("{$today} {$assignment->out_end}");

        if ($now->lt($inStart)) {
            throw new ClassroomScanRejectedException('Class has not started yet', 400);
        }

        if ($now->gt($outEnd)) {
            throw new ClassroomScanRejectedException('The attendance period for this class has ended', 400);
        }

        return $now->lte($lateThreshold) ? 'present' : 'late';
    }

    /**
     * A student is expected to scan at most twice for the same
     * assignment + date: once for time_in (creates the record) and
     * once for time_out (fills the existing record). A third scan is
     * rejected as a duplicate.
     *
     * @return array{record: RoomAttendance, action: string}
     */
    private function storeOrUpdateAttendance(
        TeachingAssignment $assignment,
        Enrollment $enrollment,
        Carbon $now,
        string $remarks
    ): array {
        $existing = RoomAttendance::where('teaching_assignment_id', $assignment->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('attendance_date', $now->toDateString())
            ->first();

        if (!$existing) {
            $record = RoomAttendance::create([
                'teaching_assignment_id' => $assignment->id,
                'enrollment_id' => $enrollment->id,
                'attendance_date' => $now->toDateString(),
                'time_in' => $now,
                'remarks' => $remarks,
            ]);

            return ['record' => $record, 'action' => 'time_in'];
        }

        if (!$existing->time_out) {
            $existing->update(['time_out' => $now]);

            return ['record' => $existing->refresh(), 'action' => 'time_out'];
        }

        throw new ClassroomScanRejectedException(
            'Attendance for this student has already been fully recorded for this class today',
            409
        );
    }
}
