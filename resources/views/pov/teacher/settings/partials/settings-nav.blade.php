<div class="mb-4">
    <div class="d-flex flex-wrap gap-2 border-bottom pb-2">
        <a href="{{ route('teacher.settings.index') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.settings.index') ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-2 px-3 py-1.5 fw-semibold"
           style="font-size: 0.82rem; border-radius: 6px;">
            <i class="fa-solid fa-sliders"></i>
            <span>Overview</span>
        </a>

        <a href="{{ route('teacher.settings.notifications') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.settings.notifications') ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-2 px-3 py-1.5 fw-semibold"
           style="font-size: 0.82rem; border-radius: 6px;">
            <i class="fa-solid fa-bell"></i>
            <span>Notifications</span>
        </a>

        <a href="{{ route('teacher.settings.appearance') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.settings.appearance') ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-2 px-3 py-1.5 fw-semibold"
           style="font-size: 0.82rem; border-radius: 6px;">
            <i class="fa-solid fa-palette"></i>
            <span>Appearance</span>
        </a>

        <a href="{{ route('teacher.settings.dashboard') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.settings.dashboard') ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} d-inline-flex align-items-center gap-2 px-3 py-1.5 fw-semibold"
           style="font-size: 0.82rem; border-radius: 6px;">
            <i class="fa-solid fa-gauge"></i>
            <span>Dashboard Preferences</span>
        </a>
    </div>
</div>

