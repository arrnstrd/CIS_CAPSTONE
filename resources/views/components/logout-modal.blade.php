{{--
    Logout Confirmation Modal Component
    -------------------------------------------------
    Usage: @include('components.logout-confirmation-modal')
    Already wired to both the admin and teacher sidebars via:
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Log Out</a>

    Backend wiring (fill in later):
        Point the form's action below to your actual logout route,
        e.g. action="{{ route('logout') }}" method="POST"
--}}

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-body text-center px-4 pt-4 pb-2">

                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-danger-soft"
                     style="width: 72px; height: 72px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="text-danger">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </div>

                <h5 class="modal-title fw-semibold mb-2" id="logoutModalLabel">
                    Log out of your account?
                </h5>
                <p class="text-muted mb-0">
                    You'll need to sign in again to access your dashboard and continue where you left off.
                </p>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-3 justify-content-center gap-2">
                <button type="button" class="btn btn-light nav-pill px-4" data-bs-dismiss="modal">
                    Cancel
                </button>

                <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-danger nav-pill px-4 d-inline-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

{{--
    Optional soft-badge color, matching the ajaxCrud soft badge convention.
    Add this to your global stylesheet if bg-danger-soft isn't already defined:

    .bg-danger-soft {
        background-color: rgba(220, 53, 69, 0.1);
    }
--}}