<x-layouts.admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

    <div class="main-content mx-2">
        <!-- Overview Cards Section -->
        <div class="row mb-4 mx-2">
            <div class="col-12">
                <div class="row g-3">
                    <!-- Total Emails Today Card -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-envelope fa-lg text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1 text-muted">Total Emails Today</h6>
                                    <h3 class="mb-0 text-primary">{{ $emailCounts['total'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sent Emails Today Card -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-paper-plane fa-lg text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1 text-muted">Sent Emails Today</h6>
                                    <h3 class="mb-0 text-success">{{ $emailCounts['sent'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Emails Today Card -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-times-circle fa-lg text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1 text-muted">Failed Emails Today</h6>
                                    <h3 class="mb-0 text-danger">{{ $emailCounts['failed'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Emails Today Card -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-hourglass-half fa-lg text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1 text-muted">Pending Emails Today</h6>
                                    <h3 class="mb-0 text-warning">{{ $emailCounts['pending'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Controls Section -->
        <div class="col mb-4 mx-2">
            <div class="bg-white rounded p-4 shadow-sm">
                <div class="row g-3 align-items-center">
                    <!-- Search Form -->
                    <form action="{{ route('emails.index') }}" method="GET"
                        class="col-12 d-lg-flex align-items-center gap-2 m-0 p-0">

                        <!-- Search Input -->
                        <div class="col-12 col-lg-5 flex-shrink-0">
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student, email..."
                                    aria-label="Search by student number, student name or email recipient..."
                                    value="{{ request('query') }}" />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        <!-- Date Filter Selector -->
                        <div class="col-12 col-sm-6 col-lg-2 flex-shrink-0">
                            <select class="form-select" name="date_filter" onchange="this.form.submit()">
                                <option value="today" {{ request('date_filter', 'today') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="last_7_days" {{ request('date_filter') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-12 col-sm-6 col-lg-2 flex-shrink-0">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <!-- Scan Type Filter -->
                        <div class="col-12 col-sm-6 col-lg-2 flex-shrink-0">
                            <select class="form-select" name="scan_type" onchange="this.form.submit()">
                                <option value="all" {{ request('scan_type', 'all') === 'all' ? 'selected' : '' }}>All Scan Type</option>
                                <option value="IN" {{ request('scan_type') === 'IN' ? 'selected' : '' }}>IN</option>
                                <option value="OUT" {{ request('scan_type') === 'OUT' ? 'selected' : '' }}>OUT</option>
                                <option value="RE_ENTRY" {{ request('scan_type') === 'RE_ENTRY' ? 'selected' : '' }}>RE ENTRY</option>
                                <option value="RE_EXIT" {{ request('scan_type') === 'RE_EXIT' ? 'selected' : '' }}>RE EXIT</option>
                            </select>
                        </div>

                        <!-- Custom Date Range (Hidden by default) -->
                        @if (request('date_filter') === 'custom')
                            <div class="col-12 col-sm-6 col-lg-2 flex-shrink-0">
                                <input type="date" name="custom_start_date" class="form-control"
                                    value="{{ request('custom_start_date') }}" placeholder="Start Date" />
                            </div>
                            <div class="col-12 col-sm-6 col-lg-2 flex-shrink-0">
                                <input type="date" name="custom_end_date" class="form-control"
                                    value="{{ request('custom_end_date') }}" placeholder="End Date" />
                            </div>
                            <div class="col-12 col-lg-auto flex-shrink-0">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-funnel"></i> Apply Filter
                                </button>
                            </div>
                        @endif

                    </form>

                    <!-- Help and Resend All Buttons -->
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <!-- Help Button -->
                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#helpModal">
                            <i class="fas fa-question-circle"></i> Help
                        </button>

                        <!-- Resend All Button -->
                        <form action="{{ route('retryAll.email') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Are you sure you want to resend all failed emails?')">
                                <i class="bi bi-arrow-clockwise"></i>
                                <span>Resend Failed</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Logs Table Section -->
        <div class="table-section mx-2 mb-4">
            <div class="bg-white rounded shadow-sm overflow-hidden">
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
                                    <span class="text-muted small">
                                        {{ $emailLog->last_attempt_at?->format('M d, Y') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="fw-500">
                                        {{ $emailLog->student?->first_name ?? '' }}
                                        {{ $emailLog->student?->last_name ?? '' }}
                                    </span>
                                    @if ($emailLog->student?->student_number)
                                        <br>
                                        <small class="text-muted">{{ $emailLog->student->student_number }}</small>
                                    @endif
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
                                    <span class="text-muted">{{ $emailLog->last_attempt_at?->format('h:i A') }}</span>
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
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block opacity-50"></i>
                                    <p class="mb-0">No email logs found for the selected criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>

                <!-- Pagination -->
                <div class="px-4 py-3 border-top bg-light">
                    {{ $emailLogs->links() }}
                </div>
            </div>
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
                                <strong>Retry Button:</strong> Appears only for failed emails. Click to attempt redelivery of the email. Maximum 4 retry attempts per email.
                            </li>
                            <li>
                                <strong>Resend Failed:</strong> Bulk action to retry all failed emails at once (respects the 4-retry limit).
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