{{-- Type A: View Assignments Overview Offcanvas Slide-Over Panel --}}
@php
    $gVal = $selectedGrade ?? $grade ?? 1;
    $sec = $activeSection ?? $section ?? null;
@endphp

<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="viewAssignmentsDrawer" aria-labelledby="viewAssignmentsDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-chalkboard-teacher fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="viewAssignmentsDrawerLabel">Teacher Assignments</h5>
                <p class="text-muted small mb-0">Allocated faculty for Section {{ $sec?->name }}</p>
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
                    <span class="small fw-semibold text-primary">Class Section Scope</span>
                </div>
                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small">
                    Grade {{ $gVal }} · {{ $sec?->name ?? 'None' }}
                </span>
            </div>
            <div class="small text-muted mt-1">
                Faculty allocations for <span class="fw-semibold text-dark">Section {{ $sec?->name ?? 'N/A' }}</span>.
            </div>
        </div>

        {{-- Assignments List --}}
        @if ($teachingAssignments->isNotEmpty())
            <div class="d-flex flex-column gap-2.5">
                @foreach ($teachingAssignments as $assignment)
                    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-bold text-dark small">{{ $assignment->subject?->name ?? 'Subject' }}</span>
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded small" style="font-size: 0.7rem;">
                                        {{ $assignment->subject?->code ?? '-' }}
                                    </span>
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-1.5">
                                    <i class="fas fa-user text-secondary fa-xs"></i>
                                    <span class="fw-medium text-dark">{{ $assignment->teacher?->full_name ?? 'Teacher' }}</span>
                                </div>
                                @if ($assignment->schoolYear)
                                    <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                        <i class="fa-regular fa-calendar fa-xs me-1"></i>S.Y. {{ $assignment->schoolYear->school_year }}
                                    </div>
                                @endif
                            </div>

                            {{-- Unassign / Delete Action --}}
                            <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST"
                                data-ajax-delete="assignment" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm p-1.5 rounded-2 d-inline-flex align-items-center justify-content-center"
                                    style="width: 28px; height: 28px;" title="Unassign teacher">
                                    <i class="fas fa-trash fa-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
                <i class="fas fa-chalkboard-user fa-2x text-muted opacity-50 mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">No Teacher Assignments</h6>
                <p class="text-muted small mb-0">No subject teachers have been allocated to this section yet.</p>
            </div>
        @endif
    </div>

    {{-- Anchored Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-between align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Close</button>
        <button type="button" class="btn btn-dark px-3 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5"
            data-bs-toggle="offcanvas" data-bs-target="#assignTeacherDrawer">
            <i class="fas fa-plus fa-sm"></i>
            <span>+ Assign Teacher</span>
        </button>
    </div>
</div>
