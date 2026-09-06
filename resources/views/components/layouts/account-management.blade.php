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

        .account-mgmt-titleblock {
            background: #1F3690;
            padding: 14px 0 0;
        }

        .account-mgmt-titleblock .back-link {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.82rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 6px;
            padding: 8px 14px;
            transition: all .15s;
        }

        .account-mgmt-titleblock .back-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .account-mgmt-titleblock .titlebar-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .account-mgmt-titleblock h1 {
            color: #ffffff;
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
        }

        .account-mgmt-titleblock p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.82rem;
            margin: 2px 0 0;
        }

        .account-mgmt-nav {
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .account-mgmt-nav .nav-link {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
            font-size: 0.88rem;
            padding: 14px 20px;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .account-mgmt-nav .nav-link i {
            font-size: 0.82rem;
        }

        .account-mgmt-nav .nav-link:hover {
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.04);
        }

        .account-mgmt-nav .nav-link.active {
            color: #ffffff;
            font-weight: 600;
            border-bottom-color: #60a5fa;
            background: rgba(255, 255, 255, 0.06);
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

    <header class="account-mgmt-titleblock">
        <div class="container-xl">
            <div class="titlebar-row">
                <div>
                    <h1>Manage Account</h1>
                    <p>Conception Integrated School</p>
                </div>
                <a href="{{ route('teacher.dashboard') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Return to Teacher Portal</span>
                </a>
            </div>

            <nav class="account-mgmt-nav d-flex gap-1">
                <a href="{{ route('teacher.account.personal-information') }}" class="nav-link {{ request()->routeIs('teacher.account.personal-information') ? 'active' : '' }}">
                    <i class="fa-solid fa-user"></i> Personal Information
                </a>
                <a href="{{ route('teacher.account.security') }}" class="nav-link {{ request()->routeIs('teacher.account.security') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i> Security &amp; Sign-in
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-grow-1 py-4">
        <div class="container-xl">
            {{ $slot }}
        </div>
    </main>

    @include('components.logout-modal')

    @stack('scripts')
</body>

</html>