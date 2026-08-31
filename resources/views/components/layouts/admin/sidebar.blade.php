@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();
    $isSchoolAdmin = $currentUser && $currentUser->isAdmin() && !$isSuperAdmin;
    $isScannerOperator = $currentUser && $currentUser->isScannerOperator();
    $firstName = $currentUser?->first_name ?? '';
    $lastName = $currentUser?->last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName) ?: ($currentUser?->name ?? 'User');
    $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
    $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'U';
    $roleName = $currentUser?->role_label ?? ($isSuperAdmin ? 'Super Admin' : ($isSchoolAdmin ? 'School Admin' : ($isScannerOperator ? 'Scanner Operator' : 'Admin')));
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

                @if($isSuperAdmin)
                    <!-- SUPER ADMIN AUDIT & ACCESS CONTROL -->
                    <li class="sidebar-section-label">
                        <small>ACCESS CONTROL & AUDITING</small>
                    </li>
                    <li>
                        <a href="/users">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/security-audit-log">
                            <i class="fas fa-shield-alt"></i>
                            <span>Security Audit Log</span>
                        </a>
                    </li>
                    <li>
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

                    @if(!$isScannerOperator)
                        <li>
                            <a href="{{ route('attendance.grade-level') }}">
                                <i class="fas fa-user-check"></i>
                                <span>Attendance</span>
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
                                <span>Teachers</span>
                            </a>
                        </li>
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
                            <small>SETUP & UTILITIES</small>
                        </li>

                        <li>
                            <a href="/academic">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Academic</span>
                            </a>
                        </li>


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
                    @endif
                @endif
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