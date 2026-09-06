<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-bell"></i>
            Settings
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Manage your in-app notification preferences and system alerts.</span>
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @include('pov.teacher.settings.partials.settings-nav')

    <div class="row g-3">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="gs-panel">
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
    </div>
</x-layouts.teacher>

