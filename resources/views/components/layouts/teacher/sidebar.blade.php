@vite(['resources/css/app.css', 'resources/js/app.js'])

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <div class="sidebar-brand d-flex flex-column align-items-center py-3 px-4 text-center">
            <img src="{{ asset('./images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo" class="sidebar-brand-logo mb-2" style="width: 50px; height: 50px; object-fit: contain;">
            <h5 class="text-white mb-0 fw-bold" style="font-size: 1.05rem;">TEACHER PORTAL</h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.62rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
        </div>


        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>

                <!-- MONITORING SECTION -->
                <li class="sidebar-section-label">
                    <small>General</small>
                </li>
                <li>
                    <a href="{{ route('room-attendance.index') }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Room Attendance</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('teacher.student-management') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Student Management</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.*') ? 'active' : '' }}">
                    <a href="#gradingSystemSubmenu" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->routeIs('teacher.grading-system.*') ? 'true' : 'false' }}"
                        aria-controls="gradingSystemSubmenu"
                        class="sidebar-submenu-toggle d-flex justify-content-between align-items-center">
                        <span class="d-flex align-items-center">
                            <i class="fas fa-user-graduate"></i>
                            <span>Grading System</span>
                        </span>
                        <i class="fas fa-chevron-down sidebar-submenu-caret me-3"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('teacher.grading-system.*') ? 'show' : '' }}" id="gradingSystemSubmenu">
                        <ul class="sidebar-submenu">
                            <li class="{{ request()->routeIs('teacher.grading-system.dashboard') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.dashboard') }}">My Classes</a>
                            </li>
                            <li class="{{ request()->routeIs('teacher.grading-system.analytics') || request()->routeIs('teacher.grading-system.analytics.*') || request()->routeIs('teacher.grading-system.by-level') || request()->routeIs('teacher.grading-system.sections') || request()->routeIs('teacher.grading-system.subjects') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.analytics') }}">Analytics</a>
                            </li>
                            <li class="{{ request()->routeIs('teacher.grading-system.at-risk') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.at-risk') }}">At-Risk</a>
                            </li>
                            <li class="{{ request()->routeIs('teacher.grading-system.student-profile') || request()->routeIs('teacher.grading-system.students') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.student-profile') }}">Students</a>
                            </li>
                            <li class="{{ request()->routeIs('teacher.grading-system.reports') || request()->routeIs('teacher.grading-system.reports.*') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.reports') }}">Reports</a>
                            </li>
                            <li class="{{ request()->routeIs('teacher.grading-system.grading-rules') ? 'active' : '' }}">
                                <a href="{{ route('teacher.grading-system.grading-rules') }}">Grading Rules</a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- <li>
                    <a href="#">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Generation</span>
                    </a>
                </li> --}}
                {{-- <li>
                    <a href="/reports">
                        <i class="fas fa-chart-bar"></i>
                        <span>Report Generation</span>
                    </a>
                </li> --}}

                <!-- SYSTEM SECTION -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>
                <li>
                    <a href="#">
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