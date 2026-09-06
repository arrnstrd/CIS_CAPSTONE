// ===========================================================================
// School Admin — Dedicated Settings Page JavaScript
//
// Sections:
//   1. Helpers (CSRF, fetch PUT, error display, toast)
//   2. School Year CRUD (AjaxCrud integration)
//   3. Name Edit Modal (3-step with server re-authentication)
//   4. Email Edit Modal (3-step with server re-authentication)
//   5. Password Change Modal (3-step progressive verification)
//   6. Password Visibility Toggle (delegated)
//   7. Lifecycle initialization
// ===========================================================================

// ── 1. Helpers ──────────────────────────────────────────────────────────────

function sett_csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Send a method-spoofed PUT via fetch with form-urlencoded body.
 * Requests JSON response so Laravel returns validation errors as 422 JSON.
 */
async function sett_fetchPut(url, fields) {
    const params = new URLSearchParams();
    params.append('_method', 'PUT');
    params.append('_token', sett_csrf());
    Object.entries(fields).forEach(function ([k, v]) {
        params.append(k, v);
    });

    return fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': sett_csrf(),
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString(),
    });
}

/** Show the shared toast notification. */
function sett_showToast(message) {
    const toast = document.getElementById('sett-toast');
    if (!toast) return;
    const msgEl = toast.querySelector('.sett-toast-msg');
    if (msgEl) msgEl.textContent = message;
    toast.classList.remove('d-none');
    clearTimeout(toast._settTimer);
    toast._settTimer = setTimeout(function () {
        toast.classList.add('d-none');
    }, 3500);
}

/** Show an inline error message in a given container element. */
function sett_showInlineError(elementId, message) {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.textContent = message;
    el.classList.remove('d-none');
}

/** Clear an inline error container. */
function sett_clearInlineError(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.textContent = '';
    el.classList.add('d-none');
}


// ── 2. School Year CRUD ─────────────────────────────────────────────────────

document.addEventListener('show.bs.modal', function (event) {
    if (event.target?.id !== 'editSchoolYearModal') return;

    const button = event.relatedTarget;
    if (!button) return;

    const form = document.getElementById('editSchoolYearForm');
    if (!form) return;

    form.action = form.dataset.updateUrl.replace(':id', button.dataset.id);
    const input = document.getElementById('edit_school_year');
    const cb    = document.getElementById('edit_is_active');
    if (input) input.value = button.dataset.schoolYear || '';
    if (cb)    cb.checked  = button.dataset.isActive === '1';
});

document.addEventListener('hidden.bs.modal', function (event) {
    const id = event.target?.id;

    if (id === 'addSchoolYearModal') {
        document.getElementById('addSchoolYearForm')?.reset();
        const cb = document.getElementById('add_is_active');
        if (cb) cb.checked = true;
    }

    if (id === 'editSchoolYearModal') {
        document.getElementById('editSchoolYearForm')?.reset();
    }

    if (id === 'editNameModal') {
        sett_resetNameModal();
    }

    if (id === 'editEmailModal') {
        sett_resetEmailModal();
    }

    if (id === 'changePasswordModal') {
        sett_resetPasswordModal();
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (form?.id === 'addSchoolYearForm' || form?.id === 'editSchoolYearForm') {
        event.preventDefault();
        if (window.ajaxCrud?.submitAjaxForm) {
            window.ajaxCrud.submitAjaxForm(form, {
                scope: form.dataset.ajaxScope || '#school-year-table-pane',
            });
        } else {
            form.submit();
        }
    }

    if (form?.matches('[data-ajax-delete="school-year"]')) {
        event.preventDefault();
        if (window.ajaxCrud?.submitAjaxDelete) {
            window.ajaxCrud.submitAjaxDelete(form, {
                scope: form.dataset.ajaxScope || '#school-year-table-pane',
            });
        } else {
            form.submit();
        }
    }

    if (form?.matches('[data-ajax-restore="school-year"]')) {
        event.preventDefault();
        if (window.ajaxCrud?.submitAjaxDelete) {
            window.ajaxCrud.submitAjaxDelete(form, {
                scope: form.dataset.ajaxScope || '#school-year-table-pane',
            });
        } else {
            form.submit();
        }
    }
});


// ── 3. Name Edit Modal (3-Step Flow with Re-authentication) ────────────────

function sett_goToNameStep(step) {
    document.querySelectorAll('.sa-name-step').forEach(function (el) {
        el.classList.toggle('d-none', parseInt(el.dataset.step) !== step);
    });

    [1, 2, 3].forEach(function (n) {
        const dot = document.getElementById('nameDot' + n);
        if (dot) dot.style.background = n <= step ? '#2F4AC0' : '#e2e8f0';
    });

    [1, 2, 3].forEach(function (n) {
        const footer = document.getElementById('nameFooter' + n);
        if (footer) footer.classList.toggle('d-none', n !== step);
    });
}

function sett_resetNameModal() {
    sett_clearInlineError('nameStep1Error');
    sett_clearInlineError('nameStep2Error');

    const pwdInput = document.getElementById('modal_name_password');
    if (pwdInput) {
        pwdInput.value = '';
        pwdInput.type = 'password';
    }

    document.querySelectorAll('#editNameModal .sett-pwd-toggle i').forEach(function (icon) {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    });

    sett_goToNameStep(1);
}

async function sett_submitNameChange() {
    sett_clearInlineError('nameStep2Error');

    const firstName = document.getElementById('modal_first_name')?.value.trim() ?? '';
    const lastName  = document.getElementById('modal_last_name')?.value.trim()  ?? '';
    const password  = document.getElementById('modal_name_password')?.value     ?? '';

    if (!password.trim()) {
        sett_showInlineError('nameStep2Error', 'Please enter your current password to confirm.');
        return;
    }

    const btn = document.getElementById('nameStep2Submit');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

    try {
        const response = await sett_fetchPut('/settings/profile', {
            settings_tab:     'account-security',
            first_name:       firstName,
            last_name:        lastName,
            current_password: password,
        });

        if (response.status === 422) {
            const data   = await response.json();
            const errors = data.errors ?? {};

            if (errors.current_password) {
                sett_showInlineError('nameStep2Error', errors.current_password[0]);
            } else if (errors.first_name || errors.last_name) {
                sett_goToNameStep(1);
                sett_clearInlineError('nameStep1Error');
                sett_showInlineError('nameStep1Error', (errors.first_name || errors.last_name)[0]);
            } else {
                const firstMsg = Object.values(errors).flat()[0] ?? 'An error occurred. Please try again.';
                sett_showInlineError('nameStep2Error', firstMsg);
            }
            return;
        }

        if (response.ok) {
            const data = await response.json();
            const fullName = data.user?.full_name || [firstName, lastName].filter(Boolean).join(' ') || 'School Administrator';

            // Update displayed name on page and in top navigation if present
            const displayName = document.getElementById('sett-display-fullname');
            if (displayName) displayName.textContent = fullName;

            const topNavName = document.querySelector('.sa-topbar__user-name');
            if (topNavName) topNavName.textContent = fullName;

            const settTopName = document.querySelector('.sett-topbar__name');
            if (settTopName) settTopName.textContent = fullName;

            sett_goToNameStep(3);
            sett_showToast('Full name updated successfully.');
        } else {
            sett_showInlineError('nameStep2Error', 'Server error. Please try again.');
        }
    } catch (err) {
        console.error('[Settings] Name update error:', err);
        sett_showInlineError('nameStep2Error', 'Network error. Please try again.');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Confirm & Save'; }
    }
}

function sett_initNameModal() {
    const modal = document.getElementById('editNameModal');
    if (!modal) return;

    // Step 1 Continue
    document.getElementById('nameStep1Continue')?.addEventListener('click', function () {
        sett_clearInlineError('nameStep1Error');
        const firstName = document.getElementById('modal_first_name')?.value.trim() ?? '';
        const lastName  = document.getElementById('modal_last_name')?.value.trim()  ?? '';

        if (!firstName || !lastName) {
            sett_showInlineError('nameStep1Error', 'Both first name and last name are required.');
            return;
        }

        sett_clearInlineError('nameStep2Error');
        const pwdInput = document.getElementById('modal_name_password');
        if (pwdInput) pwdInput.value = '';
        sett_goToNameStep(2);
    });

    // Step 2 Back
    document.getElementById('nameStep2Back')?.addEventListener('click', function () {
        sett_clearInlineError('nameStep2Error');
        sett_goToNameStep(1);
    });

    // Step 2 Submit
    document.getElementById('nameStep2Submit')?.addEventListener('click', function () {
        sett_submitNameChange();
    });
}


// ── 4. Email Edit Modal (3-Step Flow with Re-authentication) ───────────────

function sett_goToEmailStep(step) {
    document.querySelectorAll('.sa-email-step').forEach(function (el) {
        el.classList.toggle('d-none', parseInt(el.dataset.step) !== step);
    });

    [1, 2, 3].forEach(function (n) {
        const dot = document.getElementById('emailDot' + n);
        if (dot) dot.style.background = n <= step ? '#2F4AC0' : '#e2e8f0';
    });

    [1, 2, 3].forEach(function (n) {
        const footer = document.getElementById('emailFooter' + n);
        if (footer) footer.classList.toggle('d-none', n !== step);
    });
}

function sett_resetEmailModal() {
    sett_clearInlineError('emailStep1Error');
    sett_clearInlineError('emailStep2Error');

    const pwdInput = document.getElementById('modal_email_password');
    if (pwdInput) {
        pwdInput.value = '';
        pwdInput.type = 'password';
    }

    document.querySelectorAll('#editEmailModal .sett-pwd-toggle i').forEach(function (icon) {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    });

    sett_goToEmailStep(1);
}

async function sett_submitEmailChange() {
    sett_clearInlineError('emailStep2Error');

    const newEmail = document.getElementById('modal_new_email')?.value.trim() ?? '';
    const password = document.getElementById('modal_email_password')?.value     ?? '';

    if (!password.trim()) {
        sett_showInlineError('emailStep2Error', 'Please enter your current password to confirm.');
        return;
    }

    const btn = document.getElementById('emailStep2Submit');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

    try {
        const response = await sett_fetchPut('/settings/profile', {
            settings_tab:     'account-security',
            email:            newEmail,
            current_password: password,
        });

        if (response.status === 422) {
            const data   = await response.json();
            const errors = data.errors ?? {};

            if (errors.current_password) {
                sett_showInlineError('emailStep2Error', errors.current_password[0]);
            } else if (errors.email) {
                sett_goToEmailStep(1);
                sett_clearInlineError('emailStep1Error');
                sett_showInlineError('emailStep1Error', errors.email[0]);
            } else {
                const firstMsg = Object.values(errors).flat()[0] ?? 'An error occurred. Please try again.';
                sett_showInlineError('emailStep2Error', firstMsg);
            }
            return;
        }

        if (response.ok) {
            // Update displayed email on page
            const displayEmail = document.getElementById('sett-display-email');
            if (displayEmail) displayEmail.textContent = newEmail;

            // Update email in top navigation if present
            const topNavEmail = document.querySelector('.sa-topbar__user-email');
            if (topNavEmail) topNavEmail.textContent = newEmail;

            sett_goToEmailStep(3);
            sett_showToast('Email address updated successfully.');
        } else {
            sett_showInlineError('emailStep2Error', 'Server error. Please try again.');
        }
    } catch (err) {
        console.error('[Settings] Email update error:', err);
        sett_showInlineError('emailStep2Error', 'Network error. Please try again.');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Confirm & Save'; }
    }
}

function sett_initEmailModal() {
    const modal = document.getElementById('editEmailModal');
    if (!modal) return;

    // Step 1 Continue
    document.getElementById('emailStep1Continue')?.addEventListener('click', function () {
        sett_clearInlineError('emailStep1Error');
        const newEmail = document.getElementById('modal_new_email')?.value.trim() ?? '';

        if (!newEmail) {
            sett_showInlineError('emailStep1Error', 'Please enter a new email address.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
            sett_showInlineError('emailStep1Error', 'Please enter a valid email address.');
            return;
        }

        const currentEmail = document.getElementById('sett-display-email')?.textContent.trim() ?? '';
        if (newEmail.toLowerCase() === currentEmail.toLowerCase()) {
            sett_showInlineError('emailStep1Error', 'This is already your current email address.');
            return;
        }

        sett_clearInlineError('emailStep2Error');
        const pwdInput = document.getElementById('modal_email_password');
        if (pwdInput) pwdInput.value = '';
        sett_goToEmailStep(2);
    });

    // Step 2 Back
    document.getElementById('emailStep2Back')?.addEventListener('click', function () {
        sett_clearInlineError('emailStep2Error');
        sett_goToEmailStep(1);
    });

    // Step 2 Submit
    document.getElementById('emailStep2Submit')?.addEventListener('click', function () {
        sett_submitEmailChange();
    });
}


// ── 5. Password Change Modal (3-Step Flow) ──────────────────────────────────

function sett_goToPasswordStep(step) {
    document.querySelectorAll('.sa-pwd-step').forEach(function (el) {
        el.classList.toggle('d-none', parseInt(el.dataset.step) !== step);
    });

    [1, 2, 3].forEach(function (n) {
        const dot = document.getElementById('pwdDot' + n);
        if (dot) dot.style.background = n <= step ? '#2F4AC0' : '#e2e8f0';
    });

    [1, 2, 3].forEach(function (n) {
        const footer = document.getElementById('pwdFooter' + n);
        if (footer) footer.classList.toggle('d-none', n !== step);
    });
}

function sett_updatePasswordStrength(password) {
    const bar   = document.getElementById('pwdStrengthBar');
    const label = document.getElementById('pwdStrengthLabel');
    if (!bar || !label) return;

    if (!password) {
        bar.style.width = '0%';
        label.textContent = '';
        return;
    }

    const checks = [
        password.length >= 8,
        /[A-Z]/.test(password),
        /[a-z]/.test(password),
        /[0-9]/.test(password),
        /[^A-Za-z0-9]/.test(password),
    ];
    const score = checks.filter(Boolean).length;

    const levels = [
        { w: '15%', color: '#ef4444', text: 'Too short' },
        { w: '25%', color: '#ef4444', text: 'Too short' },
        { w: '45%', color: '#f97316', text: 'Weak'      },
        { w: '65%', color: '#eab308', text: 'Fair'      },
        { w: '85%', color: '#22c55e', text: 'Good'      },
        { w: '100%', color: '#16a34a', text: 'Strong'   },
    ];

    const lvl = levels[Math.min(score, 5)];
    bar.style.width           = lvl.w;
    bar.style.backgroundColor = lvl.color;
    label.textContent         = lvl.text;
    label.style.color         = lvl.color;
}

function sett_resetPasswordModal() {
    sett_clearInlineError('pwdStep1Error');
    sett_clearInlineError('pwdStep2Error');

    ['modal_current_password', 'modal_new_password', 'modal_confirm_password'].forEach(function (id) {
        const input = document.getElementById(id);
        if (!input) return;
        input.value = '';
        input.type  = 'password';
    });

    document.querySelectorAll('#changePasswordModal .sett-pwd-toggle i').forEach(function (icon) {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    });

    sett_updatePasswordStrength('');
    sett_goToPasswordStep(1);
}

async function sett_submitPasswordChange() {
    sett_clearInlineError('pwdStep1Error');
    sett_clearInlineError('pwdStep2Error');

    const currentPwd = document.getElementById('modal_current_password')?.value ?? '';
    const newPwd     = document.getElementById('modal_new_password')?.value     ?? '';
    const confirmPwd = document.getElementById('modal_confirm_password')?.value ?? '';

    if (!newPwd) {
        sett_showInlineError('pwdStep2Error', 'Please enter a new password.');
        return;
    }

    if (newPwd !== confirmPwd) {
        sett_showInlineError('pwdStep2Error', 'The new passwords do not match.');
        return;
    }

    const btn = document.getElementById('pwdSubmitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Updating…'; }

    try {
        const response = await sett_fetchPut('/settings/password', {
            settings_tab:          'account-security',
            current_password:      currentPwd,
            password:              newPwd,
            password_confirmation: confirmPwd,
        });

        if (response.status === 422) {
            const data   = await response.json();
            const errors = data.errors ?? {};

            if (errors.current_password) {
                sett_goToPasswordStep(1);
                sett_showInlineError('pwdStep1Error', errors.current_password[0]);
            } else if (errors.password) {
                sett_showInlineError('pwdStep2Error', errors.password[0]);
            } else {
                const firstMsg = Object.values(errors).flat()[0] ?? 'An error occurred. Please try again.';
                sett_showInlineError('pwdStep2Error', firstMsg);
            }
            return;
        }

        if (response.ok) {
            sett_goToPasswordStep(3);
            sett_showToast('Password changed successfully.');
        } else {
            sett_showInlineError('pwdStep2Error', 'Something went wrong. Please try again.');
        }
    } catch (err) {
        console.error('[Settings] Password change error:', err);
        sett_showInlineError('pwdStep2Error', 'Network error. Please try again.');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Update Password'; }
    }
}

function sett_initPasswordModal() {
    const modal = document.getElementById('changePasswordModal');
    if (!modal) return;

    // Step 1 Continue
    document.getElementById('pwdStep1Continue')?.addEventListener('click', function () {
        sett_clearInlineError('pwdStep1Error');
        const val = document.getElementById('modal_current_password')?.value ?? '';
        if (!val.trim()) {
            sett_showInlineError('pwdStep1Error', 'Please enter your current password.');
            return;
        }
        sett_goToPasswordStep(2);
    });

    // Step 2 Back
    document.getElementById('pwdStep2Back')?.addEventListener('click', function () {
        sett_clearInlineError('pwdStep2Error');
        sett_goToPasswordStep(1);
    });

    // Step 2 Submit
    document.getElementById('pwdSubmitBtn')?.addEventListener('click', function () {
        sett_submitPasswordChange();
    });

    // Live Strength Meter
    document.getElementById('modal_new_password')?.addEventListener('input', function () {
        sett_updatePasswordStrength(this.value);
    });
}


// ── 6. Password Visibility Toggle ───────────────────────────────────────────

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.sett-pwd-toggle');
    if (!btn) return;

    const targetId = btn.dataset.target;
    const input    = document.getElementById(targetId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    }
});


// ── 7. Lifecycle Initialization ─────────────────────────────────────────────

function sett_initAll() {
    sett_initNameModal();
    sett_initEmailModal();
    sett_initPasswordModal();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', sett_initAll);
} else {
    sett_initAll();
}
