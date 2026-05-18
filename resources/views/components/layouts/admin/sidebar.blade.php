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
{{-- 
                <!-- Dashboard -->
                <li>
                    <a href="/dashboard">
                        <i class="fa fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li> --}}

                <!-- MONITORING SECTION -->
                <li class="sidebar-section-label">
                    <small>MONITORING</small>
                </li>
                <li>
                    <a href="/entry-exit">
                        <i class="fa fa-exchange-alt"></i>
                        <span>Entry/Exit Monitoring</span>
                    </a>
                </li>
                <li>
                    <a href="/attendance">
                        <i class="fa fa-user-check"></i>
                        <span>Class Attendance</span>
                    </a>
                </li>
                {{-- <li>
                    <a href="/flagged">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span>Flagged Scans</span>
                    </a>
                </li> --}}
                <li>
                    <a href="/emails">
                        <i class="fa fa-envelope-open-text"></i>
                        <span>Email Monitoring</span>
                    </a>
                </li>

                <!-- MANAGEMENT SECTION -->
                <li class="sidebar-section-label">
                    <small>MANAGEMENT</small>
                </li>
                <li>
                    <a href="/student-management">
                        <i class="fa fa-user-graduate"></i>
                        <span>Student Management</span>
                    </a>
                </li>
                <li>
                    <a href="/enrollment">
                        <i class="fa fa-user-graduate"></i>
                        <span>Enrollment Management</span>
                    </a>
                </li>
                <li>
                    <a href="/teachers">
                        <i class="fa fa-chalkboard-teacher"></i>
                        <span>Teacher Management</span>
                    </a>
                </li>
                <li>
                    <a href="/users">
                        <i class="fa fa-user-shield"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="/grades">
                        <i class="fa fa-file-signature"></i>
                        <span>Grade Management</span>
                    </a>
                </li>

                <!-- OTHERS SECTION -->
                <li class="sidebar-section-label">
                    <small>Utilities</small>
                </li>

                <li>
                    <a href="/schedule-configuration">
                        <i class="fa fa-barcode"></i>
                        <span>Schedule Configuration</span>
                    </a>
                </li>
          
                <li>
                    <a href="/qr-generation">
                        <i class="fa fa-qrcode"></i>
                        <span>QR Generation</span>
                    </a>
                </li>
                <li>
                    <a href="/reports">
                        <i class="fa fa-file-contract"></i>
                        <span>Report Generation</span>
                    </a>
                </li>

                <!-- SYSTEM SECTION -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>
                <li>
                    <a href="/settings">
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