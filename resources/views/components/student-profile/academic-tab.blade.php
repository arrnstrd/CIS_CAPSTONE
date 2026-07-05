
@props(['student', 'currentEnrollment'])

<div class="tab-pane fade show active" id="academic" role="tabpanel">

    {{-- IDs row --}}
    <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size: 10px; letter-spacing: 0.07em;">
        Student IDs
    </p>
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Student number</p>
                <p class="mb-0 fw-medium">{{ $student->student_number }}</p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">LRN</p>
                <p class="mb-0 fw-medium">{{ $student->lrn }}</p>
            </div>
        </div>
    </div>

    {{-- Enrollment row --}}
    <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size: 10px; letter-spacing: 0.07em;">
        Current enrollment
    </p>
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Status</p>
                @if($currentEnrollment)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ $currentEnrollment->status ?? '-' }}
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                        No active enrollment
                    </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">School year</p>
                <p class="mb-0 fw-medium">{{ $currentEnrollment?->schoolYear?->school_year ?? '—' }}</p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Session type</p>
                <p class="mb-0 fw-medium">{{ $currentEnrollment?->session_type ?? '—' }}</p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Adviser</p>
                <p class="mb-0 fw-medium">
                    {{ $currentEnrollment?->adviser?->full_name ?? $currentEnrollment?->section?->advisor?->full_name ?? '—' }}
                </p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Grade level</p>
                <p class="mb-0 fw-medium">
                    {{ $currentEnrollment?->section?->grade_level ?? $currentEnrollment?->grade_level ?? '—' }}
                </p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Section</p>
                <p class="mb-0 fw-medium">{{ $currentEnrollment?->section?->name ?? '—' }}</p>
            </div>
        </div>
    </div>

</div>