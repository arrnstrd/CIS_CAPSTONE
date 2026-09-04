@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $firstName = $currentUser?->first_name ?? '';
    $lastName = $currentUser?->last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName) ?: ($currentUser?->name ?? 'Super Admin');
    $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
    $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'SA';
    $roleName = $currentUser?->role_label ?? 'Super Admin';
@endphp

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <!-- Brand -->
        <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">
                SUPER ADMIN PORTAL
            </h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.6rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>
                <li class="{{ request()->routeIs('super_admin.dashboard') || request()->is('super-admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('super_admin.dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- SUPER ADMIN AUDIT & ACCESS CONTROL -->
                <li class="sidebar-section-label">
                    <small>ACCESS CONTROL & AUDITING</small>
                </li>
                <li class="{{ request()->routeIs('users.*') || request()->is('users*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}">
                        <i class="fas fa-users-cog"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('security-audit-log.*') || request()->is('security-audit-log*') ? 'active' : '' }}">
                    <a href="{{ route('security-audit-log.index') }}">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security Audit Log</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('recent-activity.*') || request()->is('recent-activity*') ? 'active' : '' }}">
                    <a href="{{ route('recent-activity.index') }}">
                        <i class="fas fa-list-alt"></i>
                        <span>Recent Activity</span>
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
