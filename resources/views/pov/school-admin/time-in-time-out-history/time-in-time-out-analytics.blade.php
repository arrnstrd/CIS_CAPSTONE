<x-layouts.school-admin>
    <x-slot name="title">
        Attendance Analytics
    </x-slot>

    <x-slot name="pageName">
        Student Attendance Analytics
    </x-slot>

    <x-slot name="subtitle">
        Comprehensive student movement and gate attendance insights.
    </x-slot>

    @php
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $queryValue = $query ?? request('query', '');
        $scanTypeValue = $scan_type ?? request('scan_type', 'all');
        $sessionTypeValue = $session_type ?? request('session_type', 'all');
        $flagTypeValue = $flag_type ?? request('flag_type', 'all');
        $filters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        // Determine default modal start and end dates based on active filter
        $defaultStartDate = request('custom_start_date') ?: match ($currentFilter) {
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'last_7_days' => now()->subDays(6)->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };
        $defaultEndDate = request('custom_end_date') ?: match ($currentFilter) {
            'yesterday' => now()->subDay()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };

        $historyTabs = [
            ['label' => 'Scan History Logs', 'href' => route('school_admin.time-in-time-out-history.index', request()->query()), 'icon' => 'fas fa-list-ul'],
            ['label' => 'Attendance Analytics', 'href' => route('school_admin.time-in-time-out-history.analytics', request()->query()), 'active' => true, 'icon' => 'fas fa-chart-pie'],
        ];
    @endphp

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-danger mx-3 mb-3" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Navigation Tabs --}}
    <x-layouts.school-admin.nav-tabs :tabs="$historyTabs" />

    {{-- Filter Form --}}
    <div class="card border mx-3 mb-3">
        <div class="card-body p-3">
            <form action="{{ route('school_admin.time-in-time-out-history.analytics') }}" method="GET">

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="search" name="query" class="form-control" placeholder="Student number or name"
                                value="{{ $queryValue }}">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Scan type</label>
                        <select class="form-select form-select-sm" name="scan_type" onchange="this.form.submit()">
                            <option value="all" @selected($scanTypeValue === 'all')>All</option>
                            <option value="IN" @selected($scanTypeValue === 'IN')>Time In</option>
                            <option value="OUT" @selected($scanTypeValue === 'OUT')>Time Out</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Session Type</label>
                        <select class="form-select form-select-sm" name="session_type" onchange="this.form.submit()">
                            <option value="all" @selected($sessionTypeValue === 'all')>All</option>
                            <option value="morning" @selected($sessionTypeValue === 'morning')>Morning</option>
                            <option value="afternoon" @selected($sessionTypeValue === 'afternoon')>Afternoon</option>
                            <option value="whole_day" @selected($sessionTypeValue === 'whole_day')>Whole Day</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Remarks</label>
                        <select class="form-select form-select-sm" name="flag_type" onchange="this.form.submit()">
                            <option value="all" @selected($flagTypeValue === 'all')>All</option>
                            <option value="late_arrival" @selected($flagTypeValue === 'late_arrival')>Late arrival
                            </option>
                            <option value="invalid_checkout" @selected($flagTypeValue === 'invalid_checkout')>Checkout
                                issue</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-auto">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Date Filter">
                            @foreach($filters as $value => $label)
                                <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                    value="{{ $value }}" @checked($currentFilter === $value) onchange="this.form.submit()">

                                <label class="btn btn-outline-primary btn-sm rounded px-3 me-1" for="date_{{ $value }}">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if ($currentFilter === 'custom')
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <input type="date" name="custom_start_date" class="form-control form-control-sm"
                                value="{{ request('custom_start_date') }}" required />

                            <input type="date" name="custom_end_date" class="form-control form-control-sm"
                                value="{{ request('custom_end_date') }}" required />

                            <button type="submit" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-funnel"></i> Apply
                            </button>
                        </div>
                    @endif

                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                        <a href="{{ route('school_admin.time-in-time-out-history.analytics') }}"
                            class="btn btn-outline-secondary btn-sm">
                            Reset
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#downloadAnalyticsModal">
                            <i class="fas fa-file-arrow-down me-1"></i> Download Report
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Analytics Dashboard Section --}}
    <div class="mx-3 mb-4">
        @include('pov.school-admin.time-in-time-out-history.partials.attendance-analytics')
    </div>

    {{-- Download Report Modal Asking for Date Range --}}
    <div class="modal fade" id="downloadAnalyticsModal" tabindex="-1" aria-labelledby="downloadAnalyticsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="GET" action="{{ route('school_admin.time-in-time-out-history.analytics-pdf') }}">
                    {{-- Active filter preservation --}}
                    @foreach (request()->except(['date_filter', 'custom_start_date', 'custom_end_date', 'page', 'start_date', 'end_date']) as $field => $value)
                        <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                    @endforeach

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadAnalyticsModalLabel">
                            <i class="fas fa-calendar-range text-primary me-2"></i> Select Report Date Range
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Choose the specific date range for your attendance analysis
                            report:</p>

                        <div class="row g-3">
                            <div class="col-6">
                                <label for="analytics_start_date"
                                    class="form-label text-muted small fw-bold text-uppercase">Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_start_date"
                                    name="start_date" value="{{ $defaultStartDate }}" required>
                            </div>

                            <div class="col-6">
                                <label for="analytics_end_date"
                                    class="form-label text-muted small fw-bold text-uppercase">End Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_end_date"
                                    name="end_date" value="{{ $defaultEndDate }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                        <div class="d-flex gap-2">
                            <button type="submit"
                                formaction="{{ route('school_admin.time-in-time-out-history.download') }}"
                                class="btn btn-dark btn-sm">
                                <i class="fas fa-file-excel text-success me-1"></i> Excel Data
                            </button>
                            <button type="submit"
                                formaction="{{ route('school_admin.time-in-time-out-history.analytics-pdf') }}"
                                class="btn btn-danger btn-sm text-white">
                                <i class="fas fa-file-pdf me-1"></i> Executive PDF Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.school-admin>