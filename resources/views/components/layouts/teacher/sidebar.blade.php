@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $firstName = $currentUser?->first_name ?? '';
    $lastName = $currentUser?->last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName) ?: ($currentUser?->name ?? 'Teacher');
    $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
    $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'T';
    $roleName = $currentUser?->role_label ?? 'Teacher';
@endphp

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
                        <span>My Classes</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.analytics') || request()->routeIs('teacher.grading-system.analytics.*') || request()->routeIs('teacher.grading-system.by-level') || request()->routeIs('teacher.grading-system.sections') || request()->routeIs('teacher.grading-system.subjects') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.analytics') }}">
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.at-risk') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.at-risk') }}">
                        <span>At-Risk</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.student-profile') || request()->routeIs('teacher.grading-system.students') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.student-profile') }}">
                        <span>Students</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.reports') || request()->routeIs('teacher.grading-system.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.reports') }}">
                        <span>Reports</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.grading-rules') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.grading-rules') }}">
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

    <!-- User Profile Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user-block">
            <div class="sidebar-user-avatar">
                {{ $initials }}
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name" title="{{ $fullName }}">{{ $fullName }}</span>
                <span class="sidebar-user-role">{{ $roleName }}</span>
            </div>
            <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"
                class="sidebar-logout-btn" title="Log Out" aria-label="Log Out">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

</nav>