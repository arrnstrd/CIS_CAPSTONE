<x-layouts.admin pageName="Security Audit Log" subtitle="Monitors real-time authentication activity, IP addresses, devices, and security login attempts across all user accounts">

    <div class="mx-3">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Bar Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
            <div class="card-body p-3">
                <form action="{{ route('security-audit-log.index') }}" method="GET">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-8 col-lg-6">
                            <label class="form-label text-muted text-uppercase small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Search User / IP</label>
                            <div class="input-group input-group-sm">
                                <input type="search" name="query" class="form-control" placeholder="Search by name, email, or IP address..." value="{{ request('query') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label text-muted text-uppercase small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Event Result</label>
                            <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                                <option value="all" @selected(($status ?? 'all') === 'all')>All Results</option>
                                <option value="success" @selected(($status ?? '') === 'success')>Success</option>
                                <option value="failed" @selected(($status ?? '') === 'failed')>Failed</option>
                                <option value="locked_out" @selected(($status ?? '') === 'locked_out')>Locked Out</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-auto">
                            <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Date Filter">
                                @foreach(($dateFilters ?? []) as $val => $lbl)
                                    <input type="radio" class="btn-check" name="date_filter" id="date_{{ $val }}"
                                        value="{{ $val }}" @checked(($currentFilter ?? 'all') === $val) onchange="this.form.submit()">
                                    <label class="btn btn-outline-primary btn-sm rounded px-2.5 me-1 mb-1 mb-md-0" for="date_{{ $val }}">
                                        {{ $lbl }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        @if(($currentFilter ?? '') === 'custom')
                            <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                                <input type="date" name="custom_start_date" class="form-control form-control-sm" value="{{ request('custom_start_date') }}" required />
                                <span class="text-muted small">to</span>
                                <input type="date" name="custom_end_date" class="form-control form-control-sm" value="{{ request('custom_end_date') }}" required />
                                <button type="submit" class="btn btn-success btn-sm px-2.5">
                                    <i class="fas fa-filter me-1"></i> Apply
                                </button>
                            </div>
                        @endif

                        <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                            @if(request()->hasAny(['query', 'status', 'date_filter', 'custom_start_date', 'custom_end_date']))
                                <a href="{{ route('security-audit-log.index') }}" class="btn btn-outline-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#downloadReportModal">
                                <i class="fas fa-file-arrow-down me-1"></i> Download Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-3 p-4 border shadow-sm">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 border-bottom pb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-shield-alt text-primary me-2"></i>System-Wide Security Audit Log</h5>
                    <p class="text-muted small mb-0">Full historical log of user logins, failed attempts, IP addresses, and device signatures.</p>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 fs-7"><i class="fas fa-key me-1 text-success"></i> Authentication Events</span>
            </div>

            @if(empty($activityLogs) || $activityLogs->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No login or authentication audit events recorded for the selected filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <x-ui.table>
                        <thead class="text-uppercase small">
                            <tr>
                                <th style="width: 24%">User Account</th>
                                <th style="width: 15%">Authentication Event</th>
                                <th style="width: 15%">IP Address</th>
                                <th style="width: 18%">Device / Browser</th>
                                <th style="width: 16%">Date & Time</th>
                                <th style="width: 12%">Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activityLogs as $log)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark fs-7">
                                            {{ $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'Guest / System' }}
                                        </div>
                                        <div class="text-muted fs-7">
                                            <i class="far fa-envelope me-1"></i> {{ $log->email_attempted }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fs-7">
                                            <i class="fas fa-key me-1 text-primary"></i> Login Attempt
                                        </span>
                                    </td>
                                    <td>
                                        <code class="fs-7">{{ $log->ip_address ?? 'Unknown IP' }}</code>
                                    </td>
                                    <td>
                                        <span class="text-secondary small fw-medium">
                                            <i class="fas fa-desktop me-1 text-muted"></i> {{ $log->formatted_device }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-7">
                                        {{ $log->attempted_at ? \Illuminate\Support\Carbon::parse($log->attempted_at)->format('M d, Y H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        @if($log->status === 'success')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check-circle me-1"></i> Success
                                            </span>
                                        @elseif($log->status === 'locked_out')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                <i class="fas fa-lock me-1"></i> Locked Out
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-times-circle me-1"></i> Failed
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>

                <!-- Server-Side Pagination Links -->
                @if($activityLogs->hasPages())
                    <div class="px-3 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-2">
                        <div class="small text-muted">
                            Showing {{ $activityLogs->firstItem() }} to {{ $activityLogs->lastItem() }} of {{ $activityLogs->total() }} security audit events
                        </div>
                        <div>
                            {{ $activityLogs->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Download Report Modal --}}
    <div class="modal fade" id="downloadReportModal" tabindex="-1" aria-labelledby="downloadReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="GET" action="{{ route('security-audit-log.download') }}">
                    @foreach (request()->except(['date_filter', 'custom_start_date', 'custom_end_date', 'page', 'start_date', 'end_date']) as $field => $value)
                        <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                    @endforeach

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadReportModalLabel">
                            <i class="fas fa-shield-alt text-primary me-2"></i> Export Security Audit Report
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Select the date range to export audit events.</p>

                        <div class="mb-3">
                            <label for="sa_start_date" class="form-label small fw-bold">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="sa_start_date" name="start_date"
                                value="{{ request('custom_start_date', now()->subDays(7)->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="sa_end_date" class="form-label small fw-bold">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="sa_end_date" name="end_date"
                                value="{{ request('custom_end_date', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-3 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" formaction="{{ route('security-audit-log.download') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-file-excel text-success me-1"></i> Download Excel
                        </button>
                        <button type="submit" formaction="{{ route('security-audit-log.download-pdf') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-pdf text-white me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.admin>
