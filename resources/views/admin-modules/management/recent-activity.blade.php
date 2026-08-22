<x-layouts.admin pageName="Recent Activity" subtitle="Tracks administrative security operations, account changes, setup invitation resends, and access attempts">

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
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list-alt text-primary me-2"></i>Recent Administrative Activity Log</h5>
                    <p class="text-muted small mb-0">Records user creation, profile edits, status changes, setup invitations, and security access attempts.</p>
                </div>
                <span class="badge bg-light text-dark border px-2.5 py-1.5 fs-7"><i class="fas fa-user-shield me-1 text-primary"></i> Administrative Audit</span>
            </div>

            @if(empty($recentActivities) || $recentActivities->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-clipboard-list fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No administrative activity events recorded yet.</p>
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

                <!-- Server-Side Pagination Links (20 items per page) -->
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

    <!-- View Activity Details Modal -->
    <x-modal id="viewActivityModal" modalTitle="Activity Audit Details" size="modal-lg">
        <div class="p-2">
            <div class="row g-3">
                <!-- Actor Account & Email -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Actor Account</label>
                    <div class="p-2.5 bg-light rounded-3 border">
                        <div class="fw-bold text-dark" id="modalActorName">-</div>
                        <div class="text-muted small" id="modalActorEmail">-</div>
                    </div>
                </div>

                <!-- Result Status -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Execution Result</label>
                    <div class="p-2.5 bg-light rounded-3 border d-flex align-items-center">
                        <span id="modalResultBadge" class="badge px-2.5 py-1.5 fs-7">-</span>
                    </div>
                </div>

                <!-- Action Event -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Administrative Action</label>
                    <div class="p-2.5 bg-light rounded-3 border fw-semibold text-dark" id="modalAction">-</div>
                </div>

                <!-- Target Resource / Account -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Target Account / Resource</label>
                    <div class="p-2.5 bg-light rounded-3 border fw-medium text-dark" id="modalTarget">-</div>
                </div>

                <!-- Date & Time -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Timestamp (Date & Time)</label>
                    <div class="p-2.5 bg-light rounded-3 border text-dark" id="modalTimestamp">-</div>
                </div>

                <!-- IP Address & Device -->
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">IP Address & Device Signature</label>
                    <div class="p-2.5 bg-light rounded-3 border">
                        <div class="text-dark fw-medium" id="modalDevice">-</div>
                        <code class="small text-muted" id="modalIpAddress">-</code>
                    </div>
                </div>

                <!-- Additional Details / Reason -->
                <div class="col-12">
                    <label class="form-label text-muted small fw-semibold text-uppercase mb-1">Additional Context / Audit Details</label>
                    <div class="p-3 bg-light rounded-3 border text-dark fs-7 font-monospace" id="modalDetails" style="white-space: pre-wrap; word-break: break-word;">-</div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        </x-slot>
    </x-modal>

    <script>
        function openActivityDetailModal(data) {
            document.getElementById('modalActorName').textContent = data.actorName || 'System';
            document.getElementById('modalActorEmail').textContent = data.actorEmail || '-';
            document.getElementById('modalAction').textContent = data.action || '-';
            document.getElementById('modalTarget').textContent = data.target || 'System Resource';
            document.getElementById('modalTimestamp').textContent = data.timestamp || '-';
            document.getElementById('modalDevice').textContent = data.device || 'Unknown Device';
            document.getElementById('modalIpAddress').textContent = data.ipAddress || 'Unknown IP';
            document.getElementById('modalDetails').textContent = data.details || 'No additional details provided.';

            const badge = document.getElementById('modalResultBadge');
            if (data.result === 'success') {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-7';
                badge.innerHTML = '<i class="fas fa-check-circle me-1"></i> Success';
            } else {
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fs-7';
                badge.innerHTML = '<i class="fas fa-ban me-1"></i> Denied';
            }

            const modal = new bootstrap.Modal(document.getElementById('viewActivityModal'));
            modal.show();
        }
    </script>
</x-layouts.admin>
