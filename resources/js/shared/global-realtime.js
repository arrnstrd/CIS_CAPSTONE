import { initEcho } from "../echo.js";

function setupGlobalRealtime() {
    const echo = initEcho();
    if (!echo) return;

    // Standard private channel subscribed by logged-in users (Admins, Scanner Operators, Super Admins)
    const monitoringChannel = echo.private("attendance.monitoring");

    // Helper to safely trigger table auto-refresh across any tab/page
    const autoRefreshTables = () => {
        if (window.ajaxCrud && typeof window.ajaxCrud.refreshTables === "function") {
            window.ajaxCrud.refreshTables().catch(() => {});
        }
    };

    // 1. Section changes (create / edit / delete / assign adviser)
    monitoringChannel.listen(".SectionUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("section:updated", { detail: e }));
        autoRefreshTables();
    });

    // 2. Subject changes (create / edit / delete)
    monitoringChannel.listen(".SubjectUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("subject:updated", { detail: e }));
        autoRefreshTables();
    });

    // 3. Student changes (create / edit / delete)
    monitoringChannel.listen(".StudentUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("student:updated", { detail: e }));
        autoRefreshTables();
    });

    // 4. Teaching Assignment changes
    monitoringChannel.listen(".TeachingAssignmentUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("assignment:updated", { detail: e }));
        autoRefreshTables();
    });

    // 5. Attendance Scans (IN / OUT)
    monitoringChannel.listen(".AttendanceRecorded", (e) => {
        window.dispatchEvent(new CustomEvent("attendance:recorded", { detail: e }));
        autoRefreshTables();
    });

    // 6. Attendance Verifications (Teacher room attendance changes)
    monitoringChannel.listen(".AttendanceVerificationUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("attendance:verified", { detail: e }));
        autoRefreshTables();
    });

    // 7. Email Log creation & status updates
    monitoringChannel.listen(".EmailLogCreated", (e) => {
        window.dispatchEvent(new CustomEvent("email:logged", { detail: e }));
        autoRefreshTables();
    });

    // 8. Grading updates
    monitoringChannel.listen(".GradingUpdated", (e) => {
        window.dispatchEvent(new CustomEvent("grading:updated", { detail: e }));
        autoRefreshTables();
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupGlobalRealtime);
} else {
    setupGlobalRealtime();
}

export { setupGlobalRealtime };
