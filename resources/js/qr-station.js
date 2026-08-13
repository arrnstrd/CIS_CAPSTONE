// QR Station — live QR scanning dashboard
// Works with generic HID keyboard-style QR scanners (types the QR string + Enter).
// Scanner input and manual input both POST the same QR string to the same scan endpoint.
//
// Left terminal flow (prototype-style):
//   idle -> processing (validating) -> result (success shows student details,
//   errors show a rejection card) -> auto-reset to idle.
// All data shown comes from the real backend scan endpoint.

(function () {
    "use strict";

    const root = document.getElementById("qrStationApp");
    if (!root) return;

    const els = {
        scannerInput: document.getElementById("scannerInput"),
        manualInput: document.getElementById("manualQrInput"),
        manualSend: document.getElementById("manualQrSend"),
        feedback: document.getElementById("scanFeedback"),
        fbHeaderTitle: document.getElementById("fbHeaderTitle"),
        fbHeaderSub: document.getElementById("fbHeaderSub"),
        qrContent: document.getElementById("qrContent"),
        qrPopup: document.getElementById("qrPopup"),
        qrPopupCard: document.getElementById("qrPopupCard"),
        fbFill: document.getElementById("fbFill"),
        fbLabel: document.getElementById("fbLabel"),
        fbBuffer: document.getElementById("fbBuffer"),
        fbAutoReady: document.getElementById("fbAutoReady"),
        countdownBar: document.getElementById("countdownBar"),
        queueBody: document.getElementById("queueBody"),
        queueEmpty: document.getElementById("queueEmpty"),
        queueCount: document.getElementById("queueCount"),
        ovTotal: document.getElementById("ovTotal"),
        ovIn: document.getElementById("ovIn"),
        ovOut: document.getElementById("ovOut"),
        ovLate: document.getElementById("ovLate"),
    };

    const initial = JSON.parse(root.dataset.initial || "{}");

    const state = {
        overview: Object.assign(
            { total: 0, in: 0, out: 0, late: 0, flagged: 0 },
            initial.overview || {},
        ),
        queue: (initial.recent_logs || []).slice(),
        processing: false,
        buffer: "",
        lastRowId: 0,
        mode: "idle", // "idle" | "processing" | "result"
        currentResult: null,
        currentBody: {},
        qrRef: "",
    };

    const STATE_CLASSES = [
        "state-idle",
        "state-time-in",
        "state-time-out",
        "state-late",
        "state-duplicate",
        "state-invalid",
        "state-excess",
        "state-error",
        "state-processing",
    ];

    const RESULT_LABELS = {
        "time-in": "Attendance Recorded",
        "time-out": "Attendance Recorded",
        late: "Late Arrival",
        duplicate: "Duplicate Scan",
        invalid: "Invalid QR",
        excess: "Excess Scan",
        error: "Scan Failed",
    };

    const ERROR_TITLES = {
        duplicate: "Duplicate Scan",
        invalid: "Invalid QR Code",
        excess: "Too Many Scans",
        error: "Scan Failed",
    };

    const RESULT_META = {
        "time-in": { sound: "success-in", logged: true },
        "time-out": { sound: "success-out", logged: true },
        late: { sound: "late", logged: true },
        duplicate: { sound: "duplicate", logged: true },
        invalid: { sound: "invalid", logged: false },
        excess: { sound: "excess", logged: true },
        error: { sound: "error", logged: false },
    };

    // Small helper: build a result object for a given outcome key.
    function resultFromKey(key, body) {
        const meta = RESULT_META[key] || RESULT_META.error;
        const log = body && body.attendance_log;
        return {
            key,
            sound: meta.sound,
            scanType: (log && log.scan_type) || null,
            logged: meta.logged,
        };
    }

    // Map the actual backend response (status code + message) to a UI outcome.
    function classify(status, body) {
        const msg = ((body && body.message) || "").toLowerCase();
        const type = body && body.attendance_log && body.attendance_log.scan_type;

        if (status === 200) {
            return resultFromKey(
                body && body.late ? "late" : type === "OUT" ? "time-out" : "time-in",
                body,
            );
        }
        if (status === 429) return resultFromKey("excess", body);
        if (status === 404) return resultFromKey("invalid", body);
        if (msg.includes("duplicate")) return resultFromKey("duplicate", body);
        return resultFromKey("error", body);
    }

    function formatTime(value) {
        if (!value) return "";
        const d = new Date(value);
        if (isNaN(d.getTime())) return value;
        return d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" });
    }

    function esc(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function qrRefOf(qr) {
        qr = String(qr || "");
        return qr.length > 12 ? qr.slice(0, 6) + "…" + qr.slice(-4) : qr;
    }

    function themeFor(key) {
        if (key === "time-in" || key === "time-out") return "success";
        if (key === "late") return "late";
        return "error";
    }

    // ==================================================================
    //  Left terminal rendering (idle / processing / result)
    // ==================================================================

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
            '  <div class="qr-processing__ref">' + esc(state.qrRef) + "</div>" +
            "</div>"
        );
    }

    // Success / late arrival: show the student details + scan type + time.
    function renderSuccess(result, body) {
        const student = body && body.student;
        const log = body && body.attendance_log;
        const isLate = result.key === "late";

        const icon = isLate ? "fa-clock" : "fa-circle-check";
        const eyebrow = isLate ? "Late Arrival Recorded" : "Attendance Recorded";
        const note = isLate ? "Student arrived late" : "On time";

        const name = student ? student.name : "—";
        let meta = "";
        if (student) {
            const parts = [
                student.student_number,
                student.section ? String(student.section).replace(/ - /g, " · ") : null,
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
            '  <div class="qr-card__icon"><i class="fas ' + icon + '"></i></div>' +
            '  <div class="qr-card__eyebrow">' + esc(eyebrow) + "</div>" +
            '  <h1 class="qr-card__name">' + esc(name) + "</h1>" +
            (meta ? '  <div class="qr-card__meta">' + esc(meta) + "</div>" : "") +
            '  <div class="qr-card__chips">' +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Scan Type</div>' +
            '      <span class="qr-chip__badge ' + typeBadge + '">' + esc(typeLabel) + "</span>" +
            "    </div>" +
            '    <div class="qr-chip">' +
            '      <div class="qr-chip__label">Time</div>' +
            '      <div class="qr-chip__value">' + esc(time) + "</div>" +
            "    </div>" +
            "  </div>" +
            '  <div class="qr-card__note"><i class="fas ' + icon + '"></i> ' + esc(note) + "</div>" +
            "</div>"
        );
    }

    // Invalid / duplicate / excess / error: rejection card, no student details.
    function renderError(result, body) {
        const title = ERROR_TITLES[result.key] || "Scan Not Processed";
        const message = (body && body.message) || RESULT_LABELS[result.key] || "Scan rejected";

        return (
            '<div class="qr-card qr-card--error">' +
            '  <div class="qr-card__icon"><i class="fas fa-circle-xmark"></i></div>' +
            '  <div class="qr-card__eyebrow">Scan Not Processed</div>' +
            '  <h1 class="qr-card__title">' + esc(title) + "</h1>" +
            '  <p class="qr-card__msg">' + esc(message) + "</p>" +
            (state.qrRef
                ? '  <div class="qr-card__ref">' +
                  '    <div class="qr-card__ref-label">QR Reference</div>' +
                  '    <div class="qr-card__ref-value">' + esc(state.qrRef) + "</div>" +
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
        if (els.fbFill) els.fbFill.classList.remove("is-running", "is-complete");
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
        els.countdownBar.style.transition = "transform " + duration + "ms linear";
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
        showPopup(theme === "error" ? renderError(result, body) : renderSuccess(result, body));

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
    //  Right panel: live queue + overview (unchanged behavior)
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
            row.scan_type = row.scan_type || row.session_type || "—";
            row.time = formatTime(row.scan_time) || row.time || "—";
            row.statusLabel = statusLabel;
            row.statusClass = statusClass;
            return row;
        });
    }

    // ==================================================================
    //  Scan submission
    // ==================================================================

    async function submitScan(code) {
        const qr = (code || "").trim();
        if (!qr || state.processing) return;

        state.processing = true;
        state.qrRef = qrRefOf(qr);
        setTerminalMode("processing");

        let result = { key: "error" };
        let body = {};
        try {
            const res = await fetch(root.dataset.scanUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": root.dataset.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ code: qr }),
            });

            try {
                body = await res.json();
            } catch (e) {
                body = {};
            }

            result = classify(res.status, body);

            playSound(result.sound);
            addToQueue(result, body);
            updateOverview(result);
        } catch (err) {
            result = { key: "error", sound: "invalid", logged: false };
            body = { message: "Could not reach the server. Please try again." };
            playSound("invalid");
        } finally {
            // after validation, reveal the result (student details / rejection)
            setTerminalMode("result", result, body);
            state.processing = false;
            if (els.manualInput) els.manualInput.value = "";
            focusScanner();
        }
    }

    // ==================================================================
    //  Audio feedback (lightweight synthesized tones)
    // ==================================================================

    let audioCtx = null;
    function ensureAudio() {
        if (!audioCtx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (AC) audioCtx = new AC();
        }
        if (audioCtx && audioCtx.state === "suspended") audioCtx.resume();
    }

    function tone(freq, opts) {
        opts = opts || {};
        if (!audioCtx) return;
        const start = opts.start || 0;
        const duration = opts.duration || 0.12;
        const t0 = audioCtx.currentTime + start;
        const osc = audioCtx.createOscillator();
        const g = audioCtx.createGain();
        osc.type = opts.type || "sine";
        osc.frequency.setValueAtTime(freq, t0);
        g.gain.setValueAtTime(0, t0);
        g.gain.linearRampToValueAtTime(opts.gain || 0.18, t0 + 0.01);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + duration);
        osc.connect(g).connect(audioCtx.destination);
        osc.start(t0);
        osc.stop(t0 + duration + 0.05);
    }

    function playSound(kind) {
        ensureAudio();
        if (!audioCtx) return;
        switch (kind) {
            case "success-in":
                tone(523.25);
                tone(783.99, { start: 0.12, gain: 0.2 });
                break;
            case "success-out":
                tone(783.99);
                tone(523.25, { start: 0.12, gain: 0.2 });
                break;
            case "late":
                tone(440, { type: "square", gain: 0.09 });
                tone(440, { type: "square", gain: 0.09, start: 0.16 });
                break;
            case "duplicate":
                tone(660, { type: "square", gain: 0.09 });
                tone(660, { type: "square", gain: 0.09, start: 0.14 });
                break;
            case "excess":
                tone(330, { type: "square", gain: 0.1 });
                tone(330, { type: "square", gain: 0.1, start: 0.12 });
                tone(330, { type: "square", gain: 0.1, start: 0.24 });
                break;
            case "invalid":
            case "error":
            default:
                tone(196, { duration: 0.28, type: "sawtooth", gain: 0.1 });
                break;
        }
    }

    // ==================================================================
    //  HID scanner + manual input wiring
    // ==================================================================

    function focusScanner() {
        if (!els.scannerInput) return;
        if (document.activeElement !== els.manualInput) {
            els.scannerInput.focus({ preventScroll: true });
        }
    }

    // capture keyboard input globally so the operator never has to click before scanning
    document.addEventListener("keydown", (e) => {
        ensureAudio();
        const inManual = document.activeElement === els.manualInput;

        if (e.key === "Enter") {
            if (inManual) {
                submitScan(els.manualInput.value);
                return;
            }
            e.preventDefault();
            submitScan(state.buffer);
            state.buffer = "";
            if (els.fbBuffer) els.fbBuffer.textContent = "—";
            return;
        }

        if (inManual) return;

        if (
            !e.ctrlKey &&
            !e.metaKey &&
            !e.altKey &&
            e.key &&
            e.key.length === 1
        ) {
            e.preventDefault();
            state.buffer += e.key;
            if (els.fbBuffer) els.fbBuffer.textContent = state.buffer;
        }
    });

    // keep the hidden scanner input focused unless the operator is using the manual field
    if (els.scannerInput) {
        els.scannerInput.addEventListener("blur", () => {
            setTimeout(() => {
                if (document.activeElement !== els.manualInput) {
                    els.scannerInput.focus({ preventScroll: true });
                }
            }, 0);
        });
    }

    if (els.manualSend) {
        els.manualSend.addEventListener("click", () => {
            submitScan(els.manualInput.value);
        });
    }

    // Restore the most recent scan state (from Redis via initialData) on page load.
    function renderLatestScan(scan) {
        if (!scan || !scan.status || scan.status === "idle") return;

        // Only render cached latest scan if it refers to today's attendance
        // (prevents showing stale "Scan failed" or other messages from
        // previous days when the page loads).
        const log = scan.attendance_log;
        if (!log || !log.scan_time) return;
        const scanDate = new Date(log.scan_time);
        const now = new Date();
        if (
            scanDate.getFullYear() !== now.getFullYear() ||
            scanDate.getMonth() !== now.getMonth() ||
            scanDate.getDate() !== now.getDate()
        ) {
            return;
        }

        state.qrRef = state.qrRef || "";
        setTerminalMode("result", resultFromKey(scan.status, scan), scan);
    }

    // ---- init ----
    function init() {
        seedQueue();
        renderOverview();
        renderQueue();
        // ensure UI starts in the ready state; a valid latest scan will override this
        setTerminalMode("idle");
        renderLatestScan(initial.latest_scan);
        focusScanner();
    }

    init();
})();
