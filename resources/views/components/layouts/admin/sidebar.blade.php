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

                <li>
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- MONITORING SECTION -->
                <li class="sidebar-section-label">
                    <small>MONITORING</small>
                </li>
                <li>
                    <a href="{{ route('qr-station.index') }}">
                        <i class="fas fa-tower-broadcast"></i>
                        <span>QR Station</span>
                    </a>
                </li>
                <li>
                    <a href="/time-in-time-out-history">
                        <i class="fas fa-exchange-alt"></i>
                        <span>In/Out History</span>
                    </a>
                </li>
                <li>
                    <a href="/attendance">
                        <i class="fas fa-user-check"></i>
                        <span>Class Attendance</span>
                    </a>
                </li>

                <li>
                    <a href="/emails">
                        <i class="fas fa-envelope"></i>
                        <span>Email Logs</span>
                    </a>
                </li>

                <!-- MANAGEMENT SECTION -->
                <li class="sidebar-section-label">
                    <small>MANAGEMENT</small>
                </li>


                <li>
                    <a href="/teachers">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teacher Management</span>
                    </a>
                </li>
                <li>
                    <a href="/users">
                        <i class="fas fa-user-shield"></i>
                        <span>User Management</span>
                    </a>
                </li>
                {{-- uncomment after building the grade management on teacher side --}}
                {{-- <li>
                    <a href="/grades">
                        <i class="fas fa-award"></i>
                        <span>Grade Management</span>
                    </a>
                </li> --}}


                <li>
                    <a href="/student-management">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li>
                    <a href="/bulk-import">
                        <i class="fas fa-file-import"></i>
                        <span>Bulk Import</span>
                    </a>
                </li>

                <li class="sidebar-section-label">
                    <small>setup and utilities</small>
                </li>


                <li>
                    <a href="/academic">
                        <i class="fas fa-user-graduate"></i>
                        <span>Academic</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('teaching-assignments.index') }}">
                        <i class="fas fa-user-cog"></i>
                        <span>Teaching Assignments</span>
                    </a>
                </li>


                {{-- <li>
                    <a href="/enrollment">
                        <i class="fas fa-user-plus"></i>
                        <span>Enrollment Management</span>
                    </a>
                </li>

                <li>
                    <a href="/sections">
                        <i class="fas fa-layer-group"></i>
                        <span>Section List</span>
                    </a>
                </li> --}}


                <li>
                    <a href="/schedule-configuration">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule Configuration</span>
                    </a>
                </li>

                <li>
                    <a href="/qr-generation">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Generation</span>
                    </a>
                </li>


                <!-- SYSTEM SECTION -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>
                <li>
                    <a href="/settings">
                        <i class="fas fa-sliders-h"></i>
                        <span>Settings</span>
                    </a>
                </li>

            </ul>
        </div>

    </div>

    <!-- Logout -->
    <div class="sidebar-footer p-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"
            class="btn-logout-action w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>

</nav>