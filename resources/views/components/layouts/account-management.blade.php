<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Account Management' }} — Conception Integrated School</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .account-mgmt-body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .account-mgmt-header {
            background: #1F3690;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .account-mgmt-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            font-size: 0.88rem;
            padding: 0.65rem 1rem;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .account-mgmt-nav .nav-link:hover {
            color: #ffffff;
            border-bottom-color: rgba(255, 255, 255, 0.5);
        }

        .account-mgmt-nav .nav-link.active {
            color: #ffffff;
            font-weight: 600;
            border-bottom-color: #ffffff;
            background: transparent;
        }

        .account-mgmt-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .account-mgmt-field-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .account-mgmt-field-row:last-child {
            border-bottom: none;
        }

        .account-mgmt-field-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
        }

        .account-mgmt-field-value {
            font-size: 0.88rem;
            font-weight: 500;
            color: #1e293b;
            text-align: right;
        }
    </style>
</head>

<body class="account-mgmt-body">
    @php
        $user = auth()->user();
        $teacher = $user?->teacher;
        $displayName = $teacher?->full_name ?? ($user ? ($user->first_name . ' ' . $user->last_name) : 'Teacher');
        $displayEmail = $user?->email ?? '';
    @endphp

    <!-- Top Navigation Header -->
    <header class="account-mgmt-header">
        <div class="container-xl">
            <div class="d-flex align-items-center justify-content-between py-3">
                <!-- Branding & Back Button -->
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-light btn-sm d-inline-flex align-items-center gap-2" style="border-color: rgba(255, 255, 255, 0.35); font-size: 0.8rem;">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span><span class="d-none d-sm-inline">Return to Teacher Portal</span><span class="d-inline d-sm-none">Back</span></span>
                    </a>
                    <div class="vr bg-white opacity-25 d-none d-md-block" style="height: 24px;"></div>
                    <div>
                        <div class="fw-bold text-white lh-1" style="font-size: 0.95rem;">Manage Account</div>
                        <small class="text-white-50" style="font-size: 0.72rem;">Conception Integrated School</small>
                    </div>
                </div>

                <!-- User Profile & Logout -->
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold text-white lh-1" style="font-size: 0.82rem;">{{ $displayName }}</div>
                        <small class="text-white-50" style="font-size: 0.7rem;">{{ $displayEmail }}</small>
                    </div>
                    <button type="button" class="btn btn-sm text-white-50 p-1" data-bs-toggle="modal" data-bs-target="#logoutModal" title="Sign Out" aria-label="Sign Out">
                        <i class="fa-solid fa-arrow-right-from-bracket fs-6"></i>
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="account-mgmt-nav d-flex gap-1 border-top" style="border-color: rgba(255, 255, 255, 0.15) !important;">
                <a href="{{ route('teacher.account.personal-information') }}" class="nav-link {{ request()->routeIs('teacher.account.personal-information') ? 'active' : '' }}">
                    <i class="fa-solid fa-user me-1.5"></i> Personal Information
                </a>
                <a href="{{ route('teacher.account.security') }}" class="nav-link {{ request()->routeIs('teacher.account.security') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved me-1.5"></i> Security &amp; Sign-in
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-grow-1 py-4">
        <div class="container-xl">
            {{ $slot }}
        </div>
    </main>

    <!-- Logout Confirmation Modal -->
    @include('components.logout-modal')

    @stack('scripts')
</body>

</html>


