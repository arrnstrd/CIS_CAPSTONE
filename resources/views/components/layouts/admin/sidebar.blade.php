@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();
    $isSchoolAdmin = $currentUser && $currentUser->isAdmin() && !$isSuperAdmin;
    $isScannerOperator = $currentUser && $currentUser->isScannerOperator();
@endphp

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <!-- Brand -->
        <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">
                {{ $isSuperAdmin ? 'SUPER ADMIN PORTAL' : ($isSchoolAdmin ? 'SCHOOL ADMIN PORTAL' : 'ADMIN PORTAL') }}
            </h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.6rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
            @if($currentUser)
                <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                    <span class="text-white small fw-medium">{{ $currentUser->first_name }} {{ $currentUser->last_name }}</span>
                    @if($isSuperAdmin)
                        <span class="badge bg-warning text-dark fw-bold px-2 py-0.5" style="font-size: 0.65rem;">Super Admin</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 0.65rem;">{{ $currentUser->role_label }}</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                @if($isSuperAdmin)
                    <!-- SUPER ADMIN AUDIT & ACCESS CONTROL -->
                    <li class="sidebar-section-label">
                        <small>ACCESS CONTROL & AUDITING</small>
                    </li>
                    <li class="{{ request()->routeIs('users.*') || request()->is('users*') ? 'active' : '' }}">
                        <a href="/users">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('security-audit-log.*') || request()->is('security-audit-log*') ? 'active' : '' }}">
                        <a href="/security-audit-log">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security Audit Log</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('recent-activity.*') || request()->is('recent-activity*') ? 'active' : '' }}">
                        <a href="/recent-activity">
                            <i class="fas fa-list-alt"></i>
                            <span>Recent Activity</span>
                        </a>
                    </li>
                @endif

                @if(!$isSuperAdmin)
                    <!-- MONITORING SECTION -->
                    <li class="sidebar-section-label">
                        <small>MONITORING</small>
                    </li>
                    <li class="{{ request()->routeIs('qr-station.*') || request()->is('qr-station*') ? 'active' : '' }}">
                        <a href="{{ route('qr-station.index') }}">
                            <i class="fas fa-tower-broadcast"></i>
                            <span>QR Station</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('time-in-time-out-history.*') || request()->routeIs('time-in-time-out.*') || request()->is('time-in-time-out-history*') || request()->is('entry-exit*') ? 'active' : '' }}">
                        <a href="/time-in-time-out-history">
                            <i class="fas fa-exchange-alt"></i>
                            <span>In/Out History</span>
                        </a>
                    </li>

                    @if(!$isScannerOperator)
                        <li class="{{ request()->routeIs('attendance') || request()->is('attendance*') ? 'active' : '' }}">
                            <a href="/attendance">
                                <i class="fas fa-user-check"></i>
                                <span>Class Attendance</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('emails.*') || request()->routeIs('*.email') || request()->is('emails*') ? 'active' : '' }}">
                            <a href="/emails">
                                <i class="fas fa-envelope"></i>
                                <span>Email Logs</span>
                            </a>
                        </li>

                        <!-- MANAGEMENT SECTION -->
                        <li class="sidebar-section-label">
                            <small>MANAGEMENT</small>
                        </li>

                        <li class="{{ request()->routeIs('teachers.*') || request()->is('teachers*') ? 'active' : '' }}">
                            <a href="/teachers">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Teacher Management</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('student-management.*') || request()->routeIs('student.profile*') || request()->routeIs('student.*') || request()->routeIs('students.*') || request()->is('student-management*') || request()->is('student-profile*') || request()->is('students*') ? 'active' : '' }}">
                            <a href="/student-management">
                                <i class="fas fa-user-graduate"></i>
                                <span>Students</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('bulk-import*') || request()->routeIs('import.*') || request()->is('bulk-import*') || request()->is('import*') ? 'active' : '' }}">
                            <a href="/bulk-import">
                                <i class="fas fa-file-import"></i>
                                <span>Bulk Import</span>
                            </a>
                        </li>

                        <li class="sidebar-section-label">
                            <small>SETUP & UTILITIES</small>
                        </li>

                        <li class="{{ request()->routeIs('academic.*') || request()->routeIs('sections.*') || request()->routeIs('subjects.*') || request()->routeIs('school-years.*') || request()->is('academic*') || request()->is('sections*') || request()->is('subjects*') || request()->is('school-years*') ? 'active' : '' }}">
                            <a href="/academic">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Academic</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('teaching-assignments.*') || request()->is('teaching-assignments*') ? 'active' : '' }}">
                            <a href="{{ route('teaching-assignments.index') }}">
                                <i class="fas fa-user-cog"></i>
                                <span>Teaching Assignments</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('schedconfig.*') || request()->is('schedule-configuration*') ? 'active' : '' }}">
                            <a href="/schedule-configuration">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Schedule Configuration</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('qr.*') || request()->is('qr-generation*') ? 'active' : '' }}">
                            <a href="/qr-generation">
                                <i class="fas fa-qrcode"></i>
                                <span>QR Generation</span>
                            </a>
                        </li>

                        <!-- SYSTEM SECTION -->
                        <li class="sidebar-section-label">
                            <small>SYSTEM</small>
                        </li>
                        <li class="{{ request()->is('settings*') ? 'active' : '' }}">
                            <a href="/settings">
                                <i class="fas fa-sliders-h"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    @endif
                @endif
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