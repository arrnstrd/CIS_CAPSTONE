<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Per-subject grade breakdown.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'Grade Levels', 'url' => route('teacher.grading-system.grades')],
        ['label' => 'Grade ' . $enrollment->section->grade_level, 'url' => route('teacher.grading-system.grades.show', $enrollment->section->grade_level)],
        ['label' => $enrollment->section->name, 'url' => route('teacher.grading-system.sections.show', $enrollment->section->id)],
        ['label' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name, 'url' => '#'],
    ]])

    <div class="gs-panel mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="gs-section-card-title mb-1">{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</p>
            <p class="gs-section-card-meta mb-0">{{ $enrollment->student->student_number }} · {{ $enrollment->section->name }}</p>
        </div>
        <div class="text-end">
            <p class="gs-filter-label mb-0">Scans Recorded</p>
            <p class="gs-stat-value mb-0">{{ $presentCount }}</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($subjects as $subject)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="gs-section-card">
                    <span class="gs-stat-icon gs-stat-icon-neutral mb-2">
                        <i class="fa-solid fa-book-open"></i>
                    </span>
                    <p class="gs-section-card-title">{{ $subject->subject_name }}</p>
                    <p class="gs-section-card-avg">{{ $subject->average !== null ? round($subject->average, 1) : '—' }}</p>
                    @if ($subject->periods->isNotEmpty())
                        <div class="mt-2">
                            @foreach ($subject->periods as $p)
                                <div class="d-flex justify-content-between gs-row-subtext py-1">
                                    <span>{{ $p->period_name }}</span>
                                    <span class="fw-bold text-dark">{{ $p->grade !== null ? $p->grade : '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="gs-row-subtext mb-0">No grades recorded yet.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No subjects found for this student.
                </div>
            </div>
        @endforelse
    </div>

</x-layouts.teacher>