<x-layouts.settings>

    @php
        $activeTab        = old('settings_tab', session('settings_tab', 'account-security'));
        $firstName        = $user->first_name ?? '';
        $lastName         = $user->last_name  ?? '';
        $fullName         = trim($firstName . ' ' . $lastName) ?: 'School Administrator';
        $activeSchoolYear = $schoolYears->firstWhere('is_active', true);
        $otherSchoolYears = $schoolYears->where('is_active', false);
    @endphp

    <div class="sett-settings-layout">
        {{-- Google-style Settings Sidebar --}}
        <aside class="sett-sidebar" aria-label="Settings sections">
            <div class="sett-sidebar__eyebrow">Settings</div>
            <nav class="sett-nav-pills" role="tablist" aria-label="Settings sections">
                <button type="button"
                    class="sett-nav-pill {{ $activeTab === 'account-security' ? 'active' : '' }}"
                    id="settTab-account-security"
                    data-bs-toggle="pill"
                    data-bs-target="#sett-pane-account-security"
                    role="tab"
                    aria-controls="sett-pane-account-security"
                    aria-selected="{{ $activeTab === 'account-security' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Account &amp; Security</span>
                </button>

                <button type="button"
                    class="sett-nav-pill {{ $activeTab === 'school-year' ? 'active' : '' }}"
                    id="settTab-school-year"
                    data-bs-toggle="pill"
                    data-bs-target="#sett-pane-school-year"
                    role="tab"
                    aria-controls="sett-pane-school-year"
                    aria-selected="{{ $activeTab === 'school-year' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>School Year</span>
                </button>
            </nav>
        </aside>

        <section class="sett-settings-content">
            {{-- Page Header --}}
            <div class="sett-page-header">
                <h1 class="sett-page-title">Account &amp; Settings</h1>
                <p class="sett-page-subtitle">Manage your personal credentials and institution academic years.</p>
            </div>

            {{-- White Content Container Card --}}
            <div class="sett-card">
        <div class="tab-content">

            {{-- ══════════════════════════════════════════════════════════
                 1. ACCOUNT & SECURITY
            ══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $activeTab === 'account-security' ? 'show active' : '' }}"
                id="sett-pane-account-security"
                role="tabpanel"
                aria-labelledby="settTab-account-security">

                <div class="sett-card-header">
                    <h2 class="sett-card-title">Account &amp; Security</h2>
                    <p class="sett-card-desc">Review and update your administrator credentials and account security settings.</p>
                </div>

                {{-- ACCOUNT INFORMATION --}}
                <div class="sett-subheading">
                    Account Information
                </div>

                <div class="sett-list">
                    {{-- Full Name --}}
                    <div class="sett-row">
                        <div class="sett-row__info">
                            <div class="sett-row__label">Full Name</div>
                            <div class="sett-row__desc">How your name is displayed across the school admin system.</div>
                        </div>
                        <div class="sett-row__value" id="sett-display-fullname">{{ $fullName }}</div>
                        <div class="sett-row__action">
                            <button type="button"
                                class="sett-action-btn"
                                id="openEditNameBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editNameModal">
                                <span>Edit</span>
                                <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem;"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Email Address --}}
                    <div class="sett-row">
                        <div class="sett-row__info">
                            <div class="sett-row__label">Email Address</div>
                            <div class="sett-row__desc">The email used for system authentication and administrative alerts.</div>
                        </div>
                        <div class="sett-row__value" id="sett-display-email">{{ $user->email }}</div>
                        <div class="sett-row__action">
                            <button type="button"
                                class="sett-action-btn"
                                id="openEditEmailBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editEmailModal">
                                <span>Edit</span>
                                <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SECURITY --}}
                <div class="sett-subheading">
                    Security
                </div>

                <div class="sett-list">
                    {{-- Password --}}
                    <div class="sett-row">
                        <div class="sett-row__info">
                            <div class="sett-row__label">Password</div>
                            <div class="sett-row__desc">Manage your account password and protect your administrator access.</div>
                        </div>
                        <div class="sett-row__value" style="letter-spacing: 0.22em; color: #94a3b8; font-size: 1rem;" aria-label="Password hidden">
                            ••••••••
                        </div>
                        <div class="sett-row__action">
                            <button type="button"
                                class="sett-action-btn"
                                id="openChangePasswordBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <span>Change Password</span>
                                <i class="fa-solid fa-arrow-right" style="font-size: 0.72rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="height: 1.25rem;"></div>

            </div>{{-- /account-security pane --}}


            {{-- ══════════════════════════════════════════════════════════
                 2. SCHOOL YEAR
            ══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade {{ $activeTab === 'school-year' ? 'show active' : '' }}"
                id="sett-pane-school-year"
                role="tabpanel"
                aria-labelledby="settTab-school-year">

                <div class="sett-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="sett-card-title">School Year</h2>
                        <p class="sett-card-desc">Configure academic cycles and maintain past records for the institution.</p>
                    </div>
                    <button type="button"
                        class="sett-action-btn sett-action-btn--primary"
                        data-bs-toggle="modal"
                        data-bs-target="#addSchoolYearModal">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add New School Year</span>
                    </button>
                </div>

                {{-- AJAX-refreshable Scope (NO table-panel, NO nested scroll, naturally expands) --}}
                <div id="school-year-table-pane" class="sett-sy-wrapper">

                    {{-- Current / Active School Year --}}
                    @if ($activeSchoolYear)
                        <div class="sett-sy-active-card">
                            <div>
                                <div class="sett-sy-active-badge mb-1">Current Active Cycle</div>
                                <div class="sett-sy-active-year">{{ $activeSchoolYear->school_year }}</div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span>Actions</span>
                                    <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.68rem;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li>
                                        <button type="button"
                                            class="dropdown-item js-edit-school-year"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editSchoolYearModal"
                                            data-id="{{ $activeSchoolYear->id }}"
                                            data-school-year="{{ $activeSchoolYear->school_year }}"
                                            data-is-active="1">
                                            <i class="fa-regular fa-pen-to-square me-2 text-muted"></i>
                                            Edit School Year
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('school-years.destroy', $activeSchoolYear->id) }}"
                                            method="POST"
                                            data-ajax-delete="school-year"
                                            data-ajax-scope="#school-year-table-pane">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa-regular fa-circle-xmark me-2"></i>
                                                Archive Cycle
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            <div>No active school year is currently designated. Please set or create an active school year.</div>
                        </div>
                    @endif

                    {{-- Past School Years List --}}
                    @if ($otherSchoolYears->isNotEmpty())
                        <div class="sett-subheading px-0 mb-2">
                            Past School Years
                        </div>
                        <div>
                            @foreach ($otherSchoolYears as $schoolYear)
                                <div class="sett-sy-item">
                                    <div>
                                        <div class="sett-sy-item__year">{{ $schoolYear->school_year }}</div>
                                        <span class="badge rounded-pill text-secondary bg-light border px-2.5 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                            Inactive
                                        </span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            <li>
                                                <button type="button"
                                                    class="dropdown-item js-edit-school-year"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSchoolYearModal"
                                                    data-id="{{ $schoolYear->id }}"
                                                    data-school-year="{{ $schoolYear->school_year }}"
                                                    data-is-active="0">
                                                    <i class="fa-regular fa-pen-to-square me-2 text-muted"></i>
                                                    Edit
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('school-years.restore', $schoolYear->id) }}"
                                                    method="POST"
                                                    data-ajax-restore="school-year"
                                                    data-ajax-scope="#school-year-table-pane">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fa-solid fa-rotate-left me-2"></i>
                                                        Set as Active
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($schoolYears->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block opacity-25"></i>
                            <div>No school years configured yet. Click "Add New School Year" to create one.</div>
                        </div>
                    @endif

                </div>{{-- /school-year-table-pane --}}

            </div>{{-- /school-year pane --}}

        </div>{{-- /tab-content --}}
            </div>{{-- /sett-card --}}
        </section>
    </div>

    {{-- Bottom Discoverable CTA --}}
    <div class="sett-bottom-bar">
        <a href="{{ route('admin.dashboard') }}" class="sett-bottom-back">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Return to School Admin Dashboard</span>
        </a>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         CENTERED MODALS (Centered in viewport via modal-dialog-centered)
    ══════════════════════════════════════════════════════════════ --}}

    {{-- ── 1. Edit Full Name Modal (3-Step Flow with Re-authentication) ── --}}
    <div class="modal fade" id="editNameModal" tabindex="-1" aria-labelledby="editNameModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-0 pb-0 px-4 pt-4 align-items-start">
                    <div>
                        <h6 class="modal-title fw-bold" id="editNameModalLabel" style="font-size: 1rem; color: #0f172a;">
                            Edit Full Name
                        </h6>
                        {{-- Step progress indicators --}}
                        <div class="d-flex gap-1 mt-2" aria-hidden="true">
                            <div id="nameDot1" style="width: 22px; height: 3px; border-radius: 9999px; background: #2F4AC0; transition: background 0.25s;"></div>
                            <div id="nameDot2" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                            <div id="nameDot3" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pt-3 pb-2">
                    {{-- Step 1: Input Name --}}
                    <div class="sa-name-step" data-step="1">
                        <p class="text-muted small mb-3">
                            Update your first and last name.
                        </p>
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="modal_first_name" class="form-label small fw-semibold text-secondary mb-1">
                                    First name
                                </label>
                                <input type="text" id="modal_first_name" class="form-control"
                                    value="{{ $user->first_name }}" autocomplete="given-name" required>
                            </div>
                            <div class="col-6">
                                <label for="modal_last_name" class="form-label small fw-semibold text-secondary mb-1">
                                    Last name
                                </label>
                                <input type="text" id="modal_last_name" class="form-control"
                                    value="{{ $user->last_name }}" autocomplete="family-name" required>
                            </div>
                        </div>
                        <div id="nameStep1Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 2: Re-authenticate with Current Password --}}
                    <div class="sa-name-step d-none" data-step="2">
                        <div class="p-2.5 rounded mb-3 bg-light border" style="font-size: 0.82rem; color: #475569;">
                            <i class="fa-solid fa-lock text-primary me-1"></i>
                            For your security, please enter your current password to confirm this name change.
                        </div>
                        <div>
                            <label for="modal_name_password" class="form-label small fw-semibold text-secondary mb-1">
                                Current password
                            </label>
                            <div class="input-group">
                                <input type="password" id="modal_name_password"
                                    class="form-control" autocomplete="current-password"
                                    placeholder="Enter your current password">
                                <button type="button" class="btn btn-outline-secondary sett-pwd-toggle"
                                    data-target="modal_name_password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="nameStep2Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 3: Success Screen --}}
                    <div class="sa-name-step d-none" data-step="3">
                        <div class="text-center py-4">
                            <div style="width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fa-solid fa-check text-success" style="font-size: 1.35rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 1rem; color: #0f172a;">Full Name Updated</h6>
                            <p class="text-muted small mb-0">Your name has been updated successfully across the system.</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <div id="nameFooter1" class="d-flex justify-content-end gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="nameStep1Continue" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Continue
                        </button>
                    </div>
                    <div id="nameFooter2" class="d-flex justify-content-end gap-2 w-100 d-none">
                        <button type="button" class="btn btn-light rounded-pill px-3" id="nameStep2Back">Back</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="nameStep2Submit" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Confirm &amp; Save
                        </button>
                    </div>
                    <div id="nameFooter3" class="d-flex justify-content-end w-100 d-none">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Done
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ── 2. Edit Email Modal (3-Step Flow with Re-authentication) ── --}}
    <div class="modal fade" id="editEmailModal" tabindex="-1" aria-labelledby="editEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-0 pb-0 px-4 pt-4 align-items-start">
                    <div>
                        <h6 class="modal-title fw-bold" id="editEmailModalLabel" style="font-size: 1rem; color: #0f172a;">
                            Edit Email Address
                        </h6>
                        {{-- Step progress indicators --}}
                        <div class="d-flex gap-1 mt-2" aria-hidden="true">
                            <div id="emailDot1" style="width: 22px; height: 3px; border-radius: 9999px; background: #2F4AC0; transition: background 0.25s;"></div>
                            <div id="emailDot2" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                            <div id="emailDot3" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pt-3 pb-2">
                    {{-- Step 1: Input Email --}}
                    <div class="sa-email-step" data-step="1">
                        <p class="text-muted small mb-3">
                            Enter your new administrative email address.
                        </p>
                        <div>
                            <label for="modal_new_email" class="form-label small fw-semibold text-secondary mb-1">
                                New email address
                            </label>
                            <input type="email" id="modal_new_email" class="form-control"
                                value="{{ $user->email }}" autocomplete="email" required>
                        </div>
                        <div id="emailStep1Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 2: Re-authenticate with Current Password --}}
                    <div class="sa-email-step d-none" data-step="2">
                        <div class="p-2.5 rounded mb-3 bg-light border" style="font-size: 0.82rem; color: #475569;">
                            <i class="fa-solid fa-lock text-primary me-1"></i>
                            For your security, please enter your current password to confirm this email change.
                        </div>
                        <div>
                            <label for="modal_email_password" class="form-label small fw-semibold text-secondary mb-1">
                                Current password
                            </label>
                            <div class="input-group">
                                <input type="password" id="modal_email_password"
                                    class="form-control" autocomplete="current-password"
                                    placeholder="Enter your current password">
                                <button type="button" class="btn btn-outline-secondary sett-pwd-toggle"
                                    data-target="modal_email_password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="emailStep2Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 3: Success Screen --}}
                    <div class="sa-email-step d-none" data-step="3">
                        <div class="text-center py-4">
                            <div style="width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fa-solid fa-check text-success" style="font-size: 1.35rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 1rem; color: #0f172a;">Email Address Updated</h6>
                            <p class="text-muted small mb-0">Your account email has been successfully updated.</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <div id="emailFooter1" class="d-flex justify-content-end gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="emailStep1Continue" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Continue
                        </button>
                    </div>
                    <div id="emailFooter2" class="d-flex justify-content-end gap-2 w-100 d-none">
                        <button type="button" class="btn btn-light rounded-pill px-3" id="emailStep2Back">Back</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="emailStep2Submit" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Confirm &amp; Save
                        </button>
                    </div>
                    <div id="emailFooter3" class="d-flex justify-content-end w-100 d-none">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Done
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ── 3. Change Password Modal (3-Step Flow) ────────────────── --}}
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow">

                <div class="modal-header border-0 pb-0 px-4 pt-4 align-items-start">
                    <div>
                        <h6 class="modal-title fw-bold" id="changePasswordModalLabel" style="font-size: 1rem; color: #0f172a;">
                            Change Password
                        </h6>
                        {{-- Step progress indicators --}}
                        <div class="d-flex gap-1 mt-2" aria-hidden="true">
                            <div id="pwdDot1" style="width: 22px; height: 3px; border-radius: 9999px; background: #2F4AC0; transition: background 0.25s;"></div>
                            <div id="pwdDot2" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                            <div id="pwdDot3" style="width: 22px; height: 3px; border-radius: 9999px; background: #e2e8f0; transition: background 0.25s;"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pt-3 pb-2">
                    {{-- Step 1: Verify Current Password --}}
                    <div class="sa-pwd-step" data-step="1">
                        <p class="text-muted small mb-3">
                            Confirm your current password to continue.
                        </p>
                        <div>
                            <label for="modal_current_password" class="form-label small fw-semibold text-secondary mb-1">
                                Current password
                            </label>
                            <div class="input-group">
                                <input type="password" id="modal_current_password"
                                    class="form-control" autocomplete="current-password"
                                    placeholder="Enter your current password">
                                <button type="button" class="btn btn-outline-secondary sett-pwd-toggle"
                                    data-target="modal_current_password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="pwdStep1Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 2: New Password + Confirmation --}}
                    <div class="sa-pwd-step d-none" data-step="2">
                        <p class="text-muted small mb-3">
                            Create a strong, unique new password.
                        </p>
                        <div class="mb-3">
                            <label for="modal_new_password" class="form-label small fw-semibold text-secondary mb-1">
                                New password
                            </label>
                            <div class="input-group">
                                <input type="password" id="modal_new_password"
                                    class="form-control" autocomplete="new-password"
                                    placeholder="Enter new password">
                                <button type="button" class="btn btn-outline-secondary sett-pwd-toggle"
                                    data-target="modal_new_password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            {{-- Strength meter --}}
                            <div class="sett-pwd-strength-track" id="pwdStrengthTrack">
                                <div class="sett-pwd-strength-bar" id="pwdStrengthBar"></div>
                            </div>
                            <span class="sett-pwd-strength-label" id="pwdStrengthLabel"></span>
                            <div class="text-muted" style="font-size: 0.72rem; margin-top: 0.25rem;">
                                Minimum 8 characters with letters, numbers, and symbols.
                            </div>
                        </div>

                        <div>
                            <label for="modal_confirm_password" class="form-label small fw-semibold text-secondary mb-1">
                                Confirm new password
                            </label>
                            <div class="input-group">
                                <input type="password" id="modal_confirm_password"
                                    class="form-control" autocomplete="new-password"
                                    placeholder="Re-enter new password">
                                <button type="button" class="btn btn-outline-secondary sett-pwd-toggle"
                                    data-target="modal_confirm_password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div id="pwdStep2Error" class="d-none mt-2 text-danger small"></div>
                    </div>

                    {{-- Step 3: Success Screen --}}
                    <div class="sa-pwd-step d-none" data-step="3">
                        <div class="text-center py-4">
                            <div style="width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fa-solid fa-check text-success" style="font-size: 1.35rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size: 1rem; color: #0f172a;">Password Updated</h6>
                            <p class="text-muted small mb-0">Your account password has been changed successfully.</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <div id="pwdFooter1" class="d-flex justify-content-end gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="pwdStep1Continue" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Continue
                        </button>
                    </div>
                    <div id="pwdFooter2" class="d-flex justify-content-end gap-2 w-100 d-none">
                        <button type="button" class="btn btn-light rounded-pill px-3" id="pwdStep2Back">Back</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4" id="pwdSubmitBtn" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Update Password
                        </button>
                    </div>
                    <div id="pwdFooter3" class="d-flex justify-content-end w-100 d-none">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal" style="background: #2F4AC0; border-color: #2F4AC0;">
                            Done
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ── 4. Add School Year Modal (Centered in Viewport) ───────── --}}
    <div class="modal fade" id="addSchoolYearModal" tabindex="-1" aria-labelledby="addSchoolYearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h6 class="modal-title fw-bold" id="addSchoolYearModalLabel" style="font-size: 1rem; color: #0f172a;">
                        Add School Year
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">
                    <form id="addSchoolYearForm"
                        action="{{ route('school-years.store') }}"
                        method="POST"
                        data-ajax-scope="#school-year-table-pane">
                        @csrf
                        <div data-ajax-errors></div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1" for="add_school_year_input">
                                Academic Year
                            </label>
                            <input type="text" id="add_school_year_input" name="school_year"
                                class="form-control"
                                placeholder="e.g. 2026-2027" required>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                id="add_is_active" checked>
                            <label class="form-check-label small fw-semibold text-secondary" for="add_is_active">
                                Set as current active school year
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addSchoolYearForm" class="btn btn-primary rounded-pill px-4"
                        style="background: #2F4AC0; border-color: #2F4AC0;" data-loading-text="Creating…">
                        Create School Year
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- ── 5. Edit School Year Modal (Centered in Viewport) ──────── --}}
    <div class="modal fade" id="editSchoolYearModal" tabindex="-1" aria-labelledby="editSchoolYearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h6 class="modal-title fw-bold" id="editSchoolYearModalLabel" style="font-size: 1rem; color: #0f172a;">
                        Edit School Year
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">
                    <form id="editSchoolYearForm"
                        method="POST"
                        data-update-url="{{ route('school-years.update', ':id') }}"
                        data-ajax-scope="#school-year-table-pane">
                        @csrf
                        @method('PUT')
                        <div data-ajax-errors></div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1" for="edit_school_year">
                                Academic Year
                            </label>
                            <input type="text" name="school_year" id="edit_school_year" class="form-control" required>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                            <label class="form-check-label small fw-semibold text-secondary" for="edit_is_active">
                                Set as current active school year
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editSchoolYearForm" class="btn btn-primary rounded-pill px-4"
                        style="background: #2F4AC0; border-color: #2F4AC0;" data-loading-text="Saving…">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-layouts.settings>
