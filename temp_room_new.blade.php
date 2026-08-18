<x-layouts.teacher>
    <x-slot name="pageName">
        Room Attendance
    </x-slot>

    <x-slot name="subtitle">
    </x-slot>

    @php
        $filters = [
            "today" => "Today",
            "yesterday" => "Yesterday",
            "week" => "This Week",
            "custom" => "Custom",
        ];
        $currentFilter = $dateFilter ?? request("date_filter", "today");
        $currentStatus = $statusFilter ?? request("status_filter", "present");
    @endphp

    <div class="ra-filter-bar mb-4">
        <form method="GET" action="{{ route("room-attendance.index") }}">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="ra-filter-chip">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <p class="ra-filter-label mb-0">Attendance history filter</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @foreach ($filters as $value => $label)
                    <input
                        type="radio"
                        class="btn-check"
                        name="date_filter"
                        id="date_{{ $value }}"
                        value="{{ $value }}"
                        @checked($currentFilter === $value)
                        onchange="this.form.submit()"
                    >
                    <label class="ra-pill-btn" for="date_{{ $value }}">
                        {{ $label }}
                    </label>
                @endforeach

                @if ($currentFilter === "custom")
                    <input
                        type="date"
                        name="custom_start_date"
                        class="ra-range-input"
                        value="{{ $customStartDate ?? request("custom_start_date") }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                    <span class="text-muted small">to</span>
                    <input
                        type="date"
                        name="custom_end_date"
                        class="ra-range-input"
                        value="{{ $customEndDate ?? request("custom_end_date") }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                    <button type="submit" class="btn-view-history ra-btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Apply
                    </button>
                @endif
            </div>

            @if ($sections->count() > 1)
                <div class="mb-2">
                    <select name="section_id" class="ra-range-input" onchange="this.form.submit()">
                        <option value="">All my sections</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected((string) $selectedSectionId === (string) $section->id)>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center gap-2">
                <input
                    type="radio"
                    class="btn-check"
                    name="status_filter"
                    id="status_present"
                    value="present"
                    @checked($currentStatus === "present")
                    onchange="this.form.submit()"
                >
                <label class="ra-pill-btn" for="status_present">
                    <i class="fa-solid fa-circle-check me-1" style="font-size: 0.75rem;"></i>
                    Present
                </label>

                <input
                    type="radio"
                    class="btn-check"
                    name="status_filter"
                    id="status_absent"
                    value="absent"
                    @checked($currentStatus === "absent")
                    onchange="this.form.submit()"
                >
                <label class="ra-pill-btn" for="status_absent">
                    <i class="fa-solid fa-circle-xmark me-1" style="font-size: 0.75rem;"></i>
                    Absent
                </label>
            </div>
        </form>
    </div>

    @if ($currentStatus === "absent")
        <x-ui.table>
            <thead>
                <tr>
                    <th>Student No</th>
                    <th>Student Name</th>
                    <th>Grade &amp; Section</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($absentEnrollments ?? [] as $enrollment)
                    <tr>
                        <td>{{ $enrollment->student->student_number ?? "—" }}</td>
                        <td>
                            {{ $enrollment->student->first_name ?? "" }}
                            {{ $enrollment->student->last_name ?? "" }}
                        </td>
                        <td>{{ $enrollment->section?->name ?? "—" }}</td>
                        <td>
                            <span class="badge-dot dot-warning">ABSENT</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            No absences found for the selected period — everyone scanned in.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if (isset($absentEnrollments) && method_exists($absentEnrollments, "hasPages") && $absentEnrollments->hasPages())
            <div class="mx-3 mt-3">
                {{ $absentEnrollments->links() }}
            </div>
        @endif
    @else
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
                            "IN" => "success",
                            "OUT" => "primary",
                            "RE_ENTRY" => "info",
                            "RE_EXIT" => "warning",
                        ];
                        $dotScanType = $scanTypeDotMap[$log->scan_type] ?? "secondary";
                    @endphp
                    <tr>
                        <td>{{ $student->student_number ?? "—" }}</td>
                        <td>
                            {{ $student->first_name ?? "" }}
                            {{ $student->last_name ?? "" }}
                        </td>
                        <td>
                            {{ $log->enrollment?->section?->name ?? "—" }}
                        </td>
                        <td>{{ $log->scan_time?->format("M d, Y") ?? "—" }}</td>
                        <td>{{ $log->scan_time?->format("h:i A") ?? "—" }}</td>
                        <td>
                            <span class="badge-dot dot-{{ $dotScanType }}">{{ $log->scan_type }}</span>
                        </td>
                        <td>{{ ucfirst($log->session_type ?? "—") }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No attendance records found for the selected {{ $currentFilter === "custom" ? "date range" : "period" }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if (isset($roomAttendance) && method_exists($roomAttendance, "hasPages") && $roomAttendance->hasPages())
            <div class="mx-3 mt-3">
                {{ $roomAttendance->links() }}
            </div>
        @endif
    @endif

</x-layouts.teacher>