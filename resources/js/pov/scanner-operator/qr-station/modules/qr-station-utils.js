// QR Station — shared constants and pure helpers (no DOM, no state).
// Dependency-free so it can be imported by every other QR Station module.

export const STATE_CLASSES = [
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

export const RESULT_LABELS = {
    "time-in": "Time-In Recorded",
    "time-out": "Time-Out Recorded",
    late: "Late Arrival Recorded",
    duplicate: "Already Recorded",
    invalid: "Scan Notice",
    excess: "Cooldown Active",
    error: "Service Notice",
};

export const ERROR_TITLES = {
    duplicate: "Already Recorded",
    invalid: "Unable to Process Scan",
    excess: "Please Wait",
    error: "Service Notice",
};

export const RESULT_META = {
    "time-in": { sound: "success-in", logged: true },
    "time-out": { sound: "success-out", logged: true },
    late: { sound: "late", logged: true },
    duplicate: { sound: "duplicate", logged: true },
    invalid: { sound: "invalid", logged: false },
    excess: { sound: "excess", logged: true },
    error: { sound: "error", logged: false },
};

// Small helper: build a result object for a given outcome key.
export function resultFromKey(key, body) {
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
export function classify(status, body) {
    const msg = ((body && body.message) || "").toLowerCase();
    const type = body && body.attendance_log && body.attendance_log.scan_type;

    if (status === 200) {
        return resultFromKey(
            body && body.late
                ? "late"
                : type === "OUT"
                  ? "time-out"
                  : "time-in",
            body,
        );
    }
    if (status === 429) return resultFromKey("excess", body);
    if (status === 404) return resultFromKey("invalid", body);
    // 419 = CSRF token expired — the scan was never sent; ask user to refresh.
    if (status === 419) {
        if (body && !body.message) body.message = "Session expired. Please refresh the page and try again.";
        return resultFromKey("invalid", body);
    }
    // 400 = business-rule rejection (no active schedule, outside window, duplicate, etc.)
    // Show the server's actual rejection message instead of the generic "server unreachable" error.
    if (status === 400) {
        if (msg.includes("duplicate")) return resultFromKey("duplicate", body);
        return resultFromKey("invalid", body);
    }
    return resultFromKey("error", body);
}

export function formatTime(value) {
    if (!value) return "";
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    return d.toLocaleTimeString([], { hour: "numeric", minute: "2-digit" });
}

export function esc(value) {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

export function qrRefOf(qr) {
    qr = String(qr || "");
    return qr.length > 12 ? qr.slice(0, 6) + "…" + qr.slice(-4) : qr;
}

// Backend sends section as "Grade 5 - B"; split into grade + section name.
export function parseSection(section) {
    const parts = String(section || "").split(" - ");
    return {
        grade: parts[0] || null,
        section: parts[1] || null,
    };
}

export function themeFor(key) {
    if (key === "time-in" || key === "time-out") return "success";
    if (key === "late") return "late";
    return "error";
}
