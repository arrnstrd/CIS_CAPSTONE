

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const body    = document.querySelector(".main-body");
    const openBtn = document.getElementById("sidebarOpenBtn");
    const icon    = document.getElementById("toggleIcon");

    sidebar.classList.toggle("sidebar-collapsed");
    body.classList.toggle("main-expanded");

    if (sidebar.classList.contains("sidebar-collapsed")) {
        openBtn.style.display = "flex";
        if (icon) icon.className = "fa-solid fa-chevron-right";
    } else {
        openBtn.style.display = "none";
        if (icon) icon.className = "fa-solid fa-chevron-left";
    }
}
