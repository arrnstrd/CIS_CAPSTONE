<x-layouts.school-admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="subtitle">Monitor email deliveries, pending, and failed logs.</x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

    @php
        $monitoringTabs = [
            ['label' => 'In/Out Monitoring', 'href' => route('school_admin.time-in-time-out-history.index'), 'active' => false, 'icon' => 'fas fa-exchange-alt'],
            ['label' => 'Email Monitoring', 'href' => route('emails.index', request()->query()), 'active' => true, 'icon' => 'fas fa-envelope'],
        ];
    @endphp

    {{-- Navigation Tabs --}}
    <x-layouts.school-admin.nav-tabs :tabs="$monitoringTabs" />

    <div class="main-content mx-2">
        <!-- Overview Cards Section -->


        <div class="row mb-3 mx-4">
            <div class="d-flex justify-content-center align-items center gap-3">
                <x-card title="total emails today" value="{{ $emailCounts['total'] ?? 0 }} " icon="fas fa-envelope"
                    variants="primary" />
                <x-card title="sent emails today" value="{{ $emailCounts['sent'] ?? 0 }} " icon="fas fa-paper-plane"
                    variants="success" />
                <x-card title="pending emails today" value="{{ $emailCounts['pending'] ?? 0 }} "
                    icon="fa-solid fa-hourglass-half" variants="warning" />
                <x-card title="failed emails today" value="{{ $emailCounts['failed'] ?? 0 }} "
                    icon="fas fa-times-circle" variants="danger" />
            </div>
        </div>


        <!-- Filters and Controls Section -->
        <div class="col mb-4 mx-2">
            <div class="bg-white rounded p-4 border">

                {{-- Main GET filter form --}}
                <form action="{{ route('emails.index') }}" method="GET" id="filterForm">

                    {{-- ROW 1: Search (col-7) | Status (col-2) | Scan Type (col-2) --}}
                    <div class="row g-2 pt-2 align-items-center mb-3">
                        <div class="col-12 col-lg-8">
                            <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student, email..." value="{{ request('query') }}" />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label class="form-label text-muted text-uppercase small fw-bold">status</label>
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status
                                </option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed
                                </option>
                            </select>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label class="form-label text-muted text-uppercase small fw-bold">Scan type</label>
                            <select class="form-select" name="scan_type" onchange="this.form.submit()">
                                <option value="all" {{ request('scan_type', 'all') === 'all' ? 'selected' : '' }}>All
                                </option>
                                <option value="IN" {{ request('scan_type') === 'IN' ? 'selected' : '' }}>Time In</option>
                                <option value="OUT" {{ request('scan_type') === 'OUT' ? 'selected' : '' }}>Time Out
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- ROW 2: Date filter (left) | Custom range (expands inline) | Help + Resend All (right, fixed)
                    --}}
                    <div class="row g-2 align-items-center">

                        {{-- Configuration --}}
                        @php
                            $filters = [
                                'today' => 'Today',
                                'week' => 'This Week',
                                'month' => 'This Month',
                                'custom' => 'Custom'
                            ];
                            $currentFilter = $dateFilter ?? request('date_filter', 'today');
                        @endphp

                        {{-- Date Filter Pills --}}
                        <div class="col-12 col-md-auto">
                            <div class="btn-group" role="group" aria-label="Date Filter">
                                @foreach($filters as $value => $label)
                                    <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                        value="{{ $value }}" @checked($currentFilter === $value)
                                        onchange="this.form.submit()">

                                    <label class="btn btn-outline-primary nav-pill rounded px-3 me-2"
                                        for="date_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Custom Range Fields --}}
                        @if ($currentFilter === 'custom')
                            <div class="col-12 col-md-auto d-flex gap-2 animate__animated animate__fadeIn">
                                <input type="date" name="custom_start_date" class="form-control nav-pill"
                                    value="{{ request('custom_start_date') }}" required />

                                <input type="date" name="custom_end_date" class="form-control nav-pill"
                                    value="{{ request('custom_end_date') }}" required />

                                <button type="submit" class="btn btn-success nav-pill px-3">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>
                            </div>
                        @endif

                        {{-- Help button (right side, ms-auto pushes it to the end) --}}
                        <div class="col ms-auto d-flex gap-2 justify-content-end">

                            <a href="{{ route('emails.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                            {{-- Resend All — triggers a separate hidden POST form via JS to avoid form nesting --}}
                            <button type="button" class="btn btn-outline-danger" onclick="confirmResendAll()">
                                <i class="bi bi-arrow-clockwise"></i> Resend All
                            </button>
                        </div>



                    </div>

                </form>

                {{-- Separate POST form for Resend All, outside the GET form --}}
                <form id="resendAllForm" action="{{ route('retryAll.email') }}" method="POST" class="d-none">
                    @csrf
                </form>

            </div>
        </div>

        <div id="emailNotification" class="d-none"></div>

        <script>
            function showEmailAlert(type, message) {
                const container = document.getElementById('emailNotification');
                if (!container) {
                    return;
                }
                container.innerHTML =
                    '<div class="alert alert-' + type + ' alert-dismissible fade show d-flex align-items-center" role="alert">' +
                    '<span>' + message + '</span>' +
                    '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                container.classList.remove('d-none');

                setTimeout(() => {
                    const alertEl = container.querySelector('.alert');
                    if (alertEl && window.bootstrap) {
                        bootstrap.Alert.getOrCreateInstance(alertEl).close();
                    }
                }, 4000);
            }

            async function sendResendRequest(form, successMessage) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        showEmailAlert('danger', data.message || 'Resend request failed.');
                        return;
                    }

                    // Single retry: the response includes the updated log status.
                    if (data.data?.status) {
                        if (data.data.status === 'sent') {
                            showEmailAlert('success', 'Email resent successfully.');
                        } else {
                            showEmailAlert('danger', 'The email could not be resent. Please try again.');
                        }
                    } else if (typeof data.success_count === 'number') {
                        // Bulk resend: response includes success/failed counts.
                        if (data.failed_count > 0 && data.success_count === 0) {
                            showEmailAlert('danger', 'Resend failed for ' + data.failed_count + ' email(s).');
                        } else {
                            showEmailAlert('success', 'Retry completed: ' + data.success_count + ' sent, ' + data.failed_count + ' failed.');
                        }
                    } else {
                        showEmailAlert('success', successMessage || data.message || 'Emails resent successfully.');
                    }

                    // Refresh the table so statuses update and Retry buttons disappear once sent.
                    if (window.ajaxCrud?.refreshTables) {
                        window.ajaxCrud.refreshTables({ scope: null });
                    }
                } catch (error) {
                    showEmailAlert('danger', 'Something went wrong while resending.');
                }
            }

            // Single Retry buttons (one per failed row)
            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!form?.matches('[data-ajax-retry="email"]')) {
                    return;
                }
                event.preventDefault();
                if (!confirm('Retry sending this email?')) {
                    return;
                }
                sendResendRequest(form, 'Email resent successfully.');
            });

            // Resend All failed emails
            function confirmResendAll() {
                if (!confirm('Are you sure you want to resend all failed emails?')) {
                    return;
                }
                const form = document.getElementById('resendAllForm');
                if (form) {
                    sendResendRequest(form, 'All failed emails have been reprocessed.');
                }
            }
        </script>

        <!-- Email Logs Table Section -->
        <x-ui.table>
            <thead>
                <tr>
                    <th style="width: 26%">
                        <span class="fas fa-user me-1"></span> Student
                    </th>
                    <th style="width: 12%">
                        <span class="fas fa-calendar me-1"></span> Date
                    </th>
                    <th style="width: 18%">
                        <span class="fas fa-envelope me-1"></span> Recipient Email
                    </th>
                    <th style="width: 12%">
                        <span class="fas fa-qrcode me-1"></span> Scan Type
                    </th>
                    <th style="width: 12%">
                        <span class="fas fa-circle me-1"></span> Status
                    </th>
                    <th style="width: 12%">
                        <span class="fas fa-clock me-1"></span> Time
                    </th>
                    <th style="width: 8%">
                        <span class="fas fa-sliders-h me-1"></span> Actions
                    </th>
                </tr>
            </thead>

            <tbody data-email-tbody>
                @forelse($emailLogs as $emailLog)
                    @php
                        $firstName = $emailLog->student?->first_name ?? '';
                        $lastName = $emailLog->student?->last_name ?? '';
                        $studentName = trim($firstName . ' ' . $lastName) ?: 'Unknown';
                        $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '--';
                    @endphp
                    <tr data-email-row="{{ $emailLog->id }}">
                        <td class="table-name-cell">
                            <div class="table-name-wrap">
                                <div class="table-name-avatar">{{ $initials }}</div>
                                <div class="table-name-copy">
                                    <span class="table-name-main">{{ $studentName }}</span>
                                    <span class="table-name-sub">{{ $emailLog->student?->student_number ?? '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $emailLog->last_attempt_at?->format('M d, Y') }}
                        </td>

                        <td>
                            <span class="text-break">{{ $emailLog->email }}</span>
                        </td>

                        <td>
                            <span
                                class="badge-dot dot-secondary">{{ $emailLog->scan_type === 'IN' ? 'Time In' : ($emailLog->scan_type === 'OUT' ? 'Time Out' : 'Unknown') }}</span>
                        </td>

                        <td>
                            @php
                                $statusDotMap = [
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'pending' => 'warning'
                                ];
                                $dotStatus = $statusDotMap[$emailLog->status] ?? 'secondary';
                            @endphp
                            <span class="badge-dot dot-{{ $dotStatus }}">
                                {{ ucfirst($emailLog->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $emailLog->last_attempt_at?->format('h:i A') }}
                        </td>

                        <td>
                            @if ($emailLog->status === 'failed')
                                <form action="{{ route('retry.email', $emailLog->id) }}" method="POST" class="d-inline"
                                    data-ajax-retry="email">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                        <i class="bi bi-arrow-repeat"></i> Retry
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">No actions</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0">No email logs found for the selected criteria</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <!-- Pagination -->
        <div class="px-3 py-3">
            {{ $emailLogs->links() }}
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.initEcho === 'function' || window.Echo) {
                const echo = window.Echo || (window.initEcho ? window.initEcho() : null);
                if (!echo) return;

                echo.private('attendance.monitoring')
                    .listen('.EmailLogCreated', (e) => {
                        const tbody = document.querySelector('[data-email-tbody]');
                        if (!tbody) return;

                        // Check if row for this email log already exists (e.g. status updated from pending -> sent/failed)
                        let tr = tbody.querySelector(`tr[data-email-row="${e.id}"]`);

                        const statusDotMap = {
                            'sent': 'success',
                            'failed': 'danger',
                            'pending': 'warning'
                        };
                        const dotStatus = statusDotMap[e.status] || 'secondary';
                        const statusLabel = e.status.charAt(0).toUpperCase() + e.status.slice(1);
                        const scanLabel = e.scan_type === 'IN' ? 'Time In' : (e.scan_type === 'OUT' ? 'Time Out' : 'Unknown');

                        if (tr) {
                            // Update status & actions cell
                            if (tr.children[4]) {
                                tr.children[4].innerHTML = `<span class="badge-dot dot-${dotStatus}">${statusLabel}</span>`;
                            }
                            if (tr.children[6]) {
                                if (e.status === 'failed') {
                                    tr.children[6].innerHTML = `
                                        <form action="/retry-email/${e.id}" method="POST" class="d-inline" data-ajax-retry="email">
                                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                                <i class="bi bi-arrow-repeat"></i> Retry
                                            </button>
                                        </form>`;
                                } else {
                                    tr.children[6].innerHTML = `<span class="text-muted small">No actions</span>`;
                                }
                            }
                        } else {
                            // Remove empty placeholder if present
                            const emptyTd = tbody.querySelector('td[colspan]');
                            if (emptyTd) {
                                emptyTd.closest('tr')?.remove();
                            }

                            const name = e.student_name || 'Unknown';
                            const initials = name.split(/\s+/).map(n => n[0]).join('').substring(0, 2).toUpperCase() || '--';

                            tr = document.createElement('tr');
                            tr.dataset.emailRow = e.id;
                            tr.innerHTML = `
                                <td class="table-name-cell">
                                    <div class="table-name-wrap">
                                        <div class="table-name-avatar">${initials}</div>
                                        <div class="table-name-copy">
                                            <span class="table-name-main">${name}</span>
                                            <span class="table-name-sub">${e.student_number || '-'}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>${e.date || ''}</td>
                                <td><span class="text-break">${e.email || ''}</span></td>
                                <td><span class="badge-dot dot-secondary">${scanLabel}</span></td>
                                <td><span class="badge-dot dot-${dotStatus}">${statusLabel}</span></td>
                                <td>${e.time || ''}</td>
                                <td><span class="text-muted small">No actions</span></td>
                            `;
                            tbody.insertBefore(tr, tbody.firstChild);
                        }

                        // Highlight row briefly
                        tr.style.transition = 'background-color 0.4s ease';
                        tr.style.backgroundColor = '#ecfdf5';
                        setTimeout(() => { tr.style.backgroundColor = ''; }, 2500);
                    });
            }
        });
    </script>

</x-layouts.school-admin>