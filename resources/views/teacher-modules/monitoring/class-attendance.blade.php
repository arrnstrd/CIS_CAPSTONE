<x-layouts.teacher>
    <x-slot name="title">
        Class Attendance Monitoring
    </x-slot>

    <x-slot name="pageName">
        Class Attendance
    </x-slot>

    <x-slot name="subtitle">
        Monitor student attendance for your classes
    </x-slot>

    @php
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $queryValue = $query ?? request('query', '');
        $teachingAssignmentId = $teaching_assignment_id ?? request('teaching_assignment_id', '');
        $sessionType = $session_type ?? request('session_type', 'all');

        $filterOptions = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        $overviewCards = [
            ['label' => 'Total Students', 'value' => $totalStudents ?? 0, 'icon' => 'fa-solid fa-users', 'variant' => 'primary', 'textVariant' => 'primary'],
            ['label' => 'Present', 'value' => $presentCount ?? 0, 'icon' => 'fa-solid fa-check-circle', 'variant' => 'success', 'textVariant' => 'success'],
            ['label' => 'Late', 'value' => $lateCount ?? 0, 'icon' => 'fa-solid fa-clock', 'variant' => 'warning', 'textVariant' => 'warning'],
            ['label' => 'Absent', 'value' => $absentCount ?? 0, 'icon' => 'fa-solid fa-times-circle', 'variant' => 'danger', 'textVariant' => 'danger'],
        ];
    @endphp

    <div class="row g-3 mb-4 px-3">
        @foreach ($overviewCards as $card)
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card border h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-{{ $card['variant'] }} text-light d-flex align-items-center justify-content-center"
                            style="width: 3rem; height: 3rem; flex-shrink: 0;">
                            <i class="{{ $card['icon'] }} fs-4"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-bold">{{ $card['label'] }}</div>
                            <div class="fs-3 fw-bold lh-1 text-{{ $card['textVariant'] }}">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filter Section -->
    <div class="card border mx-3 mb-3">
        <div class="card-body p-4">
            <form action="{{ route('teacher.attendance') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label text-muted text-uppercase small fw-bold">Teaching Assignment</label>
                        <select name="teaching_assignment_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Select Assignment</option>
                            @foreach ($teachingAssignments ?? [] as $assignment)
                                <option value="{{ $assignment->id }}" @selected($teachingAssignmentId == $assignment->id)>
                                    {{ $assignment->subject->name }} - {{ $assignment->section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label text-muted text-uppercase small fw-bold">Session Type</label>
                        <select name="session_type" class="form-select" onchange="this.form.submit()">
                            <option value="all" @selected($sessionType === 'all')">All</option>
                            <option value="morning" @selected($sessionType === 'morning')">Morning</option>
                            <option value="afternoon" @selected($sessionType === 'afternoon')">Afternoon</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label class="form-label text-muted text-uppercase small fw-bold">Date Filter</label>
                        <div class="btn-group w-100" role="group">
                            @foreach($filterOptions as $value => $label)
                                <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                    value="{{ $value }}" @checked($currentFilter === $value) onchange="this.form.submit()">
                                <label class="btn btn-outline-primary btn-sm" for="date_{{ $value }}">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 col-lg-2">
                        <a href="{{ route('teacher.attendance') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </div>

                <!-- Custom Date Range (shown only when Custom is selected) -->
                @if ($currentFilter === 'custom')
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <input type="date" name="custom_start_date" class="form-control"
                                value="{{ request('custom_start_date') }}" required />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="custom_end_date" class="form-control"
                                value="{{ request('custom_end_date') }}" required />
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-funnel"></i> Apply
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <x-ui.table>
        <thead class="text-uppercase small">
            <tr>
                <th style="width: 15%">
                    <span class="fas fa-calendar me-1"></span> Date
                </th>
                <th style="width: 20%">
                    <span class="fas fa-user me-1"></span> Student Name
                </th>
                <th style="width: 15%">
                    <span class="fas fa-chart-simple me-1"></span> Grade/Section
                </th>
                <th style="width: 15%">
                    <span class="fas fa-clock me-1"></span> Time In
                </th>
                <th style="width: 15%">
                    <span class="fas fa-sign-out-alt me-1"></span> Time Out
                </th>
                <th style="width: 10%">
                    <span class="fas fa-flag me-1"></span> Status
                </th>
                <th style="width: 10%">
                    <span class="fas fa-exclamation-triangle me-1"></span> Remarks
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendanceRecords ?? [] as $record)
                @php
                    $student = $record->enrollment?->student;
                    $statusBadge = $record->remarks === 'present' 
                        ? 'success' 
                        : ($record->remarks === 'late' ? 'warning' : 'danger');
                @endphp
                <tr>
                    <td>{{ $record->scan_time?->format('Y-m-d') ?? '-' }}</td>
                    <td class="fw-semibold">
                        {{ trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '-' }}
                    </td>
                    <td>
                        Grade {{ $record->enrollment?->section?->grade_level ?? '-' }} / 
                        {{ $record->enrollment?->section?->name ?? '-' }}
                    </td>
                    <td>{{ $record->scan_time?->format('h:i A') ?? '-' }}</td>
                    <td>{{ $record->time_out?->format('h:i A') ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $statusBadge }}">{{ $record->remarks ?? '-' }}</span>
                    </td>
                    <td>{{ $record->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                            <p class="mb-0">No attendance records found for the selected criteria</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>

    <!-- Pagination -->
    @if ($attendanceRecords ?? [])
        <div class="px-3 py-3">
            {{ $attendanceRecords->links() }}
        </div>
    @endif

</x-layouts.teacher>