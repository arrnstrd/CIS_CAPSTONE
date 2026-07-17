function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainBody = document.querySelector('.main-body');
    const toggleIcon = document.getElementById('toggleIcon');
    const openBtn = document.getElementById('sidebarOpenBtn');

    const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');

    if (isCollapsed) {
        mainBody.classList.add('main-expanded');
        toggleIcon.className = 'fa-solid fa-chevron-right';
        openBtn.style.display = 'flex';
    } else {
        mainBody.classList.remove('main-expanded');
        toggleIcon.className = 'fa-solid fa-chevron-left';
        openBtn.style.display = 'none';
    }
}
