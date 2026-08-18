<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Search for a student to view their profile.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'studentprofile'])

    <form method="GET" action="{{ route('teacher.grading-system.student-profile') }}" class="gs-filter-bar mb-3">
        <label class="gs-filter-label">Search Student by Name or ID</label>
        <div class="d-flex gap-2">
            <input type="text" name="q" value="{{ $query }}" class="form-control form-control-sm" placeholder="e.g. Ana Reyes or STU-2026-0008" autofocus>
            <button type="submit" class="btn-view-history">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    @if ($query === '')
        <div class="gs-panel text-center text-muted py-5">
            <i class="fa-solid fa-user-magnifying-glass mb-2" style="font-size: 1.6rem; color: #c5c5c5; display: block;"></i>
            Enter a student name or ID above to view their profile.
        </div>
    @else
        <div class="gs-panel">
            <p class="gs-panel-title">Search Results</p>
            <div class="table-panel">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>ID</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Subjects</th>
                            <th>Avg Grade</th>
                            <th>Scans</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($results as $r)
                            <tr>
                                <td>
                                    <a href="{{ route('teacher.grading-system.student-profile.show', $r->enrollment_id) }}" class="text-decoration-none">
                                        {{ $r->name }}
                                    </a>
                                </td>
                                <td>{{ $r->student_number }}</td>
                                <td>{{ $r->grade_level }}</td>
                                <td>{{ $r->section_name }}</td>
                                <td class="gs-row-subtext">{{ $r->subjects }}</td>
                                <td>{{ $r->avg_grade !== null ? $r->avg_grade : '—' }}</td>
                                <td>{{ $r->present_count }}</td>
                                <td>
                                    @if ($r->risk_level)
                                        @php
                                            $riskClass = ['Low' => 'gs-badge-success', 'Moderate' => 'gs-badge-warning', 'High' => 'gs-badge-danger'][$r->risk_level];
                                        @endphp
                                        <span class="gs-badge {{ $riskClass }}">{{ $r->risk_level }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No students found matching "{{ $query }}".</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.teacher>