<?php

namespace App\Http\Controllers\Teacher\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\TeachingAssignment;
use App\Services\Notification\NotificationService;
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
            return view('pov.teacher.attendance.room-attendance-index', [
                'sections' => collect(),
            ]);
        }

        $sectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $sections = Section::whereIn('id', $sectionIds)
            ->orderBy('name')
            ->get();

        foreach ($sections as $section) {
            $enrollmentIds = Enrollment::where('section_id', $section->id)
                ->where('status', 'active')
                ->pluck('id');

            $totalStudents = $enrollmentIds->count();

            $trackingStart = $this->getTrackingStartDate($enrollmentIds);

            $presentCount = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->whereDate('scan_time', now()->toDateString())
                ->where('scan_type', 'IN')
                ->distinct('enrollment_id')
                ->count('enrollment_id');

            $section->total_students = $totalStudents;
            $section->present_count = $presentCount;

            if (
                $trackingStart === null
                || now()->startOfDay()->lt($trackingStart)
            ) {
                // No actual QR scan has happened for this section yet.
                $section->absent_count = 0;
                $section->no_data_yet = true;
            } else {
                $section->absent_count = max(
                    0,
                    $totalStudents - $presentCount
                );
                $section->no_data_yet = false;
            }
        }

        return view('pov.teacher.attendance.room-attendance-index',
            compact('sections')
        );
    }

    /**
     * Section detail page — full class roster.
     *
     * Single-day view:
     * editable, one row per student.
     *
     * Multi-day view:
     * view-only, one row per student per day.
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

        abort_unless(
            $teachingAssignment,
            403,
            'You do not have an active teaching assignment for this section.'
        );

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        [$rangeStart, $rangeEnd] = $this->resolveDateRange(
            $dateFilter,
            $customStartDate,
            $customEndDate
        );

        $isSingleDay = $rangeStart->isSameDay($rangeEnd);

        $enrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with('student')
            ->get();

        $enrollmentIds = $enrollments->pluck('id');

        $trackingStart = $this->getTrackingStartDate($enrollmentIds);

        if ($isSingleDay) {
            $roster = $this->buildSingleDayRoster(
                $enrollments,
                $enrollmentIds,
                $rangeStart,
                $teachingAssignment,
                $trackingStart
            );
        } else {
            $roster = $this->buildMultiDayRoster(
                $enrollments,
                $enrollmentIds,
                $rangeStart,
                $rangeEnd,
                $teachingAssignment,
                $trackingStart
            );
        }

        $totalStudents = $enrollments->count();

        return view('pov.teacher.attendance.room-attendance-show',
            compact(
                'section',
                'roster',
                'totalStudents',
                'isSingleDay',
                'dateFilter',
                'customStartDate',
                'customEndDate',
                'rangeStart',
                'rangeEnd'
            )
        );
    }

    /**
     * Save a teacher's status resolution for one student on one date.
     */
    public function verify(
        Request $request,
        Section $section,
        Enrollment $enrollment
    ) {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->first()
            : null;

        abort_unless($teachingAssignment, 403);

        abort_unless(
            $enrollment->section_id === $section->id,
            403,
            'Student does not belong to this section.'
        );

        $validated = $request->validate([
            'status' => 'required|in:' .
                implode(',', array_keys(AttendanceVerification::STATUSES)),
            'remarks' => 'nullable|string|max:1000',
            'attendance_date' => 'required|date',
        ]);

        $attendanceDate = Carbon::parse(
            $validated['attendance_date']
        );

        /*
        |----------------------------------------------------------------------
        | Actual QR scan for reference
        |----------------------------------------------------------------------
        |
        | Teacher verification does not create a QR attendance log.
        | It only records a teacher resolution.
        |
        */

        $log = AttendanceLog::where(
            'enrollment_id',
            $enrollment->id
        )
            ->whereDate(
                'scan_time',
                $attendanceDate->toDateString()
            )
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

        $studentName = $enrollment->student
            ? trim($enrollment->student->first_name . ' ' . $enrollment->student->last_name)
            : 'Student';
        $statusLabel = AttendanceVerification::STATUSES[$validated['status']] ?? ucfirst($validated['status']);

        NotificationService::send(
            $request->user(),
            NotificationService::CATEGORY_ATTENDANCE,
            'Attendance Record Updated',
            "Attendance for {$studentName} in Section {$section->name} was marked as {$statusLabel} for {$attendanceDate->format('M d, Y')}.",
            [
                'url' => route('room-attendance.show', ['section' => $section->id]),
                'section_id' => $section->id,
                'enrollment_id' => $enrollment->id,
                'status' => $validated['status'],
                'attendance_date' => $attendanceDate->toDateString(),
            ]
        );

        return redirect()
            ->route(
                'room-attendance.show',
                array_filter([
                    'section' => $section->id,
                    'date_filter' => $request->input(
                        'date_filter',
                        'today'
                    ),
                    'custom_start_date' => $request->input(
                        'custom_start_date'
                    ),
                    'custom_end_date' => $request->input(
                        'custom_end_date'
                    ),
                ])
            )
            ->with(
                'success',
                'Attendance status updated for ' .
                    $attendanceDate->format('M d, Y') .
                    '.'
            );
    }

    /**
     * Full chronological history for one student.
     */
    public function history(
        Request $request,
        Section $section,
        Enrollment $enrollment
    ) {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
                ->where('section_id', $section->id)
                ->where('status', 'active')
                ->first()
            : null;

        abort_unless($teachingAssignment, 403);
        abort_unless(
            $enrollment->section_id === $section->id,
            403
        );

        $scans = AttendanceLog::where(
            'enrollment_id',
            $enrollment->id
        )
            ->orderBy('scan_time', 'desc')
            ->limit(50)
            ->get();

        $verifications = AttendanceVerification::where(
            'enrollment_id',
            $enrollment->id
        )
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'student' => $enrollment->student->only([
                'first_name',
                'last_name',
                'student_number',
            ]),
            'scans' => $scans,
            'verifications' => $verifications,
        ]);
    }

    /**
     * Build one row per student for a SINGLE-DAY view.
     *
     * IMPORTANT ATTENDANCE RULE:
     *
     * A teacher verification alone does NOT establish attendance.
     *
     * The student must have an actual QR IN scan for the selected date
     * before the teacher verification can affect the displayed status.
     */
    private function buildSingleDayRoster(
        $enrollments,
        $enrollmentIds,
        Carbon $date,
        $teachingAssignment,
        ?Carbon $trackingStart = null
    ) {
        $logsByEnrollment = AttendanceLog::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->whereDate(
                'scan_time',
                $date->toDateString()
            )
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        $verificationsByEnrollment = AttendanceVerification::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->whereDate(
                'attendance_date',
                $date->toDateString()
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        return $enrollments
            ->map(function ($enrollment) use (
                $logsByEnrollment,
                $verificationsByEnrollment,
                $date,
                $trackingStart
            ) {
                $log = $logsByEnrollment->get($enrollment->id);

                $verification = $verificationsByEnrollment->get(
                    $enrollment->id
                );

                /*
                |--------------------------------------------------------------
                | Rule 1: Tracking has not started
                |--------------------------------------------------------------
                */

                if (
                    $trackingStart === null
                    || $date->lt($trackingStart)
                    || $date->gt(now())
                ) {
                    $status = AttendanceVerification::STATUS_NO_DATA;

                    $statusLabel =
                        AttendanceVerification::STATUS_LABELS_EXTENDED[
                            $status
                        ];
                }

                /*
                |--------------------------------------------------------------
                | Rule 2: No QR scan for this student
                |--------------------------------------------------------------
                |
                | Even if a teacher verification exists, do NOT show
                | Present/Late/Excused/etc.
                |
                */

                elseif (! $log) {
                    $status = AttendanceVerification::STATUS_NO_DATA;

                    $statusLabel =
                        AttendanceVerification::STATUS_LABELS_EXTENDED[
                            $status
                        ];
                }

                /*
                |--------------------------------------------------------------
                | Rule 3: QR scan exists + teacher verification exists
                |--------------------------------------------------------------
                |
                | Teacher verification may override the QR-derived Present
                | status.
                |
                */

                elseif ($verification) {
                    $status = $verification->status;
                    $statusLabel = $verification->statusLabel();
                }

                /*
                |--------------------------------------------------------------
                | Rule 4: QR scan exists and no verification
                |--------------------------------------------------------------
                */

                else {
                    $status = AttendanceVerification::STATUS_PRESENT;

                    $statusLabel =
                        AttendanceVerification::STATUSES[$status];
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
            })
            ->sortBy(
                fn ($row) =>
                    $row->status === AttendanceVerification::STATUS_ABSENT
                        ? 1
                        : 0
            )
            ->values();
    }

    /**
     * Build one row per (student, day) for a MULTI-DAY view.
     */
    private function buildMultiDayRoster(
        $enrollments,
        $enrollmentIds,
        Carbon $start,
        Carbon $end,
        $teachingAssignment,
        ?Carbon $trackingStart = null
    ) {
        $logs = AttendanceLog::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->whereBetween(
                'scan_time',
                [
                    $start->copy()->startOfDay(),
                    $end->copy()->endOfDay(),
                ]
            )
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->groupBy(
                fn ($log) =>
                    $log->enrollment_id .
                    '_' .
                    $log->scan_time->toDateString()
            )
            ->map(
                fn ($group) => $group->first()
            );

        $verifications = AttendanceVerification::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->whereBetween(
                'attendance_date',
                [
                    $start->toDateString(),
                    $end->toDateString(),
                ]
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(
                fn ($v) =>
                    $v->enrollment_id .
                    '_' .
                    $v->attendance_date->toDateString()
            )
            ->map(
                fn ($group) => $group->first()
            );

        $rows = collect();

        foreach ($enrollments as $enrollment) {
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $key =
                    $enrollment->id .
                    '_' .
                    $cursor->toDateString();

                $log = $logs->get($key);
                $verification = $verifications->get($key);

                /*
                |--------------------------------------------------------------
                | Rule 1: Tracking has not started / future date
                |--------------------------------------------------------------
                */

                if (
                    $trackingStart === null
                    || $cursor->lt($trackingStart)
                    || $cursor->gt(now())
                ) {
                    $status = AttendanceVerification::STATUS_NO_DATA;

                    $statusLabel =
                        AttendanceVerification::STATUS_LABELS_EXTENDED[
                            $status
                        ];
                }

                /*
                |--------------------------------------------------------------
                | Rule 2: No QR scan for this student on this date
                |--------------------------------------------------------------
                */

                elseif (! $log) {
                    $status = AttendanceVerification::STATUS_NO_DATA;

                    $statusLabel =
                        AttendanceVerification::STATUS_LABELS_EXTENDED[
                            $status
                        ];
                }

                /*
                |--------------------------------------------------------------
                | Rule 3: QR scan + teacher verification
                |--------------------------------------------------------------
                */

                elseif ($verification) {
                    $status = $verification->status;
                    $statusLabel = $verification->statusLabel();
                }

                /*
                |--------------------------------------------------------------
                | Rule 4: QR scan without verification
                |--------------------------------------------------------------
                */

                else {
                    $status = AttendanceVerification::STATUS_PRESENT;

                    $statusLabel =
                        AttendanceVerification::STATUSES[$status];
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

        return $rows->groupBy('date');
    }

    /**
     * Resolve a date_filter value into a [start, end] Carbon range.
     */
    private function resolveDateRange(
        $dateFilter,
        $customStartDate = null,
        $customEndDate = null
    ) {
        switch ($dateFilter) {
            case 'yesterday':
                $d = Carbon::yesterday();

                return [
                    $d->copy()->startOfDay(),
                    $d->copy()->endOfDay(),
                ];

            case 'week':
                return [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ];

            case 'custom':
                if ($customStartDate && $customEndDate) {
                    return [
                        Carbon::parse($customStartDate)->startOfDay(),
                        Carbon::parse($customEndDate)->endOfDay(),
                    ];
                }

                return [
                    now()->startOfDay(),
                    now()->startOfDay(),
                ];

            case 'today':
            default:
                return [
                    now()->startOfDay(),
                    now()->startOfDay(),
                ];
        }
    }

    /**
     * Get the tracking start date for a section.
     *
     * Attendance tracking begins based on the earliest actual QR IN scan
     * among students in the section.
     *
     * Teacher verification records do NOT start attendance tracking.
     */
    private function getTrackingStartDate(
        $enrollmentIds
    ): ?Carbon {
        $earliest = AttendanceLog::whereIn(
            'enrollment_id',
            $enrollmentIds
        )
            ->where('scan_type', 'IN')
            ->min('scan_time');

        return $earliest
            ? Carbon::parse($earliest)->startOfDay()
            : null;
    }
}