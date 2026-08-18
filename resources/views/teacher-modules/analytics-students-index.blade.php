<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Click a student to view their subject trends.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'analytics'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'School-wide', 'url' => route('teacher.grading-system.analytics')],
        ['label' => 'Grade ' . $section->grade_level, 'url' => route('teacher.grading-system.analytics.show', $section->grade_level)],
        ['label' => $section->name, 'url' => '#'],
    ]])

    <div class="row g-3">
        @forelse ($students as $s)
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('teacher.grading-system.analytics.student', $s->enrollment_id) }}" class="gs-grade-card-link">
                    <div class="gs-section-card">
                        <p class="gs-section-card-title">{{ $s->name }}</p>
                        <p class="gs-section-card-meta">{{ $s->student_number }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <p class="gs-section-card-avg mb-0">{{ $s->avg_grade !== null ? $s->avg_grade : '—' }}</p>
                            @if ($s->risk_level)
                                @php
                                    $riskClass = ['Low' => 'gs-badge-success', 'Moderate' => 'gs-badge-warning', 'High' => 'gs-badge-danger'][$s->risk_level];
                                @endphp
                                <span class="gs-badge {{ $riskClass }}">{{ $s->risk_level }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No active students enrolled in this section.
                </div>
            </div>
        @endforelse
    </div>

</x-layouts.teacher>