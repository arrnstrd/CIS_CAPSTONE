<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Settings - Teacher Portal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    {{--
        Structure:
        .teacher-settings-page-wrapper  (flex row, full viewport height)
        ├── .teacher-settings-layout__sidebar   (fixed-width, full height)
        └── .teacher-settings-layout__main      (flex:1, flex column)
            ├── .teacher-top-nav                (blue header — right column only)
            └── .teacher-settings-layout__content  (scrollable content)
    --}}
    <div class="teacher-settings-page-wrapper">

        {{-- Settings Sidebar — full height left column --}}
        <div class="teacher-settings-layout__sidebar">
            <x-teacher-settings-nav />
        </div>

        {{-- Right column: header + content --}}
        <div class="teacher-settings-layout__main">

            {{-- Blue Header — sits only above the content, NOT above the sidebar --}}
            <header class="teacher-top-nav">
                <div class="teacher-top-nav-greeting">
                    <div class="teacher-top-nav-greeting-text">Settings</div>
                    <div class="teacher-top-nav-greeting-subtext">
                        Manage your account preferences and system settings
                    </div>
                </div>

                {{-- Teacher info + Back to Dashboard stacked vertically --}}
                <div class="teacher-top-nav-actions">
                    <div class="d-flex flex-column align-items-end gap-2">
                        <div class="fw-semibold text-white" style="font-size: 0.875rem; line-height: 1.3;">
                            {{ auth()->user()?->teacher?->full_name ?? (auth()->user()?->first_name . ' ' . auth()->user()?->last_name) }}
                        </div>
                        <span class="teacher-settings-role-badge">Teacher</span>
                        <a href="{{ route('teacher.dashboard') }}"
                           class="teacher-settings-back-btn">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Back to Dashboard</span>
                        </a>
                    </div>
                </div>
            </header>

            {{-- Main scrollable content --}}
            <div class="teacher-settings-layout__content">
                {{ $slot }}
            </div>

        </div>{{-- /.teacher-settings-layout__main --}}

    </div>{{-- /.teacher-settings-page-wrapper --}}

    @include('components.logout-modal')

    <style>
        /* ============================================================
           Teacher Settings Standalone Layout
           ============================================================ */

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Full-viewport flex row container */
        .teacher-settings-page-wrapper {
            display: flex;
            flex-direction: row;
            min-height: 100vh;
            height: 100%;
            background: #f8fafc;
        }

        /* ---- Left: Sidebar ---- */
        .teacher-settings-layout__sidebar {
            width: 280px;
            flex-shrink: 0;
            background: white;
            border-right: 1px solid #e2e8f0;
            /* Full height is inherited from the flex parent's stretch */
            display: flex;
            flex-direction: column;
            align-self: stretch;
            height: 100vh;
        }

        /* ---- Right: Header + Content column ---- */
        .teacher-settings-layout__main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ---- Blue header (right column only) ---- */
        .teacher-top-nav {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 0.85rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex-shrink: 0;
            position: relative;
            width: 100%;
            max-width: 100%;
            right: auto;
            box-sizing: border-box;
            left: 0;
            min-height: 84px;
        }

        .teacher-top-nav-greeting {
            flex: 1;
        }

        .teacher-top-nav-greeting-text {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .teacher-top-nav-greeting-subtext {
            font-size: 0.82rem;
            opacity: 0.85;
        }

        .teacher-top-nav-actions {
            display: flex;
            align-items: flex-end;
        }

        /* Role badge — solid contrast so text is always visible */
        .teacher-settings-role-badge {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #1e3a8a;
            background: #ffffff;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            line-height: 1.4;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Back to Dashboard link — below the badge */
        .teacher-settings-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 0.25rem 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 6px;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .teacher-settings-back-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        /* ---- Scrollable content area ---- */
        .teacher-settings-layout__content {
            flex: 1;
            padding: 1.75rem 2rem;
            overflow-y: auto;
            width: 100%;
            box-sizing: border-box;
        }

        /* Ensure teacher badge is always visible */
        .teacher-settings-role-badge {
            visibility: visible !important;
            opacity: 1 !important;
            color: #1e3a8a !important;
            background: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        }



        /* ============================================================
           Responsive — mobile: sidebar on top, stacked layout
           ============================================================ */
        @media (max-width: 768px) {
            .teacher-settings-page-wrapper {
                flex-direction: column;
            }

            .teacher-settings-layout__sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
                align-self: auto;
                height: auto;
            }

            .teacher-settings-layout__main {
                min-height: 0;
            }

            .teacher-top-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem 1.25rem;
            }

            .teacher-top-nav-actions {
                width: 100%;
                align-items: flex-start;
            }

            .teacher-settings-layout__content {
                padding: 1.25rem 1rem;
            }
        }
    </style>

    @stack('scripts')
    
    <script>
        (function() {
            function syncSettingsHeaderHeights() {
                var blueHeader = document.querySelector('.teacher-top-nav');
                var sidebarHeader = document.querySelector('.teacher-settings-nav__header');
                if (!blueHeader || !sidebarHeader) return;
                // Reset first so we measure the header's natural height, not a stale forced one
                sidebarHeader.style.minHeight = '';
                var targetHeight = blueHeader.getBoundingClientRect().height;
                sidebarHeader.style.minHeight = targetHeight + 'px';
            }
            document.addEventListener('DOMContentLoaded', syncSettingsHeaderHeights);
            window.addEventListener('resize', syncSettingsHeaderHeights);
            // Fonts loading late can change text height slightly — re-sync once more shortly after load
            window.addEventListener('load', syncSettingsHeaderHeights);
        })();
    </script>
</body>

</html>


