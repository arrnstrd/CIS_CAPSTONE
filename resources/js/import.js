/**
 * Bulk Import — UI controller.
 *
 * Handles the full import lifecycle:
 *   Upload → Validate → Confirm (process) → Result
 *
 * All state is managed in a single ImportState object to avoid
 * race conditions from multiple polling loops.
 */
class ImportState {
    constructor() {
        this.importId = null;
        this.pollTimer = null;
        this.totalRows = 0;
        this.step = "idle"; // idle | uploading | validating | validating_done | processing | done
    }

    set(id) {
        this.importId = id;
    }
    get() {
        return this.importId;
    }
    clear() {
        this.importId = null;
        this.stopPoll();
        this.step = "idle";
    }

    startPoll(fn, ms = 1500) {
        this.stopPoll();
        this.pollTimer = setInterval(fn, ms);
    }
    stopPoll() {
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    isProcessing() {
        return this.step === "processing";
    }
}

const state = new ImportState();

// ── Bootstrap modal cache ──────────────────────────────────────────────
const modalCache = {};
function modal(id) {
    if (!modalCache[id])
        modalCache[id] = new bootstrap.Modal(document.getElementById(id));
    return modalCache[id];
}
const showM = (id) => modal(id).show();
const hideM = (id) => modal(id).hide();

// ── CSRF ───────────────────────────────────────────────────────────────
function csrf() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : "";
}

// ── Upload ─────────────────────────────────────────────────────────────
export function initUpload() {
    const form = document.getElementById("uploadForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById("file");
        if (!fileInput.files.length) return;

        state.step = "uploading";
        const btn = document.getElementById("uploadBtn");
        btn.disabled = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';

        // Close modal, show inline progress
        hideM("uploadModal");
        document.getElementById("inlineProgress").classList.remove("d-none");
        document.getElementById("inlineProgressText").textContent =
            "Uploading file...";

        const fd = new FormData();
        fd.append("file", fileInput.files[0]);

        try {
            const r = await fetch("/import/upload", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
                body: fd,
            });
            const d = await r.json();

            if (!r.ok) {
                showError(
                    "Upload Failed",
                    apiErrorMessage(d) || "Could not upload the file.",
                );
                resetUI();
                return;
            }

            state.set(d.data.id);
            await doValidate();
        } catch (err) {
            showError("Upload Failed", err.message || "Network error.");
            resetUI();
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload me-2"></i> Upload';
        }
    });

    // Dropzone
    const dz = document.getElementById("dropzone");
    const fi = document.getElementById("file");
    document.getElementById("browseLink")?.addEventListener("click", (e) => {
        e.preventDefault();
        fi.click();
    });
    dz?.addEventListener("click", () => fi.click());
    dz?.addEventListener("dragover", (e) => {
        e.preventDefault();
        dz.style.borderColor = "#0d6efd";
    });
    dz?.addEventListener("dragleave", () => {
        dz.style.borderColor = "#d0d5dd";
    });
    dz?.addEventListener("drop", (e) => {
        e.preventDefault();
        dz.style.borderColor = "#d0d5dd";
        if (e.dataTransfer.files.length) fi.files = e.dataTransfer.files;
        showFileInfo();
    });
    fi?.addEventListener("change", showFileInfo);
    document.getElementById("clearFileBtn")?.addEventListener("click", () => {
        fi.value = "";
        document.getElementById("fileInfo").classList.add("d-none");
    });

    // Reset the file selection whenever the upload modal is dismissed
    // (Cancel, close X, backdrop, or ESC) so a previously chosen file is
    // not pre-selected or re-uploaded the next time the modal is opened.
    // Safe for the in-flight upload: the FormData already captured the File
    // synchronously before this async "hidden" event fires.
    document
        .getElementById("uploadModal")
        ?.addEventListener("hidden.bs.modal", () => {
            fi.value = "";
            document.getElementById("fileInfo").classList.add("d-none");
        });
}

function showFileInfo() {
    const fi = document.getElementById("file");
    if (fi.files.length) {
        document.getElementById("fileName").textContent = fi.files[0].name;
        document.getElementById("fileInfo").classList.remove("d-none");
    }
}

// ── Validate ───────────────────────────────────────────────────────────
async function doValidate() {
    state.step = "validating";
    document.getElementById("inlineProgressText").textContent =
        "Validating spreadsheet...";

    try {
        const r = await fetch(`/import/${state.get()}/validate`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" },
        });
        const d = await r.json();

        document.getElementById("inlineProgress").classList.add("d-none");

        if (!r.ok) {
            showError(
                "Validation Failed",
                apiErrorMessage(d) || "Validation error.",
            );
            resetUI();
            return;
        }

        state.step = "validating_done";
        state.totalRows = d.data.total_rows || 0;
        showValidationResult(d.data);
        loadHistory();
    } catch (err) {
        document.getElementById("inlineProgress").classList.add("d-none");
        showError("Validation Failed", err.message || "Network error.");
        resetUI();
    }
}

// ── Validation result modal ────────────────────────────────────────────
function showValidationResult(data) {
    const hasErrors = data.error_count > 0;
    const hasWarnings = data.warning_count > 0;
    const body = document.getElementById("validationBody");
    const footer = document.getElementById("validationFooter");
    const title = document.getElementById("validationTitle");

    const summary = `
        <div class="row g-3 text-center mb-4">
            <div class="col-3"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold">${data.total_rows}</div><div class="text-muted small">Total Rows</div></div></div>
            <div class="col-3"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-success">${data.valid_count}</div><div class="text-muted small">Valid</div></div></div>
            <div class="col-3"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-danger">${data.error_count}</div><div class="text-muted small">Errors</div></div></div>
            <div class="col-3"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-warning">${data.warning_count}</div><div class="text-muted small">Warnings</div></div></div>
        </div>`;

    if (!hasErrors && !hasWarnings) {
        title.innerHTML =
            '<i class="fas fa-check-circle me-1"></i> Validation Passed';
        body.innerHTML =
            summary +
            '<p class="text-center mb-0">All rows passed validation. Ready to import.</p>';
        footer.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
             <button type="button" class="btn btn-success" id="modalProceedBtn"><i class="fas fa-check me-1"></i> Proceed</button>`;
    } else {
        title.innerHTML =
            '<i class="fas fa-exclamation-triangle me-1"></i> Validation Issues Found';
        const readyMsg =
            data.valid_count > 0
                ? `<div class="alert alert-success small d-flex align-items-center gap-2"><i class="fas fa-check-circle me-1"></i> ${data.valid_count} row(s) are ready to import. ${data.error_count} row(s) with errors will be skipped.</div>`
                : "";
        body.innerHTML =
            summary +
            readyMsg +
            '<div class="alert alert-warning small">Some rows have issues, but you can still import the valid rows.</div>' +
            '<h6 class="fw-semibold mb-2 mt-3"><i class="fas fa-list me-1"></i> Top Issues</h6>' +
            '<div id="validationIssuePreview" class="list-group list-group-flush mb-3"></div>';
        footer.innerHTML = `<a class="btn btn-outline-secondary" href="/import/${state.get()}/export-errors"><i class="fas fa-file-download me-1"></i> Download Error Report</a>
             <button type="button" class="btn btn-outline-secondary" id="modalReuploadBtn"><i class="fas fa-file-upload me-1"></i> Re-upload</button>
             <button type="button" class="btn btn-outline-primary" id="modalReviewBtn" data-bs-dismiss="modal"><i class="fas fa-search me-1"></i> Review Issues</button>
             ${
                 data.valid_count > 0
                     ? `<button type="button" class="btn btn-success" id="modalProceedBtn"><i class="fas fa-check me-1"></i> Proceed (${data.valid_count} rows)</button>`
                     : ""
             }`;
    }

    // Wire buttons (fresh listeners via cloning to avoid duplicates)
    rebind("modalProceedBtn", () => {
        hideM("validationModal");
        doProcess();
    });
    rebind("modalReuploadBtn", () => {
        hideM("validationModal");
        resetUI();
    });
    if (document.getElementById("modalReviewBtn")) {
        rebind("modalReviewBtn", () => {
            hideM("validationModal");
            switchTab("issues");
            loadIssues();
        });
    }

    showM("validationModal");

    if (hasErrors || hasWarnings) loadValidationIssuePreview();
}

/**
 * Load the first few issues into the validation modal so the user can see
 * what is wrong immediately instead of having to open the Issues tab.
 */
async function loadValidationIssuePreview() {
    const container = document.getElementById("validationIssuePreview");
    if (!container) return;

    try {
        const r = await fetch(`/import/${state.get()}/issues?per_page=5`, {
            headers: { Accept: "application/json" },
        });
        const d = await r.json();

        if (!d.data || !d.data.length) {
            container.innerHTML =
                '<div class="text-muted small py-2">No issues to display.</div>';
            return;
        }

        container.innerHTML = d.data
            .map(
                (i) => `
            <div class="list-group-item d-flex align-items-start gap-2 py-2">
                <span class="badge-dot dot-${i.severity} mt-1"></span>
                <div class="small w-100">
                    <span class="text-muted me-2">Row ${i.row_number ?? "—"}</span>
                    <span class="text-muted">${label(i.issue_type)}</span>
                    <div class="mt-1">${esc(i.message)}</div>
                </div>
            </div>`,
            )
            .join("");
    } catch (e) {
        container.innerHTML =
            '<div class="text-muted small py-2">Could not load issues.</div>';
    }
}

// ── Process (confirm) ──────────────────────────────────────────────────
async function doProcess() {
    state.step = "processing";

    // Show processing modal — start with actual row count from validation
    const total = state.totalRows || 0;
    document.getElementById("processingBar").style.width = "0%";
    document.getElementById("processingProgress").textContent =
        `0 / ${total} rows`;
    document.getElementById("processingPercent").textContent = "0%";
    document.getElementById("processingSuccess").textContent = "0";
    document.getElementById("processingFailed").textContent = "0";
    document.getElementById("processingActivity").textContent =
        "Importing students...";
    showM("processingModal");

    // Kick off the confirm request (it processes synchronously on the
    // server) and poll /status while it runs so the progress bar shows
    // real numbers instead of sitting frozen at 0%.
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
            hideM("processingModal");
            showError(
                "Processing Failed",
                apiErrorMessage(d) || "Could not start import.",
            );
            return;
        }

        fillProcessingUI(d.data);
        hideM("processingModal");

        state.step = "done";
        showResultModal(d.data);
        loadHistory();
        if (d.data.issues_count > 0) {
            document.getElementById("issuesBadge").textContent =
                d.data.issues_count;
            document.getElementById("issuesBadge").classList.remove("d-none");
        }
    } catch (err) {
        stopProgressPoll();
        hideM("processingModal");
        showError("Processing Failed", err.message || "Unexpected error.");
    }
}

/**
 * Poll the import /status endpoint while the synchronous confirm request
 * runs, feeding real counts into the processing progress UI.
 * Returns a stop() function.
 */
function startProgressPolling(intervalMs = 1200) {
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
            /* transient poll failure — keep waiting for the next tick */
        }
        if (!stopped) setTimeout(tick, intervalMs);
    };

    tick();
    return stop;
}

function fillProcessingUI(data) {
    const pct =
        data.total_rows > 0
            ? Math.round(
                  ((data.success_count + data.failed_count) / data.total_rows) *
                      100,
              )
            : 0;
    document.getElementById("processingBar").style.width = pct + "%";
    document.getElementById("processingProgress").textContent =
        `${data.success_count + data.failed_count} / ${data.total_rows} rows`;
    document.getElementById("processingPercent").textContent = pct + "%";
    document.getElementById("processingSuccess").textContent =
        data.success_count || 0;
    document.getElementById("processingFailed").textContent =
        data.failed_count || 0;
}

// ── Result modal ───────────────────────────────────────────────────────
function showResultModal(data) {
    const isSuccess = data.status === "completed";
    const header = document.getElementById("resultHeader");
    const title = document.getElementById("resultTitle");
    const body = document.getElementById("resultBody");
    const footer = document.getElementById("resultFooter");

    if (isSuccess) {
        header.className =
            "modal-header border-0 bg-success text-white rounded-top";
        title.innerHTML =
            '<i class="fas fa-check-circle me-1"></i> Import Successful';
        body.innerHTML = `
            <div class="row g-3 text-center">
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold">${data.total_rows}</div><div class="text-muted small">Total</div></div></div>
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-success">${data.success_count}</div><div class="text-muted small">Imported</div></div></div>
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold">${data.warning_count || 0}</div><div class="text-muted small">Warnings</div></div></div>
            </div>`;
        footer.innerHTML = `<button type="button" class="btn btn-outline-secondary" onclick="resetImportUI()"><i class="fas fa-plus me-1"></i> Import Another File</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>`;
    } else {
        header.className =
            "modal-header border-0 bg-warning text-dark rounded-top";
        title.innerHTML =
            '<i class="fas fa-exclamation-triangle me-1"></i> Completed With Issues';
        body.innerHTML = `
            <div class="row g-3 text-center">
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-success">${data.success_count}</div><div class="text-muted small">Imported</div></div></div>
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-danger">${data.failed_count}</div><div class="text-muted small">Failed</div></div></div>
                <div class="col-4"><div class="border rounded-3 p-3"><div class="fs-3 fw-bold text-warning">${data.warning_count || 0}</div><div class="text-muted small">Warnings</div></div></div>
            </div>
            <div class="alert alert-warning small mt-3 mb-0">Failed rows can be reviewed in the Issues tab.</div>`;
        footer.innerHTML = `<button type="button" class="btn btn-outline-secondary" onclick="resetImportUI()"><i class="fas fa-plus me-1"></i> New Import</button>
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="setTimeout(()=>{switchTab('issues');loadIssues();},100)">View Issues</button>`;
    }

    showM("resultModal");
}

// ── History ────────────────────────────────────────────────────────────
let historyPage = 1;

export async function loadHistory(page) {
    historyPage = page || historyPage;
    try {
        const r = await fetch(
            `/import/history/list?per_page=15&page=${historyPage}`,
            { headers: { Accept: "application/json" } },
        );
        const d = await r.json();
        const tbody = document.getElementById("historyBody");

        if (!d.data || d.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5">
                <div class="text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <p class="fw-medium">No imports yet.</p>
                <p class="small">Upload a spreadsheet above to get started.</p></div></td></tr>`;
            document.getElementById("historyPagination").innerHTML = "";
            return;
        }

        tbody.innerHTML = d.data
            .map(
                (h) => `
            <tr>
                <td><small class="text-muted">#${h.id}</small></td>
                <td><small>${esc(h.original_filename)}</small></td>
                <td><small>${h.created_by?.name ?? "—"}</small></td>
                <td>
                    <small class="text-success fw-medium">${h.success_count} ✓</small>
                    <small class="text-muted"> / </small>
                    <small class="text-danger fw-medium">${h.failed_count} ✗</small>
                </td>
                <td><small>${formatDateTime(h.created_at)}</small></td>
                <td><span class="badge-dot dot-${dotClass(h.status)}">${label(h.status)}</span></td>
                <td><small class="text-muted">${duration(h.created_at, h.updated_at)}</small></td>
                <td><button class="btn btn-sm btn-outline-secondary" onclick="viewImport(${h.id})"><i class="fas fa-eye"></i></button></td>
            </tr>
        `,
            )
            .join("");

        // Pagination
        if (d.meta && d.meta.last_page > 1) {
            const cp = d.meta.current_page,
                lp = d.meta.last_page;
            let p = `<ul class="pagination pagination-sm mb-0">`;
            p += `<li class="page-item ${cp <= 1 ? "disabled" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${cp - 1})">Prev</a></li>`;
            for (let i = Math.max(1, cp - 1); i <= Math.min(lp, cp + 1); i++)
                p += `<li class="page-item ${i === cp ? "active" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${i})">${i}</a></li>`;
            p += `<li class="page-item ${cp >= lp ? "disabled" : ""}"><a class="page-link" href="#" onclick="return historyPageClick(${cp + 1})">Next</a></li></ul>`;
            document.getElementById("historyPagination").innerHTML = p;
        } else {
            document.getElementById("historyPagination").innerHTML = "";
        }
    } catch (e) {
        /* silent */
    }
}

window.historyPageClick = function (p) {
    loadHistory(p);
    return false;
};

// ── View import (from history) ─────────────────────────────────────────
window.viewImport = async function (id) {
    try {
        const r = await fetch(`/import/${id}`, {
            headers: { Accept: "application/json" },
        });
        const d = await r.json();
        state.set(id);
        document.getElementById("inlineProgress").classList.add("d-none");
        if (d.data.issues_count > 0) {
            document.getElementById("issuesBadge").textContent =
                d.data.issues_count;
            document.getElementById("issuesBadge").classList.remove("d-none");
        }
        switchTab("issues");
        loadIssues();
    } catch (e) {
        /* silent */
    }
};

// ── Issues ─────────────────────────────────────────────────────────────
let issuesPage = 1;

export async function loadIssues(page) {
    issuesPage = page || issuesPage;
    if (!state.get()) return;

    // Expose the error report download for the selected import.
    const downloadBtn = document.getElementById("downloadErrorsBtn");
    if (downloadBtn) {
        downloadBtn.href = `/import/${state.get()}/export-errors`;
        downloadBtn.classList.remove("d-none");
    }

    const severity = document.getElementById("severityFilter").value;
    const status = document.getElementById("statusFilter").value;
    const search = document.getElementById("issuesSearch").value;
    let url = `/import/${state.get()}/issues?per_page=15&page=${issuesPage}`;
    if (severity) url += `&severity=${severity}`;
    if (status) url += `&status=${status}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    try {
        const r = await fetch(url, { headers: { Accept: "application/json" } });
        const d = await r.json();
        const tbody = document.getElementById("issuesBody");

        if (!d.data || d.data.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="7" class="text-center py-4 text-muted">No issues found.</td></tr>';
            document.getElementById("issuesPagination").innerHTML = "";
            return;
        }

        // Index issues by ID for the detail modal
        window.__issuesMap = window.__issuesMap || {};
        d.data.forEach((issue) => {
            window.__issuesMap[issue.id] = issue;
        });

        tbody.innerHTML = d.data
            .map((i) => {
                const name = parseLearnerName(i.raw_data);
                return `<tr>
                <td>${i.row_number}</td>
                <td><small>${esc(name)}</small></td>
                <td><small>${label(i.issue_type)}</small></td>
                <td><span class="badge-dot dot-${i.severity}">${label(i.severity)}</span></td>
                <td><span class="badge-dot dot-${i.status}">${label(i.status)}</span></td>
                <td><small class="text-muted">${new Date(i.created_at).toLocaleDateString()}</small></td>
                <td>${
                    `<button class="btn btn-sm btn-outline-secondary me-1" onclick="viewIssueDetail(${i.id})" title="View Details"><i class="fas fa-eye"></i></button>` +
                    (i.status === "unresolved"
                        ? `<button class="btn btn-sm btn-outline-success" onclick="acknowledgeIssue(${i.id})">Acknowledge</button>`
                        : '<span class="text-muted small">Done</span>')
                }</td>
            </tr>`;
            })
            .join("");

        // Pagination
        if (d.last_page > 1) {
            const cp = issuesPage;
            let p = `<ul class="pagination pagination-sm mb-0">`;
            p += `<li class="page-item ${cp <= 1 ? "disabled" : ""}"><a class="page-link" href="#" onclick="return issuesPageClick(${cp - 1})">Prev</a></li>`;
            for (
                let i = Math.max(1, cp - 1);
                i <= Math.min(d.last_page, cp + 1);
                i++
            )
                p += `<li class="page-item ${i === cp ? "active" : ""}"><a class="page-link" href="#" onclick="return issuesPageClick(${i})">${i}</a></li>`;
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

    const raw = issue.raw_data || {};
    const rawRows = Object.entries(raw)
        .filter(([, v]) => v !== null && v !== undefined && v !== "")
        .map(
            ([k, v]) =>
                `<div class="d-flex small mb-1"><span class="fw-medium text-muted" style="min-width:140px;">${esc(k)}</span><span>${esc(String(v))}</span></div>`,
        )
        .join("");

    document.getElementById("issueDetailSummary").innerHTML = `
        <div class="row g-2 small mb-3">
            <div class="col-3"><span class="text-muted">Row:</span><br><span class="fw-medium">${issue.row_number}</span></div>
            <div class="col-3"><span class="text-muted">Type:</span><br><span class="fw-medium">${label(issue.issue_type)}</span></div>
            <div class="col-3"><span class="text-muted">Severity:</span><br><span class="badge-dot dot-${issue.severity}">${label(issue.severity)}</span></div>
            <div class="col-3"><span class="text-muted">Status:</span><br><span class="badge-dot dot-${issue.status}">${label(issue.status)}</span></div>
        </div>`;

    document.getElementById("issueDetailMessage").innerHTML = `
        <div class="border rounded p-3 bg-light">
            <div class="small text-muted mb-1 fw-medium">Message</div>
            <div class="mb-0">${esc(issue.message)}</div>
        </div>`;

    document.getElementById("issueDetailRawData").innerHTML = rawRows
        ? `<div class="border rounded p-3 mt-3">
               <div class="small text-muted mb-2 fw-medium">Imported Row Data</div>
               ${rawRows}
           </div>`
        : "";

    const modal = new bootstrap.Modal(
        document.getElementById("issueDetailModal"),
    );
    modal.show();
};

// ── Acknowledge all ────────────────────────────────────────────────────
export function initAckAll() {
    document
        .getElementById("acknowledgeAllBtn")
        ?.addEventListener("click", () => {
            if (!state.get()) return;
            showConfirm(
                "Acknowledge All",
                "Mark all unresolved issues as acknowledged?",
                async () => {
                    try {
                        await fetch(
                            `/import/${state.get()}/issues/acknowledge-all`,
                            {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": csrf(),
                                    Accept: "application/json",
                                },
                            },
                        );
                        loadIssues(1);
                    } catch (e) {
                        /* silent */
                    }
                },
            );
        });
}

// ── Filter listeners ──────────────────────────────────────────────────
export function initFilters() {
    ["severityFilter", "statusFilter"].forEach((id) => {
        document
            .getElementById(id)
            ?.addEventListener("change", () => loadIssues(1));
    });
    let timer;
    document.getElementById("issuesSearch")?.addEventListener("input", () => {
        clearTimeout(timer);
        timer = setTimeout(() => loadIssues(1), 400);
    });
}

// ── Tab switching ─────────────────────────────────────────────────────
export function switchTab(tab) {
    // The Issues tab is only shown while actually viewing issues; it stays
    // hidden on the History tab so the tab bar stays clean.
    const issuesItem = document.getElementById("issuesTabItem");
    if (tab === "issues" && issuesItem) issuesItem.classList.remove("d-none");
    if (tab === "history" && issuesItem) issuesItem.classList.add("d-none");

    const btn =
        tab === "issues"
            ? document.getElementById("issues-tab")
            : document.getElementById("history-tab");
    if (btn) btn.click();
}

// ── Helpers ────────────────────────────────────────────────────────────
function apiErrorMessage(d) {
    if (!d) return "Unexpected error.";
    // 422 validation failures put the real reason in d.errors, not d.message.
    if (d.errors && typeof d.errors === "object") {
        const first = Object.values(d.errors)[0];
        if (Array.isArray(first) && first.length) return first[0];
    }
    return d.message || "Unexpected error.";
}

function showError(title, msg) {
    document.getElementById("errorModalTitle").textContent = title;
    document.getElementById("errorModalBody").innerHTML = "<p>" + msg + "</p>";
    showM("errorModal");
}

function showConfirm(title, msg, onYes) {
    document.getElementById("confirmModalTitle").textContent = title;
    document.getElementById("confirmModalBody").innerHTML =
        "<p>" + msg + "</p>";
    const yesBtn = document.getElementById("confirmModalYes");
    const clone = yesBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(clone, yesBtn);
    clone.addEventListener("click", () => {
        hideM("confirmModal");
        onYes();
    });
    showM("confirmModal");
}

function resetUI() {
    state.clear();
    document.getElementById("inlineProgress").classList.add("d-none");
    document.getElementById("file").value = "";
    document.getElementById("fileInfo").classList.add("d-none");
    document.getElementById("issuesBadge").classList.add("d-none");
    const issuesItem = document.getElementById("issuesTabItem");
    if (issuesItem) issuesItem.classList.add("d-none");
    showM("uploadModal");
}

// "Import another file" / "New import" without a full page reload: reset the
// UI and reopen the upload modal in place (fewer clicks, keeps context).
window.resetImportUI = function () {
    hideM("resultModal");
    resetUI();
};

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

/** Map backend status to badge-dot CSS class */
function dotClass(status) {
    const map = {
        completed_with_issues: "issues",
    };
    return map[status] || status;
}

function duration(created, updated) {
    const diff = new Date(updated) - new Date(created);
    const secs = Math.floor(diff / 1000);
    if (secs < 60) return secs + "s";
    return Math.floor(secs / 60) + "m " + (secs % 60) + "s";
}

function formatDateTime(dateStr) {
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

function parseLearnerName(raw) {
    if (!raw) return "—";
    if (typeof raw === "string") return raw;
    return (
        raw["Learner Name"] ||
        raw.learnerName ||
        raw.firstName + " " + raw.lastName ||
        "—"
    );
}

// ── Init ───────────────────────────────────────────────────────────────
export function init() {
    initUpload();
    initAckAll();
    initFilters();
    loadHistory();
}

// Auto-init if loaded directly
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}
