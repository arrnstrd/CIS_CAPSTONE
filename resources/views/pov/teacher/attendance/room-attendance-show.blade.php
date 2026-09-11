<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-clipboard-check"></i>
            Grade {{ $section->grade_level }} - {{ $section->name }}
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">
            <a href="{{ route('room-attendance.index') }}" class="ra-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Room Attendance
            </a>
        </span>
    </x-slot>

    @php
        $filters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This Week',
            'custom' => 'Custom',
        ];
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $presentCount = $roster->whereNotIn('status', [
        \App\Models\AttendanceVerification::STATUS_ABSENT,
        \App\Models\AttendanceVerification::STATUS_NO_DATA,
    ])->count();
    $absentCount = $roster->where('status', \App\Models\AttendanceVerification::STATUS_ABSENT)->count();
        
        $statusDotMap = [
            'present' => 'success',
            'late' => 'warning',
            'not_in_classroom' => 'warning',
            'absent' => 'danger',
            'excused' => 'info',
            'no_data' => 'secondary',
        ];
    @endphp

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary stat cards -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="ra-stat-card d-flex justify-content-between align-items-start">
                <div>
                    <p class="ra-filter-label mb-1">Total students</p>
                    <p class="ra-stat-value">{{ $totalStudents }} <span class="ra-stat-suffix">enrolled in section</span></p>
                </div>
                <span class="ra-stat-icon ra-stat-icon-neutral">
                    <i class="fa-solid fa-users"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="ra-stat-card d-flex justify-content-between align-items-start">
                <div>
                    <p class="ra-filter-label mb-1">Present</p>
                    <p class="ra-stat-value ra-stat-present">{{ $presentCount }} <span class="ra-stat-suffix">verified in class</span></p>
                </div>
                <span class="ra-stat-icon ra-stat-icon-present">
                    <i class="fa-solid fa-user-check"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="ra-stat-card d-flex justify-content-between align-items-start">
                <div>
                    <p class="ra-filter-label mb-1">Absent</p>
                    <p class="ra-stat-value ra-stat-absent">{{ $absentCount }} <span class="ra-stat-suffix">not in class today</span></p>
                </div>
                <span class="ra-stat-icon ra-stat-icon-absent">
                    <i class="fa-solid fa-user-xmark"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="ra-filter-bar mb-4">
        <form method="GET" action="{{ route('room-attendance.show', $section) }}">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="ra-filter-chip">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <p class="ra-filter-label mb-0">Attendance history filter</p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @foreach ($filters as $value => $label)
                    <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}" value="{{ $value }}"
                        @checked($currentFilter === $value) onchange="this.form.submit()">
                    <label class="ra-pill-btn" for="date_{{ $value }}">{{ $label }}</label>
                @endforeach

                @if ($currentFilter === 'custom')
                    <input type="date" name="custom_start_date" class="ra-range-input"
                        value="{{ $customStartDate ?? request('custom_start_date') }}" max="{{ now()->toDateString() }}" required>
                    <span class="text-muted small">to</span>
                    <input type="date" name="custom_end_date" class="ra-range-input"
                        value="{{ $customEndDate ?? request('custom_end_date') }}" max="{{ now()->toDateString() }}" required>
                    <button type="submit" class="btn-view-history ra-btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i> Apply
                    </button>
                @endif
            </div>
        </form>
    </div>

    @if (! $isSingleDay)
        <div class="alert alert-warning py-2 px-3 small mb-3">
            <i class="fa-solid fa-circle-info me-1"></i>
            Viewing multiple days — this view is read-only. Switch to "Today", "Yesterday", or a single-day Custom range to edit a student's status.
        </div>
    @endif

    @if ($isSingleDay)
        <!-- Inline Spacious Bulk Operations Panel (Prominently placed in the middle above roster) -->
        <div id="bulkActionBar" class="ra-bulk-panel-wrapper" aria-hidden="true">
            <div class="ra-bulk-panel-inner">
                <div class="ra-bulk-panel">
                    <div class="ra-bulk-panel-header">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="ra-bulk-count-badge">
                                <i class="fa-solid fa-users"></i>
                                <span id="bulkSelectedCount">0</span> Students Selected
                            </span>
                            <span class="text-muted small">Choose one single status below to apply simultaneously</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-0" id="btnClearBulk">
                            <i class="fa-solid fa-xmark me-1"></i>Clear selection
                        </button>
                    </div>

                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-xl-7">
                            <label class="ra-filter-label mb-2 d-block">Select Single Target Status:</label>
                            <div class="ra-bulk-status-group">
                                @foreach (App\Models\AttendanceVerification::STATUSES as $value => $label)
                                    @php
                                        $dotClass = match($value) {
                                            'present' => 'dot-success',
                                            'late', 'not_in_classroom' => 'dot-warning',
                                            'absent' => 'dot-danger',
                                            'excused' => 'dot-secondary',
                                            default => 'dot-secondary'
                                        };
                                        $iconClass = match($value) {
                                            'present' => 'fa-check',
                                            'late' => 'fa-clock',
                                            'absent' => 'fa-xmark',
                                            'excused' => 'fa-user-shield',
                                            'not_in_classroom' => 'fa-person-walking-arrow-right',
                                            default => 'fa-circle'
                                        };
                                    @endphp
                                    <label class="ra-bulk-status-pill {{ $value === 'present' ? 'active' : '' }}" for="bulk_status_{{ $value }}">
                                        <input type="radio" name="bulk_status_choice" value="{{ $value }}" id="bulk_status_{{ $value }}" {{ $value === 'present' ? 'checked' : '' }}>
                                        <span class="badge-dot {{ $dotClass }} py-1 px-2 d-inline-flex align-items-center gap-1">
                                            <i class="fa-solid {{ $iconClass }} small"></i>
                                            {{ $label }}
                                            @if ($value === 'not_in_classroom')
                                                <small class="text-muted fw-normal" style="font-size: 0.68rem;">(1h grace)</small>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 col-md-8 col-xl-3">
                            <label for="bulkRemarksInput" class="ra-filter-label mb-2 d-block">Remarks for all (optional):</label>
                            <input type="text" id="bulkRemarksInput" class="form-control form-control-sm" placeholder="e.g. Excused event, clinic...">
                        </div>

                        <div class="col-12 col-md-4 col-xl-2 text-end align-self-end">
                            <button type="button" class="btn btn-primary w-100 py-2 fw-bold" id="btnOpenBulkModal" data-bs-toggle="modal" data-bs-target="#bulkConfirmModal" disabled>
                                <i class="fa-solid fa-check-double me-1"></i> Apply Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <p class="ra-filter-label mb-2">Full class roster &mdash; every enrolled student appears, scanned or not</p>

    <x-ui.table>
        <thead>
            <tr>
                @if ($isSingleDay)
                    <th class="ra-checkbox-cell">
                        <input type="checkbox" id="selectAllStudents" class="form-check-input" title="Select all students">
                    </th>
                @endif
                <th>Student</th>
                <th>Time In</th>
                <th>Scan Type</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                $statusDotMap = [
                    'present' => 'success',
                    'late' => 'warning',
                    'not_in_classroom' => 'warning',
                    'absent' => 'danger',
                    'excused' => 'info',
                ];
            @endphp

            @if ($isSingleDay)
                @forelse ($roster as $row)
                    @php
                        $student = $row->student;
                        $firstName = $student?->first_name ?? '';
                        $lastName = $student?->last_name ?? '';
                        $studentName = trim($firstName . ' ' . $lastName) ?: '—';
                        $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '—';
                    @endphp
                    <tr data-enrollment-row="{{ $row->enrollment->id }}" @class(['table-warning-subtle' => $row->status !== 'present'])>
                        <td class="ra-checkbox-cell">
                            <input type="checkbox" 
                                class="form-check-input ra-student-checkbox" 
                                value="{{ $row->enrollment->id }}"
                                data-student-name="{{ $studentName }}"
                                data-student-no="{{ $student?->student_number ?? '—' }}"
                                data-current-status="{{ $row->status }}"
                                data-current-status-label="{{ $row->status_label }}">
                        </td>
                        <td class="table-name-cell">
                            <div class="table-name-wrap">
                                <div class="table-name-avatar">{{ $initials }}</div>
                                <div class="table-name-copy">
                                    <span class="table-name-main">{{ $studentName }}</span>
                                    <span class="table-name-sub">{{ $student?->student_number ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($row->log)
                                {{ $row->log?->scan_time?->format('h:i A') ?? '—' }}
                            @else
                                <span class="text-muted">No scan</span>
                            @endif
                        </td>
                        <td>
                            @if ($row->log)
                                <span class="badge-dot dot-success">{{ $row->log?->scan_type ?? '—' }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-dot dot-{{ $statusDotMap[$row->status] ?? 'secondary' }}">{{ $row->status_label }}</span>
                        </td>
                        <td>
                            <button type="button" class="btn-view-history ra-btn-sm"
                                data-enrollment-id="{{ $row->enrollment->id }}"
                                data-date="{{ $row->date }}"
                                data-student-name="{{ $studentName }}"
                                data-student-no="{{ $student?->student_number ?? '—' }}"
                                data-student-initials="{{ $initials }}"
                                data-time-in="{{ $row->log?->scan_time?->format('h:i A') ?? 'No scan' }}"
                                data-has-gate-scan="{{ $row->log ? '1' : '0' }}"
                                data-gate-exact-time="{{ $row->log?->scan_time?->format('h:i:s A') ?? '' }}"
                                data-gate-scan-type="{{ $row->log?->scan_type ?? '' }}"
                                data-gate-session="{{ $row->log?->session ?? '' }}"
                                data-current-status="{{ $row->status }}"
                                data-current-status-label="{{ $row->status_label }}"
                                data-current-status-dot="dot-{{ $statusDotMap[$row->status] ?? 'secondary' }}"
                                data-remarks="{{ $row->verification?->remarks ?? '' }}"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#attendanceSidePanel">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No active students enrolled in this section.
                        </td>
                    </tr>
                @endforelse
            @else
                @forelse ($roster as $date => $dayRows)
                    <tr>
                        <td colspan="5" class="fw-bold" style="background: rgba(36,56,185,0.06);">
                            {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                        </td>
                    </tr>
                    @foreach ($dayRows as $row)
                        @php
                            $student = $row->student;
                            $firstName = $student?->first_name ?? '';
                            $lastName = $student?->last_name ?? '';
                            $studentName = trim($firstName . ' ' . $lastName) ?: '—';
                            $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '—';
                        @endphp
                        <tr @class(['table-warning-subtle' => $row->status !== 'present'])>
                            <td class="table-name-cell">
                                <div class="table-name-wrap">
                                    <div class="table-name-avatar">{{ $initials }}</div>
                                    <div class="table-name-copy">
                                        <span class="table-name-main">{{ $studentName }}</span>
                                        <span class="table-name-sub">{{ $student?->student_number ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($row->log)
                                    {{ $row->log?->scan_time?->format('h:i A') ?? '—' }}
                                @else
                                    <span class="text-muted">No scan</span>
                                @endif
                            </td>
                            <td>
                                @if ($row->log)
                                    <span class="badge-dot dot-success">{{ $row->log?->scan_type ?? '—' }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-dot dot-{{ $statusDotMap[$row->status] ?? 'secondary' }}">{{ $row->status_label }}</span>
                            </td>
                            <td class="text-muted">&mdash;</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No active students enrolled in this section.
                        </td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </x-ui.table>

    @if ($isSingleDay)
        <!-- Bulk Attendance Confirmation Modal -->
        <div class="modal fade" id="bulkConfirmModal" tabindex="-1" aria-labelledby="bulkConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <form method="POST" id="bulkVerifyForm" action="{{ route('room-attendance.bulk-verify', $section->id) }}">
                        @csrf
                        <input type="hidden" name="attendance_date" value="{{ $rangeStart->toDateString() }}">
                        <input type="hidden" name="date_filter" value="{{ $currentFilter }}">
                        <input type="hidden" name="custom_start_date" value="{{ $customStartDate }}">
                        <input type="hidden" name="custom_end_date" value="{{ $customEndDate }}">
                        <input type="hidden" name="status" id="modalFormStatus">
                        <input type="hidden" name="remarks" id="modalFormRemarks">
                        <div id="modalFormEnrollmentIds"></div>

                        <div class="modal-header bg-light border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="ra-confirm-icon-box">
                                    <i class="fa-solid fa-users-gear"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="bulkConfirmModalLabel">
                                        Confirm Bulk Attendance Verification
                                    </h5>
                                    <div class="text-muted small">
                                        {{ $section->name }} &bull; {{ $rangeStart->format('l, F d, Y') }}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="ra-confirm-callout mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="ra-confirm-callout-label">Target Status to Apply:</span>
                                    <span id="modalStatusBadge" class="badge-dot dot-success fs-6 py-1 px-3">Present</span>
                                </div>
                                <p class="text-muted small mb-0">
                                    This single status will be assigned to all <strong><span id="modalSelectedCountText">0</span> selected student(s)</strong> simultaneously.
                                </p>
                            </div>

                            <div class="mb-3" id="modalRemarksRow" style="display: none;">
                                <label class="ra-filter-label mb-1">Remarks for Selected:</label>
                                <div class="ra-modal-remarks-box" id="modalRemarksText"></div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="ra-filter-label mb-0">
                                        Affected Students (<span id="modalListCount">0</span>):
                                    </label>
                                    <span class="text-muted" style="font-size: 0.72rem;">Scroll to inspect</span>
                                </div>
                                <div class="ra-modal-chips-container" id="modalStudentListContainer">
                                    <!-- Dynamic list of student chips -->
                                </div>
                            </div>

                            <div class="ra-modal-alert">
                                <i class="fa-solid fa-shield-halved text-primary mt-1 fs-6"></i>
                                <div>
                                    <strong>Protected Verification:</strong>
                                    All <span id="modalWarnCount">0</span> students will be verified with this status. Original Time In and Time Out scan records remain untouched and tamper-proof.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-top">
                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btnSubmitBulkVerify">
                                <i class="fa-solid fa-check-double me-1"></i> Confirm & Apply Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance Verification & Protected History Offcanvas Side Panel -->
        <div class="offcanvas offcanvas-end ra-side-panel" tabindex="-1" id="attendanceSidePanel" aria-labelledby="attendanceSidePanelLabel"
            data-section-id="{{ $section->id }}"
            data-base-url="{{ url('/teacher/room-attendance/' . $section->id) }}">
            <div class="offcanvas-header ra-panel-header">
                <h5 class="offcanvas-title ra-panel-title" id="attendanceSidePanelLabel">
                    <i class="fa-solid fa-user-check text-primary"></i>
                    <span>Attendance Verification</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body ra-panel-body">
                <!-- Student Header Strip -->
                <div class="ra-student-strip">
                    <div class="ra-strip-avatar" id="panelStudentAvatar">--</div>
                    <div class="ra-strip-info">
                        <div class="ra-strip-name" id="panelStudentName">Loading student...</div>
                        <div class="ra-strip-sub">
                            <span id="panelStudentNo"><i class="fa-regular fa-id-badge me-1"></i>--</span>
                            <span>&bull;</span>
                            <span>Campus Time In: <strong id="panelTimeIn">--</strong></span>
                        </div>
                    </div>
                    <div>
                        <span id="panelCurrentStatusBadge" class="badge-dot dot-secondary">--</span>
                    </div>
                </div>

                <!-- Navigation Tabs: Verify Status vs Attendance History -->
                <div class="ra-panel-nav">
                    <button type="button" class="ra-panel-nav-btn active" id="tabBtnVerify">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Verify Status</span>
                    </button>
                    <button type="button" class="ra-panel-nav-btn" id="tabBtnTimeline">
                        <i class="fa-solid fa-timeline"></i>
                        <span>Attendance History</span>
                        <span class="ra-panel-badge-count ms-1" id="panelEventCount">0</span>
                    </button>
                </div>

                <!-- Protected Raw Gate Entry Preview (Tamper-Proof) -->
                <div class="ra-gate-preview-card" id="panelGatePreviewCard">
                    <div class="ra-gate-preview-left">
                        <div class="ra-gate-preview-icon" id="panelGatePreviewIcon">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <div class="ra-gate-preview-title">Campus Time In</div>
                            <div class="ra-gate-preview-time" id="panelGatePreviewTime">--</div>
                            <div class="ra-gate-preview-sub" id="panelGatePreviewSub">
                                <i class="fa-solid fa-qrcode text-muted me-1"></i>Official Scan Log
                            </div>
                        </div>
                    </div>
                    <span class="ra-gate-preview-badge">
                        <i class="fa-solid fa-shield-halved text-primary me-1"></i>Protected
                    </span>
                </div>

                <!-- TAB 1: Verification Form -->
                <div id="panelViewVerify">
                    <form method="POST" id="editForm">
                        @csrf
                        <input type="hidden" name="attendance_date" id="editDateInput">
                        <input type="hidden" name="date_filter" value="{{ $currentFilter }}">
                        <input type="hidden" name="custom_start_date" value="{{ $customStartDate }}">
                        <input type="hidden" name="custom_end_date" value="{{ $customEndDate }}">

                        <label class="ra-filter-label mb-2 d-block">Room Attendance Status:</label>
                        <div class="ra-status-grid mb-3">
                            @foreach (App\Models\AttendanceVerification::STATUSES as $value => $label)
                                @php
                                    $dotClass = match($value) {
                                        'present' => 'dot-success',
                                        'late', 'not_in_classroom' => 'dot-warning',
                                        'absent' => 'dot-danger',
                                        'excused' => 'dot-secondary',
                                        default => 'dot-secondary'
                                    };
                                    $iconClass = match($value) {
                                        'present' => 'fa-check',
                                        'late' => 'fa-clock',
                                        'absent' => 'fa-xmark',
                                        'excused' => 'fa-user-shield',
                                        'not_in_classroom' => 'fa-person-walking-arrow-right',
                                        default => 'fa-circle'
                                    };
                                @endphp
                                <label class="ra-status-radio-card" for="status_{{ $value }}">
                                    <input type="radio" name="status" value="{{ $value }}" id="status_{{ $value }}" class="status-radio" required>
                                    <span class="badge-dot {{ $dotClass }} py-1 px-2">
                                        <i class="fa-solid {{ $iconClass }} me-1"></i>
                                    </span>
                                    <span class="ra-status-card-label">
                                        {{ $label }}
                                        @if ($value === 'not_in_classroom')
                                            <span class="d-block text-muted small fw-normal mt-1" style="font-size: 0.72rem;">
                                                <i class="fa-regular fa-clock me-1"></i>1-hour grace period for scanned students. Automatically transitions to Absent if unverified.
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mb-4">
                            <label for="remarks" class="ra-filter-label mb-2">Teacher Remarks (optional):</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Provide reason or context for this attendance verification..."></textarea>
                        </div>

                        <div class="d-flex gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="offcanvas">Cancel</button>
                            <button type="submit" class="btn btn-primary w-50">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: Full History Event Timeline -->
                <div id="panelViewTimeline" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="ra-filter-label mb-0">Activity & Verification History</span>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="refreshTimelineBtn">
                            <i class="fa-solid fa-arrows-rotate me-1"></i>Refresh
                        </button>
                    </div>

                    <div id="timelineContainer">
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                            <span>Loading history...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sectionId = @json($section->id);
            if (!sectionId) return;

            if (typeof window.initEcho === 'function' || window.Echo) {
                const echo = window.Echo || (window.initEcho ? window.initEcho() : null);
                if (!echo) return;

                const statusDotMap = {
                    'present': 'success',
                    'late': 'warning',
                    'not_in_classroom': 'warning',
                    'absent': 'danger',
                    'excused': 'info',
                    'no_data': 'secondary'
                };

                const updateRowStatus = (enrollmentId, status, statusLabel, timeIn, scanType) => {
                    const tr = document.querySelector(`tr[data-enrollment-row="${enrollmentId}"]`);
                    if (!tr) return;

                    const dotClass = statusDotMap[status] || 'secondary';

                    // Time In cell
                    if (timeIn && tr.children[2]) {
                        tr.children[2].innerHTML = `<span class="fw-semibold text-dark">${timeIn}</span>`;
                    }
                    // Scan Type cell
                    if (scanType && tr.children[3]) {
                        tr.children[3].innerHTML = `<span class="badge-dot dot-success">${scanType}</span>`;
                    }
                    // Status cell
                    if (tr.children[4]) {
                        tr.children[4].innerHTML = `<span class="badge-dot dot-${dotClass}">${statusLabel || status}</span>`;
                    }

                    // Update action button dataset
                    const btn = tr.querySelector('.btn-view-history');
                    if (btn) {
                        btn.dataset.currentStatus = status;
                        btn.dataset.currentStatusLabel = statusLabel || status;
                        btn.dataset.currentStatusDot = `dot-${dotClass}`;
                    }

                    // Flash row
                    tr.style.transition = 'background-color 0.4s ease';
                    tr.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => {
                        tr.style.backgroundColor = '';
                    }, 2500);
                };

                const channel = echo.private(`room-attendance.${sectionId}`);

                channel.listen('.AttendanceRecorded', (e) => {
                    if (e.enrollment_id) {
                        const timeStr = e.formatted_time || (e.scan_time ? new Date(e.scan_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'No scan');
                        updateRowStatus(e.enrollment_id, e.late ? 'late' : 'present', e.late ? 'Late' : 'Present', timeStr, e.scan_type);
                    }
                });

                channel.listen('.AttendanceVerificationUpdated', (e) => {
                    if (e.enrollment_id) {
                        updateRowStatus(e.enrollment_id, e.status, e.status_label, e.time_in, null);
                    }
                });
            }
        });
    </script>

</x-layouts.teacher>