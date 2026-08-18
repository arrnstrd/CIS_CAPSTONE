<x-layouts.teacher>
    <x-slot name="pageName">
        Grade {{ $section->grade_level }} - {{ $section->name }}
    </x-slot>

    <x-slot name="subtitle">
        <a href="{{ route('room-attendance.index') }}" class="ra-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Room Attendance
        </a>
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

    <p class="ra-filter-label mb-2">Full class roster &mdash; every enrolled student appears, scanned or not</p>

    <x-ui.table>
        <thead>
            <tr>
                <th>Student No</th>
                <th>Student Name</th>
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
                    <tr @class(['table-warning-subtle' => $row->status !== 'present'])>
                        <td>{{ $row->student->student_number ?? '—' }}</td>
                        <td>
                            {{ $row->student->first_name ?? '' }}
                            {{ $row->student->last_name ?? '' }}
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
                                data-student-name="{{ $row->student->first_name }} {{ $row->student->last_name }}"
                                data-student-no="{{ $row->student->student_number }}"
                                data-time-in="{{ $row->log?->scan_time?->format('h:i A') ?? 'No scan' }}"
                                data-current-status="{{ $row->status }}"
                                data-current-status-label="{{ $row->status_label }}"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
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
                        <td colspan="6" class="fw-bold" style="background: rgba(36,56,185,0.06);">
                            {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                        </td>
                    </tr>
                    @foreach ($dayRows as $row)
                        <tr @class(['table-warning-subtle' => $row->status !== 'present'])>
                            <td>{{ $row->student->student_number ?? '—' }}</td>
                            <td>
                                {{ $row->student->first_name ?? '' }}
                                {{ $row->student->last_name ?? '' }}
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
                        <td colspan="6" class="text-center text-muted py-4">
                            No active students enrolled in this section.
                        </td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </x-ui.table>

    @if ($isSingleDay)
        <!-- Edit Attendance Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editForm" action="">
                        @csrf
                        <input type="hidden" name="status" id="editStatusInput">
                        <input type="hidden" name="attendance_date" id="editDateInput">
                        <input type="hidden" name="date_filter" value="{{ $currentFilter }}">
                        <input type="hidden" name="custom_start_date" value="{{ $customStartDate }}">
                        <input type="hidden" name="custom_end_date" value="{{ $customEndDate }}">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Attendance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="fw-bold mb-3">
                                <span id="editStudentName"></span> (<span id="editStudentNo"></span>)
                            </p>
                            <p class="text-muted small mb-3">
                                Scanned: <span id="editTimeIn"></span>
                            </p>
                            <p class="mb-3">Current status: <span id="editCurrentStatus" class="badge-dot dot-secondary"></span></p>
                            
                            <div class="mb-3">
                                <label class="form-label">New status:</label>
                                <div class="d-flex flex-column gap-2">
                                    @foreach (App\Models\AttendanceVerification::STATUSES as $value => $label)
                                        <div class="ra-status-option">
                                            <input type="radio" name="status" value="{{ $value }}" id="status_{{ $value }}" class="status-radio" required>
                                            <label for="status_{{ $value }}" class="mb-0">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks (optional):</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any notes about this attendance change..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-link btn-sm p-0" id="viewHistoryBtn">
                                <i class="fa-solid fa-clock-rotate-left"></i> View full history
                            </button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attendance History Modal -->
        <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attendance History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="historyModalBody">
                        <p class="text-muted text-center py-4">Loading...</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            const editModal = document.getElementById('editModal');
            const editForm = document.getElementById('editForm');
            let currentEnrollmentId = null;
            let currentDate = null;

            editModal.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                currentEnrollmentId = btn.getAttribute('data-enrollment-id');
                currentDate = btn.getAttribute('data-date');

                document.getElementById('editStudentName').textContent = btn.getAttribute('data-student-name');
                document.getElementById('editStudentNo').textContent = btn.getAttribute('data-student-no');
                document.getElementById('editTimeIn').textContent = btn.getAttribute('data-time-in');
                document.getElementById('editCurrentStatus').textContent = btn.getAttribute('data-current-status-label');
                document.getElementById('editDateInput').value = currentDate;

                const currentStatus = btn.getAttribute('data-current-status');
                document.querySelectorAll('.status-radio').forEach(function (radio) {
                    radio.checked = radio.value === currentStatus;
                    radio.closest('.ra-status-option').classList.toggle('ra-status-option-active', radio.value === currentStatus);
                });

                editForm.action = "{{ url('/teacher/room-attendance/' . $section->id) }}/" + currentEnrollmentId + "/verify";
            });

            document.querySelectorAll('.status-radio').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    document.querySelectorAll('.ra-status-option').forEach(function (el) {
                        el.classList.remove('ra-status-option-active');
                    });
                    this.closest('.ra-status-option').classList.add('ra-status-option-active');
                    document.getElementById('editStatusInput').value = this.value;
                });
            });

            editForm.addEventListener('submit', function () {
                const checked = document.querySelector('.status-radio:checked');
                if (checked) {
                    document.getElementById('editStatusInput').value = checked.value;
                }
            });

            document.getElementById('viewHistoryBtn').addEventListener('click', function () {
                const historyModalEl = document.getElementById('historyModal');
                const historyModal = new bootstrap.Modal(historyModalEl);
                const body = document.getElementById('historyModalBody');
                body.innerHTML = '<p class="text-muted text-center py-4">Loading...</p>';
                historyModal.show();

                fetch("{{ url('/teacher/room-attendance/' . $section->id) }}/" + currentEnrollmentId + "/history")
                    .then(res => res.json())
                    .then(data => {
                        let html = '<p class="fw-bold mb-3">' + data.student.first_name + ' ' + data.student.last_name + '</p>';

                        html += '<p class="ra-filter-label mb-2">Modification history</p>';
                        if (data.verifications.length === 0) {
                            html += '<p class="text-muted small">No teacher edits yet.</p>';
                        } else {
                            html += '<div class="table-responsive"><table class="table table-sm">';
                            html += '<thead><tr><th>Date</th><th>Status</th><th>Remarks</th><th>By</th></tr></thead><tbody>';
                            data.verifications.forEach(function(v) {
                                html += '<tr><td>' + new Date(v.created_at).toLocaleDateString() + '</td>';
                                html += '<td><span class="badge-dot dot-' + (v.status === 'present' ? 'success' : v.status === 'absent' ? 'danger' : 'warning') + '">' + v.status_label + '</span></td>';
                                html += '<td>' + (v.remarks || '—') + '</td>';
                                html += '<td>' + (v.teacher ? v.teacher.first_name + ' ' + v.teacher.last_name : 'System') + '</td></tr>';
                            });
                            html += '</tbody></table></div>';
                        }

                        html += '<p class="ra-filter-label mb-2 mt-3">Raw QR scans</p>';
                        if (data.scans.length === 0) {
                            html += '<p class="text-muted small">No scans recorded.</p>';
                        } else {
                            html += '<div class="table-responsive"><table class="table table-sm">';
                            html += '<thead><tr><th>Date</th><th>Time</th><th>Type</th></tr></thead><tbody>';
                            data.scans.forEach(function(s) {
                                html += '<tr><td>' + new Date(s.scan_time).toLocaleDateString() + '</td>';
                                html += '<td>' + new Date(s.scan_time).toLocaleTimeString() + '</td>';
                                html += '<td>' + s.scan_type + '</td></tr>';
                            });
                            html += '</tbody></table></div>';
                        }

                        body.innerHTML = html;
                    });
            });
        })();
        </script>
    @endif

</x-layouts.teacher>