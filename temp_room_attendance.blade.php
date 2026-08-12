<x-layouts.teacher>
    <x-slot name="pageName">
        Room Attendance
    </x-slot>

    <x-slot name="subtitle">
    </x-slot>

    <!-- Date Filter / History Bar -->
    <div class="ra-filter-bar mb-4">
        <form method="GET" action="{{ route('room-attendance.index') }}" class="d-flex flex-column flex-md-row align-items-md-end gap-3">

            <!-- Currently viewing chip -->
            <div class="d-flex align-items-center gap-2">
                <span class="ra-filter-chip">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <div>
                    <p class="ra-filter-label mb-0">Viewing</p>
                    <p class="ra-filter-date">
                        {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                        @if ($selectedDate === now()->toDateString())
                            <span class="ra-badge-today">(Today)</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="ra-filter-divider d-none d-md-block"></div>

            <!-- Date input -->
            <div class="flex-grow-1" style="max-width: 220px;">
                <label for="date" class="ra-filter-label d-block">Jump to date</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    max="{{ now()->toDateString() }}"
                    class="ra-filter-input w-100"
                >
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn-view-history">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    View history
                </button>

                @if ($selectedDate !== now()->toDateString())
                    <a href="{{ route('room-attendance.index') }}" class="btn-back-today">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        Back to today
                    </a>
                @endif
            </div>
        </form>
    </div>

    <x-ui.table>
        <thead>
            <tr>
                <th>Student No</th>
                <th>Student Name</th>
                <th>Grade &amp; Section</th>
                <th>Date</th>
                <th>Time</th>
                <th>Scan Type</th>
                <th>Session</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roomAttendance ?? [] as $log)
                @php
                    $student = $log->enrollment?->student;
                    $scanTypeDotMap = [
                        'IN' => 'success',
                        'OUT' => 'primary',
                        'RE_ENTRY' => 'info',
                        'RE_EXIT' => 'warning',
                    ];
                    $dotScanType = $scanTypeDotMap[$log->scan_type] ?? 'secondary';
                @endphp
                <tr>
                    <td>{{ $student->student_number ?? '—' }}</td>
                    <td>
                        {{ $student->first_name ?? '' }}
                        {{ $student->last_name ?? '' }}
                    </td>
                    <td>
                        Grade {{ $log->enrollment?->section?->grade_level ?? '—' }}
                        {{ $log->enrollment?->section?->name ?? '' }}
                    </td>
                    <td>{{ $log->scan_time?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $log->scan_time?->format('h:i A') ?? '—' }}</td>
                    <td>
                        <span class="badge-dot dot-{{ $dotScanType }}">{{ $log->scan_type }}</span>
                    </td>
                    <td>{{ ucfirst($log->session_type ?? '—') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No attendance records found for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}.
                    </td>
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