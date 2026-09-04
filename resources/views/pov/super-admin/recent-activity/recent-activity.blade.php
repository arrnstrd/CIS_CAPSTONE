<x-layouts.super-admin pageName="Recent Activity" subtitle="Tracks administrative security operations, account changes, setup invitation resends, and access attempts">

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
                <form action="{{ route('recent-activity.index') }}" method="GET">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-8 col-lg-6">
                            <label class="form-label text-muted text-uppercase small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Search Actor / Action / Target</label>
                            <div class="input-group input-group-sm">
                                <input type="search" name="query" class="form-control" placeholder="Search by actor name, email, action or details..." value="{{ request('query') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label text-muted text-uppercase small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Action Result</label>
                            <select class="form-select form-select-sm" name="result" onchange="this.form.submit()">
                                <option value="all" @selected(($result ?? 'all') === 'all')>All Results</option>
                                <option value="success" @selected(($result ?? '') === 'success')>Success</option>
                                <option value="denied" @selected(($result ?? '') === 'denied')>Denied</option>
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
                            @if(request()->hasAny(['query', 'result', 'date_filter', 'custom_start_date', 'custom_end_date']))
                                <a href="{{ route('recent-activity.index') }}" class="btn btn-outline-secondary btn-sm">
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
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-alt text-primary me-2"></i>Recent Administrative Activity Log</h5>
                    <p class="text-muted small mb-0">Records user creation, profile edits, status changes, setup invitations, and security access attempts.</p>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 fs-7"><i class="fas fa-user-shield me-1 text-primary"></i> Administrative Audit</span>
            </div>

            @if(empty($recentActivities) || $recentActivities->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-clipboard-list fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No administrative activity events recorded for the selected filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <x-ui.table>
                        <thead class="text-uppercase small">
                            <tr>
                                <th style="width: 25%">Actor Account</th>
                                <th style="width: 32%">Administrative Action</th>
                                <th style="width: 20%">Date & Time</th>
                                <th style="width: 13%">Result</th>
                                <th style="width: 10%" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $act)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark fs-7">
                                            {{ $act->actor ? ($act->actor->first_name . ' ' . $act->actor->last_name) : 'System / External' }}
                                        </div>
                                        <div class="text-muted fs-7">
                                            <i class="far fa-envelope me-1"></i> {{ $act->actor_email }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark fs-7">{{ $act->action }}</div>
                                        @if($act->details)
                                            <div class="text-muted fs-7 text-truncate" style="max-width: 300px;" title="{{ $act->details }}">{{ $act->details }}</div>
                                        @endif
                                    </td>
                                    <td class="text-muted fs-7">
                                        {{ $act->created_at ? $act->created_at->format('M d, Y H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        @if($act->result === 'success')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check-circle me-1"></i> Success
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-ban me-1"></i> Denied
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary rounded-2 px-2.5 py-1 fw-medium shadow-2xs"
                                                onclick="openActivityDetailModal({
                                                    actorName: @js($act->actor ? ($act->actor->first_name . ' ' . $act->actor->last_name) : 'System / External'),
                                                    actorEmail: @js($act->actor_email),
                                                    action: @js($act->action),
                                                    target: @js(($act->target_type ? $act->target_type . ': ' : '') . ($act->target_identifier ?? 'System Resource')),
                                                    timestamp: @js($act->created_at ? $act->created_at->format('M d, Y H:i:s') : '-'),
                                                    device: @js($act->formatted_device),
                                                    ipAddress: @js($act->ip_address ?? 'Unknown IP'),
                                                    result: @js($act->result),
                                                    details: @js($act->details ?? 'No additional details provided.')
                                                })">
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>

                <!-- Server-Side Pagination Links -->
                @if($recentActivities->hasPages())
                    <div class="px-3 py-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-2">
                        <div class="small text-muted">
                            Showing {{ $recentActivities->firstItem() }} to {{ $recentActivities->lastItem() }} of {{ $recentActivities->total() }} administrative activity events
                        </div>
                        <div>
                            {{ $recentActivities->links() }}
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
                <form method="GET" action="{{ route('recent-activity.download') }}">
                    @foreach (request()->except(['date_filter', 'custom_start_date', 'custom_end_date', 'page', 'start_date', 'end_date']) as $field => $value)
                        <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                    @endforeach

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadReportModalLabel">
                            <i class="fas fa-list-alt text-primary me-2"></i> Export Administrative Activity Report
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Select the date range to export administrative activities.</p>

                        <div class="mb-3">
                            <label for="ra_start_date" class="form-label small fw-bold">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="ra_start_date" name="start_date"
                                value="{{ request('custom_start_date', now()->subDays(7)->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="ra_end_date" class="form-label small fw-bold">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="ra_end_date" name="end_date"
                                value="{{ request('custom_end_date', now()->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-3 px-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" formaction="{{ route('recent-activity.download') }}" class="btn btn-dark btn-sm">
                            <i class="fas fa-file-excel text-success me-1"></i> Download Excel
                        </button>
                        <button type="submit" formaction="{{ route('recent-activity.download-pdf') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-pdf text-white me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Activity Detail Modal --}}
    <div class="modal fade" id="activityDetailModal" tabindex="-1" aria-labelledby="activityDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark" id="activityDetailModalLabel">
                        <i class="fas fa-info-circle text-primary me-2"></i> Administrative Activity Detail
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Action</span>
                        <div class="fw-bold fs-6 text-dark" id="modalActionText">-</div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Actor</span>
                            <div class="fw-medium text-dark" id="modalActorName">-</div>
                            <small class="text-muted" id="modalActorEmail">-</small>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Target Resource</span>
                            <div class="fw-medium text-dark" id="modalTarget">-</div>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Date & Time</span>
                            <div class="fw-medium text-dark" id="modalTimestamp">-</div>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Result</span>
                            <div id="modalResultBadge">-</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Client Device & IP</span>
                        <div class="fw-medium text-dark" id="modalDevice">-</div>
                        <code class="small" id="modalIpAddress">-</code>
                    </div>
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Details & Parameters</span>
                        <div class="p-3 bg-light rounded-3 border text-secondary small font-monospace mt-1" style="max-height: 150px; overflow-y: auto;" id="modalDetailsText">-</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openActivityDetailModal(data) {
            document.getElementById('modalActionText').textContent = data.action;
            document.getElementById('modalActorName').textContent = data.actorName;
            document.getElementById('modalActorEmail').textContent = data.actorEmail;
            document.getElementById('modalTarget').textContent = data.target;
            document.getElementById('modalTimestamp').textContent = data.timestamp;
            document.getElementById('modalDevice').textContent = data.device;
            document.getElementById('modalIpAddress').textContent = data.ipAddress;
            document.getElementById('modalDetailsText').textContent = data.details;

            const resultContainer = document.getElementById('modalResultBadge');
            if (data.result === 'success') {
                resultContainer.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i> Success</span>';
            } else {
                resultContainer.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-ban me-1"></i> Denied</span>';
            }

            const modal = new bootstrap.Modal(document.getElementById('activityDetailModal'));
            modal.show();
        }
    </script>
    @endpush

</x-layouts.super-admin>
