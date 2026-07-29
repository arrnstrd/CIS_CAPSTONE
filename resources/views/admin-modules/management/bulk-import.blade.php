<x-layouts.admin>
    <x-slot name="title">Bulk Import Students</x-slot>
    <x-slot name="subtitle">Import student records from a DepEd SF-1 spreadsheet.</x-slot>
    <x-slot name="pageName">Import</x-slot>

    <div class="row justify-content-center">
        {{-- Upload Card (centered, not full width) --}}
        <div class="col-lg-8 col-xl-6">
            <div class="bg-white rounded-4 shadow-sm p-5 mb-5 text-center" id="uploadCard">
                <div class="mb-4">
                    <div class="display-6 text-muted mb-3"><i class="fas fa-file-excel"></i></div>
                    <h4 class="fw-semibold">Upload SF-1 Spreadsheet</h4>
                    <p class="text-muted small mb-4">Accepted format: <strong>.xlsx</strong> &middot; Max size: <strong>10 MB</strong></p>
                </div>

                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <div class="upload-dropzone border border-2 border-dashed rounded-3 p-5 text-center" id="dropzone"
                             style="cursor:pointer; border-color: #d0d5dd;">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="mb-1 fw-medium">Drag &amp; drop your file here, or <a href="#" id="browseLink">browse</a></p>
                            <small class="text-muted">Only .xlsx files up to 10 MB</small>
                            <input type="file" class="d-none" id="file" name="file" accept=".xlsx" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <button type="submit" class="btn btn-dark px-5" id="uploadBtn">
                            <i class="fas fa-upload me-2"></i> Upload
                        </button>
                        <a href="{{ route('import.template') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-download me-2"></i> Template
                        </a>
                    </div>
                </form>

                <div id="fileInfo" class="mt-3 d-none">
                    <div class="alert alert-info py-2 mb-0 d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-file me-2"></i> <span id="fileName"></span></span>
                        <button type="button" class="btn-close" id="clearFileBtn"></button>
                    </div>
                </div>
            </div>

            {{-- Inline progress (shown during upload/validation) --}}
            <div id="inlineProgress" class="bg-white rounded-4 shadow-sm p-5 mb-5 text-center d-none">
                <div class="spinner-border text-primary mb-3" style="width:2.5rem;height:2.5rem;"></div>
                <p class="fw-medium mb-0" id="inlineProgressText">Processing...</p>
            </div>
        </div>
    </div>

    {{-- Tabs: History (default) + Issues --}}
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill mb-0" id="mainTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="history-tab" data-bs-toggle="tab" data-bs-target="#historyPane" type="button" role="tab">
                        <i class="fas fa-history me-1"></i> History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="issues-tab" data-bs-toggle="tab" data-bs-target="#issuesPane" type="button" role="tab">
                        <i class="fas fa-exclamation-triangle me-1"></i> Issues
                        <span class="badge bg-danger ms-1 d-none" id="issuesBadge">0</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-white rounded-bottom-4 shadow-sm border border-top-0 p-4">
                {{-- HISTORY --}}
                <div class="tab-pane fade show active" id="historyPane" role="tabpanel">
                    <h5 class="fw-semibold mb-4">Import History</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th>Transaction</th>
                                    <th>File</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-center">Imported</th>
                                    <th class="text-center">Issues</th>
                                    <th class="text-center">Duration</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <tr><td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p class="fw-medium">No imports yet.</p>
                                        <p class="small">Upload a spreadsheet above to get started.</p>
                                    </div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="historyPagination" class="d-flex justify-content-center mt-3"></div>
                </div>

                {{-- ISSUES --}}
                <div class="tab-pane fade" id="issuesPane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="fw-semibold mb-0">Issues</h5>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <select class="form-select form-select-sm" id="severityFilter" style="width:auto;">
                                <option value="">All Severities</option>
                                <option value="error">Errors</option>
                                <option value="warning">Warnings</option>
                            </select>
                            <select class="form-select form-select-sm" id="statusFilter" style="width:auto;">
                                <option value="">All Statuses</option>
                                <option value="unresolved">Unresolved</option>
                                <option value="acknowledged">Acknowledged</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="issuesSearch" placeholder="Search..." style="width:160px;">
                            <button class="btn btn-sm btn-outline-success" id="acknowledgeAllBtn">
                                <i class="fas fa-check-double me-1"></i> Acknowledge All
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th style="width:60px;">Row</th>
                                    <th>Learner Name</th>
                                    <th>Issue</th>
                                    <th style="width:90px;">Severity</th>
                                    <th style="width:100px;">Status</th>
                                    <th style="width:120px;">Created</th>
                                    <th style="width:120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="issuesBody">
                                <tr><td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                    No issues found.
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="issuesPagination" class="d-flex justify-content-center mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODALS --}}
    {{-- ================================================================ --}}

    {{-- Error Modal --}}
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-circle me-1"></i> <span id="errorModalTitle">Error</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0" id="errorModalBody"><p>Something went wrong.</p></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Result Modal --}}
    <div class="modal fade" id="validationModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0" id="validationHeader">
                    <h5 class="modal-title" id="validationTitle">Validation Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="validationCloseBtn"></button>
                </div>
                <div class="modal-body" id="validationBody"></div>
                <div class="modal-footer border-0" id="validationFooter"></div>
            </div>
        </div>
    </div>

    {{-- Processing Modal --}}
    <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary mb-4" style="width:3rem;height:3rem;"></div>
                    <h5 class="fw-semibold mb-1">Processing Import</h5>
                    <p class="text-muted small mb-4" id="processingActivity">Importing student records...</p>
                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar" id="processingBar" style="width:0%;"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span id="processingProgress">0 / 0 rows</span>
                        <span id="processingPercent">0%</span>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <div class="border rounded p-2"><small class="text-muted">Created</small><br><span class="fw-bold text-success" id="processingSuccess">0</span></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2"><small class="text-muted">Failed</small><br><span class="fw-bold text-danger" id="processingFailed">0</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Modal --}}
    <div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0" id="resultHeader">
                    <h5 class="modal-title" id="resultTitle">Complete</h5>
                </div>
                <div class="modal-body" id="resultBody"></div>
                <div class="modal-footer border-0" id="resultFooter"></div>
            </div>
        </div>
    </div>

    {{-- Confirm Modal --}}
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmModalTitle">Confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0" id="confirmModalBody"><p>Are you sure?</p></div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmModalYes">Yes</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
