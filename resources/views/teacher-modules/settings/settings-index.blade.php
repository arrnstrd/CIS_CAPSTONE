<x-layouts.teacher>
    <x-slot name="pageName">
        Settings
    </x-slot>

    <x-slot name="subtitle">
        Manage your teacher profile information and account security.
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