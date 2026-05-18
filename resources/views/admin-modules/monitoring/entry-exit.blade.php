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

    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 pt-5 mx-2 shadow-sm">

            <div class="row g-3 align-items-center">

                {{-- Search --}}
                <div class="col-12 col-md-6 col-lg-7">
                    <form action="#" method="GET">
                        <div class="input-group">
                            <input type="search" name="query" class="form-control"
                                placeholder="Search by student number or name..."
                                aria-label="Search by student number or name..." />
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Level Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select class="form-select" name="level">
                        <option value="" disabled selected>Level</option>
                        <option value="elementary">Elementary</option>
                        <option value="high_school">High School</option>
                        <option value="senior_high_school">Senior High School</option>
                    </select>
                </div>

                {{-- Scan Type Filter --}}
                <div class="col-6 col-md-3 col-lg-3">
                    <select class="form-select" name="scan_type">
                        <option value="" disabled selected>Scan Type</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                        <option value="RE_ENTRY">RE ENTRY</option>
                        <option value="RE_EXIT">RE EXIT</option>
                    </select>
                </div>

            </div>

        </div>
    </div>




    <x-ui.table>
        <x-slot>
            <thead class="text-uppercase">
                <tr>
                    <th style="width: 12%">Date </th>
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
    <div class="mx-3 mt-3 mb-3">
        {{ $attendance_logs->links() }}
    </div>



</x-layouts.admin>