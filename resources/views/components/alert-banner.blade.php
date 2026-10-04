@if (session('success') || session('error') || session('warning') || session('info') || session('status') || ($errors->any() && !isset($suppressLayoutErrors)))
<div class="cis-flash-alerts mb-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm border-0 py-3 px-4 rounded-3" role="alert" style="background-color: #ecfdf5; color: #065f46; border-left: 4px solid #059669 !important;">
            <i class="fa-solid fa-circle-check fs-5 text-success flex-shrink-0"></i>
            <div class="flex-grow-1 fs-6 fw-medium">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm border-0 py-3 px-4 rounded-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
            <i class="fa-solid fa-circle-exclamation fs-5 text-danger flex-shrink-0"></i>
            <div class="flex-grow-1 fs-6 fw-medium">
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm border-0 py-3 px-4 rounded-3" role="alert" style="background-color: #fffbeb; color: #92400e; border-left: 4px solid #d97706 !important;">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-warning flex-shrink-0"></i>
            <div class="flex-grow-1 fs-6 fw-medium">
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info') || session('status'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-3 shadow-sm border-0 py-3 px-4 rounded-3" role="alert" style="background-color: #eff6ff; color: #1e40af; border-left: 4px solid #2563eb !important;">
            <i class="fa-solid fa-circle-info fs-5 text-primary flex-shrink-0"></i>
            <div class="flex-grow-1 fs-6 fw-medium">
                {{ session('info') ?? session('status') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any() && !isset($suppressLayoutErrors))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-3 shadow-sm border-0 py-3 px-4 rounded-3" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-danger mt-1 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">Please correct the following errors:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>
@endif
