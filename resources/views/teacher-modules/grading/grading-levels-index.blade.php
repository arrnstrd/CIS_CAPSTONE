<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Select a grade level to browse sections, students, then view per-subject grades.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    <form method="GET" action="{{ route('teacher.grading-system.grades') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">School Year</label>
                <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(!$selectedSchoolYearId)>All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedSchoolYearId == $sy->id)>{{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="row g-3">
        @forelse ($gradeLevels as $gl)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('teacher.grading-system.grades.show', $gl->grade_level) }}" class="gs-grade-card-link">
                    <div class="gs-grade-card">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <span class="gs-stat-icon gs-stat-icon-neutral">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </span>
                            <i class="fa-solid fa-chevron-right gs-card-arrow"></i>
                        </div>
                        <p class="gs-grade-card-title">Grade {{ $gl->grade_level }}</p>
                        <p class="gs-grade-card-meta">{{ $gl->total_students }} students · {{ $gl->section_count }} {{ Str::plural('section', $gl->section_count) }}</p>
                        <p class="gs-grade-card-avg">{{ $gl->avg_grade !== null ? $gl->avg_grade : '—' }}</p>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No active teaching assignments found.
                </div>
            </div>
        @endforelse
    </div>

</x-layouts.teacher>