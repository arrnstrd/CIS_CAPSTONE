<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Students enrolled in this section.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'Grade Levels', 'url' => route('teacher.grading-system.grades')],
        ['label' => 'Grade ' . $section->grade_level, 'url' => route('teacher.grading-system.grades.show', $section->grade_level)],
        ['label' => $section->name, 'url' => '#'],
    ]])

    <div class="gs-panel mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="gs-section-card-title mb-1">{{ $section->name }}</p>
            <p class="gs-section-card-meta mb-0">{{ $students->count() }} students</p>
        </div>
        <div class="text-end">
            <p class="gs-filter-label mb-0">Section Average</p>
            <p class="gs-stat-value mb-0">{{ $sectionAvg !== null ? $sectionAvg : '—' }}</p>
        </div>
    </div>

    <div class="gs-panel">
        <p class="gs-panel-title">Students — click a name for grade details</p>
        <div class="table-panel">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student No.</th>
                        <th>Average</th>
                        <th>Scans Recorded</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $s)
                        <tr>
                            <td>
                                <a href="{{ route('teacher.grading-system.students.show', $s->enrollment_id) }}" class="text-decoration-none">
                                    {{ $s->name }}
                                </a>
                            </td>
                            <td>{{ $s->student_number }}</td>
                            <td>{{ $s->avg_grade !== null ? $s->avg_grade : '—' }}</td>
                            <td>{{ $s->present_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No active students enrolled in this section.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.teacher>