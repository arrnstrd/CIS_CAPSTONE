<x-layouts.teacher>
    <x-slot name="pageName">
        Settings
    </x-slot>

    <x-slot name="subtitle">
        Manage your teacher profile information, notification preferences, and account security.
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">
        {{-- Profile Information (Read-Only) --}}
        <div class="col-12 col-lg-6">
            <div class="gs-panel h-100">
                <p class="gs-panel-title">Profile Information</p>
                <p class="text-muted small mb-3">
                    Your personal and academic account details are maintained by the school administration.
                </p>

                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Employee ID</span>
                        <span class="gs-rules-value">{{ $user->employee_id ?? '—' }}</span>
                    </div>
                </div>

                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Full Name</span>
                        <span class="gs-rules-value">{{ $teacher?->full_name ?? ($user->first_name . ' ' . $user->last_name) }}</span>
                    </div>
                </div>

                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Email Address</span>
                        <span class="gs-rules-value">{{ $user->email }}</span>
                    </div>
                </div>

                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Role</span>
                        <span class="gs-rules-value">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                {{ $user->role_label }}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="gs-rules-section gs-rules-section-last">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Account Status</span>
                        <span class="gs-rules-value">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                {{ ucfirst($user->status) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notification Preferences --}}
        <div class="col-12 col-lg-6">
            <div class="gs-panel h-100">
                <p class="gs-panel-title">Notification Preferences</p>
                <p class="text-muted small mb-3">
                    Choose which categories of in-app updates and alerts you wish to receive.
                </p>

                <form method="POST" action="{{ route('teacher.settings.notifications.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="attendance_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-clipboard-user text-primary me-2"></i> Attendance Updates
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Alerts for daily attendance logs and verification changes.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="attendance_enabled"
                                name="attendance_enabled"
                                value="1"
                                @checked($preferences?->attendance_enabled ?? true)
                            >
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="grading_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-graduation-cap text-success me-2"></i> Grading & Assessment
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Updates on score encoding and term completion status.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="grading_enabled"
                                name="grading_enabled"
                                value="1"
                                @checked($preferences?->grading_enabled ?? true)
                            >
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="at_risk_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> At-Risk Student Alerts
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Immediate alerts when students enter Moderate or High academic risk.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="at_risk_enabled"
                                name="at_risk_enabled"
                                value="1"
                                @checked($preferences?->at_risk_enabled ?? true)
                            >
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="analytics_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-chart-line text-info me-2"></i> Analytics & Health Trends
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Notices regarding class health trends and performance insights.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="analytics_enabled"
                                name="analytics_enabled"
                                value="1"
                                @checked($preferences?->analytics_enabled ?? true)
                            >
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="announcement_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-bullhorn text-warning me-2"></i> System Announcements
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Official notices and announcements from school administrators.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="announcement_enabled"
                                name="announcement_enabled"
                                value="1"
                                @checked($preferences?->announcement_enabled ?? true)
                            >
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                            <div>
                                <label class="form-check-label fw-semibold" for="import_enabled" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-file-import text-secondary me-2"></i> Data Import Notifications
                                </label>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">
                                    Status notifications for class and student data imports.
                                </small>
                            </div>
                            <input
                                class="form-check-input ms-3"
                                type="checkbox"
                                role="switch"
                                id="import_enabled"
                                name="import_enabled"
                                value="1"
                                @checked($preferences?->import_enabled ?? true)
                            >
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Account & Security (Change Password) --}}
        <div class="col-12 col-lg-6">
            <div class="gs-panel h-100">
                <p class="gs-panel-title">Account & Security</p>
                <p class="text-muted small mb-3">
                    Ensure your account is using a long, random password to stay secure.
                </p>

                <form method="POST" action="{{ route('teacher.settings.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold" style="font-size: 0.82rem;">
                            Current Password
                        </label>
                        <div class="input-group input-group-sm has-validation">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                required
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('current_password', this)"
                                aria-label="Toggle Current Password visibility"
                                title="Toggle password visibility"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @error('current_password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold" style="font-size: 0.82rem;">
                            New Password
                        </label>
                        <div class="input-group input-group-sm has-validation">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('password', this)"
                                aria-label="Toggle New Password visibility"
                                title="Toggle password visibility"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold" style="font-size: 0.82rem;">
                            Confirm New Password
                        </label>
                        <div class="input-group input-group-sm">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePasswordVisibility('password_confirmation', this)"
                                aria-label="Toggle Confirm New Password visibility"
                                title="Toggle password visibility"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2">
                        <button type="submit" class="btn btn-dark btn-sm px-3">
                            <i class="fa-solid fa-key me-1"></i> Update Password
                        </button>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted d-block" style="font-size: 0.76rem;">
                            <i class="fa-solid fa-shield-halved text-secondary me-1"></i>
                            Password must be at least 8 characters and include uppercase, lowercase, numbers, and symbols.
                        </small>
                    </div>
                </form>
            </div>
        </div>

        {{-- Appearance (Dark Mode / Theme) --}}
        <div class="col-12 col-lg-6">
            <div class="gs-panel h-100">
                <p class="gs-panel-title">Appearance</p>
                <p class="text-muted small mb-3">
                    Choose how the Teacher Portal should appear across your sessions and devices.
                </p>

                <form method="POST" action="{{ route('teacher.settings.appearance.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.82rem;">Theme</label>
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

        {{-- Dashboard Preferences --}}
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
                                    <label class="form-check-label fw-semibold" for="show_quick_actions" style="font-size: 0.82rem;">
                                        Quick Actions
                                    </label>
                                    <input
                                        class="form-check-input ms-3"
                                        type="checkbox"
                                        role="switch"
                                        id="show_quick_actions"
                                        name="show_quick_actions"
                                        value="1"
                                        @checked($dashboardPreferences?->show_quick_actions ?? true)
                                    >
                                </div>

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

    <script>
        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (!input) return;

            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>
</x-layouts.teacher>