<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-gear"></i>
            Settings &amp; Preferences
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Manage your portal preferences, display theme, and dashboard configurations.</span>
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @include('pov.teacher.settings.partials.settings-nav')

    <div class="row g-4">
        <!-- Notifications Card -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="gs-panel h-100 d-flex flex-column justify-content-between p-4 bg-white rounded-3 border shadow-sm">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 44px; height: 44px; font-size: 1.2rem;">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">Notifications</h5>
                            <small class="text-muted" style="font-size: 0.75rem;">In-App Alerts &amp; Updates</small>
                        </div>
                    </div>
                    <p class="text-secondary small mb-4" style="font-size: 0.82rem; line-height: 1.5;">
                        Configure which notifications and alerts you receive for attendance records, grading progress, at-risk warnings, and analytics.
                    </p>
                </div>
                <a href="{{ route('teacher.settings.notifications') }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                    <span>Manage Notifications</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>
        </div>

        <!-- Appearance Card -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="gs-panel h-100 d-flex flex-column justify-content-between p-4 bg-white rounded-3 border shadow-sm">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-info-subtle text-info" style="width: 44px; height: 44px; font-size: 1.2rem;">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">Appearance</h5>
                            <small class="text-muted" style="font-size: 0.75rem;">Theme &amp; Visual Style</small>
                        </div>
                    </div>
                    <p class="text-secondary small mb-4" style="font-size: 0.82rem; line-height: 1.5;">
                        Customize your visual display mode (Light, Dark, or System Match) for optimal comfort during school operations.
                    </p>
                </div>
                <a href="{{ route('teacher.settings.appearance') }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                    <span>Change Theme</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>
        </div>

        <!-- Dashboard Preferences Card -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="gs-panel h-100 d-flex flex-column justify-content-between p-4 bg-white rounded-3 border shadow-sm">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-success-subtle text-success" style="width: 44px; height: 44px; font-size: 1.2rem;">
                            <i class="fa-solid fa-gauge"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">Dashboard Preferences</h5>
                            <small class="text-muted" style="font-size: 0.75rem;">Default Views &amp; Layout</small>
                        </div>
                    </div>
                    <p class="text-secondary small mb-4" style="font-size: 0.82rem; line-height: 1.5;">
                        Set your default class assignment, display density, and toggle visibility of summary cards and health indicator widgets.
                    </p>
                </div>
                <a href="{{ route('teacher.settings.dashboard') }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                    <span>Configure Dashboard</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Account Management Callout Banner -->
    <div class="mt-4 p-4 rounded-3 border bg-light d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary" style="width: 42px; height: 42px; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.92rem;">Looking for Account &amp; Security?</h6>
                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                    Personal details and password management are now located in your dedicated Account Management area.
                </p>
            </div>
        </div>
        <a href="{{ route('teacher.account') }}" class="btn btn-dark btn-sm px-4 fw-semibold flex-shrink-0 d-inline-flex align-items-center gap-2">
            <span>Manage Account</span>
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.72rem;"></i>
        </a>
    </div>
</x-layouts.teacher>

