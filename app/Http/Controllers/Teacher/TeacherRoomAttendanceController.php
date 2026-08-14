<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherRoomAttendanceController extends Controller
{
    /**
     * Landing page — list the teacher's sections with Total/Present/Absent counts for today.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        if (! $teacher) {
            return view('teacher-modules.room-attendance-index', ['sections' => collect()]);
        }

        $sectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $sections = Section::whereIn('id', $sectionIds)->orderBy('name')->get();

        foreach ($sections as $section) {
            $enrollmentIds = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->pluck('id');

            $totalStudents = $enrollmentIds->count();

            $trackingStart = $this->getTrackingStartDate($enrollmentIds);

            $presentCount = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->whereDate('scan_time', now()->toDateString())
                ->distinct('enrollment_id')
                ->count('enrollment_id');

            $section->total_students = $totalStudents;
            $section->present_count = $presentCount;

            if ($trackingStart === null || now()->startOfDay()->lt($trackingStart)) {
                // No scans have ever happened yet for this section, or tracking hasn't started as of today: nothing to mark absent yet.
                $section->absent_count = 0;
                $section->no_data_yet = true;
            } else {
                $section->absent_count = $totalStudents - $presentCount;
                $section->no_data_yet = false;
            }
        }

        return view('teacher-modules.room-attendance-index', compact('sections'));
    }

    /**
     * Section detail page — full class roster.
     * Single-day view (Today/Yesterday/single-date Custom): editable, one row per student.
     * Multi-day view (This Week/multi-date Custom): view-only, one row per student per day.
     */
    public function show(Request $request, Section $section)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->first()
            : null;

        abort_unless($teachingAssignment, 403, 'You do not have an active teaching assignment for this section.');

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        [$rangeStart, $rangeEnd] = $this->resolveDateRange($dateFilter, $customStartDate, $customEndDate);
        $isSingleDay = $rangeStart->isSameDay($rangeEnd);

        $enrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $enrollmentIds = $enrollments->pluck('id');
        $trackingStart = $this->getTrackingStartDate($enrollmentIds);

        if ($isSingleDay) {
            $roster = $this->buildSingleDayRoster($enrollments, $enrollmentIds, $rangeStart, $teachingAssignment, $trackingStart);
        } else {
            $roster = $this->buildMultiDayRoster($enrollments, $enrollmentIds, $rangeStart, $rangeEnd, $teachingAssignment, $trackingStart);
        }

        $totalStudents = $enrollments->count();

        return view('teacher-modules.room-attendance-show', compact(
            'section', 'roster', 'totalStudents', 'isSingleDay',
            'dateFilter', 'customStartDate', 'customEndDate', 'rangeStart', 'rangeEnd'
        ));
    }

    /**
     * Save a teacher's status resolution for one student on one date.
     */
    public function verify(Request $request, Section $section, Enrollment $enrollment)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->first()
            : null;

        abort_unless($teachingAssignment, 403);
        abort_unless($enrollment->section_id === $section->id, 403, 'Student does not belong to this section.');

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(AttendanceVerification::STATUSES)),
            'remarks' => 'nullable|string|max:1000',
            'attendance_date' => 'required|date',
        ]);

        $attendanceDate = Carbon::parse($validated['attendance_date']);

        // Latest scan for that student on that date, if any (for reference only — not modified)
        $log = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', $attendanceDate->toDateString())
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->first();

        AttendanceVerification::create([
            'enrollment_id' => $enrollment->id,
            'teaching_assignment_id' => $teachingAssignment->id,
            'attendance_log_id' => $log?->id,
            'attendance_date' => $attendanceDate->toDateString(),
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'verified_at' => now(),
            'teacher_id' => $teacher->id,
            'resolved_by' => AttendanceVerification::RESOLVED_BY_TEACHER,
        ]);

        return redirect()
            ->route('room-attendance.show', array_filter([
                'section' => $section->id,
                'date_filter' => $request->input('date_filter', 'today'),
                'custom_start_date' => $request->input('custom_start_date'),
                'custom_end_date' => $request->input('custom_end_date'),
            ]))
            ->with('success', 'Attendance status updated for ' . $attendanceDate->format('M d, Y') . '.');
    }

    /**
     * Full chronological history for one student (all dates, all changes).
     */
    public function history(Request $request, Section $section, Enrollment $enrollment)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->first()
            : null;

        abort_unless($teachingAssignment, 403);
        abort_unless($enrollment->section_id === $section->id, 403);

        $scans = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->orderBy('scan_time', 'desc')
            ->limit(50)
            ->get();

        $verifications = AttendanceVerification::where('enrollment_id', $enrollment->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'student' => $enrollment->student->only(['first_name', 'last_name', 'student_number']),
            'scans' => $scans,
            'verifications' => $verifications,
        ]);
    }

    /**
     * Build one row per student for a SINGLE-DAY view (editable).
     */
    private function buildSingleDayRoster($enrollments, $enrollmentIds, Carbon $date, $teachingAssignment, ?Carbon $trackingStart = null)
    {
        

        $logsByEnrollment = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->whereDate('scan_time', $date->toDateString())
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        // Latest verification per enrollment for this specific date
        $verificationsByEnrollment = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
            ->whereDate('attendance_date', $date->toDateString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        return $enrollments->map(function ($enrollment) use ($logsByEnrollment, $verificationsByEnrollment, $date, $trackingStart) {
            $log = $logsByEnrollment->get($enrollment->id);
            $verification = $verificationsByEnrollment->get($enrollment->id);

            // Refined logic for single day roster
            if ($trackingStart === null || $date->lt($trackingStart) || $date->gt(now())) {
                // No scans have ever happened for this section yet, OR date is before tracking start, OR date is in the future: No Data Yet
                $status = AttendanceVerification::STATUS_NO_DATA;
                $statusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$status];
            } elseif ($verification) {
                // Student has a verification record
                $status = $verification->status;
                $statusLabel = $verification->statusLabel();
            } elseif ($log) {
                // Student has a scan record
                $status = AttendanceVerification::STATUS_PRESENT;
                $statusLabel = AttendanceVerification::STATUSES[$status];
            } else {
                // Date is during tracking period but no records: Absent
                $status = AttendanceVerification::STATUS_ABSENT;
                $statusLabel = AttendanceVerification::STATUSES[$status];
            }

            return (object) [
                'enrollment' => $enrollment,
                'student' => $enrollment->student,
                'log' => $log,
                'verification' => $verification,
                'status' => $status,
                'status_label' => $statusLabel,
                'is_teacher_edited' => (bool) $verification,
                'date' => $date->toDateString(),
            ];
        })->sortBy(fn ($row) => $row->status === AttendanceVerification::STATUS_ABSENT ? 1 : 0)->values();
    }

    /**
     * Build one row per (student, day) for a MULTI-DAY view (read-only).
     */
    private function buildMultiDayRoster($enrollments, $enrollmentIds, Carbon $start, Carbon $end, $teachingAssignment, ?Carbon $trackingStart = null)
    {

        $logs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('scan_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->groupBy(fn ($log) => $log->enrollment_id . '_' . $log->scan_time->toDateString())
            ->map(fn ($group) => $group->first());

        $verifications = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($v) => $v->enrollment_id . '_' . $v->attendance_date->toDateString())
            ->map(fn ($group) => $group->first());

        $rows = collect();

        foreach ($enrollments as $enrollment) {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $enrollment->id . '_' . $cursor->toDateString();
                $log = $logs->get($key);
                $verification = $verifications->get($key);



                // Refined logic for multi-day roster
                if ($trackingStart === null || $cursor->lt($trackingStart) || $cursor->gt(now())) {
                    // No scans have ever happened for this section yet, OR this specific day is before tracking start, OR this specific day is in the future: No Data Yet
                    $status = AttendanceVerification::STATUS_NO_DATA;
                    $statusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$status];
                } elseif ($verification) {
                    // Student has a verification record for this specific day
                    $status = $verification->status;
                    $statusLabel = $verification->statusLabel();
                } elseif ($log) {
                    // Student has a scan record for this specific day
                    $status = AttendanceVerification::STATUS_PRESENT;
                    $statusLabel = AttendanceVerification::STATUSES[$status];
                } else {
                    // This specific day is within the tracking period but has no scan or verification: Absent
                    $status = AttendanceVerification::STATUS_ABSENT;
                    $statusLabel = AttendanceVerification::STATUSES[$status];
                }

                $rows->push((object) [
                    'enrollment' => $enrollment,
                    'student' => $enrollment->student,
                    'log' => $log,
                    'verification' => $verification,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'is_teacher_edited' => (bool) $verification,
                    'date' => $cursor->toDateString(),
                ]);

                $cursor->addDay();
            }
        }

        return $rows;
    }

    /**
     * Resolve a date_filter value into a [start, end] Carbon range.
     */
    private function resolveDateRange($dateFilter, $customStartDate = null, $customEndDate = null)
    {
        switch ($dateFilter) {
            case 'yesterday':
                $d = Carbon::yesterday();
                return [$d->copy()->startOfDay(), $d->copy()->endOfDay()];

            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];

            case 'custom':
                if ($customStartDate && $customEndDate) {
                    return [Carbon::parse($customStartDate), Carbon::parse($customEndDate)];
                }
                return [now()->startOfDay(), now()->startOfDay()];

            case 'today':
            default:
                return [now()->startOfDay(), now()->startOfDay()];
        }
    }

    /**
     * Get the tracking start date for a section.
     * Uses the section's created_at date as the tracking start date.
     */
    private function getTrackingStartDate($enrollmentIds): ?Carbon
    {
        $earliest = \App\Models\AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->min('scan_time');

        return $earliest ? Carbon::parse($earliest)->startOfDay() : null;
    }
}