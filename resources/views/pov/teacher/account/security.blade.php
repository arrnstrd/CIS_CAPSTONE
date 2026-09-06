<x-layouts.account-management>
    <x-slot:title>Security &amp; Sign-in</x-slot:title>

    @php
        $initialStep = 1;
        if (session('success')) {
            $initialStep = 5;
        } elseif ($errors->has('current_password')) {
            $initialStep = 2;
        } elseif ($errors->has('password')) {
            $initialStep = 3;
        } elseif ($errors->has('password_confirmation')) {
            $initialStep = 4;
        }
    @endphp

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">

            <div class="account-mgmt-card p-4 p-md-5">
                <!-- Header -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.2rem;">Security &amp; Sign-in</h4>
                        <p class="text-muted mb-0 small">Manage your account authentication credentials and security settings.</p>
                    </div>
                </div>

                <!-- Step Progress Bar (Steps 1-4) -->
                @if ($initialStep !== 5)
                    <div class="mb-4" id="passwordStepProgress">
                        <div class="d-flex justify-content-between mb-2" style="font-size: 0.78rem; font-weight: 600;">
                            <span id="stepIndicatorLabel" class="text-primary">Step 1 of 4: Introduction</span>
                            <span id="stepIndicatorPercent" class="text-muted">25%</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px; background-color: #e2e8f0;">
                            <div id="stepProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 25%; transition: width 0.3s ease;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                @endif

                <form id="passwordUpdateForm" method="POST" action="{{ route('teacher.settings.password.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- STEP 1: Overview & Intro -->
                    <div class="pw-step-panel {{ $initialStep === 1 ? '' : 'd-none' }}" id="pwStep1">
                        <div class="text-center py-3">
                            <div class="mb-3 text-primary" style="font-size: 2.8rem;">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Update Account Password</h5>
                            <p class="text-secondary small mb-4 mx-auto" style="max-width: 440px; font-size: 0.86rem; line-height: 1.5;">
                                Follow the steps below to securely change your password.
                            </p>

                            <div class="p-3 bg-light rounded-3 text-start mb-4 mx-auto" style="max-width: 440px;">
                                <div class="fw-semibold text-dark mb-2 small"><i class="fa-solid fa-list-check text-primary me-2"></i> Password Guidelines:</div>
                                <ul class="text-muted small ps-3 mb-0" style="font-size: 0.8rem; line-height: 1.6;">
                                    <li>At least 8 characters</li>
                                    <li>Contains letters</li>
                                    <li>Contains uppercase and lowercase letters</li>
                                    <li>Contains numbers</li>
                                    <li>Contains a symbol</li>
                                    <li>New password and confirmation must match</li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-primary px-4 py-2 fw-semibold" onclick="goToStep(2)">
                                <span>Begin Password Change</span>
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Current Password -->
                    <div class="pw-step-panel {{ $initialStep === 2 ? '' : 'd-none' }}" id="pwStep2">
                        <h6 class="fw-bold text-dark mb-1">Enter Current Password</h6>
                        <p class="text-muted small mb-4">Please enter your existing password to verify your account when submitting.</p>

                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold small text-dark">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-lock"></i></span>
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Enter your current password"
                                    autocomplete="current-password"
                                    required
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
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="goToStep(1)">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back
                            </button>
                            <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" onclick="validateAndGoToStep(2, 3)">
                                Next <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: New Password -->
                    <div class="pw-step-panel {{ $initialStep === 3 ? '' : 'd-none' }}" id="pwStep3">
                        <h6 class="fw-bold text-dark mb-1">Create New Password</h6>
                        <p class="text-muted small mb-4">Enter a strong, unique password that you do not use for other applications.</p>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold small text-dark">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-key"></i></span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter at least 8 characters"
                                    autocomplete="new-password"
                                    required
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
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">
                                Must contain at least 8 characters.
                            </small>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="goToStep(2)">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back
                            </button>
                            <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" onclick="validateAndGoToStep(3, 4)">
                                Next <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4: Confirm New Password -->
                    <div class="pw-step-panel {{ $initialStep === 4 ? '' : 'd-none' }}" id="pwStep4">
                        <h6 class="fw-bold text-dark mb-1">Confirm New Password</h6>
                        <p class="text-muted small mb-4">Re-type your new password to verify and save your changes.</p>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold small text-dark">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-check-double"></i></span>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Re-enter your new password"
                                    autocomplete="new-password"
                                    required
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
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div id="passwordMismatchError" class="text-danger small mt-1 d-none" style="font-size: 0.78rem;">
                                Passwords do not match.
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="goToStep(3)">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back
                            </button>
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold" id="submitPasswordBtn">
                                <i class="fa-solid fa-check me-1"></i> Update Password
                            </button>
                        </div>
                    </div>

                    <!-- STEP 5: Success Screen -->
                    <div class="pw-step-panel {{ $initialStep === 5 ? '' : 'd-none' }}" id="pwStep5">
                        <div class="text-center py-4">
                            <div class="mb-3 text-success" style="font-size: 3.2rem;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Password Updated Successfully</h5>
                            <p class="text-secondary small mb-4 mx-auto" style="max-width: 440px; font-size: 0.86rem; line-height: 1.5;">
                                {{ session('success') ?? 'Your account password has been changed successfully. You can now use your new password for future logins.' }}
                            </p>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('teacher.account.personal-information') }}" class="btn btn-outline-secondary btn-sm px-4 fw-semibold">
                                    View Account Info
                                </a>
                                <a href="{{ route('teacher.dashboard') }}" class="btn btn-primary btn-sm px-4 fw-semibold">
                                    Return to Teacher Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            const stepLabels = {
                1: 'Step 1 of 4: Introduction',
                2: 'Step 2 of 4: Current Password',
                3: 'Step 3 of 4: Create New Password',
                4: 'Step 4 of 4: Confirm New Password'
            };

            const stepPercentages = {
                1: '25%',
                2: '50%',
                3: '75%',
                4: '100%'
            };

            function goToStep(step) {
                // Hide all steps
                document.querySelectorAll('.pw-step-panel').forEach(panel => {
                    panel.classList.add('d-none');
                });

                // Show target step
                const targetPanel = document.getElementById('pwStep' + step);
                if (targetPanel) {
                    targetPanel.classList.remove('d-none');
                }

                // Update progress bar
                const label = document.getElementById('stepIndicatorLabel');
                const percent = document.getElementById('stepIndicatorPercent');
                const bar = document.getElementById('stepProgressBar');

                if (step <= 4) {
                    if (label) label.textContent = stepLabels[step] || '';
                    if (percent) percent.textContent = stepPercentages[step] || '';
                    if (bar) {
                        bar.style.width = stepPercentages[step] || '25%';
                        bar.setAttribute('aria-valuenow', parseInt(stepPercentages[step]));
                    }
                }
            }

            function validateAndGoToStep(fromStep, toStep) {
                if (fromStep === 2) {
                    const currentPw = document.getElementById('current_password');
                    if (!currentPw || !currentPw.value.trim()) {
                        currentPw.focus();
                        currentPw.classList.add('is-invalid');
                        return;
                    }
                    currentPw.classList.remove('is-invalid');
                }

                if (fromStep === 3) {
                    const newPw = document.getElementById('password');
                    if (!newPw || !newPw.value || newPw.value.length < 8) {
                        newPw.focus();
                        newPw.classList.add('is-invalid');
                        return;
                    }
                    newPw.classList.remove('is-invalid');
                }

                goToStep(toStep);
            }

            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('passwordUpdateForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const newPw = document.getElementById('password')?.value;
                        const confirmPw = document.getElementById('password_confirmation')?.value;
                        const mismatchError = document.getElementById('passwordMismatchError');

                        if (newPw !== confirmPw) {
                            e.preventDefault();
                            if (mismatchError) mismatchError.classList.remove('d-none');
                            document.getElementById('password_confirmation')?.classList.add('is-invalid');
                            return false;
                        }
                        if (mismatchError) mismatchError.classList.add('d-none');
                    });
                }
            });

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
    @endpush
</x-layouts.account-management>

