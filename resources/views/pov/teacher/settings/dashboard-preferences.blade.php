<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-sliders"></i>
            Settings
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Customize your dashboard layout, visible panels, display elements, and default filters.</span>
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @include('pov.teacher.settings.partials.settings-nav')

    <div class="row g-3">
        <div class="col-12">
            <div class="gs-panel">
                <p class="gs-panel-title">Dashboard Preferences</p>
                <p class="text-muted small mb-4">
                    Customize your dashboard to match your preferred workspace.
                </p>

                <form method="POST" action="{{ route('teacher.settings.dashboard.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4 mb-4">
                        {{-- Layout Options --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-table-columns me-1"></i> Layout
                            </h6>

                            <div class="mb-3">
                                <label for="default_view" class="form-label fw-semibold" style="font-size: 0.82rem;">
                                    Default View
                                </label>
                                <select class="form-select form-select-sm" id="default_view" name="default_view">
                                    <option value="overview" @selected(($dashboardPreferences?->default_view ?? 'overview') === 'overview')>Overview</option>
                                    <option value="my_classes" @selected(($dashboardPreferences?->default_view ?? 'overview') === 'my_classes')>My Classes</option>
                                    <option value="analytics" @selected(($dashboardPreferences?->default_view ?? 'overview') === 'analytics')>Analytics</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="dashboard_density" class="form-label fw-semibold" style="font-size: 0.82rem;">
                                    Dashboard Density
                                </label>
                                <select class="form-select form-select-sm" id="dashboard_density" name="dashboard_density">
                                    <option value="comfortable" @selected(($dashboardPreferences?->dashboard_density ?? 'comfortable') === 'comfortable')>Comfortable</option>
                                    <option value="compact" @selected(($dashboardPreferences?->dashboard_density ?? 'comfortable') === 'compact')>Compact</option>
                                </select>
                            </div>
                        </div>

                        {{-- Default Filters --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-filter me-1"></i> Default Filters
                            </h6>

                            <div class="mb-3">
                                <label for="default_class_id" class="form-label fw-semibold" style="font-size: 0.82rem;">
                                    Default Class
                                </label>
                                <select class="form-select form-select-sm" id="default_class_id" name="default_class_id">
                                    <option value="" @selected(empty($dashboardPreferences?->default_class_id))>All Classes</option>
                                    @foreach ($assignedClasses as $ac)
                                        <option value="{{ $ac->id }}" @selected(($dashboardPreferences?->default_class_id) == $ac->id)>
                                            Grade {{ $ac->section?->grade_level }} - {{ $ac->section?->name }} ({{ $ac->subject?->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="default_term" class="form-label fw-semibold" style="font-size: 0.82rem;">
                                    Default Term
                                </label>
                                <select class="form-select form-select-sm" id="default_term" name="default_term">
                                    <option value="current" @selected(($dashboardPreferences?->default_term ?? 'current') === 'current')>Current Active Term</option>
                                    <option value="term_1" @selected(($dashboardPreferences?->default_term) === 'term_1')>Term 1</option>
                                    <option value="term_2" @selected(($dashboardPreferences?->default_term) === 'term_2')>Term 2</option>
                                    <option value="term_3" @selected(($dashboardPreferences?->default_term) === 'term_3')>Term 3</option>
                                </select>
                            </div>
                        </div>

                        {{-- Visible Sections --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-eye me-1"></i> Visible Sections
                            </h6>

                            <div class="d-flex flex-column gap-3">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_grading_progress" style="font-size: 0.82rem;">
                                        Grading Progress
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_grading_progress"
                                        name="show_grading_progress"
                                        value="1"
                                        @checked($dashboardPreferences?->show_grading_progress ?? true)
                                    >
                                </div>

                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_class_health" style="font-size: 0.82rem;">
                                        Class Health Indicators
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_class_health"
                                        name="show_class_health"
                                        value="1"
                                        @checked($dashboardPreferences?->show_class_health ?? true)
                                    >
                                </div>

                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_at_risk" style="font-size: 0.82rem;">
                                        At-Risk Student Summary
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_at_risk"
                                        name="show_at_risk"
                                        value="1"
                                        @checked($dashboardPreferences?->show_at_risk ?? true)
                                    >
                                </div>

                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_recent_activity" style="font-size: 0.82rem;">
                                        Recent Activity
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_recent_activity"
                                        name="show_recent_activity"
                                        value="1"
                                        @checked($dashboardPreferences?->show_recent_activity ?? true)
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Information Display --}}
                        <div class="col-12 col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-chart-pie me-1"></i> Information Display
                            </h6>

                            <div class="d-flex flex-column gap-3">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_summary_cards" style="font-size: 0.82rem;">
                                        Summary Cards
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_summary_cards"
                                        name="show_summary_cards"
                                        value="1"
                                        @checked($dashboardPreferences?->show_summary_cards ?? true)
                                    >
                                </div>

                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_student_counts" style="font-size: 0.82rem;">
                                        Student Counts
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_student_counts"
                                        name="show_student_counts"
                                        value="1"
                                        @checked($dashboardPreferences?->show_student_counts ?? true)
                                    >
                                </div>

                                <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                    <label class="form-check-label fw-semibold" for="show_progress_indicators" style="font-size: 0.82rem;">
                                        Progress Indicators
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_progress_indicators"
                                        name="show_progress_indicators"
                                        value="1"
                                        @checked($dashboardPreferences?->show_progress_indicators ?? true)
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-top">
                        <button type="submit" class="btn btn-dark btn-sm px-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Dashboard Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.teacher>

