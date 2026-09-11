<x-layouts.school-admin>
    <x-slot name="title">Bulk Import Students</x-slot>
    <x-slot name="subtitle">Import and verify student records from DepEd SF-1 spreadsheets.</x-slot>
    <x-slot name="pageName">Bulk Import</x-slot>

    <div class="bulk-import-page mx-2 mx-md-3 mb-4" data-import-base-url="{{ url('/import') }}">
        {{-- Navigation Bar: Mode Toggle (Import Hub vs History) + Quick Actions --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="view-switch-btn active" id="viewImportBtn">
                    <i class="fas fa-file-import me-1.5 text-primary"></i> Import Workspace
                </button>
                <button type="button" class="view-switch-btn" id="viewHistoryBtn">
                    <i class="fas fa-history me-1.5 text-secondary"></i> Transaction History
                    <span class="badge bg-secondary-subtle text-secondary ms-1" id="historyCountBadge">0</span>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('import.template') }}" class="bi-btn bi-btn--outline" id="downloadTemplateBtn">
                    <i class="fas fa-download me-1 text-primary"></i> Sample Template (.xlsx)
                </a>
            </div>
        </div>

        {{-- ==================================================================== --}}
        {{-- VIEW 1: CENTERED IMPORT WORKSPACE (PRIMARY FOCUS) --}}
        {{-- ==================================================================== --}}
        <div id="importWorkspaceView" class="import-workspace-container">
            {{-- Center Card Container --}}
            <div class="import-card-centered mx-auto">

                {{-- Interactive Stepper / Breadcrumb Trail --}}
                <div class="import-stepper mb-4">
                    <div class="step-node active" id="stepNode1">
                        <span class="step-circle"><i class="fas fa-file-upload"></i></span>
                        <span class="step-title">1. Upload File</span>
                    </div>
                    <div class="step-connector" id="stepConnector1"></div>
                    <div class="step-node" id="stepNode2">
                        <span class="step-circle"><i class="fas fa-tasks"></i></span>
                        <span class="step-title">2. Verify Rows</span>
                    </div>
                    <div class="step-connector" id="stepConnector2"></div>
                    <div class="step-node" id="stepNode3">
                        <span class="step-circle"><i class="fas fa-check-double"></i></span>
                        <span class="step-title">3. Finalize</span>
                    </div>
                </div>

                {{-- STEP 1: UPLOAD & FILE SELECTION --}}
                <div id="stepUploadSection" class="workspace-step-panel">
                    <div class="text-center mb-4">
                        <div class="import-icon-badge mx-auto mb-3">
                            <i class="fas fa-file-excel fa-2x text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Import DepEd SF-1 Spreadsheet</h4>
                        <p class="text-muted small mb-0">Upload student records (.xlsx or .xls) to automatically
                            validate rows and detect duplicates.</p>
                    </div>

                    {{-- Client-side Error / Warning Banner --}}
                    <div id="clientValidationAlert"
                        class="alert alert-danger d-none align-items-center gap-2 py-2.5 px-3 mb-3 rounded-3"
                        role="alert">
                        <i class="fas fa-exclamation-circle text-danger fs-5 flex-shrink-0"></i>
                        <div class="small fw-medium flex-grow-1" id="clientValidationMessage"></div>
                        <button type="button" class="btn-close btn-close-sm" id="dismissClientAlertBtn"
                            aria-label="Close"></button>
                    </div>

                    {{-- Drag & Drop Dropzone --}}
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="upload-dropzone p-4 p-md-5 text-center" id="dropzone">
                            <input type="file" class="d-none" id="file" name="file" accept=".xlsx,.xls" required>
                            <div class="dropzone-default-content" id="dropzonePrompt">
                                <div class="dropzone-icon-circle mx-auto mb-3">
                                    <i class="fas fa-cloud-arrow-up fa-2x text-primary"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">Drag and drop your spreadsheet here</h6>
                                <p class="text-muted small mb-3">or <a href="#" id="browseLink"
                                        class="text-primary text-decoration-none fw-semibold">browse files</a> from your
                                    computer</p>
                                <div class="d-flex align-items-center justify-content-center gap-3 text-muted small">
                                    <span><i class="far fa-file-excel me-1 text-success"></i> .xlsx / .xls format</span>
                                    <span>&bull;</span>
                                    <span><i class="fas fa-shield-alt me-1 text-info"></i> Max 10 MB</span>
                                </div>
                            </div>
                        </div>

                        {{-- File Selected Preview Card --}}
                        <div id="fileSelectedCard" class="file-preview-card mt-3 d-none">
                            <div class="d-flex align-items-center justify-content-between p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="file-badge-icon">
                                        <i class="fas fa-file-excel fa-2x text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-truncate" id="selectedFileName"
                                            style="max-width: 320px;">filename.xlsx</div>
                                        <div class="text-muted small d-flex align-items-center gap-2">
                                            <span id="selectedFileSize">0 KB</span>
                                            <span>&bull;</span>
                                            <span
                                                class="badge bg-success-subtle text-success border border-success-subtle">Ready
                                                for Verification</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5"
                                        id="clearSelectedFileBtn" title="Remove File">
                                        <i class="fas fa-trash-alt me-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Upload Action Footer --}}
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                            <span class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i> Row duplicates and data integrity will be
                                verified automatically.
                            </span>
                            <button type="submit" class="bi-btn bi-btn--primary px-4 py-2" id="uploadBtn" disabled>
                                <i class="fas fa-magnifying-glass-chart me-1.5"></i> Upload & Verify Rows
                            </button>
                        </div>
                    </form>
                </div>

                {{-- STEP 2: VERIFYING / VALIDATING STATE --}}
                <div id="stepVerifyingSection" class="workspace-step-panel d-none text-center py-5">
                    <div class="verifying-animation mb-4">
                        <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2" id="verificationStateTitle">Verifying Spreadsheet Rows...</h5>
                    <p class="text-muted small mb-4" id="verificationStateSubtitle">Scanning records for duplicates, LRN
                        validity, and section assignments.</p>

                    <div class="progress progress-animated mx-auto mb-3" style="max-width: 380px; height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100"></div>
                    </div>
                    <div class="text-muted small font-monospace" id="verificationCurrentStatus">Checking row constraints
                        & DepEd SF-1 structure...</div>
                </div>

                {{-- STEP 3: VERIFICATION SUMMARY & ISSUES PROMPT --}}
                <div id="stepValidationSummarySection" class="workspace-step-panel d-none">
                    <div
                        class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-0" id="summaryHeaderTitle">Row Verification Results</h5>
                            <small class="text-muted" id="summaryFilenameLabel">file.xlsx</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill"
                                id="reuploadFileBtn">
                                <i class="fas fa-arrow-rotate-left me-1"></i> Re-upload File
                            </button>
                        </div>
                    </div>

                    {{-- Stat Metric Cards --}}
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="verification-stat-card">
                                <div class="stat-num text-dark" id="statTotalRows">0</div>
                                <div class="stat-lbl text-muted">Total Rows</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="verification-stat-card border-success-subtle bg-success-subtle bg-opacity-10">
                                <div class="stat-num text-success" id="statValidRows">0</div>
                                <div class="stat-lbl text-success">Valid Rows</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="verification-stat-card border-danger-subtle bg-danger-subtle bg-opacity-10">
                                <div class="stat-num text-danger" id="statErrorRows">0</div>
                                <div class="stat-lbl text-danger">Errors (Blocked)</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="verification-stat-card border-warning-subtle bg-warning-subtle bg-opacity-10">
                                <div class="stat-num text-warning" id="statWarningRows">0</div>
                                <div class="stat-lbl text-warning">Warnings (Soft)</div>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Status Callout --}}
                    <div id="validationCalloutContainer" class="mb-4">
                        {{-- Injected dynamically depending on validation result (all valid vs duplicates/errors) --}}
                    </div>

                    {{-- Top Issues Preview Box (if issues present) --}}
                    <div id="summaryIssuesPreviewBox" class="issues-preview-container d-none mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-semibold small text-dark"><i
                                    class="fas fa-triangle-exclamation text-danger me-1"></i> Detected Issues
                                Breakdown</span>
                            <a href="#" id="viewAllIssuesLink"
                                class="small text-primary fw-semibold text-decoration-none">
                                View Full Issues Table <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </div>
                        <div class="issues-preview-list border rounded-3 p-2 bg-light bg-opacity-50"
                            id="summaryIssuesList">
                            {{-- Preview list items --}}
                        </div>
                    </div>

                    {{-- Validation Actions Bar --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <a href="#" id="downloadErrorReportBtn"
                                class="btn btn-outline-danger btn-sm rounded-pill d-none">
                                <i class="fas fa-file-arrow-down me-1"></i> Download Error Report (.xlsx)
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill d-none"
                                id="openIssueInspectorBtn">
                                <i class="fas fa-list-check me-1"></i> Inspect Issues
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <button type="button" class="btn btn-outline-secondary px-3 py-1.5" id="cancelImportBtn">
                                Cancel
                            </button>
                            <button type="button" class="bi-btn bi-btn--primary px-4 py-2" id="proceedImportBtn">
                                <i class="fas fa-check-circle me-1.5"></i> Proceed Import
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 4: REAL-TIME PROCESSING PROGRESS --}}
                <div id="stepProcessingSection" class="workspace-step-panel d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                        <span class="visually-hidden">Importing...</span>
                    </div>
                    <h5 class="fw-bold mb-1">Importing Student Records</h5>
                    <p class="text-muted small mb-4" id="processingStatusText">Saving records and enrolling students to
                        active sections...</p>

                    <div class="progress mb-2 mx-auto" style="max-width: 420px; height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            id="processingProgressBar" style="width: 0%;"></div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mx-auto mb-4" style="max-width: 420px;">
                        <span id="processingRowCounter">0 / 0 rows</span>
                        <span id="processingPercentCounter" class="fw-semibold text-primary">0%</span>
                    </div>

                    <div class="row g-2 justify-content-center mx-auto" style="max-width: 420px;">
                        <div class="col-6">
                            <div class="border rounded-3 p-2.5 bg-success-subtle bg-opacity-20 text-center">
                                <small class="text-muted d-block mb-0.5">Successfully Created</small>
                                <span class="fw-bold fs-5 text-success" id="processingSuccessCounter">0</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-2.5 bg-danger-subtle bg-opacity-20 text-center">
                                <small class="text-muted d-block mb-0.5">Skipped / Failed</small>
                                <span class="fw-bold fs-5 text-danger" id="processingFailedCounter">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 5: COMPLETION / RESULT --}}
                <div id="stepResultSection" class="workspace-step-panel d-none text-center py-4">
                    <div class="result-icon-circle mx-auto mb-3" id="resultIconWrapper">
                        <i class="fas fa-circle-check fa-3x text-success" id="resultIcon"></i>
                    </div>
                    <h4 class="fw-bold mb-1" id="resultCardTitle">Import Completed</h4>
                    <p class="text-muted small mb-4" id="resultCardSubtitle">The student records have been processed
                        successfully.</p>

                    <div class="row g-3 justify-content-center mb-4 mx-auto" style="max-width: 460px;">
                        <div class="col-4">
                            <div class="border rounded-3 p-3 bg-light text-center">
                                <div class="fs-4 fw-bold text-dark" id="resultTotalCount">0</div>
                                <div class="small text-muted">Total Rows</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 p-3 bg-success-subtle bg-opacity-20 text-center">
                                <div class="fs-4 fw-bold text-success" id="resultSuccessCount">0</div>
                                <div class="small text-success">Imported</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 p-3 bg-danger-subtle bg-opacity-20 text-center">
                                <div class="fs-4 fw-bold text-danger" id="resultFailedCount">0</div>
                                <div class="small text-danger">Failed</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary px-3.5 py-2 rounded-pill"
                            id="resultStartNewBtn">
                            <i class="fas fa-plus me-1.5"></i> Import Another File
                        </button>
                        <button type="button" class="btn btn-outline-primary px-3.5 py-2 rounded-pill d-none"
                            id="resultViewIssuesBtn">
                            <i class="fas fa-triangle-exclamation me-1.5"></i> View Issues
                        </button>
                        <button type="button" class="bi-btn bi-btn--primary px-4 py-2" id="resultGoHistoryBtn">
                            <i class="fas fa-history me-1.5"></i> View in Transaction History
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ==================================================================== --}}
        {{-- VIEW 2: TRANSACTION HISTORY (REQUESTED TABLE STRUCTURE) --}}
        {{-- ==================================================================== --}}
        <div id="historyView" class="history-view-container d-none">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 px-1">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fas fa-clock-rotate-left me-1.5 text-primary"></i> Import
                        Transaction History</h5>
                    <p class="text-muted small mb-0">Record of all SF-1 bulk import runs, uploaded files, row outcomes,
                        and duration.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="refreshHistoryBtn">
                        <i class="fas fa-arrows-rotate me-1"></i> Refresh History
                    </button>
                    <button type="button" class="bi-btn bi-btn--primary btn-sm" id="historyStartNewImportBtn">
                        <i class="fas fa-cloud-arrow-up me-1"></i> New Import
                    </button>
                </div>
            </div>

            <x-ui.table>
                <thead class="text-uppercase small">
                    <tr>
                        <th style="width: 11%"><span class="fas fa-receipt me-1"></span> Transaction</th>
                        <th style="width: 23%"><span class="fas fa-file-excel me-1"></span> File Name</th>
                        <th style="width: 14%"><span class="fas fa-user me-1"></span> Uploaded By</th>
                        <th style="width: 13%"><span class="fas fa-square-poll-vertical me-1"></span> Result</th>
                        <th style="width: 15%"><span class="fas fa-calendar me-1"></span> Date & Time</th>
                        <th style="width: 12%"><span class="fas fa-circle-dot me-1"></span> Status</th>
                        <th style="width: 6%"><span class="fas fa-stopwatch me-1"></span> Duration</th>
                        <th style="width: 6%" class="text-end"><span class="fas fa-sliders me-1"></span> Actions</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block text-primary"></i>
                            Loading transaction history...
                        </td>
                    </tr>
                </tbody>
            </x-ui.table>
            <div id="historyPagination" class="d-flex justify-content-center mt-3 px-3 py-3"></div>
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- MODALS & INSPECTORS --}}
    {{-- ==================================================================== --}}

    {{-- Issue Inspector Modal (Interactive Drawer/Modal) --}}
    <div class="modal fade bulk-import-page" id="issueInspectorModal" tabindex="-1"
        aria-labelledby="issueInspectorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom py-3">
                    <div>
                        <h5 class="modal-title fw-bold" id="issueInspectorModalLabel">
                            <i class="fas fa-triangle-exclamation text-warning me-2"></i> Import Issues & Duplicates
                            Inspector
                        </h5>
                        <small class="text-muted" id="issueInspectorSubtitle">Reviewing validation errors, duplicates,
                            and warning flags</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    {{-- Filter & Search Controls --}}
                    <div
                        class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 bg-light p-2.5 rounded-3 border">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <select class="form-select form-select-sm" id="severityFilter" style="width: 140px;">
                                <option value="">All Severities</option>
                                <option value="error">Errors (Blocking)</option>
                                <option value="warning">Warnings</option>
                            </select>
                            <select class="form-select form-select-sm" id="statusFilter" style="width: 150px;">
                                <option value="">All Statuses</option>
                                <option value="unresolved">Unresolved</option>
                                <option value="acknowledged">Acknowledged</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="issuesSearch"
                                placeholder="Search learner, LRN, error..." style="width: 220px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="#" id="inspectorDownloadErrorsBtn"
                                class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-file-arrow-down me-1"></i> Export Issues
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill"
                                id="acknowledgeAllBtn">
                                <i class="fas fa-check-double me-1"></i> Acknowledge All
                            </button>
                        </div>
                    </div>

                    {{-- Issues Table --}}
                    <x-ui.table>
                        <thead class="text-uppercase small">
                            <tr>
                                <th style="width: 8%"><span class="fas fa-hashtag me-1"></span> Row</th>
                                <th style="width: 22%"><span class="fas fa-user-graduate me-1"></span> Learner / Target
                                </th>
                                <th style="width: 22%"><span class="fas fa-tag me-1"></span> Issue Type</th>
                                <th style="width: 26%"><span class="fas fa-comment-dots me-1"></span> Details & Message
                                </th>
                                <th style="width: 10%"><span class="fas fa-triangle-exclamation me-1"></span> Severity
                                </th>
                                <th style="width: 12%" class="text-end"><span class="fas fa-wrench me-1"></span> Action
                                </th>
                            </tr>
                        </thead>
                        <tbody id="issuesBody">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No issues found.</td>
                            </tr>
                        </tbody>
                    </x-ui.table>
                    <div id="issuesPagination" class="d-flex justify-content-center mt-3"></div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Single Issue Raw Data Detail Modal --}}
    <div class="modal fade bulk-import-page" id="issueDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-circle-info text-primary me-2"></i> Record Details & Issue Diagnostics
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div id="issueDetailSummary" class="mb-3"></div>
                    <div id="issueDetailMessage" class="mb-3"></div>
                    <div id="issueDetailRawData" class="mb-0"></div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Modal --}}
    <div class="modal fade bulk-import-page" id="errorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-bold">
                        <i class="fas fa-triangle-exclamation me-1"></i> <span id="errorModalTitle">Error</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3" id="errorModalBody">
                    <p>Something went wrong.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm Modal --}}
    <div class="modal fade bulk-import-page" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3" id="confirmModalBody">
                    <p>Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="confirmModalYes">Yes, Proceed</button>
                </div>
            </div>
        </div>
    </div>

</x-layouts.school-admin>