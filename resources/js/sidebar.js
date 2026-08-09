document.addEventListener("DOMContentLoaded", function () {
    // ── Auto-Active Link Logic ──
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll(".sidebar-menu ul li a");

    sidebarLinks.forEach(function (link) {
        // Kapag nag-match yung href ng 'a' tag sa current URL
        if (link.getAttribute("href") === currentPath) {
            link.parentElement.classList.add("active");

            // Optional: Auto-scroll the sidebar para kita agad yung active link kung mahaba yung menu
            link.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    });
});
