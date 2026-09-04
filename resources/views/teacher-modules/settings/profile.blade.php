<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-user"></i>
            Settings
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">View your teacher profile and academic account information.</span>
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
</x-layouts.teacher>
