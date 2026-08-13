// QR Station — all UI rendering.
// Factory that captures the shared DOM refs (`els`) and app state (`state`)
// and exposes the terminal state machine, popup/progress/countdown helpers,
// and the live queue + overview renderers.

import {
    STATE_CLASSES,
    RESULT_LABELS,
    ERROR_TITLES,
    formatTime,
    esc,
    parseSection,
    themeFor,
} from "./qr-station-utils.js";

export function createRenderer({ els, state }) {
    // ---- terminal body rendering ----
    function renderContent(html) {
        if (els.qrContent) els.qrContent.innerHTML = html;
    }

    // Show the result as a pop-up card over the terminal body.
    function showPopup(html) {
        if (!els.qrPopup || !els.qrPopupCard) return;
        els.qrPopupCard.innerHTML = html;
        els.qrPopup.classList.remove("is-leaving");
        els.qrPopup.style.display = "flex";
    }

    function hidePopup() {
        if (!els.qrPopup) return;
        els.qrPopup.style.display = "none";
        if (els.qrPopupCard) els.qrPopupCard.innerHTML = "";
    }

    function renderIdle() {
        return (
            '<div class="qr-ready">' +
            '  <div class="qr-ready__rings">' +
            '    <span class="qr-ready__ring"></span>' +
            '    <span class="qr-ready__ring qr-ready__ring--2"></span>' +
            '    <div class="qr-ready__icon"><i class="fas fa-qrcode"></i></div>' +
            "  </div>" +
            '  <h1 class="qr-ready__title">Ready to Scan</h1>' +
            '  <p class="qr-ready__sub">Present your student QR code to the scanner</p>' +
            "</div>"
        );
    }

    function renderProcessing() {
        return (
            '<div class="qr-processing">' +
            '  <div class="qr-processing__spinner"></div>' +
            '  <h2 class="qr-processing__title">Verifying…</h2>' +
            '  <div class="qr-processing__sub">Checking student record</div>' +
            '  <div class="qr-processing__ref">' +
            esc(state.qrRef) +
            "</div>" +
            "</div>"
        );
    }

    // Success / late arrival: show the student details + scan type + time.
    function renderSuccess(result, body) {
        const student = body && body.student;
        const log = body && body.attendance_log;
        const isLate = result.key === "late";

        const icon = isLate ? "fa-clock" : "fa-circle-check";
        const eyebrow = isLate
            ? "Late Arrival Recorded"
            : "Attendance Recorded";
        const note = isLate ? "Student arrived late" : "On time";

        const name = student ? student.name : "—";
        let meta = "";
        let grade = "—";
        let section = "—";
        if (student) {
            const parsed = parseSection(student.section);
            grade = parsed.grade || "—";
            section = parsed.section || "—";
            const parts = [
                student.student_number,
                parsed.grade,
                parsed.section ? "Section " + parsed.section : null,
            ].filter(Boolean);
            meta = parts.join(" · ");
        }

        const scanTypeVal = (log && log.scan_type) || result.scanType;
        const typeLabel =
            scanTypeVal === "IN"
                ? "Check-In"
                : scanTypeVal === "OUT"
                  ? "Check-Out"
                  : scanTypeVal || "—";
        const typeBadge =
            scanTypeVal === "IN"
                ? "qr-chip__badge--in"
                : scanTypeVal === "OUT"
                  ? "qr-chip__badge--out"
                  : "";
        const time = log && log.scan_time ? formatTime(log.scan_time) : "—";

        return (
            '<div class="qr-card">' +
            '  <div class="qr-card__icon"><i class="fas ' +
            icon +
            '"></i></div>' +
            '  <div class="qr-card__eyebrow">' +
            esc(eyebrow) +
            "</div>" +
            '  <h1 class="qr-card__name">' +
            esc(name) +
            "</h1>" +
            (meta
                ? '  <div class="qr-card__meta">' + esc(meta) + "</div>"
                : "") +
            '  <div class="qr-card__chips">' +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Grade</div>' +
            '      <div class="qr-chip__value">' +
            esc(grade) +
            "</div>" +
            "    </div>" +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Section</div>' +
            '      <div class="qr-chip__value">' +
            esc(section) +
            "</div>" +
            "    </div>" +
            "  </div>" +
            '  <div class="qr-card__chips">' +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Scan Type</div>' +
            '      <span class="qr-chip__badge ' +
            typeBadge +
            '">' +
            esc(typeLabel) +
            "</span>" +
            "    </div>" +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Time</div>' +
            '      <div class="qr-chip__value">' +
            esc(time) +
            "</div>" +
            "    </div>" +
            "  </div>" +
            '  <div class="qr-card__note"><i class="fas ' +
            icon +
            '"></i> ' +
            esc(note) +
            "</div>" +
            "</div>"
        );
    }

    // Invalid / duplicate / excess / error: rejection card, no student details.
    function renderError(result, body) {
        const title = ERROR_TITLES[result.key] || "Scan Not Processed";
        const message =
            (body && body.message) ||
            RESULT_LABELS[result.key] ||
            "Scan rejected";

        return (
            '<div class="qr-card qr-card--error">' +
            '  <div class="qr-card__icon"><i class="fas fa-circle-xmark"></i></div>' +
            '  <div class="qr-card__eyebrow">Scan Not Processed</div>' +
            '  <h1 class="qr-card__title">' +
            esc(title) +
            "</h1>" +
            '  <p class="qr-card__msg">' +
            esc(message) +
            "</p>" +
            (state.qrRef
                ? '  <div class="qr-card__ref">' +
                  '    <div class="qr-card__ref-label">QR Reference</div>' +
                  '    <div class="qr-card__ref-value">' +
                  esc(state.qrRef) +
                  "</div>" +
                  "  </div>"
                : "") +
            "</div>"
        );
    }

    // ---- progress bar helpers ----
    function showProgressRunning() {
        if (!els.fbFill) return;
        els.fbFill.classList.remove("is-complete");
        els.fbFill.classList.add("is-running");
        if (els.fbLabel) els.fbLabel.textContent = "Processing…";
    }

    function completeProcessing() {
        if (!els.fbFill) return;
        els.fbFill.classList.remove("is-running");
        els.fbFill.classList.add("is-complete");
        if (els.fbLabel) els.fbLabel.textContent = "Completed";
        setTimeout(() => {
            els.fbFill.classList.remove("is-complete");
            if (els.fbLabel) els.fbLabel.textContent = "";
        }, 400);
    }

    function hideProgress() {
        if (els.fbFill)
            els.fbFill.classList.remove("is-running", "is-complete");
        if (els.fbLabel) els.fbLabel.textContent = "";
    }

    // ---- auto-ready countdown text + thin bar ----
    let autoReadyTimer = null;
    let resultResetTimer = null;
    let popLeaveTimer = null;
    const RESULT_DISPLAY_MS = 5000;
    const RESULT_DISPLAY_ERROR_MS = 6000;

    function startAutoReadyCountdown(totalMs) {
        stopAutoReady();
        const end = Date.now() + totalMs;
        const tick = () => {
            const remain = end - Date.now();
            if (remain <= 0) {
                if (els.fbAutoReady) els.fbAutoReady.textContent = "Auto-ready";
                clearInterval(autoReadyTimer);
                return;
            }
            if (els.fbAutoReady)
                els.fbAutoReady.textContent =
                    "Auto-ready in " + (remain / 1000).toFixed(1) + "s";
        };
        tick();
        autoReadyTimer = setInterval(tick, 100);
    }

    function stopAutoReady() {
        if (autoReadyTimer) {
            clearInterval(autoReadyTimer);
            autoReadyTimer = null;
        }
        if (els.fbAutoReady) els.fbAutoReady.textContent = "Auto-ready";
    }

    function showCountdownBar(duration) {
        if (!els.countdownBar) return;
        els.countdownBar.style.display = "block";
        els.countdownBar.style.transition = "none";
        els.countdownBar.style.transform = "scaleX(1)";
        void els.countdownBar.offsetWidth;
        els.countdownBar.style.transition =
            "transform " + duration + "ms linear";
        els.countdownBar.style.transform = "scaleX(0)";
    }

    function hideCountdown() {
        if (els.countdownBar) els.countdownBar.style.display = "none";
    }

    // ---- terminal state machine ----
    function setTerminalMode(mode, result, body) {
        state.mode = mode;
        state.currentResult = result || null;
        state.currentBody = body || {};

        const terminal = els.feedback;
        STATE_CLASSES.forEach((c) => terminal.classList.remove(c));
        terminal.classList.remove("is-animating");

        // any pending auto-reset is cancelled when the state changes
        if (resultResetTimer) {
            clearTimeout(resultResetTimer);
            resultResetTimer = null;
        }
        if (popLeaveTimer) {
            clearTimeout(popLeaveTimer);
            popLeaveTimer = null;
        }

        if (mode === "idle") {
            terminal.classList.add("state-idle");
            els.fbHeaderTitle.textContent = "Scanner Active";
            els.fbHeaderSub.textContent = "Awaiting input…";
            hidePopup();
            hideProgress();
            hideCountdown();
            stopAutoReady();
            renderContent(renderIdle());
            return;
        }

        if (mode === "processing") {
            terminal.classList.add("state-processing");
            els.fbHeaderTitle.textContent = "Processing…";
            els.fbHeaderSub.textContent = "Verifying QR token";
            hidePopup();
            hideCountdown();
            showProgressRunning();
            renderContent(renderProcessing());
            return;
        }

        // result mode
        const key = result.key;
        const theme = themeFor(key);
        terminal.classList.add("state-" + key);
        void terminal.offsetWidth;
        terminal.classList.add("is-animating");

        if (theme === "success") {
            els.fbHeaderTitle.textContent = "Scan Complete";
            els.fbHeaderSub.textContent = "Attendance recorded";
        } else if (theme === "late") {
            els.fbHeaderTitle.textContent = "Late Arrival";
            els.fbHeaderSub.textContent = "Late arrival flagged";
        } else {
            els.fbHeaderTitle.textContent = "Scan Issue";
            els.fbHeaderSub.textContent = "Scan rejected";
        }

        hideProgress();
        showPopup(
            theme === "error"
                ? renderError(result, body)
                : renderSuccess(result, body),
        );

        // display the result for a short while, then auto-reset to idle
        const duration =
            theme === "error" ? RESULT_DISPLAY_ERROR_MS : RESULT_DISPLAY_MS;
        startAutoReadyCountdown(duration);
        showCountdownBar(duration);
        resultResetTimer = setTimeout(() => {
            resultResetTimer = null;
            // play the pop-out animation, then reset to idle
            if (els.qrPopup) els.qrPopup.classList.add("is-leaving");
            popLeaveTimer = setTimeout(() => {
                popLeaveTimer = null;
                setTerminalMode("idle");
            }, 240);
        }, duration);
    }

    // ==================================================================
    //  Right panel: live queue + overview
    // ==================================================================

    function addToQueue(result, body) {
        const student = body && body.student;
        const log = body && body.attendance_log;

        let statusLabel, statusClass;
        switch (result.key) {
            case "time-in":
            case "time-out":
                statusLabel = "Success";
                statusClass = "dot-success";
                break;
            case "late":
                statusLabel = "Late";
                statusClass = "dot-warning";
                break;
            case "duplicate":
                statusLabel = "Duplicate";
                statusClass = "dot-danger";
                break;
            case "excess":
                statusLabel = "Excess";
                statusClass = "dot-danger";
                break;
            default:
                statusLabel = "Rejected";
                statusClass = "dot-secondary";
                break;
        }

        state.queue.unshift({
            id: "live-" + ++state.lastRowId,
            isNew: true,
            name: student ? student.name : "—",
            number: student ? student.student_number : "",
            grade: student ? parseSection(student.section).grade : "—",
            section: student ? parseSection(student.section).section : "—",
            scan_type: (log && log.scan_type) || result.scanType || "—",
            time:
                formatTime(log && log.scan_time) ||
                formatTime(new Date().toISOString()),
            statusLabel,
            statusClass,
        });

        if (state.queue.length > 30) state.queue.pop();
        renderQueue();
    }

    function renderQueue() {
        els.queueBody.innerHTML = "";

        if (!state.queue.length) {
            els.queueBody.appendChild(els.queueEmpty);
            els.queueEmpty.style.display = "";
            els.queueCount.textContent = "0";
            return;
        }
        els.queueEmpty.style.display = "none";

        const frag = document.createDocumentFragment();
        state.queue.forEach((row, idx) => {
            const tr = document.createElement("tr");
            if (idx === 0 && row.isNew) tr.classList.add("is-new");

            const nameTd = document.createElement("td");
            nameTd.className = "fw-semibold";
            nameTd.textContent = row.name;

            const gradeTd = document.createElement("td");
            gradeTd.textContent = row.grade || "—";

            const sectionTd = document.createElement("td");
            sectionTd.textContent = row.section || "—";

            const timeTd = document.createElement("td");
            timeTd.textContent = row.time || "—";

            const typeTd = document.createElement("td");
            const typeDot =
                { IN: "success", OUT: "primary" }[row.scan_type] || "secondary";
            const typeBadge = document.createElement("span");
            typeBadge.className = "badge-dot dot-" + typeDot;
            typeBadge.textContent = row.scan_type || "—";
            typeTd.appendChild(typeBadge);

            const statusTd = document.createElement("td");
            const statusBadge = document.createElement("span");
            statusBadge.className = "badge-dot " + row.statusClass;
            statusBadge.textContent = row.statusLabel;
            statusTd.appendChild(statusBadge);

            tr.appendChild(nameTd);
            tr.appendChild(gradeTd);
            tr.appendChild(sectionTd);
            tr.appendChild(timeTd);
            tr.appendChild(typeTd);
            tr.appendChild(statusTd);
            frag.appendChild(tr);
        });

        els.queueBody.appendChild(frag);
        els.queueCount.textContent = String(state.queue.length);
    }

    function updateOverview(result) {
        if (!result.logged) return;
        const o = state.overview;
        o.total += 1;
        if (result.scanType === "IN") o.in += 1;
        if (result.scanType === "OUT") o.out += 1;
        if (result.key === "late") o.late += 1;
        renderOverview();
    }

    function renderOverview() {
        els.ovTotal.textContent = state.overview.total;
        els.ovIn.textContent = state.overview.in;
        els.ovOut.textContent = state.overview.out;
        els.ovLate.textContent = state.overview.late;
    }

    // derive status from server-provided flags on the seeded queue
    function seedQueue() {
        state.queue = state.queue.map((row) => {
            const flags = row.flags || [];
            let statusLabel = "Success";
            let statusClass = "dot-success";
            if (flags.includes("late_arrival")) {
                statusLabel = "Late";
                statusClass = "dot-warning";
            } else if (flags.includes("duplicate_scan")) {
                statusLabel = "Duplicate";
                statusClass = "dot-danger";
            } else if (flags.includes("excess_scan")) {
                statusLabel = "Excess";
                statusClass = "dot-danger";
            }
            // normalize server-provided fields to match the live row shape
            row.name = row.student_name || row.name || "—";
            row.number = row.student_number || row.number || "";
            row.grade = row.grade || "—";
            row.section = row.section || "—";
            row.scan_type = row.scan_type || row.session_type || "—";
            row.time = formatTime(row.scan_time) || row.time || "—";
            row.statusLabel = statusLabel;
            row.statusClass = statusClass;
            return row;
        });
    }

    return {
        renderContent,
        showPopup,
        hidePopup,
        renderIdle,
        renderProcessing,
        renderSuccess,
        renderError,
        showProgressRunning,
        completeProcessing,
        hideProgress,
        startAutoReadyCountdown,
        stopAutoReady,
        showCountdownBar,
        hideCountdown,
        setTerminalMode,
        addToQueue,
        renderQueue,
        updateOverview,
        renderOverview,
        seedQueue,
    };
}
