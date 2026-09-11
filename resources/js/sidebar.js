document.addEventListener("DOMContentLoaded", function () {
    // ── Auto-Active Link Logic ──
    const currentPath = window.location.pathname;

    const sidebarLinks = document.querySelectorAll(
        ".sa-sidebar-menu ul li a, .teacher-sidebar-menu ul li a, .scanner-sidebar-menu ul li a, .super-admin-sidebar-menu ul li a, .sidebar-menu ul li a"
    );

    sidebarLinks.forEach(function (link) {
        // Kapag nag-match yung href ng 'a' tag sa current URL
        if (link.getAttribute("href") === currentPath) {
            link.parentElement.classList.add("active");

            // Optional: Auto-scroll the sidebar para kita agad yung active link kung mahaba yung menu
            link.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });


    // ── Responsive Hamburger / Sidebar Drawer ──
    // Works for all POVs: School Admin, Teacher, Scanner Operator, Super Admin.
    // Toggle IDs: saSidebarToggle, teacherSidebarToggle, scannerSidebarToggle, superAdminSidebarToggle
    // Overlay IDs: saSidebarOverlay, teacherSidebarOverlay, scannerSidebarOverlay, superAdminSidebarOverlay
    // Sidebar wrapper: first element with class ending in "-sidebar-wrapper"

    const TABLET_BREAKPOINT = 899;

    const togglePairs = [
        { toggleId: "saSidebarToggle",         overlayId: "saSidebarOverlay",         sidebarClass: "sa-sidebar-wrapper" },
        { toggleId: "teacherSidebarToggle",    overlayId: "teacherSidebarOverlay",    sidebarClass: "teacher-sidebar-wrapper" },
        { toggleId: "scannerSidebarToggle",    overlayId: "scannerSidebarOverlay",    sidebarClass: "scanner-sidebar-wrapper" },
        { toggleId: "superAdminSidebarToggle", overlayId: "superAdminSidebarOverlay", sidebarClass: "super-admin-sidebar-wrapper" },
    ];

    togglePairs.forEach(function ({ toggleId, overlayId, sidebarClass }) {
        const toggleBtn = document.getElementById(toggleId);
        const overlay   = document.getElementById(overlayId);
        const sidebar   = document.querySelector("." + sidebarClass);

        if (!toggleBtn || !overlay || !sidebar) return;

        function openSidebar() {
            sidebar.classList.add("is-open");
            overlay.classList.add("is-visible");
            toggleBtn.setAttribute("aria-expanded", "true");
            document.body.style.overflow = "hidden"; // prevent body scroll when drawer open
        }

        function closeSidebar() {
            sidebar.classList.remove("is-open");
            overlay.classList.remove("is-visible");
            toggleBtn.setAttribute("aria-expanded", "false");
            document.body.style.overflow = "";
        }

        toggleBtn.addEventListener("click", function () {
            if (sidebar.classList.contains("is-open")) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        overlay.addEventListener("click", closeSidebar);

        // Close on Escape key
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && sidebar.classList.contains("is-open")) {
                closeSidebar();
                toggleBtn.focus();
            }
        });

        // On resize: if viewport exceeds tablet breakpoint, ensure sidebar is not stuck in drawer state
        window.addEventListener("resize", function () {
            if (window.innerWidth > TABLET_BREAKPOINT) {
                sidebar.classList.remove("is-open");
                overlay.classList.remove("is-visible");
                toggleBtn.setAttribute("aria-expanded", "false");
                document.body.style.overflow = "";
            }
        });
    });
});
