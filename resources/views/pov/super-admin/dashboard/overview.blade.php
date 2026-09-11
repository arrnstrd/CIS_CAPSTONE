<x-layouts.super-admin pageName="Super Admin Dashboard" subtitle="System Overview, User Account Security & Administrative Operations">

    @push('styles')
    <style>
        .sa-dashboard-table .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sa-dashboard-table .table-panel {
            border: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: transparent !important;
        }
        .sa-card-stat {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .sa-card-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08) !important;
        }
    </style>
    @endpush

    <div class="main-content mx-3">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Metric Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card sa-card-stat border-0 shadow-sm rounded-3 bg-white h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.04em;">Total Users</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalUsers ?? 0) }}</h3>
                        </div>
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="/users" class="small text-decoration-none fw-semibold text-primary">
                            View User Directory <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card sa-card-stat border-0 shadow-sm rounded-3 bg-white h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.04em;">Active Accounts</span>
                            <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($activeAccounts ?? 0) }}</h3>
                        </div>
                        <div class="rounded-3 p-3 bg-success-subtle text-success">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <span class="small text-muted fw-medium">Operational system accounts</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card sa-card-stat border-0 shadow-sm rounded-3 bg-white h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.04em;">Pending Invitations</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($pendingInvitations ?? 0) }}</h3>
                        </div>
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning">
                            <i class="fas fa-envelope-open-text fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <span class="small text-muted fw-medium">Awaiting setup completion</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card sa-card-stat border-0 shadow-sm rounded-3 bg-white h-100 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.04em;">Inactive Accounts</span>
                            <h3 class="fw-bold text-secondary mb-0 mt-1">{{ number_format($inactiveAccounts ?? 0) }}</h3>
                        </div>
                        <div class="rounded-3 p-3 bg-secondary-subtle text-secondary">
                            <i class="fas fa-user-slash fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <span class="small text-muted fw-medium">Deactivated accounts</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Invitations Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-clock me-2 text-warning"></i>Pending Account Invitations
                    </h5>
                    <p class="text-muted small mb-0 mt-0.5">Users who have been issued setup invitations and have not yet completed registration.</p>
                </div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">
                    {{ count($pendingUsers ?? []) }} Pending
                </span>
            </div>
            <div class="card-body p-0 sa-dashboard-table">
                @if(empty($pendingUsers) || $pendingUsers->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-2 opacity-50"></i>
                        <p class="mb-0">No pending user invitations at this time.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <x-ui.table>
                            <thead class="text-uppercase small">
                                <tr>
                                    <th>User Account</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingUsers as $pUser)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $pUser->first_name }} {{ $pUser->last_name }}</div>
                                            <small class="text-muted">{{ $pUser->employee_id ?? 'No ID' }}</small>
                                        </td>
                                        <td>{{ $pUser->email }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $pUser->role_label }}</span>
                                        </td>
                                        <td class="text-muted small">{{ $pUser->created_at ? $pUser->created_at->format('M d, Y') : '-' }}</td>
                                        <td class="text-end pe-3">
                                            <form action="/api/users/{{ $pUser->id }}/resend-invitation" method="POST" class="d-inline" data-ajax-form="resend">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary fw-medium rounded-2">
                                                    <i class="fas fa-paper-plane me-1"></i> Resend Invitation
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                @endif
            </div>
        </div>

        <!-- System Audit Log Overviews (Recent Logins & Recent Activity) -->
        <div class="row g-4">
            <!-- Recent Security Logins -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-shield-alt text-primary me-2"></i>Recent Security Audits
                            </h6>
                            <small class="text-muted">Real-time authentication attempts & IP logs</small>
                        </div>
                        <a href="/security-audit-log" class="small text-decoration-none fw-semibold">View All <i class="fas fa-arrow-right ms-0.5"></i></a>
                    </div>
                    <div class="card-body p-0 sa-dashboard-table">
                        @if(empty($recentLoginLogs) || $recentLoginLogs->isEmpty())
                            <div class="text-center text-muted py-4">No recent authentication logs.</div>
                        @else
                            <div class="table-responsive">
                                <x-ui.table>
                                    <thead class="text-uppercase small">
                                        <tr>
                                            <th>Account</th>
                                            <th>Device / IP</th>
                                            <th class="text-end pe-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentLoginLogs as $log)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold text-dark small">{{ $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'Guest' }}</div>
                                                    <small class="text-muted">{{ $log->email_attempted }}</small>
                                                </td>
                                                <td class="small text-muted">
                                                    <div>{{ $log->formatted_device }}</div>
                                                    <code>{{ $log->ip_address }}</code>
                                                </td>
                                                <td class="text-end pe-3">
                                                    @if($log->status === 'success')
                                                        <span class="badge bg-success-subtle text-success">Success</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Failed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </x-ui.table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Administrative Operations -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                    <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-list-alt text-primary me-2"></i>Recent Administrative Activity
                            </h6>
                            <small class="text-muted">Recorded system operations & audit trail</small>
                        </div>
                        <a href="/recent-activity" class="small text-decoration-none fw-semibold">View All <i class="fas fa-arrow-right ms-0.5"></i></a>
                    </div>
                    <div class="card-body p-0 sa-dashboard-table">
                        @if(empty($recentActivities) || $recentActivities->isEmpty())
                            <div class="text-center text-muted py-4">No recent administrative activities.</div>
                        @else
                            <div class="table-responsive">
                                <x-ui.table>
                                    <thead class="text-uppercase small">
                                        <tr>
                                            <th>Actor</th>
                                            <th>Action</th>
                                            <th class="text-end pe-3">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentActivities as $act)
                                            <tr>
                                                <td class="small">
                                                    <div class="fw-bold text-dark">{{ $act->actor ? ($act->actor->first_name . ' ' . $act->actor->last_name) : 'System' }}</div>
                                                    <span class="text-muted">{{ $act->actor_email }}</span>
                                                </td>
                                                <td class="small fw-medium text-dark">{{ $act->action }}</td>
                                                <td class="small text-muted text-end pe-3">{{ $act->created_at ? $act->created_at->format('M d, H:i') : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </x-ui.table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.super-admin>
