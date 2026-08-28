<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Search and view students enrolled in your assigned classes. Click 'View Profile' to see their complete academic record.
    </x-slot>

    <form method="GET" action="{{ route('teacher.grading-system.student-profile') }}" class="gs-filter-bar mb-3">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="gs-filter-label">Search Student</label>
                <input type="text" name="q" value="{{ $query }}" class="form-control form-control-sm" placeholder="e.g. Ana Reyes or STU-2026-0008">
            </div>
        </div>
    </form>

    <div class="gs-panel">
        <div class="table-panel">
            <table class="table table-hover mb-0 gs-students-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>LRN</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Average</th>
                        <th>Overall Attendance</th>
                        <th>Trend</th>
                        <th>Risk</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($results as $r)
                        <tr>
                            <td>{{ $r->name }} <span class="gs-row-subtext">· {{ $r->student_number }}</span></td>
                            <td>{{ $r->lrn }}</td>
                            <td>{{ $r->grade_level }}</td>
                            <td>{{ $r->section_name }}</td>
                            <td>{{ $r->average !== null ? $r->average : '—' }}</td>
                            <td>{{ $r->attendance !== null ? $r->attendance . '%' : '—' }}</td>
                            <td>
                                @php
                                    $trendClass = [
                                        'Improving' => 'text-success', 
                                        'Declining' => 'text-danger', 
                                        'Stable' => 'text-warning',
                                        'N/A' => 'text-muted'
                                    ];
                                @endphp
                                <span class="{{ $trendClass[$r->trend] ?? 'text-muted' }}">{{ $r->trend }}</span>
                            </td>
                            <td>
                                @php
                                    $riskClass = $r->risk_level === 'High' ? 'gs-badge-danger' : 'gs-badge-warning';
                                @endphp
                                <span class="gs-badge {{ $riskClass }}">{{ $r->risk_level }}</span>
                            </td>
                            <td>
                                <a href="{{ route('teacher.grading-system.student-profile.show', $r->enrollment_id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-user"></i>
                                    <span>View Profile</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No students match your current search/filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.teacher>