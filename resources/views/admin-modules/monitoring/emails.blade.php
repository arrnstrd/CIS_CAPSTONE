<x-layouts.admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

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
            <div class="bg-white rounded p-4 shadow-sm">

                {{-- Main GET filter form --}}
                <form action="{{ route('emails.index') }}" method="GET" id="filterForm">

                    {{-- ROW 1: Search (col-7) | Status (col-2) | Scan Type (col-2) --}}
                    <div class="row g-2 pt-2 align-items-center mb-3">
                        <div class="col-12 col-lg-8">
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student, email..." value="{{ request('query') }}" />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-lg-2">
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
                            <select class="form-select" name="scan_type" onchange="this.form.submit()">
                                <option value="all" {{ request('scan_type', 'all') === 'all' ? 'selected' : '' }}>All Scan
                                    Type</option>
                                <option value="IN" {{ request('scan_type') === 'IN' ? 'selected' : '' }}>IN</option>
                                <option value="OUT" {{ request('scan_type') === 'OUT' ? 'selected' : '' }}>OUT</option>
                                <option value="RE_ENTRY" {{ request('scan_type') === 'RE_ENTRY' ? 'selected' : '' }}>RE
                                    ENTRY</option>
                                <option value="RE_EXIT" {{ request('scan_type') === 'RE_EXIT' ? 'selected' : '' }}>RE EXIT
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- ROW 2: Date filter (left) | Custom range (expands inline) | Help + Resend All (right, fixed)
                    --}}
                    <div class="row g-2 align-items-center">

                        {{-- Date filter dropdown --}}
                        <div class="col-6 col-sm-4 col-lg-2">
                            <select class="form-select" name="date_filter" onchange="this.form.submit()">
                                <option value="today" {{ request('date_filter', 'today') === 'today' ? 'selected' : '' }}>
                                    Today</option>
                                <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>This Week
                                </option>
                                <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>This
                                    Month</option>
                                <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom
                                    Range</option>
                            </select>
                        </div>

                        {{-- Custom date fields expand to the right of the dropdown --}}
                        @if (request('date_filter') === 'custom')
                            <div class="col-6 col-sm-4 col-lg-2">
                                <input type="date" name="custom_start_date" class="form-control"
                                    value="{{ request('custom_start_date') }}" />
                            </div>
                            <div class="col-6 col-sm-4 col-lg-2">
                                <input type="date" name="custom_end_date" class="form-control"
                                    value="{{ request('custom_end_date') }}" />
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>
                            </div>
                        @endif

                        {{-- Help button (right side, ms-auto pushes it to the end) --}}
                        <div class="col ms-auto d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal"
                                data-bs-target="#helpModal">
                                <i class="fas fa-question-circle"></i> Help
                            </button>
                            <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal"
                                data-bs-target="#">
                                <i class="fas fa-download"></i> Download Excel
                            </button>

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

        <script>
            function confirmResendAll() {
                if (confirm('Are you sure you want to resend all failed emails?')) {
                    document.getElementById('resendAllForm').submit();
                }
            }
        </script>

        <!-- Email Logs Table Section -->
        <x-ui.table>
            <thead>
                <tr>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">Student Name</th>
                    <th style="width: 20%">Recipient Email</th>
                    <th style="width: 12%">Scan Type</th>
                    <th style="width: 12%">Status</th>
                    <th style="width: 12%">Time</th>
                    <th style="width: 15%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($emailLogs as $emailLog)
                    <tr>
                        <td>
                            {{ $emailLog->last_attempt_at?->format('M d, Y') }}
                        </td>

                        <td>
                            <span class="fw-500">
                                {{ $emailLog->student?->first_name ?? '' }}
                                {{ $emailLog->student?->last_name ?? '' }}
                            </span>
                        </td>

                        <td>
                            <span class="text-break">{{ $emailLog->email }}</span>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark">{{ $emailLog->scan_type }}</span>
                        </td>

                        <td>
                            @php
                                $statusColors = [
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'pending' => 'warning'
                                ];
                                $statusColor = $statusColors[$emailLog->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                                {{ ucfirst($emailLog->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $emailLog->last_attempt_at?->format('h:i A') }}
                        </td>

                        <td>
                            @if ($emailLog->status === 'failed')
                                <form action="{{ route('retry.email', $emailLog->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3"
                                        onclick="return confirm('Retry sending this email?')">
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
        <div class=" mx-2 mt-3 ">
            {{ $emailLogs->links() }}
        </div>

    </div>


    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title" id="helpModalLabel">
                        <i class="fas fa-question-circle me-2 text-info"></i>
                        Email Logs Guide
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Overview Section -->
                    <section class="mb-4">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-chart-bar me-2"></i>Overview Cards
                        </h6>
                        <p class="text-muted small mb-2">
                            The dashboard displays four key metrics for the selected date period:
                        </p>
                        <ul class="small text-muted mb-3">
                            <li><strong>Total Emails:</strong> Complete count of all email logs</li>
                            <li><strong>Sent Emails:</strong> Successfully delivered emails</li>
                            <li><strong>Failed Emails:</strong> Emails that failed to deliver</li>
                            <li><strong>Pending Emails:</strong> Emails awaiting delivery</li>
                        </ul>
                    </section>

                    <!-- Date Filtering Section -->
                    <section class="mb-4">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-calendar me-2"></i>Date Filtering
                        </h6>
                        <p class="text-muted small mb-2">
                            Filter email logs by date range:
                        </p>
                        <ul class="small text-muted mb-3">
                            <li><strong>Today:</strong> Shows only emails from the current day</li>
                            <li><strong>Yesterday:</strong> Shows only emails from the previous day</li>
                            <li><strong>Last 7 Days:</strong> Shows emails from the past week</li>
                            <li><strong>Custom Range:</strong> Select specific start and end dates</li>
                        </ul>
                    </section>

                    <!-- Status Badges Section -->
                    <section class="mb-4">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-tag me-2"></i>Status Indicators
                        </h6>
                        <p class="text-muted small mb-3">
                            Email statuses are displayed with colored badges:
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-success bg-opacity-10 text-success">Sent</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger">Failed</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                        </div>
                    </section>

                    <!-- Actions Section -->
                    <section class="mb-4">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-cogs me-2"></i>Available Actions
                        </h6>
                        <p class="text-muted small mb-2">
                            Manage email delivery:
                        </p>
                        <ul class="small text-muted mb-3">
                            <li>
                                <strong>Retry Button:</strong> Appears only for failed emails. Click to attempt
                                redelivery of the email. Maximum 4 retry attempts per email.
                            </li>
                            <li>
                                <strong>Resend Failed:</strong> Bulk action to retry all failed emails at once (respects
                                the 4-retry limit).
                            </li>
                        </ul>
                    </section>

                    <!-- Search Section -->
                    <section class="mb-4">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-search me-2"></i>Search & Filtering
                        </h6>
                        <p class="text-muted small mb-2">
                            Find specific email logs by:
                        </p>
                        <ul class="small text-muted">
                            <li><strong>Student Name or Number:</strong> Type in the search box</li>
                            <li><strong>Email Address:</strong> Search by recipient email</li>
                            <li><strong>Scan Type:</strong> Filter by IN, OUT, RE_ENTRY, or RE_EXIT</li>
                            <li><strong>Status:</strong> Filter by Sent, Pending, or Failed</li>
                        </ul>
                    </section>

                    <!-- Tips Section -->
                    <section>
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="fas fa-lightbulb me-2"></i>Tips
                        </h6>
                        <ul class="small text-muted">
                            <li>Use custom date ranges to analyze email performance over specific periods</li>
                            <li>Combine multiple filters to narrow down results quickly</li>
                            <li>Failed emails with attempt_count ≥ 4 cannot be retried automatically</li>
                            <li>Pagination shows 25 logs per page for better performance</li>
                        </ul>
                    </section>
                </div>

                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>