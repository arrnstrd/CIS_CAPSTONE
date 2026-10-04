import { initEcho } from "../echo.js";

// Inject the .realtime-live-dot CSS once on first load.
(function injectLiveDotStyle() {
    if (document.getElementById("realtime-live-dot-style")) return;
    const style = document.createElement("style");
    style.id = "realtime-live-dot-style";
    style.textContent = `
        .realtime-live-dot {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: #6b7280;
            vertical-align: middle;
            margin-left: 8px;
            transition: color 0.3s;
            user-select: none;
        }
        .realtime-live-dot.is-live {
            color: #22c55e;
            animation: live-dot-pulse 0.6s ease-out;
        }
        @keyframes live-dot-pulse {
            0%   { transform: scale(1.2); opacity: 1; }
            60%  { transform: scale(1.0); opacity: 1; }
            100% { transform: scale(1.0); opacity: 0.7; }
        }
    `;
    document.head.appendChild(style);
})();

function pulseLiveDots() {
    document.querySelectorAll("[data-realtime-live-dot]").forEach((dot) => {
        dot.classList.remove("is-live");
        void dot.offsetWidth;
        dot.classList.add("is-live");
        setTimeout(() => dot.classList.remove("is-live"), 700);
    });
}

function updateDashboardOverview(overview) {
    const values = {
        "scans-today": overview.total,
        "time-in": overview.in,
        "time-out": overview.out,
        late: overview.late,
    };

    Object.entries(values).forEach(([key, value]) => {
        document.querySelectorAll(`[data-realtime-card="${key}"]`).forEach((element) => {
            element.textContent = String(value ?? 0);
        });
    });
}

function emit(name, detail) {
    window.dispatchEvent(new CustomEvent(name, { detail }));
}

function renderDashboardScanRow(scan) {
    const row = document.createElement("tr");
    const student = scan.student || {};
    const type = scan.scan_type || "Unknown";
    const typeLabel = type === "IN" ? "Time In" : type === "OUT" ? "Time Out" : type;
    const typeClass = type === "IN" ? "success" : type === "OUT" ? "primary" : "secondary";
    const flags = (scan.flags || []).join(", ");
    const name = student.name || scan.student_name || "Unknown";
    const grade = student.grade || scan.grade || "-";
    const section = student.section || scan.section || "-";
    const time = scan.formatted_time || scan.scan_time || "-";

    row.innerHTML = `<td><div class="table-name-wrap"><div class="table-name-avatar"></div><div><span class="table-name-main"></span><span class="table-name-sub"></span></div></div></td><td><span class="fw-semibold text-dark"></span><span class="text-muted d-block" style="font-size: 0.68rem"></span></td><td><span class="badge-dot dot-${typeClass}"></span></td><td><div class="fw-semibold text-dark"></div>${flags ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1" style="font-size: 0.62rem">${flags}</span>` : ""}</td>`;
    const cells = row.querySelectorAll("td");
    cells[0].querySelector(".table-name-avatar").textContent = name.split(/\s+/).map((part) => part[0]).join("").slice(0, 2).toUpperCase();
    cells[0].querySelector(".table-name-main").textContent = name;
    cells[0].querySelector(".table-name-sub").textContent = student.student_number || scan.student_number || "-";
    cells[1].querySelector(".fw-semibold").textContent = `Grade ${grade}`;
    cells[1].querySelector(".text-muted").textContent = section;
    cells[2].querySelector(".badge-dot").textContent = typeLabel;
    cells[3].querySelector(".fw-semibold").textContent = time;
    row.classList.add("is-new");
    return row;
}

// Keep at most 5 displayed rows on resync
function updateDashboardRecentScans(scans) {
    const body = document.querySelector("[data-realtime-recent-scans]");
    if (!body) return;

    body.replaceChildren(...scans.slice(0, 5).map(renderDashboardScanRow));
}

// Catmull-Rom to Cubic Bezier smooth path helper (matches PHP $toSmoothPath)
function toSmoothPath(pts) {
    const n = pts.length;
    if (n === 0) return "";
    if (n === 1) return `M ${pts[0].x},${pts[0].y}`;
    let d = `M ${pts[0].x},${pts[0].y}`;
    for (let i = 0; i < n - 1; i++) {
        const p0 = pts[i - 1] || pts[i];
        const p1 = pts[i];
        const p2 = pts[i + 1];
        const p3 = pts[i + 2] || p2;
        const c1x = p1.x + (p2.x - p0.x) / 6;
        const c1y = p1.y + (p2.y - p0.y) / 6;
        const c2x = p2.x - (p3.x - p1.x) / 6;
        const c2y = p2.y - (p3.y - p1.y) / 6;
        d += ` C ${c1x.toFixed(2)},${c1y.toFixed(2)} ${c2x.toFixed(2)},${c2y.toFixed(2)} ${p2.x.toFixed(2)},${p2.y.toFixed(2)}`;
    }
    return d;
}

function initChartsRealtime() {
    const timelineEl = document.querySelector("[data-chart-timeline]");
    const weeklyEl = document.querySelector("[data-chart-weekly]");

    let timelineBuckets = [];
    let timelineInterval = 15;
    let timelineStart = "06:00";
    let maxBucketTotal = 1;

    let weeklyBuckets = [];
    let weeklyMaxBucketTotal = 1;

    if (timelineEl) {
        try {
            timelineBuckets = JSON.parse(timelineEl.dataset.timelineBuckets || "[]");
        } catch (e) {
            timelineBuckets = [];
        }
        timelineInterval = parseInt(timelineEl.dataset.timelineInterval || "15", 10);
        timelineStart = timelineEl.dataset.timelineStart || "06:00";
        maxBucketTotal = Math.max(1, ...timelineBuckets.map((b) => b.total || 0));
    }

    if (weeklyEl) {
        try {
            weeklyBuckets = JSON.parse(weeklyEl.dataset.weeklyBuckets || "[]");
        } catch (e) {
            weeklyBuckets = [];
        }
        weeklyMaxBucketTotal = Math.max(1, ...weeklyBuckets.map((w) => w.total || 0));
    }

    function renderTimeline() {
        if (!timelineEl || !timelineBuckets.length) return;

        maxBucketTotal = Math.max(1, ...timelineBuckets.map((b) => b.total || 0));

        // 1. Guides
        const g75 = timelineEl.querySelector('[data-timeline-guide="75"]');
        if (g75) g75.textContent = Math.round(maxBucketTotal * 0.75);
        const g50 = timelineEl.querySelector('[data-timeline-guide="50"]');
        if (g50) g50.textContent = Math.round(maxBucketTotal * 0.5);
        const g25 = timelineEl.querySelector('[data-timeline-guide="25"]');
        if (g25) g25.textContent = Math.round(maxBucketTotal * 0.25);

        // 2. Line View (Smooth SVG Paths)
        const span = Math.max(timelineBuckets.length - 1, 1);
        ["in", "out"].forEach((key) => {
            const field = key === "in" ? "time_in" : "time_out";
            const points = timelineBuckets.map((b, i) => {
                const x = 3 + (i / span) * 94;
                const count = b[field] || 0;
                const ratio = maxBucketTotal > 0 ? count / maxBucketTotal : 0;
                const y = 8 + (1 - ratio) * 84;
                return { x: Number(x.toFixed(2)), y: Number(y.toFixed(2)) };
            });
            const pathD = toSmoothPath(points);
            const stroke = timelineEl.querySelector(`[data-timeline-stroke="${key}"]`);
            if (stroke) stroke.setAttribute("d", pathD);
            const area = timelineEl.querySelector(`[data-timeline-area="${key}"]`);
            if (area && pathD) area.setAttribute("d", pathD + " L 97 92 L 3 92 Z");
        });

        // 3. Bars View
        timelineBuckets.forEach((b, idx) => {
            const col = timelineEl.querySelector(`.scan-chart__col[data-bucket-idx="${idx}"]`);
            if (!col) return;
            const bTotal = b.total || 0;
            const bIn = b.time_in || 0;
            const bOut = b.time_out || 0;
            const bHeight = bTotal > 0 ? Math.max(Math.round((bTotal / maxBucketTotal) * 145), 4) : 2;
            const inHeight = bTotal > 0 ? Math.max(Math.round((bIn / bTotal) * bHeight), bIn > 0 ? 2 : 0) : 0;
            const outHeight = bTotal > 0 ? Math.max(Math.round((bOut / bTotal) * bHeight), bOut > 0 ? 2 : 0) : 0;

            const tTotal = col.querySelector("[data-tooltip-total]");
            if (tTotal) tTotal.textContent = `${bTotal} scans`;
            const tIn = col.querySelector("[data-tooltip-in]");
            if (tIn) tIn.textContent = String(bIn);
            const tOut = col.querySelector("[data-tooltip-out]");
            if (tOut) tOut.textContent = String(bOut);

            const bar = col.querySelector("[data-bar-wrap]");
            if (bar) {
                bar.style.height = `${bHeight}px`;
                bar.replaceChildren();
                if (inHeight > 0) {
                    const segIn = document.createElement("div");
                    segIn.className = "scan-chart__segment";
                    segIn.style.height = `${inHeight}px`;
                    segIn.style.background = "#10b981";
                    segIn.title = `Time-In: ${bIn}`;
                    bar.appendChild(segIn);
                }
                if (outHeight > 0) {
                    const segOut = document.createElement("div");
                    segOut.className = "scan-chart__segment";
                    segOut.style.height = `${outHeight}px`;
                    segOut.style.background = "#3b82f6";
                    segOut.title = `Time-Out: ${bOut}`;
                    bar.appendChild(segOut);
                }
            }
        });

        // 4. Table View
        timelineBuckets.forEach((b, idx) => {
            const row = timelineEl.querySelector(`tr[data-bucket-idx="${idx}"]`);
            if (!row) return;
            const tIn = row.querySelector("[data-table-in]");
            if (tIn) tIn.textContent = String(b.time_in || 0);
            const tOut = row.querySelector("[data-table-out]");
            if (tOut) tOut.textContent = String(b.time_out || 0);
            const tTotal = row.querySelector("[data-table-total]");
            if (tTotal) tTotal.textContent = String(b.total || 0);
        });

        // 5. Legends & Peak
        const totalIn = timelineBuckets.reduce((sum, b) => sum + (b.time_in || 0), 0);
        const totalOut = timelineBuckets.reduce((sum, b) => sum + (b.time_out || 0), 0);
        timelineEl.querySelectorAll('[data-timeline-legend="in"]').forEach((el) => {
            el.textContent = String(totalIn);
        });
        timelineEl.querySelectorAll('[data-timeline-legend="out"]').forEach((el) => {
            el.textContent = String(totalOut);
        });

        const sorted = [...timelineBuckets].sort((a, b) => (b.total || 0) - (a.total || 0));
        const peak = sorted[0];
        const peakEl = timelineEl.querySelector("[data-timeline-peak]");
        if (peakEl && peak) {
            peakEl.textContent = `${peak.label} (${peak.total || 0})`;
        }
    }

    function renderWeekly() {
        if (!weeklyEl || !weeklyBuckets.length) return;

        weeklyMaxBucketTotal = Math.max(1, ...weeklyBuckets.map((w) => w.total || 0));

        // 1. Guides
        const g75 = weeklyEl.querySelector('[data-weekly-guide="75"]');
        if (g75) g75.textContent = Math.round(weeklyMaxBucketTotal * 0.75);
        const g50 = weeklyEl.querySelector('[data-weekly-guide="50"]');
        if (g50) g50.textContent = Math.round(weeklyMaxBucketTotal * 0.5);
        const g25 = weeklyEl.querySelector('[data-weekly-guide="25"]');
        if (g25) g25.textContent = Math.round(weeklyMaxBucketTotal * 0.25);

        // 2. Bars View & Tooltips
        weeklyBuckets.forEach((w, idx) => {
            const col = weeklyEl.querySelector(`.scan-chart__col[data-weekly-idx="${idx}"]`);
            if (!col) return;
            const wTotal = w.total || 0;
            const wHeight = wTotal > 0 ? Math.max(Math.round((wTotal / weeklyMaxBucketTotal) * 145), 4) : 2;

            const tTotal = col.querySelector("[data-weekly-tooltip-total]");
            if (tTotal) tTotal.textContent = `${wTotal} scans`;

            const bar = col.querySelector("[data-weekly-bar]");
            if (bar) bar.style.height = `${wHeight}px`;
        });

        // 3. Table View
        weeklyBuckets.forEach((w, idx) => {
            const row = weeklyEl.querySelector(`tr[data-weekly-idx="${idx}"]`);
            if (!row) return;
            const tTotal = row.querySelector("[data-weekly-table-total]");
            if (tTotal) tTotal.textContent = Number(w.total || 0).toLocaleString();
        });

        // 4. Stats
        const totalScans = weeklyBuckets.reduce((sum, w) => sum + (w.total || 0), 0);
        const avg = Math.round((totalScans / weeklyBuckets.length) * 10) / 10;
        const sorted = [...weeklyBuckets].sort((a, b) => (b.total || 0) - (a.total || 0));
        const peak = sorted[0];

        const totalEl = weeklyEl.querySelector("[data-weekly-stat-total]");
        if (totalEl) totalEl.textContent = totalScans.toLocaleString();
        const avgEl = weeklyEl.querySelector("[data-weekly-stat-avg]");
        if (avgEl) avgEl.textContent = `${avg}/wk`;
        const peakEl = weeklyEl.querySelector("[data-weekly-stat-peak]");
        if (peakEl && peak) peakEl.textContent = String(peak.total || 0);
    }

    function onScanRecorded(payload) {
        if (timelineBuckets.length > 0) {
            let scanDate = new Date();
            if (payload.scan_time) {
                const parsed = new Date(payload.scan_time);
                if (!isNaN(parsed.getTime())) scanDate = parsed;
            }

            const scanMinutes = scanDate.getHours() * 60 + scanDate.getMinutes();
            const [startH, startM] = timelineStart.split(":").map(Number);
            const startMinutes = (startH || 0) * 60 + (startM || 0);
            const bucketIdx = Math.floor((scanMinutes - startMinutes) / timelineInterval);

            if (bucketIdx >= 0 && bucketIdx < timelineBuckets.length) {
                const bucket = timelineBuckets[bucketIdx];
                if (payload.scan_type === "IN") {
                    bucket.time_in = (bucket.time_in || 0) + 1;
                } else if (payload.scan_type === "OUT") {
                    bucket.time_out = (bucket.time_out || 0) + 1;
                }
                bucket.total = (bucket.time_in || 0) + (bucket.time_out || 0);
                renderTimeline();
            }
        }

        if (weeklyBuckets.length > 0) {
            const lastIdx = weeklyBuckets.length - 1;
            weeklyBuckets[lastIdx].total = (weeklyBuckets[lastIdx].total || 0) + 1;
            renderWeekly();
        }
    }

    function onResync(payload) {
        if (payload.scan_buckets && Array.isArray(payload.scan_buckets)) {
            timelineBuckets = payload.scan_buckets;
            renderTimeline();
        }
        if (payload.weekly_buckets && Array.isArray(payload.weekly_buckets)) {
            weeklyBuckets = payload.weekly_buckets;
            renderWeekly();
        }
    }

    window.addEventListener("attendance:recorded", (e) => onScanRecorded(e.detail));
    window.addEventListener("attendance:resynced", (e) => onResync(e.detail));
}

function createRealtimeClient(root) {
    if (!root) return;

    const echo = initEcho();
    if (!echo) return;

    const resyncUrl = root.dataset.resyncUrl;
    let reconnecting = false;
    let resyncInFlight = null;

    async function resync() {
        if (!resyncUrl || resyncInFlight) return resyncInFlight;

        resyncInFlight = fetch(resyncUrl, {
            headers: { Accept: "application/json" },
            credentials: "same-origin",
        })
            .then((response) => {
                if (!response.ok) throw new Error(`Attendance resync failed: ${response.status}`);
                return response.json();
            })
            .then((payload) => {
                updateDashboardOverview(payload.overview || {});
                emit("attendance:resynced", payload);
                updateDashboardRecentScans(payload.recent_logs || []);
                return payload;
            })
            .catch(() => undefined)
            .finally(() => {
                resyncInFlight = null;
            });

        return resyncInFlight;
    }

    const channel = echo.private("attendance.monitoring");
    channel.listen(".AttendanceRecorded", (payload) => {
        const currentValue = (key) => {
            const text = document.querySelector(`[data-realtime-card="${key}"]`)?.textContent || "0";
            return Number.parseInt(text.replace(/[^0-9-]/g, ""), 10) || 0;
        };

        updateDashboardOverview({
            total: currentValue("scans-today") + 1,
            in: currentValue("time-in") + (payload.scan_type === "IN" ? 1 : 0),
            out: currentValue("time-out") + (payload.scan_type === "OUT" ? 1 : 0),
            late: currentValue("late") + (payload.late ? 1 : 0),
        });
        pulseLiveDots();
        emit("attendance:recorded", payload);

        // Keep maximum 5 visible rows in Recent Activity
        const body = document.querySelector("[data-realtime-recent-scans]");
        if (body) {
            body.prepend(renderDashboardScanRow(payload));
            while (body.children.length > 5) {
                body.lastElementChild.remove();
            }
        }
    });

    const connection = echo.connector?.pusher?.connection;
    connection?.bind("state_change", (states) => {
        if (states.current === "unavailable" || states.current === "connecting") {
            reconnecting = true;
            emit("attendance:connection", { state: states.current });
        }

        if (states.current === "connected") {
            emit("attendance:connection", { state: reconnecting ? "reconnected" : "connected" });
            if (reconnecting) resync();
            reconnecting = false;
        }
    });

    initChartsRealtime();
    resync();
}

const root = document.querySelector("[data-attendance-realtime]");
if (root) createRealtimeClient(root);

export { createRealtimeClient };
