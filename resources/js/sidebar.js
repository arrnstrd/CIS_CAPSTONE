document.addEventListener("DOMContentLoaded", function () {

    // ── Sidebar accordion dropdowns ──
    const dropdownLinks = document.querySelectorAll(".sidebar-dropdown > a");

    dropdownLinks.forEach(function (link) {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            const parent = this.parentElement;
            const submenu = parent.querySelector(".sidebar-submenu");
            const isAlreadyActive = parent.classList.contains("active");

            // Close all open dropdowns first
            document.querySelectorAll(".sidebar-dropdown.active").forEach(function (openItem) {
                openItem.classList.remove("active");
                const openSubmenu = openItem.querySelector(".sidebar-submenu");
                if (openSubmenu) openSubmenu.style.display = "none";
            });

            // Open clicked one if it wasn't already active
            if (!isAlreadyActive) {
                parent.classList.add("active");
                if (submenu) submenu.style.display = "block";
            }
        });
    });

    // ── Sidebar toggle ──
    const toggleBtn = document.getElementById("sidebarToggle");
    const wrapper = document.querySelector(".page-wrapper");

    if (toggleBtn && wrapper) {
        toggleBtn.addEventListener("click", function () {
            wrapper.classList.toggle("sidebar-collapsed");
        });
    }

});