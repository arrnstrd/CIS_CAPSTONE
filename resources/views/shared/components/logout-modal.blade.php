{{--
    Logout Confirmation Modal Component
    -------------------------------------------------
    Usage: @include('components.logout-modal')
--}}

<div class="modal fade sleek-logout-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sleek-logout-card">

            <!-- Modal Header -->
            <div class="sleek-logout-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="sleek-header-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <h6 class="sleek-modal-title mb-0" id="logoutModalLabel">Confirm Sign Out</h6>
                </div>
                <button type="button" class="sleek-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="sleek-logout-body">
                <p class="sleek-logout-text mb-0">
                    Are you sure you want to log out of your account? Any unsaved progress may be lost.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="sleek-logout-footer">
                <button type="button" class="btn-sleek-cancel" data-bs-dismiss="modal">
                    Cancel
                </button>

                <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-sleek-logout">
                        <i class="fas fa-sign-out-alt me-1.5"></i> Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
.sleek-logout-modal .modal-dialog {
    max-width: 380px;
    margin: 1.5rem auto;
}

.sleek-logout-card {
    border: 1px solid #e2e8f0 !important;
    border-radius: 0 !important;
    background: #ffffff;
    box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

/* Header */
.sleek-logout-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
}

.sleek-header-icon {
    width: 28px;
    height: 28px;
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fee2e2;
    border-radius: 0 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    flex-shrink: 0;
}

.sleek-modal-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
}

.sleek-close-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 0.85rem;
    padding: 4px 6px;
    line-height: 1;
    cursor: pointer;
    transition: color 0.15s ease;
    border-radius: 0 !important;
}

.sleek-close-btn:hover {
    color: #0f172a;
}

/* Body */
.sleek-logout-body {
    padding: 16px 18px;
    background: #ffffff;
}

.sleek-logout-text {
    font-size: 0.83rem;
    color: #475569;
    line-height: 1.5;
}

/* Footer */
.sleek-logout-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 18px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

/* Sharp Buttons */
.btn-sleek-cancel {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 0 !important;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 7px 14px;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1.3;
}

.btn-sleek-cancel:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

.btn-sleek-logout {
    background: #dc2626;
    border: 1px solid #dc2626;
    border-radius: 0 !important;
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 7px 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    line-height: 1.3;
}

.btn-sleek-logout:hover {
    background: #b91c1c;
    border-color: #b91c1c;
    color: #ffffff;
}
</style>