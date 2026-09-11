<x-layouts.teacher-settings-layout>
    <div class="teacher-settings-content">
        <div class="teacher-settings-section">
            <div class="teacher-settings-section__header">
                <h2 class="teacher-settings-section__title">
                    <i class="fa-solid fa-bell me-3"></i>
                    Notifications
                </h2>
                <p class="teacher-settings-section__description">
                    Manage your in-app notification preferences and system alerts.
                </p>
            </div>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12">
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
        </div>
    </div>

    <style>
    .teacher-settings-section {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .teacher-settings-section__header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .teacher-settings-section__title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .teacher-settings-section__description {
        color: #64748b;
        font-size: 0.875rem;
        margin: 0.5rem 0 0 0;
        line-height: 1.5;
    }

    .teacher-settings-content {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    </style>
</x-layouts.teacher-settings-layout>

