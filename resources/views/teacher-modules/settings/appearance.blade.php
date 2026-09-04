<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-palette"></i>
            Settings
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Customize the appearance and visual theme of your Teacher Portal.</span>
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Appearance</p>
                <p class="text-muted small mb-3">
                    Choose how the Teacher Portal should appear across your sessions and devices.
                </p>

                <form method="POST" action="{{ route('teacher.settings.appearance.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Theme Mode</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="theme"
                                    id="theme_light"
                                    value="light"
                                    @checked(($dashboardPreferences?->theme ?? 'system') === 'light')
                                >
                                <label class="form-check-label" for="theme_light" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-sun text-warning me-2"></i> Light
                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="theme"
                                    id="theme_dark"
                                    value="dark"
                                    @checked(($dashboardPreferences?->theme ?? 'system') === 'dark')
                                >
                                <label class="form-check-label" for="theme_dark" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-moon text-primary me-2"></i> Dark
                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="theme"
                                    id="theme_system"
                                    value="system"
                                    @checked(($dashboardPreferences?->theme ?? 'system') === 'system')
                                >
                                <label class="form-check-label" for="theme_system" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-circle-half-stroke text-secondary me-2"></i> System Default
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">
                            <i class="fa-solid fa-paint-roller me-1"></i> Save Appearance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.teacher>
