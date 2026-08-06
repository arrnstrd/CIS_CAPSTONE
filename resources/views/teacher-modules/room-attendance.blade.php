<x-layouts.teacher>
    <x-slot name="pageName">
        Room Attendance
    </x-slot>

    <x-slot name="subtitle">
    </x-slot>

    <x-ui.table>
        <thead>
            <tr>
                <th>Student No</th>
                <th>Student Name</th>
                <th>Grade &amp; Section</th>
                <th>Date</th>
                <th>Time</th>
                <th>Session</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roomAttendance ?? [] as $attendance)
                <tr>
                    <td>{{ $attendance->enrollment->student->student_number ?? '—' }}</td>
                    <td>
                        {{ $attendance->enrollment->student->first_name ?? '' }} 
                        {{ $attendance->enrollment->student->last_name ?? '' }}
                    </td>
                    <td>
                        {{ $attendance->enrollment->section->name ?? $attendance->enrollment->section ?? '—' }}
                    </td>
                    <td>
                        {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('M d, Y') : '—' }}
                    </td>
                    <td>
                        {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : '—' }}
                        @if ($attendance->time_out)
                            – {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') }}
                        @endif
                    </td>
                    <td>{{ ucfirst($attendance->teachingAssignment->session_type ?? '—') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>

    @if (isset($roomAttendance) && method_exists($roomAttendance, 'hasPages') && $roomAttendance->hasPages())
        <div class="mx-3 mt-3">
            {{ $roomAttendance->links() }}
        </div>
    @endif

</x-layouts.teacher>