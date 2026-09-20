@php
    $topNavUser     = auth()->user();
    $topNavFirst    = $topNavUser?->first_name ?? '';
    $topNavLast     = $topNavUser?->last_name ?? '';
    $topNavFullName = trim($topNavFirst . ' ' . $topNavLast) ?: 'School Administrator';
    $topNavEmail    = $topNavUser?->email ?? 'admin@example.com';
    $topNavInitials = mb_strtoupper(mb_substr($topNavFirst, 0, 1) . mb_substr($topNavLast, 0, 1)) ?: 'SA';
@endphp

<header class="sa-topbar" id="saTopBar">
    <div class="sa-topbar__inner">
        {{-- Hamburger menu button (visible on tablet/narrow, hidden on desktop) --}}
        <button type="button"
            class="sidebar-hamburger-btn"
            id="saSidebarToggle"
            aria-label="Toggle navigation menu"
            aria-expanded="false"
            aria-controls="sidebar">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        {{-- Left side: Subtle branding / portal indicator --}}
        <div class="sa-topbar__left"></div>


        {{-- Right side: Account Profile Area with Dropdown & Guide Trigger --}}
        <div class="d-flex align-items-center gap-3">
            <button type="button"
                id="saTourGuideTrigger"
                class="sa-topbar__guide-btn"
                title="Start Interactive Page Guide"
                aria-label="Start Interactive Page Guide">
                <i class="fa-solid fa-circle-question" aria-hidden="true"></i>
                <span>Guide</span>
            </button>

            <div class="sa-topbar__account-wrapper">
            <button type="button"
                class="sa-topbar__profile-btn"
                id="saTopNavProfileTrigger"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="saTopNavDropdown"
                title="Account menu for {{ $topNavFullName }}">
                <div class="sa-topbar__avatar" aria-hidden="true">
                    {{ $topNavInitials }}
                </div>
                <div class="sa-topbar__user-info">
                    <span class="sa-topbar__user-name">{{ $topNavFullName }}</span>
                    <span class="sa-topbar__user-email">{{ $topNavEmail }}</span>
                </div>
                <i class="fa-solid fa-chevron-down sa-topbar__caret" aria-hidden="true"></i>
            </button>

            {{-- Compact Profile Dropdown --}}
            <div class="sa-topbar__dropdown"
                id="saTopNavDropdown"
                role="menu"
                aria-hidden="true">
                <div class="sa-topbar__dropdown-header">
                    <div class="sa-topbar__dropdown-name">{{ $topNavFullName }}</div>
                    <div class="sa-topbar__dropdown-email">{{ $topNavEmail }}</div>
                </div>

                <div class="sa-topbar__dropdown-divider"></div>

                <a href="{{ route('settings.index') }}"
                    class="sa-topbar__dropdown-item"
                    role="menuitem">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    <span>Settings / Account</span>
                </a>

                <div class="sa-topbar__dropdown-divider"></div>

                <button type="button"
                    class="sa-topbar__dropdown-item sa-topbar__dropdown-item--danger"
                    data-bs-toggle="modal"
                    data-bs-target="#logoutModal"
                    role="menuitem">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</div>
</header>

