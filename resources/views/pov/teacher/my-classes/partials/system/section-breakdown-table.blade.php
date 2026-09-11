<div class="gs-panel">
    <p class="gs-panel-title">Section Breakdown</p>
    <div class="table-panel">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Students</th>
                    <th>Avg Grade</th>
                    <th>Passing Rate</th>
                    <th>Attendance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sectionBreakdown as $row)
                    <tr>
                        <td>
                            <div>{{ $row->section_name }}</div>
                            <div class="gs-grade-subtext">Grade {{ $row->grade_level }}</div>
                        </td>
                        <td>{{ $row->subject_name }}</td>
                        <td>{{ $row->total_students }}</td>
                        <td>{{ $row->avg_grade !== null ? $row->avg_grade : '—' }}</td>
                        <td>{{ $row->passing_rate !== null ? $row->passing_rate.'%' : '—' }}</td>
                        <td>
                            @php
                                $rate = $row->attendance_rate;
                                $badgeClass = $rate >= 90 ? 'gs-badge-success' : ($rate >= 50 ? 'gs-badge-warning' : 'gs-badge-danger');
                            @endphp
                            <span class="gs-badge {{ $badgeClass }}">{{ $rate }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No active teaching assignments found for this school year.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
