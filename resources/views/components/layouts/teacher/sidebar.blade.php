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
                <li class="sidebar-section-label">
                    <small>Grading System</small>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.dashboard') }}">
                        <i class="fas fa-school"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.analytics') || request()->routeIs('teacher.grading-system.analytics.*') || request()->routeIs('teacher.grading-system.by-level') || request()->routeIs('teacher.grading-system.sections') || request()->routeIs('teacher.grading-system.subjects') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.analytics') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.at-risk') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.at-risk') }}">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>At-Risk</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.student-profile') || request()->routeIs('teacher.grading-system.students') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.student-profile') }}">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.reports') || request()->routeIs('teacher.grading-system.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.reports') }}">
                        <i class="fas fa-file-lines"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.grading-rules') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.grading-rules') }}">
                        <i class="fas fa-scale-balanced"></i>
                        <span>Grading Rules</span>
                    </a>
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

                <!-- SETTINGS SECTION -->
                <li class="sidebar-section-label">
                    <small>SETTINGS</small>
                </li>
                <li class="{{ request()->routeIs('teacher.settings.profile') ? 'active' : '' }}">
                    <a href="{{ route('teacher.settings.profile') }}">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.settings.notifications') ? 'active' : '' }}">
                    <a href="{{ route('teacher.settings.notifications') }}">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.settings.appearance') ? 'active' : '' }}">
                    <a href="{{ route('teacher.settings.appearance') }}">
                        <i class="fas fa-palette"></i>
                        <span>Appearance</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.settings.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('teacher.settings.dashboard') }}">
                        <i class="fas fa-sliders"></i>
                        <span>Dashboard Preferences</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.settings.security') ? 'active' : '' }}">
                    <a href="{{ route('teacher.settings.security') }}">
                        <i class="fas fa-shield-halved"></i>
                        <span>Account & Security</span>
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
