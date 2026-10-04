<x-layouts.school-admin>
    <x-slot name="title">Attendance — Grade {{ $grade }} {{ $section->name }}</x-slot>
    <x-slot name="subtitle">Monitor attendance records and verification status by section.</x-slot>
    <x-slot name="pageName">Attendance</x-slot>

    @php
        $filters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This Week',
            'custom' => 'Custom',
        ];
        $currentFilter = $dateFilter ?? request('date_filter', 'today');

        $statusDotMap = [
            'present' => 'success',
            'late' => 'warning',
            'not_in_classroom' => 'warning',
            'absent' => 'danger',
            'excused' => 'info',
            'no_data' => 'secondary',
        ];
    @endphp

    {{-- 1. SELECTED GRADE LEVEL / SECTION & ATTENDANCE INFORMATION --}}
    <div class="bg-white rounded-3 p-3 border shadow-sm mb-3">
        {{-- Primary & Secondary Section Information --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom sa-attendance-context-header">
            <div>
                <h2 class="fw-bold text-dark m-0 fs-5">Grade {{ $grade }} <span class="text-muted fw-normal">/</span> {{ $section->name }}</h2>
                <div class="text-muted small d-flex align-items-center gap-3 flex-wrap">
                    <span>
                        <i class="fas fa-users text-muted me-1"></i>Students:
                        <strong class="text-dark">{{ $totalStudents }} / {{ $section->capacity }}</strong>
                    </span>
                    <span class="text-muted">&bull;</span>
                    <span>
                        <i class="fas fa-chalkboard-user text-muted me-1"></i>Adviser:
                        <strong class="text-dark">{{ $section->advisor?->full_name ?? ($section->advisor?->name ?? 'Not Assigned') }}</strong>
                    </span>
                    <span class="text-muted">&bull;</span>
                    <span class="text-secondary">
                        <i class="fas fa-shield-halved me-1 text-primary"></i>Read-Only Monitoring Mode
                    </span>
                </div>
            </div>

            <div>
                <a href="{{ route('attendance.section', $grade) }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 rounded-3 d-inline-flex align-items-center gap-1.5">
                    <i class="fas fa-arrow-left fa-sm"></i>
                    <span>Back to Sections</span>
                </a>
            </div>
        </div>

        {{-- Attendance Information (Summary Status Cards) --}}
        <div class="sa-attendance-summary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    Attendance Overview &bull; {{ $rangeStart->format('l, F d, Y') }}
                </span>
            </div>
            <div class="row g-2">
                <div class="col-6 col-sm-4 col-md">
                    <div class="sa-attendance-summary-card rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center justify-content-between text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <span>Present</span>
                            <span class="badge-dot dot-success"></span>
                        </div>
                        <div class="mt-1">
                            <span class="sa-summary-value fw-bold text-success">{{ $presentCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <div class="sa-attendance-summary-card rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center justify-content-between text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <span>Late</span>
                            <span class="badge-dot dot-warning"></span>
                        </div>
                        <div class="mt-1">
                            <span class="sa-summary-value fw-bold text-warning">{{ $lateCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <div class="sa-attendance-summary-card rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center justify-content-between text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <span>Absent</span>
                            <span class="badge-dot dot-danger"></span>
                        </div>
                        <div class="mt-1">
                            <span class="sa-summary-value fw-bold text-danger">{{ $absentCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <div class="sa-attendance-summary-card rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center justify-content-between text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <span>Excused</span>
                            <span class="badge-dot dot-secondary"></span>
                        </div>
                        <div class="mt-1">
                            <span class="sa-summary-value fw-bold text-secondary">{{ $excusedCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-md">
                    <div class="sa-attendance-summary-card rounded-3 border bg-light h-100">
                        <div class="d-flex align-items-center justify-content-between text-muted text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                            <span>Teacher Verified</span>
                            <i class="fas fa-chalkboard-user text-primary" style="font-size: 0.75rem;"></i>
                        </div>
                        <div class="mt-1">
                            <span class="sa-summary-value fw-bold text-primary">{{ $teacherVerifiedCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. ATTENDANCE DATE AND SUBJECT CONTAINER --}}
    <div class="bg-white rounded-3 border shadow-sm mb-3 sa-attendance-context">
        <form method="GET" action="{{ route('attendance.section.show', ['grade' => $grade, 'section' => $section->id]) }}">
            <div class="sa-attendance-date-panel">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-primary"></i>
                        <span class="fw-semibold text-dark small">Attendance Date:</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Date Filter">
                            @foreach ($filters as $value => $label)
                                <input type="radio" class="btn-check" name="date_filter" id="sa_date_{{ $value }}" value="{{ $value }}"
                                    @checked($currentFilter === $value) onchange="this.form.submit()">
                                <label class="btn btn-outline-primary btn-sm px-3" for="sa_date_{{ $value }}">{{ $label }}</label>
                            @endforeach
                        </div>

                        @if ($currentFilter === 'custom')
                            <div class="d-flex align-items-center gap-1">
                                <input type="date" name="custom_start_date" class="form-control form-control-sm"
                                    value="{{ $customStartDate ?? request('custom_start_date') }}" max="{{ now()->toDateString() }}" required>
                                <span class="text-muted small">to</span>
                                <input type="date" name="custom_end_date" class="form-control form-control-sm"
                                    value="{{ $customEndDate ?? request('custom_end_date') }}" max="{{ now()->toDateString() }}" required>
                                <button type="submit" class="btn btn-primary btn-sm px-2.5" aria-label="Apply date range">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <span class="text-muted small fw-semibold">Subject:</span>
                    <div class="sa-subject-pills" role="group" aria-label="Subject filter">
                        <input type="radio" class="btn-check" name="subject_id" id="sa_subject_all" value=""
                            @checked(!$selectedSubjectId) onchange="this.form.submit()">
                        <label class="sa-subject-pill {{ !$selectedSubjectId ? 'is-selected' : '' }}" for="sa_subject_all">All subjects</label>
                        @foreach ($subjectAssignments as $assignment)
                            <input type="radio" class="btn-check" name="subject_id" id="sa_subject_{{ $assignment->subject_id }}"
                                value="{{ $assignment->subject_id }}" @checked((int) $selectedSubjectId === (int) $assignment->subject_id)
                                onchange="this.form.submit()">
                            <label class="sa-subject-pill {{ (int) $selectedSubjectId === (int) $assignment->subject_id ? 'is-selected' : '' }}"
                                for="sa_subject_{{ $assignment->subject_id }}">
                                {{ $assignment->subject?->name ?? 'Subject' }}
                            </label>
                        @endforeach
                    </div>
                    @if ($selectedAssignment)
                        <span class="text-muted small ms-md-2">
                            Teacher: <strong class="text-dark">{{ $selectedAssignment->teacher?->full_name ?: 'Not Assigned' }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- 3. STUDENT ATTENDANCE TABLE --}}
    <div class="mb-4 sa-attendance-table-shell">
        <div class="px-3 py-3 border d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white rounded-top-3">
            <div>
                <h3 class="fw-bold text-dark mb-0 fs-6">Student Attendance Records</h3>
                <span class="text-muted" style="font-size: 0.75rem;">All active students in this section</span>
            </div>
            <span class="badge bg-white text-secondary border px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
                {{ $roster->count() }} {{ Str::plural('Student', $roster->count()) }}
            </span>
        </div>

        <x-ui.table>
                <thead class="text-uppercase small">
                    <tr>
                        <th class="sa-student-cell">Student</th>
                        <th class="sa-status-cell">Status</th>
                        <th>Time In</th>
                        <th>Verification</th>
                        <th>Remarks</th>
                        <th class="text-end">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roster as $row)
                        @php
                            $student = $row->student;
                            $firstName = $student?->first_name ?? '';
                            $lastName = $student?->last_name ?? '';
                            $studentName = trim($firstName . ' ' . $lastName) ?: '—';
                            $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '—';
                        @endphp
                        <tr>
                            <td class="table-name-cell sa-student-cell">
                                <div class="table-name-wrap">
                                    <div class="table-name-avatar">{{ $initials }}</div>
                                    <div class="table-name-copy">
                                        <span class="table-name-main">{{ $studentName }}</span>
                                        <span class="table-name-sub">{{ $student?->student_number ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="sa-status-cell">
                                <span class="badge-dot dot-{{ $statusDotMap[$row->status] ?? 'secondary' }}">
                                    {{ $row->status_label }}
                                </span>
                            </td>
                            <td class="sa-time-in-cell">
                                @if ($row->in_log)
                                    <span class="sa-time-in-value">{{ $row->in_log->scan_time?->format('h:i A') }}</span>
                                @else
                                    <span class="sa-time-in-empty">No Scan</span>
                                @endif
                            </td>
                            <td class="sa-verification-cell">
                                <div class="sa-verification-copy">
                                    @if ($row->is_teacher_modified)
                                        <span class="sa-verification-badge {{ $row->status === 'excused' ? 'sa-verification-excused' : ($row->status === 'present' ? 'sa-verification-teacher' : 'sa-verification-danger') }}">
                                            <i class="fas fa-user-check"></i>
                                            {{ $row->status === 'present' ? 'Teacher Verified' : ($row->status === 'excused' ? 'Excused' : 'Not in Classroom') }}
                                        </span>
                                    @elseif ($row->in_log)
                                        <span class="sa-verification-badge sa-verification-classroom">
                                            <i class="fas fa-circle-check"></i>
                                            In Classroom
                                        </span>
                                    @else
                                        <span class="sa-verification-badge sa-verification-none">
                                            <i class="fas fa-circle-minus"></i>
                                            No Scan
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if (count($row->flagged_reasons) > 0)
                                    <div class="sa-remark-list" title="{{ collect($row->flagged_reasons)->pluck('label')->implode(', ') }}">
                                        <span class="sa-remark">{{ $row->flagged_reasons[0]['label'] }}</span>
                                        @if (count($row->flagged_reasons) > 1)
                                            <span class="sa-remark-more">+{{ count($row->flagged_reasons) - 1 }} more</span>
                                        @endif
                                    </div>
                                @elseif ($row->remarks)
                                    <span class="sa-remark" title="{{ $row->remarks }}">{{ $row->remarks }}</span>
                                @else
                                    <span class="text-muted small">No remarks</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm px-2 py-1 rounded-3 d-inline-flex align-items-center gap-1 sa-history-action"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#saAttendanceHistoryDrawer"
                                    data-history-url="{{ route('attendance.section.student-history', ['grade' => $grade, 'section' => $section->id, 'enrollment' => $row->enrollment->id, 'date' => $row->date, 'subject_id' => $selectedSubjectId]) }}"
                                    data-student-name="{{ $studentName }}"
                                    data-student-no="{{ $student?->student_number ?? '—' }}"
                                    data-initials="{{ $initials }}"
                                    data-status="{{ $row->status }}"
                                    data-status-label="{{ $row->status_label }}"
                                    data-status-dot="dot-{{ $statusDotMap[$row->status] ?? 'secondary' }}">
                                    <i class="fas fa-clock-rotate-left fa-sm"></i>
                                    <span>History</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-user-graduate fa-2x mb-2 d-block opacity-50"></i>
                                <span>No active students enrolled in this section.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        </x-ui.table>
    </div>

    {{-- READ-ONLY TEACHER VERIFICATION & AUDIT DRAWER --}}
    <div class="offcanvas offcanvas-end sa-att-history-panel" tabindex="-1" id="saAttendanceHistoryDrawer" aria-labelledby="saAttendanceHistoryDrawerLabel">
        <div class="sa-att-history-header">
            <div class="sa-att-history-heading">
                <div class="sa-att-history-heading-icon">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                </div>
                <div class="sa-att-history-heading-copy">
                    <h5 id="saAttendanceHistoryDrawerLabel">
                        Attendance History
                    </h5>
                    <span>Read-Only Monitoring Audit</span>
                </div>
            </div>
            <button type="button" class="sa-att-history-close" data-bs-dismiss="offcanvas" aria-label="Close">&times;</button>
        </div>

        <div class="sa-att-history-body">
            <div class="sa-att-history-context" id="drawerContext" aria-label="Attendance context">
                <div class="sa-att-history-context-item"><span>Grade Level</span><strong id="drawerGradeLevel">--</strong></div>
                <div class="sa-att-history-context-item"><span>Section</span><strong id="drawerSection">--</strong></div>
                <div class="sa-att-history-context-item"><span>Subject Teacher</span><strong id="drawerSubjectTeacher">--</strong></div>
                <div class="sa-att-history-context-item"><span>Adviser</span><strong id="drawerAdviser">--</strong></div>
            </div>

            {{-- Student Summary Card --}}
            <div class="sa-att-history-student">
                <div class="sa-att-history-student-row">
                    <div class="sa-att-history-student-identity">
                        <div class="sa-att-history-student-avatar" id="drawerStudentAvatar">
                            --
                        </div>
                        <div class="sa-att-history-student-copy">
                            <strong id="drawerStudentName">Loading...</strong>
                            <span id="drawerStudentNo">ID: --</span>
                        </div>
                    </div>
                    <span id="drawerStatusBadge" class="sa-att-history-status">--</span>
                </div>
            </div>

            {{-- Audit Date Header --}}
            <div class="sa-att-history-date">
                <span>Activity Progression</span>
                <time id="drawerDateFormatted">--</time>
            </div>

            {{-- Timeline Stepper Container --}}
            <div id="drawerTimelineContainer">
                <div class="sa-att-history-loading">
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    <span>Loading audit records...</span>
                </div>
            </div>

            <div class="sa-att-history-section" id="drawerRemarksContainer">
                <span class="sa-att-history-section-label">Remarks</span>
                <div class="sa-att-history-remarks" id="drawerRemarks">No remarks</div>
            </div>

            {{-- Attendance Scans Summary Container --}}
            <div class="sa-att-history-section" id="drawerScansContainer" style="display: none;">
                <span class="sa-att-history-section-label">Attendance Scan Records</span>
                <div class="sa-att-history-scans" id="drawerScansList">
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript for History Fetching --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const drawer = document.getElementById('saAttendanceHistoryDrawer');
            if (!drawer) return;

            const nameEl = document.getElementById('drawerStudentName');
            const noEl = document.getElementById('drawerStudentNo');
            const avatarEl = document.getElementById('drawerStudentAvatar');
            const badgeEl = document.getElementById('drawerStatusBadge');
            const dateEl = document.getElementById('drawerDateFormatted');
            const timelineEl = document.getElementById('drawerTimelineContainer');
            const scansBox = document.getElementById('drawerScansContainer');
            const scansList = document.getElementById('drawerScansList');
            const contextEls = {
                gradeLevel: document.getElementById('drawerGradeLevel'),
                section: document.getElementById('drawerSection'),
                subjectTeacher: document.getElementById('drawerSubjectTeacher'),
                adviser: document.getElementById('drawerAdviser'),
            };
            const remarksEl = document.getElementById('drawerRemarks');

            const statusDotMap = {
                present: 'dot-success',
                late: 'dot-warning',
                not_in_classroom: 'dot-warning',
                absent: 'dot-danger',
                excused: 'dot-secondary',
                no_data: 'dot-secondary'
            };

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            drawer.addEventListener('show.bs.offcanvas', function (e) {
                const button = e.relatedTarget;
                if (!button) return;

                const url = button.getAttribute('data-history-url');
                const studentName = button.getAttribute('data-student-name') || 'Student';
                const studentNo = button.getAttribute('data-student-no') || '—';
                const initials = button.getAttribute('data-initials') || '--';
                const statusLabel = button.getAttribute('data-status-label') || '—';
                const statusDot = button.getAttribute('data-status-dot') || 'dot-secondary';

                nameEl.textContent = studentName;
                noEl.textContent = 'ID: ' + studentNo;
                avatarEl.textContent = initials;
                badgeEl.textContent = statusLabel;
                badgeEl.className = 'badge-dot ' + statusDot;

                timelineEl.innerHTML = '<div class="sa-att-history-loading"><i class="fa-solid fa-spinner fa-spin"></i><span>Loading audit records...</span></div>';
                if (scansBox) scansBox.style.display = 'none';

                fetch(url, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        dateEl.textContent = data.date_formatted || '';
                        const context = data.context || {};
                        contextEls.gradeLevel.textContent = context.grade_level || '—';
                        contextEls.section.textContent = context.section || '—';
                        contextEls.subjectTeacher.textContent = context.subject_teacher || '—';
                        contextEls.adviser.textContent = context.adviser || '—';

                        const remarks = (data.stepper || [])
                            .map(step => step.remarks)
                            .filter(Boolean);
                        remarksEl.textContent = remarks.length > 0 ? remarks[remarks.length - 1] : 'No remarks';

                        if (Array.isArray(data.stepper) && data.stepper.length > 0) {
                            let html = '<div class="sa-att-history-timeline">';
                            data.stepper.forEach((step, idx) => {
                                const isRaw = step.is_raw;
                                const isSystem = step.is_system;
                                const isCurrent = step.is_current;

                                html += '<div class="sa-att-history-event">';
                                html += '  <div class="sa-att-history-event-head">';
                                html += '    <strong><i class="fa-solid ' + (isRaw ? 'fa-id-badge' : (isSystem ? 'fa-robot' : 'fa-user-check')) + '"></i>' + escapeHtml(step.title) + '</strong>';
                                html += '    <time>' + escapeHtml(step.time || '—') + '</time>';
                                html += '  </div>';

                                if (isRaw) {
                                    html += '  <div class="sa-att-history-event-states">';
                                    html += '    <span class="sa-att-history-state ' + (statusDotMap[step.status] || 'dot-secondary') + '">' + escapeHtml(step.status_label) + '</span>';
                                    html += '    <span class="sa-att-history-initial">Initial Record</span>';
                                    html += '  </div>';
                                    html += '  <div class="sa-att-history-actor"><i class="fa-solid fa-id-card"></i>' + escapeHtml(step.actor) + '</div>';
                                } else {
                                    html += '  <div class="sa-att-history-event-states">';
                                    html += '    <span class="sa-att-history-state ' + (statusDotMap[step.previous_status] || 'dot-secondary') + '">' + escapeHtml(step.previous_status_label) + '</span>';
                                    html += '    <i class="fa-solid fa-arrow-right sa-att-history-arrow"></i>';
                                    html += '    <span class="sa-att-history-state ' + (statusDotMap[step.new_status] || 'dot-secondary') + '">' + escapeHtml(step.new_status_label) + '</span>';
                                    if (isCurrent) {
                                        html += '    <span class="sa-att-history-current">Current Active</span>';
                                    }
                                    html += '  </div>';
                                    html += '  <div class="sa-att-history-actor"><i class="fa-solid fa-user-pen"></i>' + escapeHtml(step.actor) + '</div>';
                                    if (step.remarks) {
                                        html += '  <div class="sa-att-history-event-remark"><strong>Note:</strong> ' + escapeHtml(step.remarks) + '</div>';
                                    }
                                }

                                html += '</div>';
                            });
                            html += '</div>';
                            timelineEl.innerHTML = html;
                        } else {
                            timelineEl.innerHTML = '<div class="sa-att-history-empty"><i class="fa-regular fa-calendar-xmark"></i><span>No changes recorded for this date.</span></div>';
                        }

                        // Scans
                        if (Array.isArray(data.scans) && data.scans.length > 0 && scansBox && scansList) {
                            scansBox.style.display = 'block';
                            let sHtml = '';
                            data.scans.forEach(s => {
                                const isIN = s.scan_type === 'IN';
                                sHtml += '<div class="sa-att-history-scan">';
                                sHtml += '  <span class="sa-att-history-state dot-' + (isIN ? 'success' : 'primary') + '">' + (isIN ? 'Time In' : 'Time Out') + '</span>';
                                sHtml += '  <span class="sa-att-history-scan-session">' + escapeHtml(s.session) + ' Session</span>';
                                sHtml += '  <time>' + escapeHtml(s.scan_time) + '</time>';
                                sHtml += '</div>';
                            });
                            scansList.innerHTML = sHtml;
                        }
                    })
                    .catch(err => {
                        console.error('Audit load error:', err);
                        timelineEl.innerHTML = '<div class="sa-att-history-error"><i class="fa-solid fa-circle-exclamation"></i><span>Failed to load audit history.</span></div>';
                    });
            });
        });
    </script>
</x-layouts.school-admin>
