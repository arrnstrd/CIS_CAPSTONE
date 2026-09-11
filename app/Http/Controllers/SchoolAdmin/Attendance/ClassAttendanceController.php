<?php

namespace App\Http\Controllers\SchoolAdmin\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\AttendanceVerificationHistory;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\TeachingAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassAttendanceController extends Controller
{
    public function index()
    {
        return $this->gradeLevel();
    }

    public function gradeLevel()
    {
        $grades = [
            'Elementary' => range(1, 6),
            'Junior High School' => range(7, 10),
            'Senior High School' => range(11, 12),
        ];

        $activeSchoolYear = SchoolYear::active()->first();

        $gradeCounts = Enrollment::query()
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->selectRaw('grade_level, COUNT(DISTINCT student_id) as total')
            ->groupBy('grade_level')
            ->pluck('total', 'grade_level');

        return view('pov.school-admin.attendance.grade-level', compact('grades', 'gradeCounts'));
    }

    public function section($grade)
    {
        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::active()->first();

        $sections = Section::query()
            ->where('grade_level', $grade)
            ->with('advisor.user')
            ->withCount(['enrollments as student_count' => function ($q) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
            }])
            ->orderBy('name')
            ->get();

        return view('pov.school-admin.attendance.section-selection', compact('grade', 'sections'));
    }

    /**
     * Display the read-only attendance monitoring view for a selected section.
     */
    public function show(Request $request, $grade, Section $section)
    {
        $grade = (int) $grade;
        if ((int) $section->grade_level !== $grade) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::active()->first();

        // Expire any unverified 'not_in_classroom' records that passed grace period
        AttendanceVerification::expireNotInClassroomRecords($section->id);

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');
        $selectedSubjectId = $request->integer('subject_id') ?: null;

        $today = Carbon::today();
        [$rangeStart, $rangeEnd] = match ($dateFilter) {
            'yesterday' => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay()],
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'custom' => [
                $customStartDate ? Carbon::parse($customStartDate)->startOfDay() : $today->copy()->startOfDay(),
                $customEndDate ? Carbon::parse($customEndDate)->endOfDay() : $today->copy()->endOfDay(),
            ],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
        };

        $isSingleDay = $rangeStart->isSameDay($rangeEnd);

        $enrollments = Enrollment::where('section_id', $section->id)
            ->where('status', 'active')
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->with('student')
            ->get()
            ->sortBy(fn($e) => ($e->student?->last_name ?? '') . ' ' . ($e->student?->first_name ?? ''))
            ->values();

        $enrollmentIds = $enrollments->pluck('id');

        $subjectAssignments = TeachingAssignment::with(['subject', 'teacher.user'])
            ->where('section_id', $section->id)
            ->where('status', 'active')
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->orderBy('subject_id')
            ->get()
            ->unique('subject_id')
            ->values();

        $selectedAssignment = $subjectAssignments->firstWhere('subject_id', $selectedSubjectId);
        $selectedSubjectId = $selectedAssignment?->subject_id;

        // Fetch gate logs
        $logs = AttendanceLog::whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('scan_time', [$rangeStart, $rangeEnd])
            ->with('flagged_scans')
            ->orderBy('scan_time', 'asc')
            ->get();

        $inLogsByEnrollment = $logs->where('scan_type', 'IN')->groupBy('enrollment_id');
        $outLogsByEnrollment = $logs->where('scan_type', 'OUT')->groupBy('enrollment_id');

        // Fetch verifications
        $verifications = AttendanceVerification::with(['teacher.user', 'histories.changedBy'])
            ->whereIn('enrollment_id', $enrollmentIds)
            ->whereBetween('attendance_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when($selectedAssignment, fn($q) => $q->where('teaching_assignment_id', $selectedAssignment->id))
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('enrollment_id');

        $isFuture = $rangeStart->isFuture() && ! $rangeStart->isToday();

        $flagReasonLabels = [
            'late_arrival' => 'Late Arrival',
            'duplicate_scan' => 'Duplicate Scan',
            'invalid_checkout' => 'Invalid Checkout',
            'missing_in' => 'Missing IN',
            'missing_out' => 'Missing OUT',
            'excess_scan' => 'Excess Scan',
            'invalid_qr' => 'Invalid QR',
            'early_out' => 'Early Departure',
            'early_timeout' => 'Early Timeout',
            'too_early' => 'Early Scan',
            'invalid_session' => 'Invalid Session',
        ];

        $roster = $enrollments->map(function ($enrollment) use (
            $inLogsByEnrollment,
            $outLogsByEnrollment,
            $verifications,
            $isFuture,
            $rangeStart,
            $flagReasonLabels
        ) {
            $inLog = $inLogsByEnrollment->get($enrollment->id)?->first();
            $outLog = $outLogsByEnrollment->get($enrollment->id)?->last();
            $verification = $verifications->get($enrollment->id)?->first();

            if ($isFuture) {
                $status = AttendanceVerification::STATUS_NO_DATA;
                $statusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$status];
            } elseif ($verification) {
                $status = $verification->status;
                $statusLabel = $verification->statusLabel();
            } elseif ($inLog) {
                $hasLateFlag = $inLog->flagged_scans->contains('flag_type', 'late_arrival');
                $status = $hasLateFlag ? AttendanceVerification::STATUS_LATE : AttendanceVerification::STATUS_PRESENT;
                $statusLabel = AttendanceVerification::STATUSES[$status] ?? ucfirst($status);
            } else {
                $status = AttendanceVerification::STATUS_ABSENT;
                $statusLabel = AttendanceVerification::STATUSES[$status] ?? 'Absent';
            }

            $teacherName = null;
            if ($verification) {
                $teacherUser = $verification->teacher?->user;
                $teacherName = $teacherUser
                    ? trim(($teacherUser->first_name ?? '') . ' ' . ($teacherUser->last_name ?? ''))
                    : ($verification->resolved_by === 'teacher' ? 'Teacher' : 'System');
            }

            $isTeacherModified = false;
            $previousStatus = null;
            $previousStatusLabel = null;
            $changedAt = null;

            if ($verification) {
                $isTeacherModified = $verification->resolved_by === AttendanceVerification::RESOLVED_BY_TEACHER;
                $latestHistory = $verification->histories?->sortByDesc('id')->first();
                if ($latestHistory) {
                    $previousStatus = $latestHistory->previous_status;
                    $previousStatusLabel = AttendanceVerification::STATUS_LABELS_EXTENDED[$previousStatus] ?? ucfirst(str_replace('_', ' ', $previousStatus));
                    $changedAt = $latestHistory->created_at;
                } else {
                    $previousStatus = $inLog ? 'present' : 'absent';
                    $previousStatusLabel = $inLog ? 'Present' : 'Absent';
                    $changedAt = $verification->updated_at;
                }
            }

            $flaggedReasons = collect([$inLog, $outLog])
                ->filter()
                ->flatMap(fn($log) => $log->flagged_scans->map(function ($flag) use ($flagReasonLabels) {
                    $label = $flagReasonLabels[$flag->flag_type]
                        ?? ucwords(str_replace('_', ' ', (string) $flag->flag_type));

                    return [
                        'label' => $label ?: 'Needs Review',
                        'description' => $flag->description,
                    ];
                }))
                ->unique('label')
                ->values()
                ->all();

            return (object) [
                'enrollment' => $enrollment,
                'student' => $enrollment->student,
                'in_log' => $inLog,
                'out_log' => $outLog,
                'verification' => $verification,
                'status' => $status,
                'status_label' => $statusLabel,
                'is_teacher_modified' => $isTeacherModified,
                'teacher_name' => $teacherName,
                'previous_status' => $previousStatus,
                'previous_status_label' => $previousStatusLabel,
                'changed_at' => $changedAt,
                'remarks' => $verification?->remarks ?? null,
                'flagged_reasons' => $flaggedReasons,
                'date' => $rangeStart->toDateString(),
            ];
        });

        // Summary counts
        $totalStudents = $enrollments->count();
        $presentCount = $roster->where('status', AttendanceVerification::STATUS_PRESENT)->count();
        $lateCount = $roster->where('status', AttendanceVerification::STATUS_LATE)->count();
        $absentCount = $roster->where('status', AttendanceVerification::STATUS_ABSENT)->count();
        $excusedCount = $roster->where('status', AttendanceVerification::STATUS_EXCUSED)->count();
        $teacherVerifiedCount = $roster->where('is_teacher_modified', true)->count();

        $section->load('advisor.user');

        return view('pov.school-admin.attendance.show', compact(
            'grade',
            'section',
            'roster',
            'totalStudents',
            'presentCount',
            'lateCount',
            'absentCount',
            'excusedCount',
            'teacherVerifiedCount',
            'dateFilter',
            'customStartDate',
            'customEndDate',
            'subjectAssignments',
            'selectedSubjectId',
            'selectedAssignment',
            'rangeStart',
            'rangeEnd',
            'isSingleDay'
        ));
    }

    /**
     * Fetch read-only student attendance and teacher verification history.
     */
    public function studentHistory(Request $request, $grade, Section $section, Enrollment $enrollment)
    {
        $grade = (int) $grade;
        abort_unless((int) $section->grade_level === $grade, 404);
        abort_unless((int) $enrollment->section_id === (int) $section->id, 404);

        $targetDate = $request->query('date');
        $dateCarbon = $targetDate ? Carbon::parse($targetDate) : now();
        $selectedSubjectId = $request->integer('subject_id') ?: null;
        $section->load('advisor.user');

        $subjectAssignment = TeachingAssignment::with('teacher.user')
            ->where('section_id', $section->id)
            ->where('subject_id', $selectedSubjectId)
            ->where('school_year_id', $enrollment->school_year_id)
            ->where('status', 'active')
            ->first();

        $rawScans = AttendanceLog::where('enrollment_id', $enrollment->id)
            ->whereDate('scan_time', $dateCarbon->toDateString())
            ->orderBy('scan_time', 'asc')
            ->get();

        $rawInScan = $rawScans->firstWhere('scan_type', 'IN');

        $verification = AttendanceVerification::with(['teacher.user', 'teachingAssignment.subject', 'teachingAssignment.teacher.user', 'histories.changedBy'])
            ->where('enrollment_id', $enrollment->id)
            ->whereDate('attendance_date', $dateCarbon->toDateString())
            ->when($selectedSubjectId, fn($q) => $q->whereHas('teachingAssignment', function ($assignmentQuery) use ($selectedSubjectId, $section, $enrollment) {
                $assignmentQuery
                    ->where('subject_id', $selectedSubjectId)
                    ->where('section_id', $section->id)
                    ->where('school_year_id', $enrollment->school_year_id)
                    ->where('status', 'active');
            }))
            ->orderBy('updated_at', 'desc')
            ->first();

        $stepper = [];

        // 1. Origin Step: Initial Scan Record
        if ($rawInScan) {
            $stepper[] = [
                'step_index' => 1,
                'stage' => 'initial_scan',
                'title' => 'Initial Scan Record',
                'time' => $rawInScan->scan_time?->format('h:i:s A'),
                'date' => $rawInScan->scan_time?->format('M d, Y'),
                'status' => 'present',
                'status_label' => 'Time-In Recorded',
                'actor' => 'Attendance Scanner (' . ucfirst($rawInScan->session ?? 'regular') . ' session)',
                'note' => 'Original tamper-proof scan record',
                'is_raw' => true,
                'has_modifications' => (bool) $verification,
            ];
        } else {
            $stepper[] = [
                'step_index' => 1,
                'stage' => 'no_scan',
                'title' => 'Attendance Baseline',
                'time' => 'No scan recorded',
                'date' => $dateCarbon->format('M d, Y'),
                'status' => 'absent',
                'status_label' => 'No Scan Recorded',
                'actor' => 'Attendance Scanner',
                'note' => 'Initial baseline status: Absent',
                'is_raw' => true,
                'has_modifications' => (bool) $verification,
            ];
        }

        // 2. Verification history
        if ($verification) {
            $histories = $verification->histories;
            if ($histories && $histories->isNotEmpty()) {
                $totalHistories = $histories->count();
                foreach ($histories as $idx => $h) {
                    $user = $h->changedBy;
                    $userName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : null;
                    $isSystemAction = is_null($h->changed_by);

                    $actor = $isSystemAction
                        ? 'Automated System Action'
                        : ('Updated by ' . ($userName ?: 'Teacher'));

                    $stepper[] = [
                        'step_index' => count($stepper) + 1,
                        'stage' => $isSystemAction ? 'system_auto_transition' : 'verification_change',
                        'title' => $isSystemAction ? 'Grace Period Expired (Automated Transition)' : 'Teacher Room Verification',
                        'time' => $h->created_at?->format('h:i:s A'),
                        'date' => $h->created_at?->format('M d, Y'),
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
                    'previous_status' => $rawInScan ? 'present' : 'absent',
                    'previous_status_label' => $rawInScan ? 'Present (Initial Scan)' : 'Absent (No Scan)',
                    'new_status' => $verification->status,
                    'new_status_label' => $verification->statusLabel(),
                    'status' => $verification->status,
                    'status_label' => $verification->statusLabel(),
                    'actor' => 'Verified by ' . ($teacherName ?: 'Teacher'),
                    'remarks' => $verification->remarks,
                    'is_current' => true,
                    'is_raw' => false,
                    'is_system' => false,
                ];
            }
        }

        $formattedScans = $rawScans->map(function ($scan) {
            return [
                'scan_type' => $scan->scan_type,
                'scan_time' => $scan->scan_time?->format('h:i:s A'),
                'session' => ucfirst($scan->session ?? 'regular'),
            ];
        });

        return response()->json([
            'student' => [
                'name' => trim(($enrollment->student?->first_name ?? '') . ' ' . ($enrollment->student?->last_name ?? '')) ?: 'Student',
                'student_number' => $enrollment->student?->student_number ?? '—',
            ],
            'context' => [
                'grade_level' => 'Grade ' . $section->grade_level,
                'section' => $section->name,
                'subject_teacher' => $verification?->teachingAssignment?->teacher?->full_name
                    ?? $subjectAssignment?->teacher?->full_name
                    ?? ($selectedSubjectId ? 'Not Assigned' : 'All Subjects'),
                'adviser' => $section->advisor?->full_name ?? ($section->advisor?->name ?? 'Not Assigned'),
            ],
            'subject_id' => $selectedSubjectId,
            'date_formatted' => $dateCarbon->format('l, F d, Y'),
            'stepper' => $stepper,
            'scans' => $formattedScans,
        ]);
    }
}
