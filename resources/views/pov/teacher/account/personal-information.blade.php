<x-layouts.account-management>
    <x-slot:title>Personal Information</x-slot:title>

    <div class="row g-4">
        <!-- Main Info Column -->
        <div class="col-12 col-lg-8">
            <div class="account-mgmt-card p-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.2rem;">Personal Information</h4>
                        <p class="text-muted mb-0 small">View your academic identity and verified account details.</p>
                    </div>
                </div>

                <div class="alert alert-info border-0 d-flex align-items-start gap-3 mb-4" style="background-color: #e0f2fe; color: #0369a1; border-radius: 8px;">
                    <i class="fa-solid fa-circle-info fs-5 mt-0.5"></i>
                    <div style="font-size: 0.84rem;">
                        <strong>Administrator Managed Profile:</strong> Your personal credentials, employee ID, and official name records are maintained directly by the Conception Integrated School administration. To request corrections or updates, please contact the IT or Registrar department.
                    </div>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Employee ID</span>
                    <span class="account-mgmt-field-value font-monospace">{{ $user->employee_id ?? '—' }}</span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Full Name</span>
                    <span class="account-mgmt-field-value">{{ $teacher?->full_name ?? ($user->first_name . ' ' . $user->last_name) }}</span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">First Name</span>
                    <span class="account-mgmt-field-value">{{ $user->first_name ?? '—' }}</span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Last Name</span>
                    <span class="account-mgmt-field-value">{{ $user->last_name ?? '—' }}</span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Email Address</span>
                    <span class="account-mgmt-field-value">{{ $user->email }}</span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Portal Role</span>
                    <span class="account-mgmt-field-value">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                            {{ $user->role_label ?? ucfirst($user->role) }}
                        </span>
                    </span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Account Status</span>
                    <span class="account-mgmt-field-value">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ ucfirst($user->status ?? 'Active') }}
                        </span>
                    </span>
                </div>

                <div class="account-mgmt-field-row">
                    <span class="account-mgmt-field-label">Account Registered</span>
                    <span class="account-mgmt-field-value text-muted">{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Sidebar / Security Link Column -->
        <div class="col-12 col-lg-4">
            <div class="account-mgmt-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-shield-halved text-primary fs-5"></i>
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Security &amp; Password</h5>
                </div>
                <p class="text-secondary small mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                    Keep your account secure by updating your password periodically with strong alphanumeric characters and symbols.
                </p>
                <a href="{{ route('teacher.account.security') }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                    <span>Manage Security</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>

            <div class="account-mgmt-card p-4 bg-light">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-sliders text-secondary fs-6"></i>
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.88rem;">Need Portal Preferences?</h6>
                </div>
                <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                    Configure your notifications, color theme, and dashboard layout inside Teacher Settings.
                </p>
                <a href="{{ route('teacher.settings.index') }}" class="btn btn-secondary btn-sm w-100 fw-semibold">
                    Open Settings &amp; Preferences
                </a>
            </div>
        </div>
    </div>
</x-layouts.account-management>

