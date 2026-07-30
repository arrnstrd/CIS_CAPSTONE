<x-layouts.admin>
    <x-slot name="title">Bulk Import Students</x-slot>
    <x-slot name="subtitle">Import student records from a DepEd SF-1 spreadsheet.</x-slot>
    <x-slot name="pageName">Import</x-slot>

    {{-- Action Buttons + Inline Progress --}}
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end align-items-center gap-2">
            <div id="inlineProgress" class="d-none text-muted small me-2">
                <span class="spinner-border spinner-border-sm me-1"></span>
                <span id="inlineProgressText">Processing...</span>
            </div>
            <a href="{{ route('import.template') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium">
                <i class="fas fa-download me-1"></i> Template
            </a>
            <button class="btn btn-dark px-4 py-2 rounded-3 fw-medium" data-bs-toggle="modal"
                data-bs-target="#uploadModal">
                <i class="fas fa-file-import me-2"></i> Bulk Import
            </button>
        </div>
    </div>

    {{-- Tabs: History (default) + Issues --}}
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill mx-3 mb-4" id="mainTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="history-tab" data-bs-toggle="tab" data-bs-target="#historyPane"
                        type="button" role="tab">
                        <i class="fas fa-history me-1"></i> History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="issues-tab" data-bs-toggle="tab" data-bs-target="#issuesPane"
                        type="button" role="tab">
                        <i class="fas fa-exclamation-triangle me-1"></i> Issues
                        <span class="badge-dot dot-danger ms-1 d-none" id="issuesBadge">0</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content ">

                {{-- === HISTORY TAB === --}}
                <div class="tab-pane fade show active" id="historyPane" role="tabpanel">
                    <x-ui.table>
                        <thead class="text-uppercase small">
                            <tr>
                                <th style="width: 10%"><span class="fas fa-hashtag me-1"></span> Transaction</th>
                                <th style="width: 15%"><span class="fas fa-file me-1"></span> File Name</th>
                                <th style="width: 15%"><span class="fas fa-user me-1"></span> Uploaded By</th>
                                <th style="width: 13%"><span class="fas fa-check-circle me-1"></span> Result</th>
                                <th style="width: 15%"><span class="fas fa-calendar me-1"></span> Date & Time</th>
                                <th style="width: 12%"><span class="fas fa-circle me-1"></span> Status</th>
                                <th style="width: 8%"><span class="fas fa-clock me-1"></span> Duration</th>
                                <th style="width: 5%"><span class="fas fa-sliders-h me-1"></span></th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p class="fw-medium">No imports yet.</p>
                                        <p class="small">Click "Bulk Import" to upload a spreadsheet.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </x-ui.table>
                    <div id="historyPagination" class="d-flex justify-content-center mt-3 px-3 py-3"></div>
                </div>

                {{-- === ISSUES TAB === --}}
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
                            <input type="text" class="form-control form-control-sm" id="issuesSearch"
                                placeholder="Search..." style="width:160px;">
                            <button class="btn btn-sm btn-outline-success" id="acknowledgeAllBtn">
                                <i class="fas fa-check-double me-1"></i> Acknowledge All
                            </button>
                        </div>
                    </div>
                    <x-ui.table>
                        <thead class="text-uppercase small">
                            <tr>
                                <th style="width: 7%"><span class="fas fa-list-ol me-1"></span> Row</th>
                                <th style="width: 18%"><span class="fas fa-user-graduate me-1"></span> Learner</th>
                                <th style="width: 20%"><span class="fas fa-tag me-1"></span> Issue</th>
                                <th style="width: 10%"><span class="fas fa-exclamation me-1"></span> Severity</th>
                                <th style="width: 10%"><span class="fas fa-circle me-1"></span> Status</th>
                                <th style="width: 12%"><span class="fas fa-calendar me-1"></span> Created</th>
                                <th style="width: 13%"><span class="fas fa-sliders-h me-1"></span> Action</th>
                            </tr>
                        </thead>
                        <tbody id="issuesBody">
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                    No issues found.
                                </td>
                            </tr>
                        </tbody>
                    </x-ui.table>
                    <div id="issuesPagination" class="d-flex justify-content-center mt-3 px-3 py-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================================================================== --}}
    {{-- MODALS --}}
    {{-- ==================================================================== --}}

    {{-- Upload Modal (replaces inline upload form) --}}
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fas fa-file-excel me-1"></i> Upload SF-1 Spreadsheet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">Accepted format: <strong>.xlsx</strong> &middot; Max size:
                        <strong>10 MB</strong>
                    </p>
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <div class="upload-dropzone border border-2 border-dashed rounded-3 p-5 text-center"
                                id="dropzone" style="cursor:pointer; border-color: #d0d5dd;">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-1 fw-medium">Drag & drop your file here, or <a href="#"
                                        id="browseLink">browse</a></p>
                                <small class="text-muted">Only .xlsx files up to 10 MB</small>
                                <input type="file" class="d-none" id="file" name="file" accept=".xlsx" required>
                            </div>
                        </div>
                        <div id="fileInfo" class="mt-3 d-none">
                            <div class="alert alert-info py-2 mb-0 d-flex align-items-center justify-content-between">
                                <span><i class="fas fa-file me-2"></i> <span id="fileName"></span></span>
                                <button type="button" class="btn-close" id="clearFileBtn"></button>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark px-4" id="uploadBtn">
                                <i class="fas fa-upload me-2"></i> Upload & Validate
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Error Modal --}}
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger"><i class="fas fa-exclamation-circle me-1"></i> <span
                            id="errorModalTitle">Error</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0" id="errorModalBody">
                    <p>Something went wrong.</p>
                </div>
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
                            <div class="border rounded p-2"><small class="text-muted">Created</small><br><span
                                    class="fw-bold text-success" id="processingSuccess">0</span></div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2"><small class="text-muted">Failed</small><br><span
                                    class="fw-bold text-danger" id="processingFailed">0</span></div>
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
                <div class="modal-body pt-0" id="confirmModalBody">
                    <p>Are you sure?</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmModalYes">Yes</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Issue Detail Modal --}}
    <div class="modal fade" id="issueDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold">
                        <i class="fas fa-info-circle me-1"></i> Issue Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0" id="issueDetailBody">
                    <div id="issueDetailSummary" class="mb-3"></div>
                    <div id="issueDetailMessage" class="mb-3"></div>
                    <div id="issueDetailRawData" class="mb-0"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>