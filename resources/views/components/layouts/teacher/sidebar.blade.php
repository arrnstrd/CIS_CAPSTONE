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
<nav id="sidebar" class="teacher-sidebar-wrapper">

    <div class="teacher-sidebar-brand d-flex flex-column align-items-center py-3 px-4 text-center">
        <img src="{{ asset('./images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo" class="teacher-sidebar-brand-logo mb-2" style="width: 50px; height: 50px; object-fit: contain;">
        <h5 class="text-white mb-0 fw-bold" style="font-size: 1.05rem;">TEACHER PORTAL</h5>
        <small class="text-uppercase text-white fw-semibold" style="font-size: 0.62rem; letter-spacing: 1px;">
            CONCEPCION INTEGRATED SCHOOL
        </small>
    </div>

    <div class="teacher-sidebar-content">

        <!-- Navigation Menu -->
        <div class="teacher-sidebar-menu">
            <ul>

                <!-- MONITORING SECTION -->
                <li class="teacher-sidebar-section-label">
                    <small>General</small>
                </li>
                <li class="{{ request()->routeIs('room-attendance.*') || request()->routeIs('teacher.dashboard') || request()->routeIs('teacher.attendance') || request()->routeIs('teacher.time-in-time-out-history.*') ? 'active' : '' }}">
                    <a href="{{ route('room-attendance.index') }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Room Attendance</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('teacher.student-management*') || request()->routeIs('teacher.student-profile*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.student-management') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Student Management</span>
                    </a>
                </li>
                <li class="teacher-sidebar-section-label">
                    <small>Grading System</small>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.dashboard') || request()->routeIs('teacher.grading-system') || request()->routeIs('teacher.grading-system.grades') || request()->routeIs('teacher.grading-system.grades.*') || request()->routeIs('teacher.grading-system.grade-sheet*') || request()->routeIs('teacher.grading-system.assessments.*') || request()->routeIs('teacher.grading-system.import-data') || request()->routeIs('teacher.grading-system.debug-risk-scores') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.dashboard') }}">
                        <i class="fas fa-school"></i>
                        <span>My Classes</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.analytics*') || request()->routeIs('teacher.grading-system.by-level*') || request()->routeIs('teacher.grading-system.sections*') || request()->routeIs('teacher.grading-system.subjects*') || request()->routeIs('teacher.grading-system.attendance') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.analytics') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.at-risk*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.at-risk') }}">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>At-Risk</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.student-profile*') || request()->routeIs('teacher.grading-system.students*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.student-profile') }}">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.reports*') ? 'active' : '' }}">
                    <a href="{{ route('teacher.grading-system.reports') }}">
                        <i class="fas fa-file-lines"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teacher.grading-system.grading-rules*') || request()->routeIs('teacher.grading-system.comp-rules*') ? 'active' : '' }}">
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
            </ul>
        </div>

    </div>

    <!-- User Profile Footer -->
    <div class="teacher-sidebar-footer">
        <div class="teacher-sidebar-user-block">
            <div class="teacher-sidebar-user-avatar">
                {{ $initials }}
            </div>
            <div class="teacher-sidebar-user-info">
                <span class="teacher-sidebar-user-name" title="{{ $fullName }}">{{ $fullName }}</span>
                <span class="teacher-sidebar-user-role">{{ $roleName }}</span>
            </div>
            <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"
                class="teacher-sidebar-logout-btn" title="Log Out" aria-label="Log Out">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

</nav>
