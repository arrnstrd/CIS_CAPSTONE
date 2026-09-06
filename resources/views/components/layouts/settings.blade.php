@php
    $sUser      = auth()->user();
    $sfName     = $sUser?->first_name ?? '';
    $slName     = $sUser?->last_name  ?? '';
    $sFullName  = trim($sfName . ' ' . $slName) ?: 'School Administrator';
    $sEmail     = $sUser?->email ?? 'admin@example.com';
    $sInitials  = mb_strtoupper(mb_substr($sfName, 0, 1) . mb_substr($slName, 0, 1)) ?: 'SA';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account &amp; Settings — CIS Management System</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        /* ═══════════════════════════════════════════════════════════
           School Admin — Dedicated Settings Layout
           Prefix: sett-
        ═══════════════════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; }

        body.sett-body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f0f4f9;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #1e293b;
        }

        /* ── Thin Blue Top Navigation ────────────────────────────── */
        .sett-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #2F4AC0;
            background: linear-gradient(90deg, #1e3a8a 0%, #2F4AC0 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 52px;
        }

        .sett-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.855rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: background 0.15s ease, transform 0.12s ease;
        }

        .sett-back-link i { font-size: 0.8rem; }

        .sett-back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateX(-2px);
        }

        .sett-topbar__identity {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .sett-topbar__avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            color: #1e3a8a;
            font-size: 0.76rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        .sett-topbar__name {
            font-size: 0.84rem;
            color: #ffffff;
            font-weight: 600;
        }

        /* ── Main Container ──────────────────────────────────────── */
        .sett-main {
            padding: 2.25rem 1.5rem 3.5rem;
            min-height: calc(100vh - 52px);
        }

        .sett-container {
            max-width: 1180px;
            margin: 0 auto;
        }

        .sett-settings-layout {
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr);
            align-items: start;
            gap: 2.5rem;
        }

        .sett-sidebar {
            position: sticky;
            top: 76px;
        }

        .sett-sidebar__eyebrow {
            padding: 0 1rem 0.7rem;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        /* ── Header Area ─────────────────────────────────────────── */
        .sett-page-header {
            margin-bottom: 1.5rem;
        }

        .sett-page-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.35rem;
            letter-spacing: -0.02em;
        }

        .sett-page-subtitle {
            font-size: 0.9rem;
            color: #64748b;
            margin: 0;
        }

        /* ── Google-style Sidebar Pill Navigation ─────────────────── */
        .sett-nav-pills {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .sett-nav-pill {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 0.55rem;
            padding: 0.9rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            background: transparent;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .sett-nav-pill i {
            font-size: 0.85rem;
            color: #64748b;
            transition: color 0.15s ease;
        }

        .sett-nav-pill:hover {
            color: #0f172a;
            background: #f1f5f9;
        }

        .sett-nav-pill.active {
            color: #ffffff;
            background: #2F4AC0;
            background: linear-gradient(135deg, #1e3a8a 0%, #2F4AC0 100%);
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
        }

        .sett-nav-pill.active::after {
            content: "";
            width: 6px;
            height: 6px;
            margin-left: auto;
            border-radius: 50%;
            background: #bfdbfe;
        }

        .sett-nav-pill.active i {
            color: #ffffff;
        }

        /* ── White Main Content Card ─────────────────────────────── */
        .sett-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
            overflow: visible;
        }

        .sett-card-header {
            padding: 1.75rem 2rem 0;
        }

        .sett-card-title {
            font-size: 1.18rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.3rem;
            letter-spacing: -0.01em;
        }

        .sett-card-desc {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }

        /* ── Sub-headings (Prominent as requested) ────────────────── */
        .sett-subheading {
            padding: 1.75rem 2rem 0.6rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sett-subheading::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #f1f5f9;
        }

        /* ── List Rows (Section -> Setting -> Value -> Action) ───── */
        .sett-list {
            padding: 0 2rem 1rem;
        }

        .sett-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.15rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .sett-row:last-child {
            border-bottom: none;
        }

        .sett-row__info {
            flex: 1;
            min-width: 0;
        }

        .sett-row__label {
            font-size: 0.94rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.2rem;
        }

        .sett-row__desc {
            font-size: 0.81rem;
            color: #64748b;
            line-height: 1.35;
        }

        .sett-row__value {
            font-size: 0.92rem;
            color: #334155;
            font-weight: 500;
            text-align: right;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }

        .sett-row__action {
            flex-shrink: 0;
        }

        /* ── Action Buttons ──────────────────────────────────────── */
        .sett-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.42rem 1.05rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e3a8a;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 9999px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .sett-action-btn:hover {
            background: #2F4AC0;
            color: #ffffff;
            border-color: #2F4AC0;
            box-shadow: 0 2px 6px rgba(47, 74, 192, 0.25);
        }

        .sett-action-btn--primary {
            background: #2F4AC0;
            color: #ffffff;
            border-color: #2F4AC0;
        }

        .sett-action-btn--primary:hover {
            background: #1e3a8a;
            border-color: #1e3a8a;
        }

        /* ── School Year Elements (Natural & Unconstrained) ──────── */
        .sett-sy-wrapper {
            padding: 1.25rem 2rem 2rem;
        }

        .sett-sy-active-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%);
            border: 1px solid #bbf7d0;
            border-radius: 0.85rem;
            padding: 1.15rem 1.35rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .sett-sy-active-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0.75rem;
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #15803d;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sett-sy-active-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #16a34a;
        }

        .sett-sy-active-year {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .sett-sy-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.95rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .sett-sy-item:last-child {
            border-bottom: none;
        }

        .sett-sy-item__year {
            font-size: 0.94rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* ── Bottom Discoverable CTA ─────────────────────────────── */
        .sett-bottom-bar {
            margin-top: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sett-bottom-back {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #475569;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
        }

        .sett-bottom-back:hover {
            color: #1e3a8a;
            background: #e2e8f0;
        }

        /* ── Toast Notification ──────────────────────────────────── */
        .sett-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1090;
            background: #0f172a;
            color: #ffffff;
            padding: 0.85rem 1.35rem;
            border-radius: 0.85rem;
            font-size: 0.88rem;
            font-weight: 500;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            gap: 0.65rem;
            animation: settToastIn 0.25s ease;
        }

        @keyframes settToastIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Strength Meter ──────────────────────────────────────── */
        .sett-pwd-strength-track {
            height: 4px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            margin: 0.45rem 0 0.25rem;
        }

        .sett-pwd-strength-bar {
            height: 100%;
            border-radius: 9999px;
            width: 0%;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .sett-pwd-strength-label {
            font-size: 0.74rem;
            font-weight: 600;
        }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 767px) {
            .sett-topbar { padding: 0 1rem; }
            .sett-main { padding: 1.5rem 1rem 2.5rem; }
            .sett-settings-layout { display: block; }
            .sett-sidebar { position: static; margin-bottom: 1.5rem; }
            .sett-sidebar__eyebrow { padding-left: 0.25rem; }
            .sett-nav-pills { gap: 0.4rem; }
            .sett-nav-pill { padding: 0.75rem 1rem; }
            .sett-card-header,
            .sett-subheading,
            .sett-list,
            .sett-sy-wrapper { padding-left: 1.25rem; padding-right: 1.25rem; }
            .sett-row { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .sett-row__value { text-align: left; }
        }
    </style>
</head>
<body class="sett-body">

    {{-- Shared Toast Notification --}}
    <div id="sett-toast" class="sett-toast d-none" role="status" aria-live="polite">
        <i class="fa-solid fa-circle-check" style="color:#4ade80; flex-shrink:0;"></i>
        <span class="sett-toast-msg"></span>
    </div>

    {{-- Thin Blue Top Bar --}}
    <header class="sett-topbar">
        <a href="{{ route('admin.dashboard') }}" class="sett-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
        <div class="sett-topbar__identity">
            <div class="sett-topbar__avatar" aria-hidden="true">{{ $sInitials }}</div>
            <span class="sett-topbar__name">{{ $sFullName }}</span>
        </div>
    </header>

    {{-- Dedicated Settings Content --}}
    <main class="sett-main" id="sett-main-content">
        <div class="sett-container">
            {{ $slot }}
        </div>
    </main>

    {{-- Shared Logout Modal --}}
    @include('components.logout-modal')

    @stack('scripts')

</body>
</html>
