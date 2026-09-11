{{-- Type A: View Advisor Overview Offcanvas Slide-Over Panel --}}
@php
    $gVal = $selectedGrade ?? $grade ?? 1;
    $sec = $activeSection ?? $section ?? null;
    $advisor = $sec?->advisor;
@endphp

<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="viewAdvisorDrawer" aria-labelledby="viewAdvisorDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-chalkboard-user fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="viewAdvisorDrawerLabel">Class Adviser Overview</h5>
                <p class="text-muted small mb-0">Designated faculty in charge of Section {{ $sec?->name }}</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        {{-- Context Badge --}}
        <div class="class-hub-context-badge card border-0 bg-primary bg-opacity-10 p-3 mb-3 rounded-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-lock text-primary small"></i>
                    <span class="small fw-semibold text-primary">Class Cohort Scope</span>
                </div>
                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small">
                    Grade {{ $gVal }} · {{ $sec?->name ?? 'None' }}
                </span>
            </div>
            <div class="small text-muted mt-1">
                Advisory assignment for <span class="fw-semibold text-dark">Section {{ $sec?->name ?? 'N/A' }}</span>.
            </div>
        </div>

        @if ($advisor)
            {{-- Faculty Profile Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 52px; height: 52px;">
                            {{ strtoupper(substr($advisor->first_name ?? '', 0, 1) . substr($advisor->last_name ?? '', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $advisor->full_name }}</h6>
                            <p class="text-muted small mb-0">{{ $advisor->user?->email ?? 'No email on record' }}</p>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 mt-1 small">
                                <i class="fas fa-check-circle fa-xs me-1"></i>Active Faculty
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Employee ID</span>
                            <span class="fw-semibold text-dark">{{ $advisor->employee_id ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Contact Number</span>
                            <span class="fw-semibold text-dark">{{ $advisor->contact_number ?? 'N/A' }}</span>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Designation</span>
                            <span class="fw-semibold text-dark">Primary Class Adviser · Grade {{ $gVal }} - {{ $sec?->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section Cohort Summary Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <h6 class="fw-bold text-dark mb-2.5 small text-uppercase tracking-wide">Cohort Details</h6>
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Grade Level</span>
                            <span class="fw-semibold text-dark">Grade {{ $gVal }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Session Schedule</span>
                            <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $sec?->session_type ?? 'Whole Day')) }}</span>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Class Capacity</span>
                            <span class="fw-semibold text-dark">{{ $sec?->capacity ?? '40' }} max</span>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Assigned Since</span>
                            <span class="fw-semibold text-dark">{{ $sec?->updated_at?->format('M d, Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
                <i class="fas fa-user-slash fa-2x text-muted opacity-50 mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">No Adviser Designated</h6>
                <p class="text-muted small mb-0">This class section does not currently have a faculty adviser assigned.</p>
            </div>
        @endif
    </div>

    {{-- Anchored Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-between align-items-center gap-2">
        @if ($advisor && $sec)
            <form action="{{ route('sections.update-advisor', $sec->id) }}" method="POST" data-ajax-form="advisor" class="m-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="advisor_id" value="">
                <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-3 fw-medium" title="Unassign current adviser">
                    <i class="fas fa-user-minus me-1"></i> Remove
                </button>
            </form>
        @else
            <div></div>
        @endif

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Close</button>
            <button type="button" class="btn btn-dark px-3 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5"
                data-bs-toggle="offcanvas" data-bs-target="#assignAdvisorDrawer">
                <i class="fas fa-user-pen fa-sm"></i>
                <span>{{ $advisor ? 'Change Adviser' : 'Assign Adviser' }}</span>
            </button>
        </div>
    </div>
</div>
