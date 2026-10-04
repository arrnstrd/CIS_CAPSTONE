// QR Station — application orchestrator.
// Builds the shared DOM refs + state, wires the render / audio / api / input
// modules together (dependency injection avoids circular imports), and
// bootstraps the station. The terminal always boots to the idle "Ready to
// Scan" state — it never replays a previous scan on page load.

import { createAudio } from "./qr-station-audio.js";
import { createRenderer } from "./qr-station-render.js";
import { createApi } from "./qr-station-api.js";
import { createInput } from "./qr-station-input.js";

export function createQrStation(root) {
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

    const audio = createAudio();
    const render = createRenderer({ els, state });

    // Circular-free wiring: input reads the latest submit handler lazily so
    // it never imports api directly (and api never imports input).
    let submitScan = null;
    const getSubmit = () => submitScan;

    const input = createInput({
        els,
        state,
        getSubmit,
        ensureAudio: audio.ensureAudio,
    });

    const api = createApi({
        root,
        els,
        state,
        render,
        audio,
        focusScanner: input.focusScanner,
    });
    submitScan = api.submitScan;
    input.wire();

    window.addEventListener("attendance:recorded", (event) => {
        const payload = event.detail;
        if (!payload || !payload.attendance_log_id) return;

        if (state.queue.some((row) => String(row.id) === String(payload.attendance_log_id))) return;

        render.addBroadcastToQueue(payload);
    });

    window.addEventListener("attendance:resynced", (event) => {
        const payload = event.detail;
        if (!payload) return;

        render.applyResync(payload);
    });

    // ---- init ----
    function init() {
        render.seedQueue();
        render.renderOverview();
        render.renderQueue();
        // always boot the terminal to the ready state; never replay a previous
        // scan result from Redis on page load (would look like an untriggered scan)
        render.setTerminalMode("idle");
        input.focusScanner();
    }

    init();
}
