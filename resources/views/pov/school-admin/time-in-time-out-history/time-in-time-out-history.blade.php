<x-layouts.school-admin>
    <x-slot name="title">
        Time In Time Out History
    </x-slot>

    <x-slot name="pageName">
        Student Time In Time Out History
    </x-slot>

    <x-slot name="subtitle">
        School time in time out scans.
    </x-slot>


    @php
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $queryValue = $query ?? request('query', '');
        $scanTypeValue = $scan_type ?? request('scan_type', 'all');
        $sessionTypeValue = $session_type ?? request('session_type', 'all');
        $flagTypeValue = $flag_type ?? request('flag_type', 'all');
        $scanTypeLabels = [
            'IN' => 'Time In',
            'OUT' => 'Time Out',
        ];
        $flagTypeLabels = [
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
        $flagTypeDotMap = [
            'late_arrival' => 'warning',
            'duplicate_scan' => 'danger',
            'invalid_checkout' => 'danger',
            'missing_in' => 'warning',
            'missing_out' => 'warning',
            'excess_scan' => 'danger',
            'invalid_qr' => 'danger',
            'early_out' => 'warning',
            'early_timeout' => 'warning',
            'too_early' => 'warning',
            'invalid_session' => 'secondary',
        ];

        $filters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        $overviewCards = [
            ['label' => 'Total logs', 'value' => $statusCounts['TOTAL'] ?? 0, 'icon' => 'fa-solid fa-clipboard-list', 'variant' => 'dark', 'textVariant' => 'dark'],
            ['label' => 'Time In scans', 'value' => $statusCounts['IN'] ?? 0, 'icon' => 'fa-solid fa-right-to-bracket', 'variant' => 'success', 'textVariant' => 'success'],
            ['label' => 'Time Out scans', 'value' => $statusCounts['OUT'] ?? 0, 'icon' => 'fa-solid fa-right-from-bracket', 'variant' => 'primary', 'textVariant' => 'primary'],
            ['label' => 'Remarks', 'value' => $statusCounts['FLAGGED'] ?? 0, 'icon' => 'fa-solid fa-triangle-exclamation', 'variant' => 'danger', 'textVariant' => 'danger'],
        ];

        $historyTabs = [
            ['label' => 'Scan History Logs', 'href' => route('school_admin.time-in-time-out-history.index', request()->query()), 'active' => true, 'icon' => 'fas fa-list-ul'],
            ['label' => 'Attendance Analytics', 'href' => route('school_admin.time-in-time-out-history.analytics', request()->query()), 'icon' => 'fas fa-chart-pie'],
        ];
    @endphp

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-danger mx-3 mb-3" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Navigation Tabs --}}
    <x-layouts.school-admin.nav-tabs :tabs="$historyTabs" />

    {{-- Overview Cards --}}
    <div class="row g-3 mb-4 px-3">
        @foreach ($overviewCards as $card)
            <div class="col-6 col-lg-3">
                <div class="card border-0 rounded-4 h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3 p-3 p-lg-4">
                        <div class="rounded-4 bg-{{ $card['variant'] }} bg-opacity-10 text-{{ $card['variant'] }} d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 3.25rem; height: 3.25rem;">
                            <i class="{{ $card['icon'] }} fs-4"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-uppercase text-muted small fw-bold"
                                style="letter-spacing: 0.05em; font-size: 0.7rem;">
                                {{ $card['label'] }}
                            </div>
                            <div class="fs-3 fw-bold lh-1 text-{{ $card['textVariant'] }} mt-1">
                                {{ $card['value'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <div class="card border mx-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('school_admin.time-in-time-out-history.index') }}" method="GET">

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="search" name="query" class="form-control" placeholder="Student number or name"
                                value="{{ $queryValue }}">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Scan type</label>
                        <select class="form-select form-select-sm" name="scan_type" onchange="this.form.submit()">
                            <option value="all" @selected($scanTypeValue === 'all')>All</option>
                            <option value="IN" @selected($scanTypeValue === 'IN')>Time In</option>
                            <option value="OUT" @selected($scanTypeValue === 'OUT')>Time Out</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Session Type</label>
                        <select class="form-select form-select-sm" name="session_type" onchange="this.form.submit()">
                            <option value="all" @selected($sessionTypeValue === 'all')>All</option>
                            <option value="morning" @selected($sessionTypeValue === 'morning')>Morning</option>
                            <option value="afternoon" @selected($sessionTypeValue === 'afternoon')>Afternoon</option>
                            <option value="whole_day" @selected($sessionTypeValue === 'whole_day')>Whole Day</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Remarks</label>
                        <select class="form-select form-select-sm" name="flag_type" onchange="this.form.submit()">
                            <option value="all" @selected($flagTypeValue === 'all')>All</option>
                            <option value="late_arrival" @selected($flagTypeValue === 'late_arrival')>Late Arrival</option>
                            <option value="duplicate_scan" @selected($flagTypeValue === 'duplicate_scan')>Duplicate Scan</option>
                            <option value="invalid_checkout" @selected($flagTypeValue === 'invalid_checkout')>Invalid Checkout</option>
                            <option value="missing_in" @selected($flagTypeValue === 'missing_in')>Missing IN</option>
                            <option value="missing_out" @selected($flagTypeValue === 'missing_out')>Missing OUT</option>
                            <option value="excess_scan" @selected($flagTypeValue === 'excess_scan')>Excess Scan</option>
                            <option value="invalid_qr" @selected($flagTypeValue === 'invalid_qr')>Invalid QR</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 align-items-center">

                    <div class="col-12 col-md-auto">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Date Filter">
                            @foreach($filters as $value => $label)
                                <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                    value="{{ $value }}" @checked($currentFilter === $value) onchange="this.form.submit()">

                                <label class="btn btn-outline-primary btn-sm rounded px-3 me-1" for="date_{{ $value }}">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if ($currentFilter === 'custom')
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <input type="date" name="custom_start_date" class="form-control form-control-sm"
                                value="{{ request('custom_start_date') }}" required />

                            <input type="date" name="custom_end_date" class="form-control form-control-sm"
                                value="{{ request('custom_end_date') }}" required />

                            <button type="submit" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-funnel"></i> Apply
                            </button>
                        </div>
                    @endif

                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                        <a href="{{ route('school_admin.time-in-time-out-history.index') }}"
                            class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#downloadReportModal">
                            <i class="fas fa-file-arrow-down me-1"></i> Download Report
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <x-ui.table>
        <thead class="text-uppercase small">
            <tr>
                <th style="width: 24%">Student</th>
                <th style="width: 12%">Date</th>
                <th style="width: 16%">Grade & Section</th>
                <th style="width: 12%">Scan Type</th>
                <th style="width: 10%">Session</th>
                <th style="width: 10%">Gate Time</th>
                <th style="width: 10%">Remarks</th>
                <th style="width: 6%">Action</th>
            </tr>
        </thead>
        <tbody data-history-tbody>
            @forelse ($attendance_logs as $attendance_log)
                @php
                    $student = $attendance_log->enrollment?->student;
                    $flagTypes = $attendance_log->flagged_scans->pluck('flag_type')->filter()->unique();
                    $firstName = $student?->first_name ?? '';
                    $lastName = $student?->last_name ?? '';
                    $studentName = trim($firstName . ' ' . $lastName) ?: '-';
                    $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '--';
                @endphp
                <tr>
                    <td class="table-name-cell">
                        <div class="table-name-wrap">
                            <div class="table-name-avatar">{{ $initials }}</div>
                            <div class="table-name-copy">
                                <span class="table-name-main">{{ $studentName }}</span>
                                <span class="table-name-sub">{{ $student?->student_number ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>{{ $attendance_log->scan_time?->format('M d, Y') ?? '-' }}</td>
                    <td>
                        <span class="fw-semibold">Grade
                            {{ $attendance_log->enrollment?->section->grade_level ?? '-' }}</span>
                        <span class="text-muted">—</span>
                        <span>{{ $attendance_log->enrollment?->section?->name ?? '-' }}</span>
                    </td>
                    <td>
                        @php
                            $scanTypeDotMap = [
                                'IN' => 'success',
                                'OUT' => 'primary',
                            ];
                            $dotScanType = $scanTypeDotMap[$attendance_log->scan_type] ?? 'secondary';
                        @endphp
                        <span
                            class="badge-dot dot-{{ $dotScanType }}">{{ $scanTypeLabels[$attendance_log->scan_type] ?? 'Unknown' }}</span>
                    </td>
                    <td>{{ $attendance_log->session_type ? Str::headline(str_replace('_', ' ', $attendance_log->session_type)) : '-' }}
                    </td>
                    <td>{{ $attendance_log->scan_time?->format('h:i A') ?? '-' }}</td>
                    <td>
                        @if($flagTypes->isNotEmpty())
                            @foreach($flagTypes as $ftype)
                                @php
                                    $dotColor = $flagTypeDotMap[$ftype] ?? 'secondary';
                                    $label = $flagTypeLabels[$ftype] ?? (Str::headline(str_replace('_', ' ', $ftype)) ?: 'Needs Review');
                                @endphp
                                <span class="badge-dot dot-{{ $dotColor }}">{{ $label }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($flagTypes->isNotEmpty())
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#flagModal{{ $attendance_log->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                            <p class="mb-0">No gate scan logs found for the selected criteria</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>

    <!-- Pagination -->
    <div class="px-3 py-3">
        {{ $attendance_logs->links() }}
    </div>

    <!-- Remarks Modals -->
    @foreach ($attendance_logs as $attendance_log)
        @php
            $student = $attendance_log->enrollment?->student;
            $flagTypes = $attendance_log->flagged_scans->pluck('flag_type')->filter()->unique();

            $scanTypeDotMap = [
                'IN' => 'success',
                'OUT' => 'primary',
            ];
            $scanTypeLabels = [
                'IN' => 'Time In',
                'OUT' => 'Time Out',
            ];

            $flagDescriptions = [
                'late_arrival' => 'Student arrived past the scheduled entry window.',
                'duplicate_scan' => 'Repeated scan recorded within the cooldown window or session.',
                'invalid_checkout' => 'Time-out attempted without a corresponding check-in.',
                'missing_in' => 'No morning check-in scan was recorded.',
                'missing_out' => 'No afternoon check-out scan was recorded.',
                'excess_scan' => 'Daily maximum permitted scans reached.',
                'invalid_qr' => 'Unrecognized or invalid QR code presented.',
                'early_out' => 'Student checked out prior to the official dismissal window.',
                'early_timeout' => 'Student checked out prior to the official dismissal window.',
                'too_early' => 'Scan recorded prior to gate opening hours.',
                'invalid_session' => 'Scan recorded outside of scheduled school hours.',
            ];

            $flagIcons = [
                'late_arrival' => 'fas fa-clock',
                'duplicate_scan' => 'fas fa-copy',
                'invalid_checkout' => 'fas fa-circle-xmark',
                'missing_in' => 'fas fa-arrow-right-to-bracket',
                'missing_out' => 'fas fa-arrow-right-from-bracket',
                'excess_scan' => 'fas fa-ban',
                'invalid_qr' => 'fas fa-qrcode',
                'early_out' => 'fas fa-person-walking-arrow-right',
                'early_timeout' => 'fas fa-person-walking-arrow-right',
                'too_early' => 'fas fa-hourglass-start',
                'invalid_session' => 'fas fa-calendar-xmark',
            ];
            $flagLabels = $flagTypeLabels;

            $firstName = $student?->first_name ?? '';
            $lastName = $student?->last_name ?? '';
            $studentName = trim($firstName . ' ' . $lastName) ?: '-';
            $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '?';
        @endphp
        @if($flagTypes->isNotEmpty())
            <div class="modal fade flag-modal-clean" id="flagModal{{ $attendance_log->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        {{-- Header --}}
                        <div class="modal-header">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title">Remarks</h5>
                                    <div class="modal-subtitle">Review details and notes</div>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body">
                            {{-- Student Info --}}
                            <div class="student-card">
                                <div class="student-avatar">{{ $initials }}</div>
                                <div class="student-info">
                                    <span class="student-name">{{ $studentName }}</span>
                                    <div class="student-meta">
                                        Grade {{ $attendance_log->enrollment?->section->grade_level ?? '-' }} &middot;
                                        {{ $attendance_log->enrollment?->section?->name ?: '-' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Scan Grid --}}
                            <div class="scan-grid">
                                <div class="scan-item">
                                    <span class="scan-label">Date</span>
                                    <span class="scan-value">{{ $attendance_log->scan_time?->format('M d, Y') ?: '-' }}</span>
                                </div>
                                <div class="scan-item">
                                    <span class="scan-label">Time</span>
                                    <span class="scan-value">{{ $attendance_log->scan_time?->format('h:i A') ?: '-' }}</span>
                                </div>
                                <div class="scan-item">
                                    <span class="scan-label">Session</span>
                                    <span
                                        class="scan-value">{{ $attendance_log->session_type ? Str::headline(str_replace('_', ' ', $attendance_log->session_type)) : '-' }}</span>
                                </div>
                                <div class="scan-item">
                                    <span class="scan-label">Scan Type</span>
                                    <span class="scan-value">
                                        <span
                                            class="badge-dot dot-{{ $scanTypeDotMap[$attendance_log->scan_type] ?? 'secondary' }}">{{ $scanTypeLabels[$attendance_log->scan_type] ?? 'Unknown' }}</span>
                                    </span>
                                </div>
                            </div>

                            {{-- Remarks --}}
                            <div class="flags-title">Remarks</div>
                            <div class="flag-list">
                                @foreach($attendance_log->flagged_scans as $flaggedScan)
                                    @php
                                        $fType = $flaggedScan->flag_type;
                                        $fLabel = $flagLabels[$fType] ?? (Str::headline(str_replace('_', ' ', $fType)) ?: 'Needs Review');
                                        $fDesc = $flaggedScan->description ?: ($flagDescriptions[$fType] ?? 'This scan requires administrative review.');
                                        $fIcon = $flagIcons[$fType] ?? 'fas fa-circle-exclamation';
                                    @endphp
                                    <div class="flag-item {{ $fType }}">
                                        <div class="flag-icon-box">
                                            <i class="{{ $fIcon }}"></i>
                                        </div>
                                        <div class="flag-content">
                                            <span class="flag-name">{{ $fLabel }}</span>
                                            <span class="flag-desc">{{ $fDesc }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dismiss" data-bs-dismiss="modal">Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Download Report Modal --}}
    <div class="modal fade" id="downloadReportModal" tabindex="-1" aria-labelledby="downloadReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="GET" action="{{ route('school_admin.time-in-time-out-history.download') }}">
                    {{-- Carry over the active filters so the export matches what is on screen --}}
                    @foreach (request()->except(['date_filter', 'custom_start_date', 'custom_end_date', 'page', 'start_date', 'end_date']) as $field => $value)
                        <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                    @endforeach

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadReportModalLabel">
                            <i class="fas fa-calendar-range text-primary me-2"></i> Select Report Date Range
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small mb-3">Select a date range to export. Max range is one month (31
                            days).</p>

                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div id="dlDateError" class="alert alert-danger py-2 mb-0" style="display: none;"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" formaction="{{ route('school_admin.time-in-time-out-history.download') }}"
                            class="btn btn-dark">
                            <i class="fas fa-file-excel text-success me-1"></i> Download Excel
                        </button>
                        <button type="submit"
                            formaction="{{ route('school_admin.time-in-time-out-history.download-pdf') }}"
                            class="btn btn-primary">
                            <i class="fas fa-file-pdf text-white me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.initEcho === 'function' || window.Echo) {
                const echo = window.Echo || (window.initEcho ? window.initEcho() : null);
                if (!echo) return;

                echo.private('attendance.monitoring')
                    .listen('.AttendanceRecorded', (e) => {
                        const tbody = document.querySelector('[data-history-tbody]');
                        if (!tbody) return;

                        // Remove empty state placeholder row if present
                        const emptyTd = tbody.querySelector('td[colspan]');
                        if (emptyTd) {
                            emptyTd.closest('tr')?.remove();
                        }

                        const student = e.student || {};
                        const name = student.name || 'Unknown Student';
                        const studentNumber = student.student_number || '-';
                        const grade = student.grade ? `Grade ${student.grade}` : '-';
                        const section = student.section || '-';
                        const scanType = e.scan_type || 'IN';
                        const scanTypeLabel = scanType === 'IN' ? 'Time In' : 'Time Out';
                        const dotColor = scanType === 'IN' ? 'success' : 'primary';
                        const timeStr = e.formatted_time || (e.scan_time ? new Date(e.scan_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '-');
                        const initials = name.split(/\s+/).map(n => n[0]).join('').substring(0, 2).toUpperCase() || '??';
                        const todayDateStr = new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });

                        const newRow = document.createElement('tr');
                        newRow.className = 'table-success-subtle transition-all';
                        newRow.style.backgroundColor = '#ecfdf5';
                        newRow.innerHTML = `
                            <td class="table-name-cell">
                                <div class="table-name-wrap">
                                    <div class="table-name-avatar">${initials}</div>
                                    <div class="table-name-copy">
                                        <span class="table-name-main">${name}</span>
                                        <span class="table-name-sub">${studentNumber}</span>
                                    </div>
                                </div>
                            </td>
                            <td>${todayDateStr}</td>
                            <td>
                                <span class="fw-semibold text-dark">${grade}</span>
                                <span class="text-muted d-block small">${section}</span>
                            </td>
                            <td><span class="badge-dot dot-${dotColor}">${scanTypeLabel}</span></td>
                            <td>Regular</td>
                            <td>${timeStr}</td>
                            <td>${e.late ? '<span class="badge-dot dot-warning">Late Arrival</span>' : '<span class="text-muted">-</span>'}</td>
                            <td><span class="text-muted">-</span></td>
                        `;

                        tbody.insertBefore(newRow, tbody.firstChild);

                        setTimeout(() => {
                            newRow.style.backgroundColor = '';
                        }, 2500);
                    });
            }
        });
    </script>

</x-layouts.school-admin>