@vite(['resources/css/app.css', 'resources/js/app.js'])

<aside class="sidebar" id="sidebar">
    <div class="sidebar-teacher-info">
        <div class="sidebar-teacher-avatar">
            <i class="fa-solid fa-circle-user"></i>
        </div>
        <div class="sidebar-teacher-details">
            <span class="sidebar-teacher-name">Ms. Maria Santos</span>
            <span class="sidebar-teacher-email">ms.santos@school.edu</span>
            <span class="sidebar-teacher-class">Grade 8 - Rizal</span>
        </div>
    </div>
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-chevron-left" id="toggleIcon"></i>
    </button>
    <nav class="sidebar-nav">
        <div class="nav-group">
            <p class="nav-label">MONITORING (DAILY USE)</p>
            <ul>
                <li><a href="student_masterlist.html" class="nav-item active">
                    <i class="fa-solid fa-users"></i> Student Masterlist
                </a></li>
                <li><a href="attendance.html" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i> Attendance
                </a></li>
            </ul>
        </div>
    </nav>
    <div class="sidebar-footer">
        <a href="#" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
