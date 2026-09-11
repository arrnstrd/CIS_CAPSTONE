<?php

namespace App\Http\Controllers\Teacher\Attendance;

use App\Events\AttendanceVerificationUpdated;
use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\AttendanceVerificationHistory;
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
            return view('pov.teacher.attendance.room-attendance-index', [
                'sections' => collect(),
            ]);
        }

        $sectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id')
            ->unique();

        $sections = Section::whereIn('id', $sectionIds)
            ->orderBy('name')
            ->get();

        $today = now()->toDateString();

        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();

        foreach ($sections as $section) {
            $enrollmentsQuery = Enrollment::where('section_id', $section->id)
                ->where('status', 'active');
                
            if ($activeSchoolYear) {
                $enrollmentsQuery->where('school_year_id', $activeSchoolYear->id);
            }
            
            $enrollments = $enrollmentsQuery->get();

            $enrollmentIds = $enrollments->pluck('id');
            $totalStudents = $enrollmentIds->count();
            $section->total_students = $totalStudents;

            if ($totalStudents === 0) {
                $section->present_count = 0;
                $section->absent_count = 0;
                $section->no_data_yet = true;
                continue;
            }

            // Gate IN scans recorded for today
            $scannedEnrollmentIds = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
                ->whereDate('scan_time', $today)
                ->where('scan_type', 'IN')
                ->pluck('enrollment_id')
                ->unique();

            // Teacher verifications recorded for today
            $verifications = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
                ->whereDate('attendance_date', $today)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->keyBy('enrollment_id');

            $presentCount = 0;
            $absentCount = 0;

            foreach ($enrollments as $enrollment) {
                $verification = $verifications->get($enrollment->id);

                if ($verification) {
                    if ($verification->status === AttendanceVerification::STATUS_ABSENT) {
                        $absentCount++;
                    } else {
                        // present, late, excused, not_in_classroom
                        $presentCount++;
                    }
                } elseif ($scannedEnrollmentIds->contains($enrollment->id)) {
                    $presentCount++;
                } else {
                    $absentCount++;
                }
            }

            $section->present_count = $presentCount;
            $section->absent_count = $absentCount;
            $section->no_data_yet = false;
        }

        return view('pov.teacher.attendance.room-attendance-index', compact('sections'));
    }

    /**
     * Section detail page — full class roster.
     *
     * Single-day view: editable, one row per student.
     * Multi-day view: view-only, grouped by date.
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

        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();
        
        $enrollmentsQuery = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->with('student');
            
        if ($activeSchoolYear) {
            $enrollmentsQuery->where('school_year_id', $activeSchoolYear->id);
        }
        
        $enrollments = $enrollmentsQuery->get();

        $enrollmentIds = $enrollments->pluck('id');

        // Automatically expire any 'not_in_classroom' records that passed the 1-hour grace period
        AttendanceVerification::expireNotInClassroomRecords($section->id);

        if ($isSingleDay) {
            $roster = $this->buildSingleDayRoster(
                $enrollments,
                $enrollmentIds,
                $rangeStart,
                $teachingAssignment
            );
        } else {
            $roster = $this->buildMultiDayRoster(
                $enrollments,
                $enrollmentIds,
                $rangeStart,
                $rangeEnd,
                $teachingAssignment
            );
        }

        $totalStudents = $enrollments->count();

        return view(
            'pov.teacher.attendance.room-attendance-show',
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
    public function verify(Request $request, Section $section, Enrollment $enrollment)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $section->id)
            ->where('status', 'active')
            ->first()
            : null;

        abort_unless($teachingAssignment, 403, 'You do not have an active teaching assignment for this section.');

        abort_unless(
            $enrollment->section_id === $section->id,
            403,
            'Student does not belong to this section.'
        );

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(AttendanceVerification::STATUSES)),
            'remarks' => 'nullable|string|max:1000',
            'attendance_date' => 'required|date',
        ]);

        $attendanceDate = Carbon::parse($validated['attendance_date']);

        $log = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', $attendanceDate->toDateString())
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->first();

        $existing = AttendanceVerification::where('enrollment_id', $enrollment->id)
            ->whereDate('attendance_date', $attendanceDate->toDateString())
            ->where('teaching_assignment_id', $teachingAssignment->id)
            ->first();

        $previousStatus = $existing
            ? $existing->status
            : ($log ? AttendanceVerification::STATUS_PRESENT : AttendanceVerification::STATUS_ABSENT);

        $verification = AttendanceVerification::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'teaching_assignment_id' => $teachingAssignment->id,
                'attendance_date' => $attendanceDate->toDateString(),
            ],
            [
                'attendance_log_id' => $log?->id,
                'teacher_id' => $teacher->id,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null,
                'verified_at' => now(),
                'resolved_by' => AttendanceVerification::RESOLVED_BY_TEACHER,
            ]
        );

        // Record audit history tracking
        AttendanceVerificationHistory::create([
            'attendance_verification_id' => $verification->id,
            'previous_status' => $previousStatus,
            'new_status' => $verification->status,
            'changed_by' => $request->user()->id,
            'remarks' => $verification->remarks,
        ]);

        try {
            AttendanceVerificationUpdated::dispatch([
                'section_id' => $section->id,
                'enrollment_id' => $enrollment->id,
                'status' => $verification->status,
                'status_label' => $verification->statusLabel(),
                'remarks' => $verification->remarks,
                'attendance_date' => $attendanceDate->toDateString(),
                'time_in' => $log?->scan_time?->format('h:i A') ?? 'No scan',
            ]);
        } catch (\Throwable $e) {
            // Log realtime broadcast error without interrupting request
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance status updated successfully.',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'status' => $verification->status,
                    'status_label' => $verification->statusLabel(),
                    'remarks' => $verification->remarks,
                    'time_in' => $log?->scan_time?->format('h:i A') ?? 'No scan',
                ],
            ]);
        }

        return redirect()
            ->route(
                'room-attendance.show',
                array_filter([
                    'section' => $section->id,
                    'date_filter' => $request->input('date_filter', 'today'),
                    'custom_start_date' => $request->input('custom_start_date'),
                    'custom_end_date' => $request->input('custom_end_date'),
                ])
            )
            ->with(
                'success',
                'Attendance status updated for ' . $attendanceDate->format('M d, Y') . '.'
            );
    }

    /**
     * Save a teacher's bulk status resolution for multiple students on one date.
     */
    public function bulkVerify(Request $request, Section $section)
    {
        $teacher = $request->user()->teacher;

        $teachingAssignment = $teacher
            ? TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $section->id)
            ->where('status', 'active')
            ->first()
            : null;

        abort_unless($teachingAssignment, 403, 'You do not have an active teaching assignment for this section.');

        $validated = $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'integer|exists:enrollments,id',
            'status' => 'required|in:' . implode(',', array_keys(AttendanceVerification::STATUSES)),
            'remarks' => 'nullable|string|max:1000',
            'attendance_date' => 'required|date',
        ]);

        $attendanceDate = Carbon::parse($validated['attendance_date']);
        $enrollmentIds = $validated['enrollment_ids'];
        $newStatus = $validated['status'];
        $remarks = $validated['remarks'] ?? null;
        $updatedCount = 0;

        $enrollments = Enrollment::whereIn('id', $enrollmentIds)
            ->where('section_id', $section->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            $log = AttendanceLog::where('enrollment_id', $enrollment->id)
                ->whereDate('scan_time', $attendanceDate->toDateString())
                ->where('scan_type', 'IN')
                ->orderBy('scan_time', 'asc')
                ->first();

            $existing = AttendanceVerification::where('enrollment_id', $enrollment->id)
                ->whereDate('attendance_date', $attendanceDate->toDateString())
                ->where('teaching_assignment_id', $teachingAssignment->id)
                ->first();

            $previousStatus = $existing
                ? $existing->status
                : ($log ? AttendanceVerification::STATUS_PRESENT : AttendanceVerification::STATUS_ABSENT);

            $verification = AttendanceVerification::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'teaching_assignment_id' => $teachingAssignment->id,
                    'attendance_date' => $attendanceDate->toDateString(),
                ],
                [
                    'attendance_log_id' => $log?->id,
                    'teacher_id' => $teacher->id,
                    'status' => $newStatus,
                    'remarks' => $remarks,
                    'verified_at' => now(),
                    'resolved_by' => AttendanceVerification::RESOLVED_BY_TEACHER,
                ]
            );

            AttendanceVerificationHistory::create([
                'attendance_verification_id' => $verification->id,
                'previous_status' => $previousStatus,
                'new_status' => $verification->status,
                'changed_by' => $request->user()->id,
                'remarks' => $verification->remarks,
            ]);

            try {
                AttendanceVerificationUpdated::dispatch([
                    'section_id' => $section->id,
                    'enrollment_id' => $enrollment->id,
                    'status' => $verification->status,
                    'status_label' => $verification->statusLabel(),
                    'remarks' => $verification->remarks,
                    'attendance_date' => $attendanceDate->toDateString(),
                    'time_in' => $log?->scan_time?->format('h:i A') ?? 'No scan',
                ]);
            } catch (\Throwable $e) {
                // Log realtime broadcast error without interrupting request
            }

            $updatedCount++;
        }

        $statusLabel = AttendanceVerification::STATUSES[$newStatus] ?? ucfirst($newStatus);

        return redirect()
            ->route(
                'room-attendance.show',
                array_filter([
                    'section' => $section->id,
                    'date_filter' => $request->input('date_filter', 'today'),
                    'custom_start_date' => $request->input('custom_start_date'),
                    'custom_end_date' => $request->input('custom_end_date'),
                ])
            )
            ->with(
                'success',
                "Successfully marked {$updatedCount} student(s) as {$statusLabel} for {$attendanceDate->format('M d, Y')}."
            );
    }

    /**
     * Full chronological history for one student.
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

        $targetDate = $request->query('date');
        $dateCarbon = $targetDate ? Carbon::parse($targetDate) : now();

        // Expire any unverified 'not_in_classroom' records for this date/section
        AttendanceVerification::expireNotInClassroomRecords($section->id, $dateCarbon);

        $rawScan = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', $dateCarbon->toDateString())
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->first();

        $verification = AttendanceVerification::with(['teacher.user', 'histories.changedBy'])
            ->where('enrollment_id', $enrollment->id)
            ->whereDate('attendance_date', $dateCarbon->toDateString())
            ->orderBy('updated_at', 'desc')
            ->first();

        $stepper = [];

        // 1. Origin Step: Raw Gate Time-In (or No Gate Scan baseline)
        if ($rawScan) {
            $stepper[] = [
                'step_index' => 1,
                'stage' => 'raw_gate_in',
                'title' => 'Campus Gate Entry (Raw Log)',
                'time' => $rawScan->scan_time?->format('h:i:s A'),
                'date' => $rawScan->scan_time?->format('M d, Y'),
                'timestamp' => $rawScan->scan_time?->toISOString(),
                'status' => 'present',
                'status_label' => 'Gate Time-In Detected',
                'actor' => 'Campus Turnstile Scanner (' . ucfirst($rawScan->session ?? 'regular') . ' session)',
                'note' => 'Original tamper-proof gate telemetry',
                'is_raw' => true,
                'has_modifications' => (bool) $verification,
            ];
        } else {
            $stepper[] = [
                'step_index' => 1,
                'stage' => 'no_gate_scan',
                'title' => 'Campus Gate Entry (Raw Log)',
                'time' => 'No scan recorded',
                'date' => $dateCarbon->format('M d, Y'),
                'timestamp' => null,
                'status' => 'absent',
                'status_label' => 'No Gate Scan Detected',
                'actor' => 'Campus Turnstile Scanner',
                'note' => 'Initial baseline status: Absent',
                'is_raw' => true,
                'has_modifications' => (bool) $verification,
            ];
        }

        // 2. Connected modifications downward
        if ($verification) {
            $histories = $verification->histories;

            if ($histories && $histories->isNotEmpty()) {
                $totalHistories = $histories->count();
                foreach ($histories as $idx => $h) {
                    $user = $h->changedBy;
                    $userName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : null;
                    $isSystemAction = is_null($h->changed_by);

                    $actor = $isSystemAction
                        ? 'Automated System Action (Grace Period Expiry)'
                        : ('Updated by ' . ($userName ?: 'Teacher'));

                    $title = $isSystemAction
                        ? 'Grace Period Expired (Automated Transition)'
                        : 'Teacher Room Verification';

                    $stage = $isSystemAction
                        ? 'system_auto_transition'
                        : 'verification_change';

                    $stepper[] = [
                        'step_index' => count($stepper) + 1,
                        'stage' => $stage,
                        'title' => $title,
                        'time' => $h->created_at?->format('h:i:s A'),
                        'date' => $h->created_at?->format('M d, Y'),
                        'timestamp' => $h->created_at?->toISOString(),
                        'previous_status' => $h->previous_status,
                        'previous_status_label' => AttendanceVerification::STATUS_LABELS_EXTENDED[$h->previous_status] ?? ucfirst(str_replace('_', ' ', $h->previous_status)),
                        'new_status' => $h->new_status,
                        'new_status_label' => AttendanceVerification::STATUS_LABELS_EXTENDED[$h->new_status] ?? ucfirst(str_replace('_', ' ', $h->new_status)),
                        'status' => $h->new_status,
                        'status_label' => AttendanceVerification::STATUS_LABELS_EXTENDED[$h->new_status] ?? ucfirst(str_replace('_', ' ', $h->new_status)),
                        'actor' => $actor,
                        'remarks' => $h->remarks,
                        'is_current' => ($idx === $totalHistories - 1),
                        'is_raw' => false,
                        'is_system' => $isSystemAction,
                    ];
                }
            } else {
                $teacherUser = $verification->teacher?->user;
                $teacherName = $teacherUser ? trim(($teacherUser->first_name ?? '') . ' ' . ($teacherUser->last_name ?? '')) : 'Teacher';
                $stepper[] = [
                    'step_index' => count($stepper) + 1,
                    'stage' => 'verification_change',
                    'title' => 'Teacher Room Verification',
                    'time' => $verification->updated_at?->format('h:i:s A'),
                    'date' => $verification->updated_at?->format('M d, Y'),
                    'timestamp' => $verification->updated_at?->toISOString(),
                    'previous_status' => $rawScan ? 'present' : 'absent',
                    'previous_status_label' => $rawScan ? 'Present (Gate Scan)' : 'Absent (No Scan)',
                    'new_status' => $verification->status,
                    'new_status_label' => $verification->statusLabel(),
                    'status' => $verification->status,
                    'status_label' => $verification->statusLabel(),
                    'actor' => 'Verified by ' . ($teacherName ?: 'Teacher'),
                    'remarks' => $verification->remarks,
                    'is_current' => true,
                    'is_raw' => false,
                ];
            }
        }

        $scans = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->orderBy('scan_time', 'desc')
            ->limit(50)
            ->get();

        $verifications = AttendanceVerification::with(['teacher.user'])
            ->where('enrollment_id', $enrollment->id)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($v) {
                $teacherUser = $v->teacher?->user;
                $teacherName = $teacherUser
                    ? trim(($teacherUser->first_name ?? '') . ' ' . ($teacherUser->last_name ?? ''))
                    : ($v->resolved_by === 'teacher' ? 'Teacher' : 'System');

                return [
                    'created_at' => $v->created_at?->toISOString() ?? now()->toISOString(),
                    'status' => $v->status,
                    'status_label' => $v->statusLabel(),
                    'remarks' => $v->remarks,
                    'teacher' => [
                        'first_name' => $teacherName,
                        'last_name' => '',
                    ],
                ];
            });

        return response()->json([
            'student' => $enrollment->student->only([
                'first_name',
                'last_name',
                'student_number',
            ]),
            'date' => $dateCarbon->format('Y-m-d'),
            'date_formatted' => $dateCarbon->format('l, F d, Y'),
            'stepper' => $stepper,
            'scans' => $scans,
            'verifications' => $verifications,
        ]);
    }

    /**
     * Build one row per student for a SINGLE-DAY view.
     *
     * Hierarchy of Authority:
     * 1. Future dates -> No Data Yet
     * 2. Teacher Verification -> Teacher's classroom determination always takes precedence
     * 3. Gate QR Scan IN -> Present
     * 4. No scan and no verification -> Absent
     */
    private function buildSingleDayRoster(
        $enrollments,
        $enrollmentIds,
        Carbon $date,
        $teachingAssignment
    ) {
        $dateStr = $date->toDateString();
        $isFuture = $date->isFuture() && ! $date->isToday();

        $logsByEnrollment = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->whereDate('scan_time', $dateStr)
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        $verificationsByEnrollment = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
            ->whereDate('attendance_date', $dateStr)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('enrollment_id')
            ->keyBy('enrollment_id');

        return $enrollments
            ->map(function ($enrollment) use (
                $logsByEnrollment,
                $verificationsByEnrollment,
                $dateStr,
                $isFuture,
                $date
            ) {
                $log = $logsByEnrollment->get($enrollment->id);
                $verification = $verificationsByEnrollment->get($enrollment->id);

                if ($isFuture) {
                    $status = AttendanceVerification::STATUS_NO_DATA;
                    $statusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$status];
                } elseif ($verification) {
                    $status = $verification->status;
                    $statusLabel = $verification->statusLabel();
                } elseif ($log) {
                    $status = AttendanceVerification::STATUS_PRESENT;
                    $statusLabel = AttendanceVerification::STATUSES[$status];
                } else {
                    $status = AttendanceVerification::STATUS_ABSENT;
                    $statusLabel = AttendanceVerification::STATUSES[$status];
                }

                $isGracePeriodActive = false;
                $graceMinutesRemaining = null;

                if ($status === AttendanceVerification::STATUS_NOT_IN_CLASSROOM && $verification) {
                    $verifiedTime = $verification->verified_at ?? $verification->updated_at ?? now();
                    $elapsedMinutes = $verifiedTime->diffInMinutes(now());
                    if ($elapsedMinutes < AttendanceVerification::NOT_IN_CLASSROOM_GRACE_MINUTES && $date->isToday()) {
                        $isGracePeriodActive = true;
                        $graceMinutesRemaining = max(1, AttendanceVerification::NOT_IN_CLASSROOM_GRACE_MINUTES - $elapsedMinutes);
                    }
                }

                return (object) [
                    'enrollment' => $enrollment,
                    'student' => $enrollment->student,
                    'log' => $log,
                    'verification' => $verification,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'is_teacher_edited' => (bool) $verification,
                    'is_grace_period_active' => $isGracePeriodActive,
                    'grace_minutes_remaining' => $graceMinutesRemaining,
                    'date' => $dateStr,
                ];
            })
            ->sortBy(function ($row) {
                // Keep Present and Late at top, Absent below
                return match ($row->status) {
                    AttendanceVerification::STATUS_PRESENT => 1,
                    AttendanceVerification::STATUS_LATE => 2,
                    AttendanceVerification::STATUS_EXCUSED => 3,
                    AttendanceVerification::STATUS_NOT_IN_CLASSROOM => 4,
                    AttendanceVerification::STATUS_ABSENT => 5,
                    default => 6,
                };
            })
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
        $teachingAssignment
    ) {
        $logs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('scan_time', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->where('scan_type', 'IN')
            ->orderBy('scan_time', 'asc')
            ->get()
            ->groupBy(fn($log) => $log->enrollment_id . '_' . $log->scan_time->toDateString())
            ->map(fn($group) => $group->first());

        $verifications = AttendanceVerification::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('attendance_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy(fn($v) => $v->enrollment_id . '_' . $v->attendance_date->toDateString())
            ->map(fn($group) => $group->first());

        $rows = collect();

        foreach ($enrollments as $enrollment) {
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                $dateStr = $cursor->toDateString();
                $key = $enrollment->id . '_' . $dateStr;

                $log = $logs->get($key);
                $verification = $verifications->get($key);
                $isFuture = $cursor->isFuture() && ! $cursor->isToday();

                if ($isFuture) {
                    $status = AttendanceVerification::STATUS_NO_DATA;
                    $statusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$status];
                } elseif ($verification) {
                    $status = $verification->status;
                    $statusLabel = $verification->statusLabel();
                } elseif ($log) {
                    $status = AttendanceVerification::STATUS_PRESENT;
                    $statusLabel = AttendanceVerification::STATUSES[$status];
                } else {
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
                    'date' => $dateStr,
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
}
