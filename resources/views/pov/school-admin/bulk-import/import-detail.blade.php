<x-layouts.school-admin>
    <x-slot name="title">Import Transaction Detail</x-slot>
    <x-slot name="subtitle">Row-level overview of imported records and detected issues.</x-slot>
    <x-slot name="pageName">Import Detail</x-slot>

    <div class="import-detail-page mx-2 mx-md-3 mb-5"
         data-import-id="{{ $import->id }}"
         data-import-base-url="{{ url('/import') }}">

        {{-- PAGE HEADER --}}
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <a href="{{ route('bulk-import') }}"
                   class="d-inline-flex align-items-center gap-1 text-muted small text-decoration-none mb-2 detail-back-link">
                    <i class="fas fa-arrow-left fa-sm"></i> Back to Bulk Import
                </a>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="far fa-file-excel text-success me-2"></i>{{ $import->original_filename }}
                    </h4>
                    <span id="pageStatusBadge" class="badge rounded-pill px-3 py-1 fw-semibold"></span>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-3 text-muted small">
                    <span><i class="fas fa-hashtag me-1"></i>Transaction <span class="fw-semibold font-monospace text-dark">#{{ $import->id }}</span></span>
                    <span><i class="far fa-user me-1"></i>Uploaded by <span class="fw-semibold text-dark">{{ $import->createdBy ? trim(($import->createdBy->first_name ?? '') . ' ' . ($import->createdBy->last_name ?? '')) : 'Administrator' }}</span></span>
                    <span><i class="far fa-clock me-1"></i>{{ $import->created_at->format('M j, Y · g:i A') }}</span>
                    @if ($import->issues_count > 0)
                        <span class="text-warning"><i class="fas fa-triangle-exclamation me-1"></i>{{ $import->issues_count }} {{ Str::plural('issue', $import->issues_count) }} detected</span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="{{ route('import.export-errors', $import) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-file-arrow-down me-1"></i> Export Issues (.xlsx)
                </a>
                <a href="{{ route('bulk-import') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-cloud-arrow-up me-1"></i> New Import
                </a>
            </div>
        </div>

        {{-- STAT CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="detail-stat-card h-100">
                    <div class="detail-stat-icon bg-primary-subtle text-primary"><i class="fas fa-table-list"></i></div>
                    <div class="detail-stat-num text-dark">{{ number_format($import->total_rows) }}</div>
                    <div class="detail-stat-lbl text-muted">Total Rows</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="detail-stat-card h-100 border-success-subtle">
                    <div class="detail-stat-icon bg-success-subtle text-success"><i class="fas fa-user-check"></i></div>
                    <div class="detail-stat-num text-success">{{ number_format($import->success_count) }}</div>
                    <div class="detail-stat-lbl text-success">Students Imported</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="detail-stat-card h-100 border-danger-subtle">
                    <div class="detail-stat-icon bg-danger-subtle text-danger"><i class="fas fa-user-xmark"></i></div>
                    <div class="detail-stat-num text-danger">{{ number_format($import->failed_count) }}</div>
                    <div class="detail-stat-lbl text-danger">Rows Failed</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="detail-stat-card h-100 border-warning-subtle">
                    <div class="detail-stat-icon bg-warning-subtle text-warning"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="detail-stat-num text-warning">{{ number_format($import->issues_count) }}</div>
                    <div class="detail-stat-lbl text-warning">Issues Logged</div>
                </div>
            </div>
        </div>

        {{-- Success callout --}}
        @if ($import->success_count > 0)
        <div class="alert alert-success border-success-subtle bg-success-subtle bg-opacity-10 rounded-3 py-2 px-3 mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-circle-check text-success fs-5 flex-shrink-0"></i>
            <div class="small">
                <strong class="text-dark">{{ number_format($import->success_count) }} student {{ Str::plural('record', $import->success_count) }}</strong>
                successfully imported and enrolled.
                @if($import->failed_count > 0)
                <span class="text-muted">{{ number_format($import->failed_count) }} {{ Str::plural('row', $import->failed_count) }} could not be processed — review flagged rows below.</span>
                @endif
            </div>
        </div>
        @endif

        {{-- ROWS TABLE CARD --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom rounded-top-4 px-4 py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-1" id="rowTabGroup">
                        <button type="button" class="detail-tab-btn active" data-filter="">
                            <i class="fas fa-table-list me-1"></i> All Rows
                            <span class="badge bg-secondary-subtle text-secondary ms-1" id="tabCountAll">—</span>
                        </button>
                        <button type="button" class="detail-tab-btn" data-filter="clean">
                            <i class="fas fa-check me-1 text-success"></i> Clean Rows
                            <span class="badge bg-success-subtle text-success ms-1" id="tabCountClean">—</span>
                        </button>
                        <button type="button" class="detail-tab-btn" data-filter="error">
                            <i class="fas fa-circle-xmark me-1 text-danger"></i> Errors
                            <span class="badge bg-danger-subtle text-danger ms-1" id="tabCountError">—</span>
                        </button>
                        <button type="button" class="detail-tab-btn" data-filter="warning">
                            <i class="fas fa-triangle-exclamation me-1 text-warning"></i> Warnings
                            <span class="badge bg-warning-subtle text-warning-emphasis ms-1" id="tabCountWarning">—</span>
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-magnifying-glass text-muted fa-sm"></i></span>
                            <input type="text" class="form-control border-start-0 bg-light" id="detailSearch" placeholder="Search name, LRN, section…">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" id="detailAckAllBtn">
                            <i class="fas fa-check-double me-1"></i> Acknowledge All
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <x-ui.table>
                    <thead class="text-uppercase small border-bottom">
                        <tr>
                            <th style="width: 8%"><span class="fas fa-hashtag me-1"></span> Row #</th>
                            <th style="width: 26%"><span class="fas fa-user-graduate me-1"></span> Student Name</th>
                            <th style="width: 18%"><span class="fas fa-id-card me-1"></span> Learner Reference No.</th>
                            <th style="width: 18%"><span class="fas fa-chalkboard me-1"></span> Grade & Section</th>
                            <th style="width: 12%"><span class="fas fa-circle-dot me-1"></span> Status</th>
                            <th style="width: 10%"><span class="fas fa-tag me-1"></span> Issue Type</th>
                            <th style="width: 8%" class="text-end"><span class="fas fa-sliders me-1"></span> Action</th>
                        </tr>
                    </thead>
                    <tbody id="detailRowsBody">
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block text-primary"></i>
                                Loading row records…
                            </td>
                        </tr>
                    </tbody>
                </x-ui.table>
            </div>

            <div class="card-footer bg-white border-top rounded-bottom-4 px-4 py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <small class="text-muted" id="detailPaginationInfo"></small>
                    <div id="detailPagination" class="d-flex justify-content-end"></div>
                </div>
            </div>
        </div>

    </div>

    {{-- VIEW SPECIFIC ROW DETAIL MODAL --}}
    <div class="modal fade" id="rowDetailModal" tabindex="-1" aria-labelledby="rowDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="modal-icon-circle bg-primary-subtle text-primary">
                            <i class="fas fa-file-lines"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="rowDetailModalLabel">
                                Row <span id="modalRowNumber">—</span> Details
                            </h5>
                            <small class="text-muted" id="modalRowSub">Inspection of record data and validation outcome</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Row Info Cards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <h6 class="text-uppercase small text-muted fw-bold mb-2">Student Information</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Full Name</small>
                                    <span class="fw-bold text-dark" id="modalStudentName">—</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Learner Reference No. (LRN)</small>
                                    <span class="font-monospace text-dark fw-semibold" id="modalLrn">—</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Grade & Section</small>
                                    <span class="text-dark" id="modalGradeSection">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <h6 class="text-uppercase small text-muted fw-bold mb-2">Validation Status</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Status</small>
                                    <span id="modalStatusBadge"></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Issue Type</small>
                                    <span class="fw-semibold text-dark" id="modalIssueType">—</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Affected Field</small>
                                    <span class="text-dark" id="modalField">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Issue / Result Description Banner --}}
                    <div id="modalMessageCard" class="alert alert-light border rounded-3 p-3 mb-0">
                        <div class="d-flex align-items-start gap-2">
                            <div id="modalMessageIcon" class="mt-0.5"></div>
                            <div>
                                <h6 class="fw-bold mb-1" id="modalMessageTitle">Details</h6>
                                <p class="small mb-0 text-secondary" id="modalMessageText"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 justify-content-between">
                    <span id="modalAckStatusText" class="small text-muted"></span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 d-none" id="modalAckBtn">
                            <i class="fas fa-check me-1"></i> Acknowledge Issue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Error modal --}}
    <div class="modal fade" id="detailErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title text-danger fw-bold"><i class="fas fa-triangle-exclamation me-1"></i> Error</h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3 small" id="detailErrorBody">Something went wrong.</div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .detail-stat-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.1rem 1.25rem;display:flex;flex-direction:column;align-items:flex-start;gap:.35rem;transition:box-shadow .18s ease}
        .detail-stat-card:hover{box-shadow:0 4px 18px rgba(0,0,0,.06)}
        .detail-stat-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;margin-bottom:.2rem}
        .detail-stat-num{font-size:1.75rem;font-weight:700;line-height:1}
        .detail-stat-lbl{font-size:.75rem;font-weight:500}
        .detail-tab-btn{background:transparent;border:1px solid transparent;border-radius:8px;padding:.35rem .85rem;font-size:.82rem;font-weight:500;color:#6b7280;cursor:pointer;transition:all .15s ease}
        .detail-tab-btn:hover{background:#f3f4f6;color:#111827}
        .detail-tab-btn.active{background:#eff6ff;border-color:#bfdbfe;color:#2563eb;font-weight:600}
        .sev-chip{display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:600;padding:.2rem .65rem;border-radius:20px}
        .sev-chip.sev-clean{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
        .sev-chip.sev-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
        .sev-chip.sev-warning{background:#fffbeb;color:#b45309;border:1px solid #fde68a}
        .issue-type-chip{display:inline-flex;align-items:center;font-size:.78rem;font-weight:500;padding:.2rem .65rem;border-radius:6px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
        .detail-back-link:hover{color:#2563eb !important}
        #pageStatusBadge{font-size:.75rem}
        .modal-icon-circle{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem}
    </style>

    <script>
    (() => {
        const page     = document.querySelector('.import-detail-page');
        const importId = page?.dataset.importId;
        const baseUrl  = page?.dataset.importBaseUrl || '/import';
        const apiUrl   = (path) => `${baseUrl}${path}`;

        let currentPage   = 1;
        let currentFilter = '';
        let searchTimer   = null;
        let loadedRowsMap = new Map();

        function esc(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str ?? ''));
            return d.innerHTML;
        }

        function renderStatusBadge(status) {
            const map = {
                completed:             ['bg-success text-white',             'Completed'],
                completed_with_issues: ['bg-warning text-dark',              'Completed with Issues'],
                failed:                ['bg-danger text-white',              'Failed'],
                processing:            ['bg-primary text-white',             'Processing'],
                validated:             ['bg-info text-white',                'Validated'],
                pending:               ['bg-secondary-subtle text-secondary','Pending'],
                cancelled:             ['bg-secondary text-white',           'Cancelled'],
            };
            const [cls, lbl] = map[status] ?? ['bg-secondary text-white', (status ?? '—')];
            const el = document.getElementById('pageStatusBadge');
            if (el) { el.className = `badge rounded-pill px-3 py-1 fw-semibold ${cls}`; el.textContent = lbl; }
        }

        async function loadSummary() {
            try {
                const r = await fetch(apiUrl(`/${importId}`), { headers: { Accept: 'application/json' } });
                const d = await r.json();
                if (d?.data?.status) renderStatusBadge(d.data.status);
            } catch {}
        }

        async function loadRows(page = 1) {
            currentPage = page;
            const tbody  = document.getElementById('detailRowsBody');
            if (!tbody) return;
            const search = document.getElementById('detailSearch')?.value?.trim() || '';
            let url = apiUrl(`/${importId}/rows?per_page=20&page=${page}`);
            if (currentFilter) url += `&filter=${encodeURIComponent(currentFilter)}`;
            if (search)        url += `&search=${encodeURIComponent(search)}`;

            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-xl d-block mb-2 text-primary"></i>Loading row records…</td></tr>`;

            try {
                const r = await fetch(url, { headers: { Accept: 'application/json' } });
                const d = await r.json();
                updatePaginationInfo(d);
                renderPagination(d);
                updateCounts(d?.meta?.counts);

                loadedRowsMap.clear();

                if (!d.data || d.data.length === 0) {
                    if (currentFilter === 'error' || currentFilter === 'warning') {
                        // Notice: NO icon on "No issues found" per requirement
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="fw-semibold d-block">No issues found</span><small>All rows in this filter are clean.</small></td></tr>`;
                    } else if (currentFilter === 'clean') {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="fw-semibold d-block">No clean rows found</span><small>All rows in this import have issues or none were found.</small></td></tr>`;
                    } else {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="fw-semibold d-block">No records found</span><small>No rows match the current filter or search criteria.</small></td></tr>`;
                    }
                    return;
                }

                d.data.forEach(item => {
                    loadedRowsMap.set(String(item.row_number), item);
                });

                tbody.innerHTML = d.data.map(i => {
                    let sevClass = 'sev-clean';
                    let sevIcon  = 'fa-check';
                    let sevLabel = 'Clean';

                    if (i.status === 'error') {
                        sevClass = 'sev-error';
                        sevIcon  = 'fa-circle-xmark';
                        sevLabel = 'Error';
                    } else if (i.status === 'warning') {
                        sevClass = 'sev-warning';
                        sevIcon  = 'fa-triangle-exclamation';
                        sevLabel = 'Warning';
                    }

                    const nameVal = i.student_name || '';
                    const nameHtml = nameVal
                        ? `<span class="fw-semibold text-dark">${esc(nameVal)}</span>`
                        : `<span class="text-muted fst-italic small">Not recorded</span>`;

                    const typeHtml = i.issue_type
                        ? `<span class="issue-type-chip">${esc(i.issue_type)}</span>`
                        : `<span class="text-muted">—</span>`;

                    // Action buttons: Eye button to open detailed view modal, plus compact quick acknowledge if unresolved
                    let actionHtml = `<div class="d-inline-flex align-items-center justify-content-end gap-1">`;
                    actionHtml += `<button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" onclick="window.viewRowDetails('${i.row_number}')" title="View Row Details"><i class="fas fa-eye"></i></button>`;

                    if (i.issue_id) {
                        if (!i.is_acknowledged) {
                            actionHtml += `<button type="button" class="btn btn-sm btn-outline-success px-2 py-1" onclick="window.detailAckOne(${i.issue_id})" title="Acknowledge"><i class="fas fa-check"></i></button>`;
                        } else {
                            actionHtml += `<span class="badge bg-success-subtle text-success px-2 py-1" title="Acknowledged"><i class="fas fa-check"></i></span>`;
                        }
                    }
                    actionHtml += `</div>`;

                    return `<tr id="row-item-${i.row_number}">
                        <td class="font-monospace fw-semibold text-muted">#${esc(String(i.row_number ?? '—'))}</td>
                        <td>${nameHtml}</td>
                        <td><span class="font-monospace small text-dark">${esc(i.lrn || '—')}</span></td>
                        <td><span class="small text-dark">${esc(i.grade_section || '—')}</span></td>
                        <td><span class="sev-chip ${sevClass}"><i class="fas ${sevIcon} fa-sm"></i>${sevLabel}</span></td>
                        <td>${typeHtml}</td>
                        <td class="text-end">${actionHtml}</td>
                    </tr>`;
                }).join('');
            } catch {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-triangle-exclamation me-1"></i>Failed to load records. Please refresh the page.</td></tr>`;
            }
        }

        // View specific row details in modal
        window.viewRowDetails = function(rowNum) {
            const row = loadedRowsMap.get(String(rowNum));
            if (!row) return;

            const g = (id) => document.getElementById(id);
            g('modalRowNumber').textContent = `#${row.row_number}`;
            g('modalStudentName').textContent = row.student_name || 'Not recorded';
            g('modalLrn').textContent = row.lrn || '—';
            g('modalGradeSection').textContent = row.grade_section || '—';
            g('modalIssueType').textContent = row.issue_type || (row.status === 'clean' ? 'None (Clean Record)' : 'Unspecified');
            g('modalField').textContent = row.field ? esc(row.field) : '—';

            let sevClass = 'sev-clean';
            let sevIcon  = 'fa-check';
            let sevLabel = 'Clean / Success';
            let alertClass = 'alert-success border-success-subtle bg-success-subtle bg-opacity-10';
            let iconHtml = '<i class="fas fa-circle-check text-success fs-5"></i>';

            if (row.status === 'error') {
                sevClass = 'sev-error';
                sevIcon  = 'fa-circle-xmark';
                sevLabel = 'Validation Error';
                alertClass = 'alert-danger border-danger-subtle bg-danger-subtle bg-opacity-10';
                iconHtml = '<i class="fas fa-circle-xmark text-danger fs-5"></i>';
            } else if (row.status === 'warning') {
                sevClass = 'sev-warning';
                sevIcon  = 'fa-triangle-exclamation';
                sevLabel = 'Warning Flag';
                alertClass = 'alert-warning border-warning-subtle bg-warning-subtle bg-opacity-10';
                iconHtml = '<i class="fas fa-triangle-exclamation text-warning fs-5"></i>';
            }

            g('modalStatusBadge').innerHTML = `<span class="sev-chip ${sevClass}"><i class="fas ${sevIcon} fa-sm"></i>${sevLabel}</span>`;
            
            const card = g('modalMessageCard');
            card.className = `alert rounded-3 p-3 mb-0 ${alertClass}`;
            g('modalMessageIcon').innerHTML = iconHtml;
            g('modalMessageTitle').textContent = row.status === 'clean' ? 'Clean Record Description' : (row.status === 'error' ? 'Error Description' : 'Warning Description');
            g('modalMessageText').textContent = row.message || (row.status === 'clean' ? 'No issues were detected on this row.' : 'No description provided.');

            const ackBtn = g('modalAckBtn');
            const ackStatusText = g('modalAckStatusText');

            if (row.issue_id && !row.is_acknowledged) {
                ackBtn.classList.remove('d-none');
                ackBtn.onclick = async () => {
                    await window.detailAckOne(row.issue_id);
                    bootstrap.Modal.getInstance(g('rowDetailModal'))?.hide();
                };
                ackStatusText.textContent = 'This issue is currently unresolved.';
            } else if (row.issue_id && row.is_acknowledged) {
                ackBtn.classList.add('d-none');
                ackStatusText.innerHTML = '<span class="text-success"><i class="fas fa-circle-check me-1"></i>This issue has been acknowledged.</span>';
            } else {
                ackBtn.classList.add('d-none');
                ackStatusText.textContent = '';
            }

            new bootstrap.Modal(g('rowDetailModal')).show();
        };

        function updateCounts(counts) {
            if (!counts) return;
            const g = (id) => document.getElementById(id);
            if (g('tabCountAll'))     g('tabCountAll').textContent     = counts.all     ?? '0';
            if (g('tabCountClean'))   g('tabCountClean').textContent   = counts.clean   ?? '0';
            if (g('tabCountError'))   g('tabCountError').textContent   = counts.error   ?? '0';
            if (g('tabCountWarning')) g('tabCountWarning').textContent = counts.warning ?? '0';
        }

        function updatePaginationInfo(d) {
            const el = document.getElementById('detailPaginationInfo');
            if (!el || !d?.meta?.total) { if (el) el.textContent = ''; return; }
            const from = ((d.meta.current_page - 1) * d.meta.per_page) + 1;
            const to   = Math.min(d.meta.current_page * d.meta.per_page, d.meta.total);
            el.textContent = `Showing ${from}–${to} of ${d.meta.total} record${d.meta.total !== 1 ? 's' : ''}`;
        }

        function renderPagination(d) {
            const el = document.getElementById('detailPagination');
            if (!el) return;
            const meta = d?.meta;
            if (!meta || !meta.last_page || meta.last_page <= 1) { el.innerHTML = ''; return; }
            const cp = meta.current_page, lp = meta.last_page;
            let html = `<ul class="pagination pagination-sm mb-0">`;
            html += `<li class="page-item ${cp <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="return window.detailPageClick(${cp - 1})"><i class="fas fa-chevron-left fa-xs"></i></a></li>`;
            for (let i = Math.max(1, cp - 2); i <= Math.min(lp, cp + 2); i++) {
                html += `<li class="page-item ${i === cp ? 'active' : ''}"><a class="page-link" href="#" onclick="return window.detailPageClick(${i})">${i}</a></li>`;
            }
            html += `<li class="page-item ${cp >= lp ? 'disabled' : ''}"><a class="page-link" href="#" onclick="return window.detailPageClick(${cp + 1})"><i class="fas fa-chevron-right fa-xs"></i></a></li></ul>`;
            el.innerHTML = html;
        }

        window.detailPageClick = function(p) { loadRows(p); return false; };

        window.detailAckOne = async function(id) {
            try {
                const r = await fetch(apiUrl(`/issues/${id}/acknowledge`), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });
                if (!r.ok) throw new Error();
                await loadRows(currentPage);
            } catch {
                document.getElementById('detailErrorBody').textContent = 'Could not acknowledge this issue. Please try again.';
                new bootstrap.Modal(document.getElementById('detailErrorModal')).show();
            }
        };

        document.getElementById('detailAckAllBtn')?.addEventListener('click', async () => {
            const btn = document.getElementById('detailAckAllBtn');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Acknowledging…';
            try {
                const r = await fetch(apiUrl(`/${importId}/issues/acknowledge-all`), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                });
                if (!r.ok) throw new Error();
                await loadRows(currentPage);
            } catch {
                document.getElementById('detailErrorBody').textContent = 'Could not acknowledge all issues. Please try again.';
                new bootstrap.Modal(document.getElementById('detailErrorModal')).show();
            } finally { btn.disabled = false; btn.innerHTML = orig; }
        });

        document.getElementById('rowTabGroup')?.querySelectorAll('.detail-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.detail-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.dataset.filter ?? '';
                loadRows(1);
            });
        });

        document.getElementById('detailSearch')?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => loadRows(1), 350);
        });

        loadSummary();
        loadRows(1);
    })();
    </script>

</x-layouts.school-admin>
