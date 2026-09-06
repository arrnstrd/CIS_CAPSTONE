<x-layouts.account-management>
    <x-slot:title>Personal Information</x-slot:title>

    @php
        $initials = strtoupper(substr($user->first_name ?? 'T', 0, 1) . substr($user->last_name ?? 'M', 0, 1));
    @endphp

    <div class="row g-4">
        <div class="col-12">
            <div class="account-mgmt-card p-4">

                <!-- Profile Identity Banner -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="width: 64px; height: 64px; font-size: 1.4rem; flex-shrink: 0;">
                        {{ $initials }}
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark" style="font-size: 1.25rem;">{{ $teacher?->full_name ?? ($user->first_name . ' ' . $user->last_name) }}</h4>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                {{ $user->role_label ?? ucfirst($user->role) }}
                            </span>
                            <span class="text-muted small font-monospace">{{ $user->employee_id ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 d-flex align-items-start gap-3 mb-4" style="background-color: #e0f2fe; color: #0369a1; border-radius: 8px;">
                    <i class="fa-solid fa-circle-info fs-5 mt-1"></i>
                    <div style="font-size: 0.84rem;">
                        <strong>Administrator Managed Profile:</strong> Your personal credentials, employee ID, and official name records are maintained directly by the Conception Integrated School administration. To request corrections or updates, please contact the IT or Registrar department.
                    </div>
                </div>

                <!-- Identity Group -->
                <h6 class="fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-id-badge me-1"></i> Identity
                </h6>
                <div class="mb-4">
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
                </div>

                <!-- Contact Group -->
                <h6 class="fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-envelope me-1"></i> Contact
                </h6>
                <div class="mb-4">
                    <div class="account-mgmt-field-row">
                        <span class="account-mgmt-field-label">Email Address</span>
                        <span class="account-mgmt-field-value">{{ $user->email }}</span>
                    </div>
                </div>

                <!-- Account Group -->
                <h6 class="fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-shield me-1"></i> Account
                </h6>
                <div>
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
        </div>
    </div>
</x-layouts.account-management>
