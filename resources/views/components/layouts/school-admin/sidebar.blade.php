@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $firstName = $currentUser?->first_name ?? '';
    $lastName = $currentUser?->last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName) ?: ($currentUser?->name ?? 'School Admin');
    $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
    $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'SA';
    $roleName = $currentUser?->role_label ?? 'School Admin';
@endphp

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <!-- Brand -->
        <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">
                SCHOOL ADMIN PORTAL
            </h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.6rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>
                <li class="{{ request()->routeIs('admin.dashboard') || request()->is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- MONITORING SECTION -->
                <li class="sidebar-section-label">
                    <small>MONITORING</small>
                </li>
                <li class="{{ request()->routeIs('school_admin.qr-station.*') || request()->is('school-admin/qr-station*') ? 'active' : '' }}">
                    <a href="{{ route('school_admin.qr-station.index') }}">
                        <i class="fas fa-tower-broadcast"></i>
                        <span>QR Station</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('school_admin.time-in-time-out-history.*') || request()->is('school-admin/time-in-time-out-history*') ? 'active' : '' }}">
                    <a href="{{ route('school_admin.time-in-time-out-history.index') }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>In/Out History</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('attendance') || request()->routeIs('attendance.*') || request()->is('attendance*') || request()->is('school_admin/attendance*') ? 'active' : '' }}">
                    <a href="{{ route('attendance') }}">
                        <i class="fas fa-user-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('emails.*') || request()->routeIs('*.email') || request()->is('emails*') ? 'active' : '' }}">
                    <a href="{{ route('emails.index') }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email Logs</span>
                    </a>
                </li>

                <!-- MANAGEMENT SECTION -->
                <li class="sidebar-section-label">
                    <small>MANAGEMENT</small>
                </li>
                <li class="{{ request()->routeIs('teachers.*') || request()->is('teachers*') ? 'active' : '' }}">
                    <a href="{{ route('teachers.index') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teachers</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('student-management.*') || request()->routeIs('student.profile*') || request()->routeIs('student.*') || request()->routeIs('students.*') || request()->is('student-management*') || request()->is('student-profile*') || request()->is('students*') ? 'active' : '' }}">
                    <a href="{{ route('student-management.index') }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('bulk-import*') || request()->routeIs('import.*') || request()->is('bulk-import*') || request()->is('import*') ? 'active' : '' }}">
                    <a href="{{ route('bulk-import') }}">
                        <i class="fas fa-file-import"></i>
                        <span>Bulk Import</span>
                    </a>
                </li>

                <!-- ACADEMIC SETUP -->
                <li class="sidebar-section-label">
                    <small>ACADEMIC SETUP</small>
                </li>
                <li class="{{ request()->routeIs('academic.*') || request()->routeIs('sections.*') || request()->routeIs('subjects.*') || request()->is('academic*') || request()->is('sections*') || request()->is('subjects*') ? 'active' : '' }}">
                    <a href="{{ route('academic.index') }}">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Academic Setup</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('teaching-assignments.*') || request()->is('teaching-assignments*') ? 'active' : '' }}">
                    <a href="{{ route('teaching-assignments.index') }}">
                        <i class="fas fa-user-tag"></i>
                        <span>Teaching Assignments</span>
                    </a>
                </li>

                <!-- UTILITIES SECTION -->
                <li class="sidebar-section-label">
                    <small>UTILITIES</small>
                </li>
                <li class="{{ request()->routeIs('schedconfig.*') || request()->routeIs('schedule-configuration.*') || request()->is('schedule-configuration*') ? 'active' : '' }}">
                    <a href="{{ route('schedule-configuration.index') }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule Configuration</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('qr.*') || request()->is('qr-generation*') ? 'active' : '' }}">
                    <a href="{{ route('qr.index') }}">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Generation</span>
                    </a>
                </li>

                <!-- SYSTEM SECTION -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>
                <li class="{{ request()->routeIs('settings.*') || request()->is('settings*') ? 'active' : '' }}">
                    <a href="{{ route('settings.index') }}">
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
