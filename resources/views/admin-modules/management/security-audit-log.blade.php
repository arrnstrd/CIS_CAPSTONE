<x-layouts.admin pageName="Security Audit Log" subtitle="Monitors real-time authentication activity, IP addresses, devices, and security login attempts across all user accounts">

    <div class="mx-3">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                    <p class="mb-0">No login or authentication audit events recorded yet.</p>
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

                <!-- Server-Side Pagination Links (20 items per page) -->
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
</x-layouts.admin>
