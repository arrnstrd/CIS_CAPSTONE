@props(['student', 'currentEnrollment', 'enrollmentHistory' => collect()])

<div class="tab-pane fade mx-4 show active" id="academic" role="tabpanel">

    {{-- IDs row --}}
    <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size: 10px; letter-spacing: 0.07em;">
        Student IDs
    </p>
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Student
                    number</p>
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
                    <span
                        class="badge {{ $currentEnrollment->status === 'active' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border">
                        {{ Str::headline($currentEnrollment->status ?? '-') }}
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
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">School year
                </p>
                <p class="mb-0 fw-medium">{{ $currentEnrollment?->schoolYear?->school_year ?? '—' }}</p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Session type
                </p>
                <p class="mb-0 fw-medium">{{ $currentEnrollment?->session_type ?? '—' }}</p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Adviser</p>
                <p class="mb-0 fw-medium">
                    {{ $currentEnrollment?->sectionModel?->advisor?->full_name ?: '—' }}
                </p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Grade level
                </p>
                <p class="mb-0 fw-medium">
                    {{ $currentEnrollment?->sectionModel?->grade_level ?? $currentEnrollment?->grade_level ?? '—' }}
                </p>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Section</p>
                <p class="mb-0 fw-medium">
                    {{ $currentEnrollment?->sectionModel?->name ?? $currentEnrollment?->getAttribute('section') ?? '—' }}
                </p>
            </div>
        </div>
    </div>
{{-- 
    <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size: 10px; letter-spacing: 0.07em;">
        Enrollment records
    </p>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>School Year</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Session</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollmentHistory as $enrollment)
                    <tr>
                        <td>{{ $enrollment->schoolYear?->school_year ?? '—' }}</td>
                        <td>{{ $enrollment->sectionModel?->grade_level ?? $enrollment->grade_level ?? '—' }}</td>
                        <td>{{ $enrollment->sectionModel?->name ?? $enrollment->getAttribute('section') ?? '—' }}</td>
                        <td>{{ Str::headline(str_replace('_', ' ', $enrollment->session_type ?? '—')) }}</td>
                        <td>
                            <span
                                class="badge {{ $enrollment->status === 'active' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border">
                                {{ Str::headline($enrollment->status ?? '—') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No enrollment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div> --}}

</div>