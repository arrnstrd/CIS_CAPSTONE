/**
 * Bulk Import — Interactive Controller & State Machine.
 *
 * Handles the full import lifecycle:
 *   File Drop / Pick -> Client Validation -> Upload & Row Verification ->
 *   Validation Summary & Issue Inspector -> Confirm / Process -> Completion Summary -> History
 */

class ImportState {
    constructor() {
        this.importId = null;
        this.currentFilename = "";
        this.totalRows = 0;
        this.validRows = 0;
        this.errorRows = 0;
        this.warningRows = 0;
        this.step = "upload"; // upload | verifying | summary | processing | result
        this.pollTimer = null;
    }

    set(id, filename = "") {
        this.importId = id;
        if (filename) this.currentFilename = filename;
    }

    get() {
        return this.importId;
    }

    clear() {
        this.importId = null;
        this.currentFilename = "";
        this.totalRows = 0;
        this.validRows = 0;
        this.errorRows = 0;
        this.warningRows = 0;
        this.step = "upload";
        this.stopPoll();
    }

    stopPoll() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }
}

const state = new ImportState();

// ── Bootstrap Modal Helper Cache ─────────────────────────────────────────
const modalCache = {};
function modal(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    if (!modalCache[id]) {
        modalCache[id] = new bootstrap.Modal(el);
    }
    return modalCache[id];
}
const showM = (id) => modal(id)?.show();
const hideM = (id) => modal(id)?.hide();

// ── CSRF Token ──────────────────────────────────────────────────────────
function csrf() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : "";
}

// ── Step Navigation & Stepper UI ─────────────────────────────────────────
function setStep(stepName) {
    state.step = stepName;

    // Panels
    const panels = {
        upload: document.getElementById("stepUploadSection"),
        verifying: document.getElementById("stepVerifyingSection"),
        summary: document.getElementById("stepValidationSummarySection"),
        processing: document.getElementById("stepProcessingSection"),
        result: document.getElementById("stepResultSection"),
    };

    Object.keys(panels).forEach((k) => {
        if (panels[k]) {
            if (k === stepName) {
                panels[k].classList.remove("d-none");
            } else {
                panels[k].classList.add("d-none");
            }
        }
    });

    // Stepper nodes
    const node1 = document.getElementById("stepNode1");
    const node2 = document.getElementById("stepNode2");
    const node3 = document.getElementById("stepNode3");
    const conn1 = document.getElementById("stepConnector1");
    const conn2 = document.getElementById("stepConnector2");

    if (!node1 || !node2 || !node3) return;

    node1.className = "step-node";
    node2.className = "step-node";
    node3.className = "step-node";
    conn1?.classList.remove("active");
    conn2?.classList.remove("active");

    if (stepName === "upload") {
        node1.classList.add("active");
    } else if (stepName === "verifying" || stepName === "summary") {
        node1.classList.add("completed");
        conn1?.classList.add("active");
        node2.classList.add("active");
    } else if (stepName === "processing" || stepName === "result") {
        node1.classList.add("completed");
        conn1?.classList.add("active");
        node2.classList.add("completed");
        conn2?.classList.add("active");
        node3.classList.add("active");
    }
}

// ── View Switcher (Import Workspace vs History) ──────────────────────────
function initViewSwitcher() {
    const importBtn = document.getElementById("viewImportBtn");
    const historyBtn = document.getElementById("viewHistoryBtn");
    const importView = document.getElementById("importWorkspaceView");
    const historyView = document.getElementById("historyView");
    const historyStartNewBtn = document.getElementById("historyStartNewImportBtn");
    const refreshHistoryBtn = document.getElementById("refreshHistoryBtn");

    function switchToImport() {
        importBtn?.classList.add("active");
        historyBtn?.classList.remove("active");
        importView?.classList.remove("d-none");
        historyView?.classList.add("d-none");
    }

    function switchToHistory() {
        historyBtn?.classList.add("active");
        importBtn?.classList.remove("active");
        historyView?.classList.remove("d-none");
        importView?.classList.add("d-none");
        loadHistory();
    }

    importBtn?.addEventListener("click", switchToImport);
    historyBtn?.addEventListener("click", switchToHistory);
    historyStartNewBtn?.addEventListener("click", () => {
        resetImportUI();
        switchToImport();
    });
    refreshHistoryBtn?.addEventListener("click", () => loadHistory(1));
}

// ── Client-side Validation Alerts ────────────────────────────────────────
function showClientAlert(message) {
    const alertBox = document.getElementById("clientValidationAlert");
    const msgBox = document.getElementById("clientValidationMessage");
    if (alertBox && msgBox) {
        msgBox.textContent = message;
        alertBox.classList.remove("d-none");
    }
}

function hideClientAlert() {
    const alertBox = document.getElementById("clientValidationAlert");
    if (alertBox) {
        alertBox.classList.add("d-none");
    }
}

// ── File Selection & Dropzone ────────────────────────────────────────────
export function initUpload() {
    const form = document.getElementById("uploadForm");
    const fileInput = document.getElementById("file");
    const dropzone = document.getElementById("dropzone");
    const browseLink = document.getElementById("browseLink");
    const dropzonePrompt = document.getElementById("dropzonePrompt");
    const filePreviewCard = document.getElementById("fileSelectedCard");
    const selectedFileName = document.getElementById("selectedFileName");
    const selectedFileSize = document.getElementById("selectedFileSize");
    const clearSelectedFileBtn = document.getElementById("clearSelectedFileBtn");
    const uploadBtn = document.getElementById("uploadBtn");
    const dismissAlertBtn = document.getElementById("dismissClientAlertBtn");

    if (!form || !fileInput) return;

    dismissAlertBtn?.addEventListener("click", hideClientAlert);

    // Browse click
    browseLink?.addEventListener("click", (e) => {
        e.preventDefault();
        fileInput.click();
    });

    dropzone?.addEventListener("click", (e) => {
        if (e.target !== browseLink && !fileInput.files.length) {
            fileInput.click();
        }
    });

    // Drag & Drop
    ["dragenter", "dragover"].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add("drag-over");
        });
    });

    ["dragleave", "drop"].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove("drag-over");
        });
    });

    dropzone?.addEventListener("drop", (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files.length) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener("change", () => {
        if (fileInput.files.length) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    clearSelectedFileBtn?.addEventListener("click", () => {
        clearSelectedFile();
    });

    function handleFileSelect(file) {
        hideClientAlert();

        if (!file) return;

        // 1. Check file extension (.xlsx or .xls)
        const validExtensions = [".xlsx", ".xls"];
        const fileName = file.name.toLowerCase();
        const hasValidExt = validExtensions.some((ext) => fileName.endsWith(ext));

        if (!hasValidExt) {
            showClientAlert("Invalid file format. Please upload an Excel spreadsheet (.xlsx or .xls).");
            clearSelectedFile();
            return;
        }

        // 2. Check file size (Max 10 MB = 10 * 1024 * 1024 bytes)
        const maxSizeBytes = 10 * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            showClientAlert(`File exceeds the 10 MB limit (${sizeMB} MB). Please reduce or split your spreadsheet.`);
            clearSelectedFile();
            return;
        }

        // Put file in input if assigned from drag-and-drop
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Update preview UI
        selectedFileName.textContent = file.name;
        selectedFileSize.textContent = formatBytes(file.size);
        filePreviewCard.classList.remove("d-none");
        dropzonePrompt.classList.add("d-none");
        uploadBtn.disabled = false;
    }

    function clearSelectedFile() {
        fileInput.value = "";
        filePreviewCard?.classList.add("d-none");
        dropzonePrompt?.classList.remove("d-none");
        if (uploadBtn) uploadBtn.disabled = true;
    }

    let isUploading = false;
    // Submit handler: Upload file & proceed to verification
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (isUploading) return;
        if (!fileInput.files.length) return;

        isUploading = true;
        const file = fileInput.files[0];
        state.currentFilename = file.name;

        // Transition to verifying state
        setStep("verifying");
        document.getElementById("verificationStateTitle").textContent = "Uploading & Verifying Rows...";
        document.getElementById("verificationCurrentStatus").textContent = "Parsing DepEd SF-1 structure...";

        const fd = new FormData();
        fd.append("file", file);

        try {
            const uploadRes = await fetch("/import/upload", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
                body: fd,
            });
            const uploadData = await uploadRes.json();

            if (!uploadRes.ok) {
                setStep("upload");
                showError("Upload Failed", apiErrorMessage(uploadData) || "Could not upload the file.");
                return;
            }

            state.set(uploadData.data.id, file.name);

            // Trigger server-side row validation
            await doValidate();
        } catch (err) {
            setStep("upload");
            showError("Upload Failed", err.message || "Network communication error.");
        } finally {
            isUploading = false;
        }
    });
}

// ── Row Verification ─────────────────────────────────────────────────────
async function doValidate() {
    document.getElementById("verificationStateTitle").textContent = "Verifying Spreadsheet Rows...";
    document.getElementById("verificationCurrentStatus").textContent = "Scanning records, checking duplicates and section alignments...";

    try {
        const r = await fetch(`/import/${state.get()}/validate`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
        });
        const d = await r.json();

        if (!r.ok) {
            setStep("upload");
            showError("Validation Failed", apiErrorMessage(d) || "Validation failed.");
            return;
        }

        const data = d.data;
        state.totalRows = data.total_rows || 0;
        state.validRows = data.valid_count || 0;
        state.errorRows = data.error_count || 0;
        state.warningRows = data.warning_count || 0;

        renderValidationSummary(data);
        loadHistory();
    } catch (err) {
        setStep("upload");
        showError("Validation Failed", err.message || "Network error occurred.");
    }
}

// ── Render Validation Summary & Interactive Prompt ───────────────────────
function renderValidationSummary(data) {
    setStep("summary");

    document.getElementById("summaryFilenameLabel").textContent = state.currentFilename || "spreadsheet.xlsx";
    document.getElementById("statTotalRows").textContent = data.total_rows || 0;
    document.getElementById("statValidRows").textContent = data.valid_count || 0;
    document.getElementById("statErrorRows").textContent = data.error_count || 0;
    document.getElementById("statWarningRows").textContent = data.warning_count || 0;

    const calloutContainer = document.getElementById("validationCalloutContainer");
    const previewBox = document.getElementById("summaryIssuesPreviewBox");
    const downloadErrorBtn = document.getElementById("downloadErrorReportBtn");
    const openInspectorBtn = document.getElementById("openIssueInspectorBtn");
    const proceedBtn = document.getElementById("proceedImportBtn");

    const hasErrors = (data.error_count || 0) > 0;
    const hasWarnings = (data.warning_count || 0) > 0;

    // Reset buttons
    if (downloadErrorBtn) {
        downloadErrorBtn.href = `/import/${state.get()}/export-errors`;
        if (hasErrors || hasWarnings) {
            downloadErrorBtn.classList.remove("d-none");
        } else {
            downloadErrorBtn.classList.add("d-none");
        }
    }

    if (openInspectorBtn) {
        if (hasErrors || hasWarnings) {
            openInspectorBtn.classList.remove("d-none");
        } else {
            openInspectorBtn.classList.add("d-none");
        }
    }

    // Dynamic banner content & duplicate prompt
    if (!hasErrors && !hasWarnings) {
        calloutContainer.innerHTML = `
            <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 mb-0" role="alert">
                <i class="fas fa-circle-check fs-3 text-success flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">Spreadsheet Verified Successfully!</h6>
                    <p class="small mb-0">All <strong>${data.total_rows}</strong> student records passed data integrity and duplicate checks. Ready to import.</p>
                </div>
            </div>`;
        previewBox?.classList.add("d-none");

        proceedBtn.innerHTML = `<i class="fas fa-check-circle me-1.5"></i> Complete Import (${data.valid_count} Students)`;
        proceedBtn.disabled = false;
    } else {
        const duplicateWarning = `<div class="alert alert-warning border-warning-subtle d-flex align-items-start gap-3 p-3 rounded-3 mb-0" role="alert">
            <i class="fas fa-triangle-exclamation fs-3 text-warning flex-shrink-0 mt-0.5"></i>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <h6 class="fw-bold mb-0 text-dark">Issues Detected in Spreadsheet</h6>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold" id="calloutInspectIssuesBtn">
                        <i class="fas fa-search me-1"></i> Inspect Issues (${data.error_count + data.warning_count})
                    </button>
                </div>
                <p class="small text-muted mb-2">Duplicate LRNs, invalid entries, or missing required fields were found. Rows with errors will be automatically skipped.</p>
                <div class="d-flex align-items-center gap-2 small">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">${data.valid_count} Valid Ready</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">${data.error_count} Errors (Blocked)</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">${data.warning_count} Warnings</span>
                </div>
            </div>
        </div>`;
        calloutContainer.innerHTML = duplicateWarning;

        // Load preview of top issues
        previewBox?.classList.remove("d-none");
        loadSummaryIssuePreview();

        document.getElementById("calloutInspectIssuesBtn")?.addEventListener("click", () => {
            openIssueInspector();
        });

        if (data.valid_count > 0) {
            proceedBtn.innerHTML = `<i class="fas fa-file-import me-1.5"></i> Proceed with Valid Rows (${data.valid_count})`;
            proceedBtn.disabled = false;
        } else {
            proceedBtn.innerHTML = `<i class="fas fa-ban me-1.5"></i> No Valid Rows to Import`;
            proceedBtn.disabled = true;
        }
    }

    // Rebind action buttons
    rebind("proceedImportBtn", () => doProcess());
    rebind("cancelImportBtn", () => resetImportUI());
    rebind("reuploadFileBtn", () => resetImportUI());
    rebind("openIssueInspectorBtn", () => openIssueInspector());
    rebind("viewAllIssuesLink", (e) => {
        e.preventDefault();
        openIssueInspector();
    });
}

// ── Load Issues Preview List ─────────────────────────────────────────────
async function loadSummaryIssuePreview() {
    const listEl = document.getElementById("summaryIssuesList");
    if (!listEl || !state.get()) return;

    try {
        const r = await fetch(`/import/${state.get()}/issues?per_page=4`, {
            headers: { Accept: "application/json" },
        });
        const d = await r.json();

        if (!d.data || !d.data.length) {
            listEl.innerHTML = '<div class="text-muted small py-1 px-2">No detailed issues.</div>';
            return;
        }

        listEl.innerHTML = d.data
            .map((i) => {
                const isErr = i.severity === "error";
                const badgeDotClass = isErr ? "dot-danger" : "dot-warning";
                const icon = isErr ? "fa-circle-xmark text-danger" : "fa-triangle-exclamation text-warning";
                return `
                <div class="issue-preview-item">
                    <i class="fas ${icon} mt-1 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fw-semibold text-dark">Row ${i.row_number ?? "—"} &bull; ${label(i.issue_type)}</span>
                            <span class="badge-dot ${badgeDotClass}">${label(i.severity)}</span>
                        </div>
                        <div class="text-muted small mt-0.5">${esc(i.message)}</div>
                    </div>
                </div>`;
            })
            .join("");
    } catch (e) {
        listEl.innerHTML = '<div class="text-muted small py-1 px-2">Unable to load issues preview.</div>';
    }
}

let isConfirming = false;

// ── Processing (Confirm & Save) ──────────────────────────────────────────
async function doProcess() {
    if (isConfirming) return;
    isConfirming = true;
    
    const proceedBtn = document.getElementById("proceedImportBtn");
    if (proceedBtn) proceedBtn.disabled = true;

    setStep("processing");

    const total = state.totalRows || 0;
    document.getElementById("processingProgressBar").style.width = "0%";
    document.getElementById("processingRowCounter").textContent = `0 / ${total} rows`;
    document.getElementById("processingPercentCounter").textContent = "0%";
    document.getElementById("processingSuccessCounter").textContent = "0";
    document.getElementById("processingFailedCounter").textContent = "0";
    document.getElementById("processingStatusText").textContent = "Enrolling students and resolving sections...";

    const confirmRes = fetch(`/import/${state.get()}/confirm`, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
    });
    const stopProgressPoll = startProgressPolling();

    try {
        const r = await confirmRes;
        stopProgressPoll();
        const d = await r.json();

        if (!r.ok) {
            setStep("summary");
            showError("Processing Failed", apiErrorMessage(d) || "Could not complete import.");
            return;
        }

        fillProcessingUI(d.data);
        renderResult(d.data);
        loadHistory();
    } catch (err) {
        stopProgressPoll();
        setStep("summary");
        showError("Processing Failed", err.message || "An unexpected error occurred during processing.");
    } finally {
        isConfirming = false;
    }
}

function startProgressPolling(intervalMs = 1000) {
    let stopped = false;
    const stop = () => {
        stopped = true;
    };

    const tick = async () => {
        if (stopped) return;
        try {
            const r = await fetch(`/import/${state.get()}/status`, {
                headers: { Accept: "application/json" },
            });
            const d = await r.json();
            if (!stopped && d) fillProcessingUI(d);
        } catch (e) {
            /* transient poll error */
        }
        if (!stopped) setTimeout(tick, intervalMs);
    };

    tick();
    return stop;
}

function fillProcessingUI(data) {
    const total = data.total_rows || 1;
    const processed = (data.success_count || 0) + (data.failed_count || 0);
    const pct = Math.min(100, Math.round((processed / total) * 100));

    const pBar = document.getElementById("processingProgressBar");
    if (pBar) pBar.style.width = pct + "%";

    const pRow = document.getElementById("processingRowCounter");
    if (pRow) pRow.textContent = `${processed} / ${data.total_rows || 0} rows`;

    const pPct = document.getElementById("processingPercentCounter");
    if (pPct) pPct.textContent = pct + "%";

    const pSucc = document.getElementById("processingSuccessCounter");
    if (pSucc) pSucc.textContent = data.success_count || 0;

    const pFail = document.getElementById("processingFailedCounter");
    if (pFail) pFail.textContent = data.failed_count || 0;
}

// ── Completion & Result Screen ───────────────────────────────────────────
function renderResult(data) {
    setStep("result");

    const isSuccess = data.status === "completed";
    const icon = document.getElementById("resultIcon");
    const iconWrapper = document.getElementById("resultIconWrapper");
    const title = document.getElementById("resultCardTitle");
    const subtitle = document.getElementById("resultCardSubtitle");
    const viewIssuesBtn = document.getElementById("resultViewIssuesBtn");

    document.getElementById("resultTotalCount").textContent = data.total_rows || 0;
    document.getElementById("resultSuccessCount").textContent = data.success_count || 0;
    document.getElementById("resultFailedCount").textContent = data.failed_count || 0;

    if (isSuccess) {
        icon.className = "fas fa-circle-check fa-3x text-success";
        iconWrapper.className = "result-icon-circle mx-auto mb-3 bg-success-subtle";
        title.textContent = "Bulk Import Completed!";
        subtitle.textContent = `Successfully imported ${data.success_count} student record(s) into the system.`;
        viewIssuesBtn?.classList.add("d-none");
    } else {
        icon.className = "fas fa-triangle-exclamation fa-3x text-warning";
        iconWrapper.className = "result-icon-circle mx-auto mb-3 bg-warning-subtle";
        title.textContent = "Import Completed with Issues";
        subtitle.textContent = `${data.success_count} students imported, ${data.failed_count} row(s) skipped due to errors.`;
        viewIssuesBtn?.classList.remove("d-none");
    }

    rebind("resultStartNewBtn", () => resetImportUI());
    rebind("resultViewIssuesBtn", () => openIssueInspector());
    rebind("resultGoHistoryBtn", () => {
        document.getElementById("viewHistoryBtn")?.click();
    });
}

// ── Reset Import UI ──────────────────────────────────────────────────────
export function resetImportUI() {
    state.clear();
    const fileInput = document.getElementById("file");
    if (fileInput) fileInput.value = "";

    document.getElementById("fileSelectedCard")?.classList.add("d-none");
    document.getElementById("dropzonePrompt")?.classList.remove("d-none");
    const uploadBtn = document.getElementById("uploadBtn");
    if (uploadBtn) uploadBtn.disabled = true;

    hideClientAlert();
    setStep("upload");
}

// ── Transaction History Table ────────────────────────────────────────────
let historyPage = 1;

export async function loadHistory(page = 1) {
    historyPage = page;
    const tbody = document.getElementById("historyBody");
    if (!tbody) return;

    try {
        const r = await fetch(`/import/history/list?per_page=15&page=${historyPage}`, {
            headers: { Accept: "application/json" },
        });
        const d = await r.json();

        // Update badge count
        const badge = document.getElementById("historyCountBadge");
        if (badge && d.meta) {
            badge.textContent = d.meta.total || d.data?.length || 0;
        }

        if (!d.data || d.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary opacity-50"></i>
                        <p class="fw-semibold mb-1">No import transactions recorded yet.</p>
                        <p class="small text-muted mb-0">Use the Import Workspace to upload your first DepEd SF-1 spreadsheet.</p>
                    </td>
                </tr>`;
            document.getElementById("historyPagination").innerHTML = "";
            return;
        }

        tbody.innerHTML = d.data
            .map((h) => {
                const statusDot = dotClass(h.status);
                const statusName = label(h.status);
                const transactionId = `#${h.id}`;

                let resultHtml = '<span class="text-muted">—</span>';
                if (h.status === "completed") {
                    resultHtml = `<span class="text-success fw-bold">${h.success_count ?? 0} ✓</span>`;
                } else if (h.status === "completed_with_issues" || ((h.failed_count || 0) > 0 && (h.success_count || 0) > 0)) {
                    resultHtml = `<span class="text-success fw-bold">${h.success_count ?? 0} ✓</span> <span class="text-muted mx-0.5">/</span> <span class="text-danger fw-bold">${h.failed_count ?? 0} ✗</span>`;
                } else if (h.status === "failed" || (h.failed_count || 0) > 0) {
                    resultHtml = `<span class="text-danger fw-bold">${h.failed_count ?? 0} ✗</span>`;
                } else if ((h.total_rows || 0) > 0) {
                    resultHtml = `<span class="text-muted">${h.total_rows} rows</span>`;
                }

                return `
                <tr>
                    <td>
                        <span class="fw-bold font-monospace text-dark">${transactionId}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2" title="${esc(h.original_filename)}">
                            <i class="far fa-file-excel text-success fs-5 flex-shrink-0"></i>
                            <span class="text-truncate fw-medium text-dark" style="max-width: 100%;">
                                ${esc(h.original_filename)}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1.5 text-truncate" title="${esc(h.created_by?.name ?? "Admin")}">
                            <i class="far fa-user text-muted flex-shrink-0"></i>
                            <span class="text-dark">${esc(h.created_by?.name ?? "Admin")}</span>
                        </div>
                    </td>
                    <td>
                        <div class="small">${resultHtml}</div>
                    </td>
                    <td>
                        <span class="small text-dark">${formatDateTime(h.created_at)}</span>
                    </td>
                    <td>
                        <span class="badge-dot dot-${statusDot}">${statusName}</span>
                    </td>
                    <td>
                        <small class="text-muted font-monospace">${duration(h.created_at, h.updated_at)}</small>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary px-2.5 py-1" onclick="viewImportTransaction(${h.id})" title="View Transaction Issues">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>`;
            })
            .join("");

        // Pagination
        if (d.meta && d.meta.last_page > 1) {
            const cp = d.meta.current_page;
            const lp = d.meta.last_page;
            let p = `<ul class="pagination pagination-sm mb-0">`;
            p += `<li class="page-item ${cp <= 1 ? "disabled" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${cp - 1})">Prev</a></li>`;
            for (let i = Math.max(1, cp - 1); i <= Math.min(lp, cp + 1); i++) {
                p += `<li class="page-item ${i === cp ? "active" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${i})">${i}</a></li>`;
            }
            p += `<li class="page-item ${cp >= lp ? "disabled" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${cp + 1})">Next</a></li></ul>`;
            document.getElementById("historyPagination").innerHTML = p;
        } else {
            document.getElementById("historyPagination").innerHTML = "";
        }
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load history.</td></tr>`;
    }
}

window.historyPageClick = function (p) {
    loadHistory(p);
    return false;
};

// ── View Transaction & Open Inspector ────────────────────────────────────
window.viewImportTransaction = async function (id) {
    try {
        const r = await fetch(`/import/${id}`, {
            headers: { Accept: "application/json" },
        });
        const d = await r.json();
        state.set(id, d.data.original_filename);
        openIssueInspector();
    } catch (e) {
        showError("Unable to Load", "Could not load transaction details.");
    }
};

// ── Issues Inspector & Modal ─────────────────────────────────────────────
let issuesPage = 1;

export function openIssueInspector() {
    if (!state.get()) return;

    document.getElementById("issueInspectorSubtitle").textContent = `Transaction #${state.get()} &bull; ${state.currentFilename || "spreadsheet.xlsx"}`;
    const exportBtn = document.getElementById("inspectorDownloadErrorsBtn");
    if (exportBtn) {
        exportBtn.href = `/import/${state.get()}/export-errors`;
    }

    showM("issueInspectorModal");
    loadIssues(1);
}

export async function loadIssues(page = 1) {
    issuesPage = page;
    if (!state.get()) return;

    const severity = document.getElementById("severityFilter")?.value || "";
    const status = document.getElementById("statusFilter")?.value || "";
    const search = document.getElementById("issuesSearch")?.value || "";

    let url = `/import/${state.get()}/issues?per_page=15&page=${issuesPage}`;
    if (severity) url += `&severity=${severity}`;
    if (status) url += `&status=${status}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    try {
        const r = await fetch(url, { headers: { Accept: "application/json" } });
        const d = await r.json();
        const tbody = document.getElementById("issuesBody");

        if (!d.data || d.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i> No issues matching the criteria.</td></tr>';
            document.getElementById("issuesPagination").innerHTML = "";
            return;
        }

        window.__issuesMap = window.__issuesMap || {};
        d.data.forEach((issue) => {
            window.__issuesMap[issue.id] = issue;
        });

        tbody.innerHTML = d.data
            .map((i) => {
                const learnerName = parseLearnerName(i.raw_data);
                const isError = i.severity === "error";
                const dot = isError ? "dot-error" : "dot-warning";

                return `
                <tr>
                    <td><span class="fw-bold font-monospace">Row ${i.row_number ?? "—"}</span></td>
                    <td>
                        <div class="fw-medium text-dark">${esc(learnerName)}</div>
                        <small class="text-muted font-monospace">${esc(i.field ? "Field: " + i.field : "")}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">${label(i.issue_type)}</span></td>
                    <td><span class="small text-dark">${esc(i.message)}</span></td>
                    <td><span class="badge-dot ${dot}">${label(i.severity)}</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1 rounded-pill" onclick="viewIssueDetail(${i.id})" title="View Raw Data">
                            <i class="fas fa-circle-info"></i>
                        </button>
                        ${
                            i.status === "unresolved"
                                ? `<button type="button" class="btn btn-sm btn-outline-success rounded-pill" onclick="acknowledgeIssue(${i.id})">Acknowledge</button>`
                                : '<span class="text-muted small"><i class="fas fa-check text-success"></i> Acknowledged</span>'
                        }
                    </td>
                </tr>`;
            })
            .join("");

        // Pagination
        if (d.last_page > 1) {
            const cp = issuesPage;
            let p = `<ul class="pagination pagination-sm mb-0">`;
            p += `<li class="page-item ${cp <= 1 ? "disabled" : ""}"><a class="page-link" href="#" onclick="return issuesPageClick(${cp - 1})">Prev</a></li>`;
            for (let i = Math.max(1, cp - 1); i <= Math.min(d.last_page, cp + 1); i++) {
                p += `<li class="page-item ${i === cp ? "active" : ""}"><a class="page-link" href="#" onclick="return issuesPageClick(${i})">${i}</a></li>`;
            }
            p += `<li class="page-item ${cp >= d.last_page ? "disabled" : ""}"><a class="page-link" href="#" onclick="return issuesPageClick(${cp + 1})">Next</a></li></ul>`;
            document.getElementById("issuesPagination").innerHTML = p;
        } else {
            document.getElementById("issuesPagination").innerHTML = "";
        }
    } catch (e) {
        /* silent */
    }
}

window.issuesPageClick = function (p) {
    loadIssues(p);
    return false;
};

window.acknowledgeIssue = async function (id) {
    try {
        await fetch(`/import/issues/${id}/acknowledge`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
        });
        loadIssues(issuesPage);
    } catch (e) {
        /* silent */
    }
};

window.viewIssueDetail = function (id) {
    const issue = window.__issuesMap?.[id];
    if (!issue) return;

    let raw = {};
    try {
        raw = typeof issue.raw_data === "string" ? JSON.parse(issue.raw_data) : issue.raw_data || {};
    } catch (e) {
        raw = issue.raw_data || {};
    }

    const rawRows = Object.entries(raw)
        .filter(([, v]) => v !== null && v !== undefined && v !== "")
        .map(
            ([k, v]) => `
            <div class="d-flex small py-1 border-bottom">
                <span class="fw-semibold text-secondary" style="min-width:180px;">${esc(k)}</span>
                <span class="text-dark font-monospace">${esc(String(v))}</span>
            </div>`,
        )
        .join("");

    document.getElementById("issueDetailSummary").innerHTML = `
        <div class="row g-2 small p-3 bg-light rounded-3 mb-3 border">
            <div class="col-3"><span class="text-muted">Row Number:</span><br><span class="fw-bold">Row ${issue.row_number}</span></div>
            <div class="col-3"><span class="text-muted">Issue Type:</span><br><span class="fw-bold">${label(issue.issue_type)}</span></div>
            <div class="col-3"><span class="text-muted">Severity:</span><br><span class="badge-dot ${issue.severity === "error" ? "dot-error" : "dot-warning"}">${label(issue.severity)}</span></div>
            <div class="col-3"><span class="text-muted">Status:</span><br><span class="badge-dot ${issue.status === "unresolved" ? "dot-warning" : "dot-acknowledged"}">${label(issue.status)}</span></div>
        </div>`;

    document.getElementById("issueDetailMessage").innerHTML = `
        <div class="border rounded-3 p-3 bg-white mb-3">
            <div class="small text-muted mb-1 fw-semibold">Validation Diagnostic Message</div>
            <div class="text-danger fw-medium">${esc(issue.message)}</div>
        </div>`;

    document.getElementById("issueDetailRawData").innerHTML = rawRows
        ? `<div class="border rounded-3 p-3 bg-white">
               <div class="small text-muted mb-2 fw-semibold"><i class="fas fa-table me-1"></i> Row Raw Data from Spreadsheet</div>
               <div style="max-height: 250px; overflow-y: auto;">${rawRows}</div>
           </div>`
        : "";

    showM("issueDetailModal");
};

// ── Acknowledge All & Filters ────────────────────────────────────────────
export function initAckAll() {
    document.getElementById("acknowledgeAllBtn")?.addEventListener("click", () => {
        if (!state.get()) return;
        showConfirm("Acknowledge All Issues", "Are you sure you want to mark all unresolved issues in this import as acknowledged?", async () => {
            try {
                await fetch(`/import/${state.get()}/issues/acknowledge-all`, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
                });
                loadIssues(1);
            } catch (e) {
                /* silent */
            }
        });
    });
}

export function initFilters() {
    ["severityFilter", "statusFilter"].forEach((id) => {
        document.getElementById(id)?.addEventListener("change", () => loadIssues(1));
    });
    let timer;
    document.getElementById("issuesSearch")?.addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(() => loadIssues(1), 350);
    });
}

// ── Utility Helpers ──────────────────────────────────────────────────────
function apiErrorMessage(d) {
    if (!d) return "An unexpected error occurred.";
    if (d.errors && typeof d.errors === "object") {
        const first = Object.values(d.errors)[0];
        if (Array.isArray(first) && first.length) return first[0];
    }
    return d.message || "An error occurred while processing the request.";
}

function showError(title, msg) {
    const t = document.getElementById("errorModalTitle");
    const b = document.getElementById("errorModalBody");
    if (t) t.textContent = title;
    if (b) b.innerHTML = `<p class="mb-0 text-dark">${esc(msg)}</p>`;
    showM("errorModal");
}

function showConfirm(title, msg, onYes) {
    const t = document.getElementById("confirmModalTitle");
    const b = document.getElementById("confirmModalBody");
    if (t) t.textContent = title;
    if (b) b.innerHTML = `<p class="mb-0 text-dark">${esc(msg)}</p>`;

    const yesBtn = document.getElementById("confirmModalYes");
    if (yesBtn) {
        const clone = yesBtn.cloneNode(true);
        yesBtn.parentNode.replaceChild(clone, yesBtn);
        clone.addEventListener("click", () => {
            hideM("confirmModal");
            onYes();
        });
    }
    showM("confirmModal");
}

function rebind(id, handler) {
    const el = document.getElementById(id);
    if (!el) return;
    const clone = el.cloneNode(true);
    el.parentNode.replaceChild(clone, el);
    clone.addEventListener("click", handler);
}

function esc(str) {
    const d = document.createElement("div");
    d.appendChild(document.createTextNode(str ?? ""));
    return d.innerHTML;
}

function label(s) {
    return (s ?? "")
        .replace(/_/g, " ")
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

function dotClass(status) {
    const map = {
        completed: "completed",
        completed_with_issues: "completed_with_issues",
        failed: "failed",
        pending: "pending",
        validated: "validated",
        processing: "processing",
        cancelled: "cancelled",
    };
    return map[status] || status;
}

function duration(created, updated) {
    if (!created || !updated) return "—";
    const diff = new Date(updated) - new Date(created);
    const secs = Math.max(0, Math.floor(diff / 1000));
    if (secs < 60) return secs + "s";
    return Math.floor(secs / 60) + "m " + (secs % 60) + "s";
}

function formatDateTime(dateStr) {
    if (!dateStr) return "—";
    const d = new Date(dateStr);
    const date = d.toLocaleDateString("en-PH", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
    const time = d.toLocaleTimeString("en-PH", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
    return date + ", " + time;
}

function formatBytes(bytes, decimals = 1) {
    if (!+bytes) return "0 Bytes";
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

function parseLearnerName(raw) {
    if (!raw) return "—";
    if (typeof raw === "string") {
        try {
            raw = JSON.parse(raw);
        } catch (e) {
            return raw;
        }
    }
    return (
        raw["Learner Name"] ||
        raw.learnerName ||
        (raw.firstName && raw.lastName ? `${raw.lastName}, ${raw.firstName}` : null) ||
        raw.lrn ||
        "—"
    );
}

// ── Initialize on DOM Ready ──────────────────────────────────────────────
export function init() {
    initViewSwitcher();
    initUpload();
    initAckAll();
    initFilters();
    loadHistory();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}
