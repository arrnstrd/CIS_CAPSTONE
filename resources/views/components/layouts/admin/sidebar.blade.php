@vite(['resources/css/app.css', 'resources/js/app.js'])

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <!-- Brand -->
        <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">ADMIN PORTAL</h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.6rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>

                <!-- Dashboard -->
                <li>
                    <a href="/dashboard">
                        <i class="fa fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Monitoring -->
                <li class="sidebar-dropdown">
                    <a href="javascript:void(0)">
                        <i class="fa fa-desktop"></i>
                        <span>Monitoring</span>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li><a href="/entry_exit"><i class="fa fa-exchange-alt me-2"></i>Entry/Exit Monitoring</a></li>
                            <li><a href="#"><i class="fa fa-user-check me-2"></i>Class Attendance</a></li>
                            <li><a href="#"><i class="fa fa-exclamation-triangle me-2"></i>Flagged Scans</a></li>
                            <li><a href="#"><i class="fa fa-envelope-open-text me-2"></i>Email Monitoring</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Management -->
                <li class="sidebar-dropdown">
                    <a href="javascript:void(0)">
                        <i class="fa fa-users-cog"></i>
                        <span>Management</span>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li><a href="#"><i class="fa fa-user-graduate me-2"></i>Student Management</a></li>
                            <li><a href="#"><i class="fa fa-chalkboard-teacher me-2"></i>Teacher Management</a></li>
                            <li><a href="#"><i class="fa fa-user-shield me-2"></i>User Role Management</a></li>
                            <li><a href="#"><i class="fa fa-file-signature me-2"></i>Grade Management</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Others -->
                <li class="sidebar-dropdown">
                    <a href="javascript:void(0)">
                        <i class="fa fa-folder-plus"></i>
                        <span>Others</span>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li><a href="#"><i class="fa fa-history me-2"></i>In/Out History</a></li>
                            <li><a href="#"><i class="fa fa-qrcode me-2"></i>QR Generation</a></li>
                            <li><a href="#"><i class="fa fa-file-contract me-2"></i>Report Generation</a></li>
                        </ul>
                    </div>
                </li>

                <!-- System label -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>

                <!-- Settings -->
                <li>
                    <a href="#">
                        <i class="fa fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>

            </ul>
        </div>

    </div>

    <!-- Logout -->
    <div class="sidebar-footer p-3">
        <a href="#"
           data-bs-toggle="modal"
           data-bs-target="#logoutModal"
           class="btn-logout-action w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="fa fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>

</nav>