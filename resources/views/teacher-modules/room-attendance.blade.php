<x-layouts.teacher>
    <x-slot name="pageName">
        Room Attendance
    </x-slot>

    <x-slot name="subtitle">

    </x-slot>

    <div class="card border mx-3 mb-3">
        <div class="card-body p-4">
            <form action="{{ route('room-attendance.index') }}" method="GET">

                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                        <div class="input-group">
                            <input type="search" name="query" class="form-control" placeholder="Student number or name">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="form-label text-muted text-uppercase small fw-bold">Classroom</label>
                        <select class="form-select" name="section_id" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            <option value="1">Grade 8 - Rizal</option>
                            <option value="2">Grade 8 - Bonifacio</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Session Type</label>
                        <select class="form-select" name="session_type" onchange="this.form.submit()">
                            <option value="all">All</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                        </select>
                    </div>
                </div>

                {{-- ROW 2: Date filter (left) | Custom range (expands inline) | Reset + Download (right, fixed)
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
                        $currentFilter = $dateFilter ?? request('date_filter', 'today');
                    @endphp

                    {{-- Date Filter Pills --}}
                    <div class="col-12 col-md-auto">
                        <div class="btn-group" role="group" aria-label="Date Filter">
                            @foreach($filters as $value => $label)
                                <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                    value="{{ $value }}" @checked($currentFilter === $value) onchange="this.form.submit()">

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

                        <a href="{{ route('room-attendance.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#">
                            <i class="fas fa-download"></i> Download Excel
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>


    <x-ui.table>
        <thead>
                <tr>
                    <th>Student No</th>
                    <th>Student Name </th>
                    <th>Grade & Section</th>
                    <th>Date </th>
                    <th>Time</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody>
                <td> </td>
            </tbody>

    </x-ui.table>

</x-layouts.teacher>