<x-layouts.teacher-settings-layout>
    <div class="teacher-settings-content">
        <div class="teacher-settings-section">
            <div class="teacher-settings-section__header">
                <h2 class="teacher-settings-section__title">
                    <i class="fa-solid fa-user me-3"></i>
                    Profile
                </h2>
                <p class="teacher-settings-section__description">
                    View your teacher profile and academic account information.
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
