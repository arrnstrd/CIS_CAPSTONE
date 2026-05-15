<x-layouts.admin>

    <x-slot name="title">
        Entry/Exit Monitoring
    </x-slot>
    <x-slot name="pageName">
        Student Entry and Exit Monitoring
    </x-slot>

    <x-slot name="subtitle">
        School entry/exit gate scans.
    </x-slot>

    <div class="row mb-3 mx-2">
        <div class="d-flex justify-content-center align-items center gap-3">
            <x-card title="entry" value="0" icon="fa-solid fa-right-to-bracket" variants="success" />
            <x-card title="exit" value="0" icon="fa-solid fa-door-open" variants="primary" />
            <x-card title="late" value="0" icon="fa-solid fa-hourglass-half" variants="warning" />
        </div>
    </div>




    <x-ui.table>
        <x-slot>
            <thead class="text-uppercase">
                <tr>
                    <th>Date </th>
                    <th>Student No.</th>
                    <th>Student Name</th>
                    <th>Scan Type</th>
                    <th>Session</th>
                    <th>Time</th>
                </tr>
            </thead>

            <tbody>
                @forelse($attendance_logs as $attendance_log)
                    <tr>
                        <td>
                            {{ $attendance_log->scan_time->format('Y-m-d') }}
                        </td>

                        <td>
                            {{ $attendance_log->enrollment->student->student_number ?? '-' }}
                        </td>

                        <td>
                            {{ $attendance_log->enrollment->student->first_name ?? '' }}
                            {{ $attendance_log->enrollment->student->last_name ?? '' }}
                        </td>

                        <td>{{ $attendance_log->scan_type }}</td>

                        <td>{{ $attendance_log->session_type }}</td>

                        <td>
                            {{ $attendance_log->scan_time->format('h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No logs yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-slot>
    </x-ui.table>

    {{-- pagination --}}
    <div class="mx-3">
        {{ $attendance_logs->links() }}
    </div>



</x-layouts.admin>