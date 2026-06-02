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

    @php
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $queryValue = $query ?? request('query', '');
        $scanTypeValue = $scan_type ?? request('scan_type', 'all');
        $sessionTypeValue = $session_type ?? request('session_type', 'all');
        $flagTypeValue = $flag_type ?? request('flag_type', 'all');

        $filterOptions = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];


        $overviewCards = [
            ['label' => 'Total logs', 'value' => $statusCounts['TOTAL'] ?? 0, 'icon' => 'fa-solid fa-clipboard-list', 'variant' => 'dark'],
            ['label' => 'Entry scans', 'value' => $statusCounts['IN'] ?? 0, 'icon' => 'fa-solid fa-right-to-bracket', 'variant' => 'success'],
            ['label' => 'Exit scans', 'value' => $statusCounts['OUT'] ?? 0, 'icon' => 'fa-solid fa-right-from-bracket', 'variant' => 'primary'],
            ['label' => 'Re-entry', 'value' => $statusCounts['RE_ENTRY'] ?? 0, 'icon' => 'fa-solid fa-rotate-right', 'variant' => 'info'],
            ['label' => 'Re-exit', 'value' => $statusCounts['RE_EXIT'] ?? 0, 'icon' => 'fa-solid fa-rotate-left', 'variant' => 'warning'],
            ['label' => 'Flagged scans', 'value' => $statusCounts['FLAGGED'] ?? 0, 'icon' => 'fa-solid fa-triangle-exclamation', 'variant' => 'danger'],
        ];
    @endphp

    <div class="row g-3 mb-4 px-3">
        @foreach ($overviewCards as $card)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-{{ $card['variant'] }}  text-light  d-flex align-items-center justify-content-center"
                            style="width: 3rem; height: 3rem; flex-shrink: 0;">
                            <i class="{{ $card['icon'] }} fs-4"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-bold">{{ $card['label'] }}</div>
                            <div class="fs-3 fw-bold lh-1">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <div class="card border mx-3 mb-3">
        <div class="card-body p-4">
            <form action="{{ route('attendance.index') }}" method="GET">

                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                        <div class="input-group">
                            <input type="search" name="query" class="form-control" placeholder="Student number or name"
                                value="{{ $queryValue }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Scan type</label>
                        <select class="form-select" name="scan_type" onchange="this.form.submit()">
                            <option value="all" @selected($scanTypeValue === 'all')>All</option>
                            <option value="IN" @selected($scanTypeValue === 'IN')>IN</option>
                            <option value="OUT" @selected($scanTypeValue === 'OUT')>OUT</option>
                            <option value="RE_ENTRY" @selected($scanTypeValue === 'RE_ENTRY')>Re-entry</option>
                            <option value="RE_EXIT" @selected($scanTypeValue === 'RE_EXIT')>Re-exit</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Session Type</label>
                        <select class="form-select" name="session_type" onchange="this.form.submit()">
                            <option value="all" @selected($sessionTypeValue === 'all')>All</option>
                            <option value="morning" @selected($sessionTypeValue === 'morning')>Morning</option>
                            <option value="afternoon" @selected($sessionTypeValue === 'afternoon')>Afternoon</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Flag type</label>
                        <select class="form-select" name="flag_type" onchange="this.form.submit()">
                            <option value="all" @selected($flagTypeValue === 'all')>All</option>
                            <option value="late_arrival" @selected($flagTypeValue === 'late_arrival')>Late arrival
                            </option>
                            <option value="invalid_checkout" @selected($flagTypeValue === 'invalid_checkout')>Invalid
                                checkout</option>
                        </select>
                    </div>
                </div>

               {{-- ROW 2: Date filter (left) | Custom range (expands inline) | Help + Resend All (right, fixed)
                    --}}
                    <div class="row g-2 align-items-center">

                        {{-- Configuration --}}
                        @php
                            $filters = [
                                'today' => 'Today',
                                'week' => 'This Week',
                                'month' => 'This Month',
                                'custom' => 'Custom'
                            ];
                            $currentFilter = request('date_filter', 'today');
                        @endphp

                        {{-- Date Filter Pills --}}
                        <div class="col-12 col-md-auto">
                            <div class="btn-group" role="group" aria-label="Date Filter">
                                @foreach($filters as $value => $label)
                                    <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                        value="{{ $value }}" @checked($currentFilter === $value)
                                        onchange="this.form.submit()">

                                    <label class="btn btn-outline-primary nav-pill rounded px-3 me-2" for="date_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Custom Range Fields --}}
                        @if ($currentFilter === 'custom')
                            <div class="col-12 col-md-auto d-flex gap-2 animate__animated animate__fadeIn">
                                <input type="date" name="custom_start_date" class="form-control nav-pill"
                                    value="{{ request('custom_start_date') }}" required />

                                <input type="date" name="custom_end_date" class="form-control nav-pill"
                                    value="{{ request('custom_end_date') }}" required />

                                <button type="submit" class="btn btn-success nav-pill px-3">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>
                            </div>
                        @endif

                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">

                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                           <button type="button" class="btn btn-dark" data-bs-toggle="modal"
                                data-bs-target="#">
                                <i class="fas fa-download"></i> Download Excel
                            </button>
                    </div>
                </div>

            </form>
        </div>
    </div>


    <x-ui.table>
        <thead class=" text-uppercase small">
            <tr>
                <th>Date</th>
                <th>Student No.</th>
                <th>Student Name</th>
                <th>Grade</th>
                <th>Section</th>
                <th>Scan Type</th>
                <th>Session</th>
                <th>Gate Time</th>
                <th>Flag Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendance_logs as $attendance_log)
                @php
                    $student = $attendance_log->enrollment?->student;
                    $flagTypes = $attendance_log->flagged_scans->pluck('flag_type')->filter()->unique()->join(', ');
                @endphp
                <tr>
                    <td>{{ $attendance_log->scan_time?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $student?->student_number ?? '-' }}</td>
                    <td>{{ trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '-' }}</td>
                    <td>{{ $attendance_log->enrollment?->grade_level ?? '-' }}</td>
                    <td>{{ $attendance_log->enrollment?->section ?? '-' }}</td>
                    <td><span class="badge bg-light text-dark">{{ $attendance_log->scan_type }}</span></td>
                    <td>{{ $attendance_log->session_type ?? '-' }}</td>
                    <td>{{ $attendance_log->scan_time?->format('h:i A') ?? '-' }}</td>
                    <td>{{ $flagTypes !== '' ? $flagTypes : '-' }}</td>
                </tr>
            @empty
                <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-history fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0">No gate scan logs found for the selected criteria</p>
                            </div>
                        </td>
                    </tr>
            @endforelse
        </tbody>
    </x-ui.table>

    <div class="d-flex justify-content-end mx-3 mt-3 mb-3">
        {{ $attendance_logs->links() }}
    </div>

</x-layouts.admin>