<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Directory of students enrolled in your assigned classes.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'students'])

    <form method="GET" action="{{ route('teacher.grading-system.student-profile') }}" class="gs-filter-bar mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="gs-filter-label">Search Student</label>
                <input type="text" name="q" value="{{ $query }}" class="form-control form-control-sm" placeholder="e.g. Ana Reyes or STU-2026-0008">
            </div>
            <div class="col-md-2">
                <label class="gs-filter-label">Classes</label>
                <select name="class" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" {{ $classFilter == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="gs-filter-label">Grade Level</label>
                <select name="grade_level" class="form-select form-select-sm">
                    <option value="">All Grades</option>
                    @foreach ($gradeLevels as $gl)
                        <option value="{{ $gl }}" {{ $gradeLevelFilter == $gl ? 'selected' : '' }}>
                            Grade {{ $gl }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="gs-filter-label">Subject</label>
                <select name="subject" class="form-select form-select-sm">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $s)
                        <option value="{{ $s }}" {{ $subjectFilter == $s ? 'selected' : '' }}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="gs-filter-label">Term</label>
                <select name="term" class="form-select form-select-sm">
                    <option value="">All Terms</option>
                    <option value="1" {{ $termFilter == '1' ? 'selected' : '' }}>Term 1</option>
                    <option value="2" {{ $termFilter == '2' ? 'selected' : '' }}>Term 2</option>
                    <option value="3" {{ $termFilter == '3' ? 'selected' : '' }}>Term 3</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="gs-filter-label">&nbsp;</label>
                <button type="submit" class="btn-view-history d-block w-100">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="gs-panel">
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
                        <tr><td colspan="8" class="text-center text-muted py-4">No students match your current search/filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.teacher>