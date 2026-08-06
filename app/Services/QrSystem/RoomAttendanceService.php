<?php

namespace App\Services\QrSystem;

use App\Models\RoomAttendance;
use App\Models\TeachingAssignment;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomAttendanceService
{
    /**
     * Create a new room attendance record.
     *
     * @param array{
     *   teaching_assignment_id: int,
     *   enrollment_id: int,
     *   attendance_date: string,
     *   time_in: string,
     *   time_out?: string,
     *   remarks?: string
     * } $data
     * @return RoomAttendance
     * @throws \InvalidArgumentException
     */
    public function create(array $data): RoomAttendance
    {
        // Validate that the enrollment belongs to the teaching assignment's section
        $teachingAssignment = TeachingAssignment::findOrFail($data['teaching_assignment_id']);
        $enrollment = Enrollment::findOrFail($data['enrollment_id']);

        if ($enrollment->section_id !== $teachingAssignment->section_id) {
            throw new \InvalidArgumentException('The enrollment does not belong to the teaching assignment\'s section.');
        }

        // Check for duplicate attendance on the same date for the same enrollment and teaching assignment
        $existing = RoomAttendance::where('teaching_assignment_id', $data['teaching_assignment_id'])
            ->where('enrollment_id', $data['enrollment_id'])
            ->where('attendance_date', $data['attendance_date'])
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('Room attendance already exists for this enrollment on this date.');
        }

        return DB::transaction(function () use ($data) {
            return RoomAttendance::create([
                'teaching_assignment_id' => $data['teaching_assignment_id'],
                'enrollment_id' => $data['enrollment_id'],
                'attendance_date' => $data['attendance_date'],
                'time_in' => $data['time_in'],
                'time_out' => $data['time_out'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);
        });
    }

    /**
     * Update an existing room attendance record.
     *
     * @param RoomAttendance $roomAttendance
     * @param array{
     *   time_in?: string,
     *   time_out?: string,
     *   remarks?: string
     * } $data
     * @return RoomAttendance
     */
    public function update(RoomAttendance $roomAttendance, array $data): RoomAttendance
    {
        return DB::transaction(function () use ($roomAttendance, $data) {
            $roomAttendance->update(array_filter($data, fn($value) => $value !== null));
            return $roomAttendance->fresh();
        });
    }

    /**
     * Delete a room attendance record.
     *
     * @param RoomAttendance $roomAttendance
     * @return bool
     */
    public function delete(RoomAttendance $roomAttendance): bool
    {
        return DB::transaction(function () use ($roomAttendance) {
            return $roomAttendance->delete();
        });
    }

    /**
     * Get room attendance for a specific teaching assignment on a specific date.
     *
     * @param int $teachingAssignmentId
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTeachingAssignmentAndDate(int $teachingAssignmentId, string $date)
    {
        return RoomAttendance::with(['enrollment.student', 'enrollment.section'])
            ->where('teaching_assignment_id', $teachingAssignmentId)
            ->where('attendance_date', $date)
            ->get();
    }

    /**
     * Get room attendance for a specific student (enrollment) within a date range.
     *
     * @param int $enrollmentId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByEnrollmentAndDateRange(int $enrollmentId, string $startDate, string $endDate)
    {
        return RoomAttendance::with(['teachingAssignment.subject', 'teachingAssignment.section'])
            ->where('enrollment_id', $enrollmentId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date')
            ->get();
    }

    /**
     * Bulk create room attendance records for a class.
     *
     * @param int $teachingAssignmentId
     * @param string $attendanceDate
     * @param array<int, array{enrollment_id: int, time_in: string, time_out?: string, remarks?: string}> $attendanceData
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function bulkCreate(int $teachingAssignmentId, string $attendanceDate, array $attendanceData)
    {
        $teachingAssignment = TeachingAssignment::findOrFail($teachingAssignmentId);

        return DB::transaction(function () use ($teachingAssignmentId, $teachingAssignment, $attendanceDate, $attendanceData) {
            $records = [];

            foreach ($attendanceData as $data) {
                $enrollment = Enrollment::findOrFail($data['enrollment_id']);

                if ($enrollment->section_id !== $teachingAssignment->section_id) {
                    Log::warning("Skipping enrollment {$data['enrollment_id']}: not in teaching assignment's section");
                    continue;
                }

                // Check for existing record
                $existing = RoomAttendance::where('teaching_assignment_id', $teachingAssignmentId)
                    ->where('enrollment_id', $data['enrollment_id'])
                    ->where('attendance_date', $attendanceDate)
                    ->first();

                if ($existing) {
                    Log::warning("Skipping enrollment {$data['enrollment_id']}: attendance already exists for this date");
                    continue;
                }

                $records[] = RoomAttendance::create([
                    'teaching_assignment_id' => $teachingAssignmentId,
                    'enrollment_id' => $data['enrollment_id'],
                    'attendance_date' => $attendanceDate,
                    'time_in' => $data['time_in'],
                    'time_out' => $data['time_out'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                ]);
            }

            return collect($records);
        });
    }
}
