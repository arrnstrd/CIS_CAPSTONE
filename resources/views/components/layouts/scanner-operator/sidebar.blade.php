@vite(['resources/css/app.css', 'resources/js/app.js'])

@php
    $currentUser = auth()->user();
    $firstName = $currentUser?->first_name ?? '';
    $lastName = $currentUser?->last_name ?? '';
    $fullName = trim($firstName . ' ' . $lastName) ?: ($currentUser?->name ?? 'Scanner Operator');
    $firstInitial = $firstName !== '' ? mb_substr($firstName, 0, 1) : '';
    $lastInitial = $lastName !== '' ? mb_substr($lastName, 0, 1) : '';
    $initials = mb_strtoupper($firstInitial . $lastInitial) ?: 'OP';
    $roleName = $currentUser?->role_label ?? 'Scanner Operator';
@endphp

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <!-- Brand -->
        <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">
                QR STATION OPERATOR
            </h5>
            <small class="text-uppercase text-white fw-semibold" style="font-size: 0.6rem; letter-spacing: 1px;">
                CONCEPCION INTEGRATED SCHOOL
            </small>
        </div>

        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>
                <!-- DASHBOARD -->
                <li class="{{ request()->routeIs('scanner_operator.dashboard') || request()->is('scanner/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('scanner_operator.dashboard') }}">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- SCANNER STATION -->
                <li class="sidebar-section-label">
                    <small>ATTENDANCE TERMINAL</small>
                </li>
                <li class="{{ request()->routeIs('qr-station.*') || request()->is('qr-station*') ? 'active' : '' }}">
                    <a href="{{ route('qr-station.index') }}">
                        <i class="fas fa-tower-broadcast"></i>
                        <span>QR Station</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('time-in-time-out-history.*') || request()->routeIs('time-in-time-out.*') || request()->is('time-in-time-out-history*') || request()->is('entry-exit*') ? 'active' : '' }}">
                    <a href="{{ route('time-in-time-out-history.index') }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>In/Out History</span>
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
