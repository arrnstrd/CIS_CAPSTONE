<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Browse sections in this grade level.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'Grade Levels', 'url' => route('teacher.grading-system.grades')],
        ['label' => 'Grade ' . $gradeLevel, 'url' => '#'],
    ]])

    <div class="row g-3">
        @forelse ($sections as $s)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="gs-section-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="gs-stat-icon gs-stat-icon-neutral">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </span>
                        <span class="gs-grade-pill">Grade {{ $gradeLevel }}</span>
                    </div>
                    <p class="gs-section-card-title">{{ $s->section_name }}</p>
                    <p class="gs-section-card-meta">{{ $s->total_students }} students</p>
                    <p class="gs-section-card-avg">{{ $s->avg_grade !== null ? $s->avg_grade : '—' }}</p>
                    <a href="{{ route('teacher.grading-system.sections.show', $s->section_id) }}" class="btn-view-history w-100 justify-content-center text-decoration-none mt-2">
                        View Students
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No sections found for this grade level.
                </div>
            </div>
        @endforelse
    </div>

</x-layouts.teacher>