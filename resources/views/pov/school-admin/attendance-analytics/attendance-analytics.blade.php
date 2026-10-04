<x-layouts.school-admin>
    <x-slot name="title">
        IN and OUT Analytics
    </x-slot>

    <x-slot name="pageName">
        IN and OUT Analytics
    </x-slot>

    <x-slot name="subtitle">
        School-wide analysis of student Time In, Time Out, Late Arrivals, and Missing Time Out patterns.
    </x-slot>

    @php
        $activeWindowLabel = match ($dateFilter ?? '') {
            'last_3_months' => 'Past 3 Months (Default)',
            'school_year' => 'Full School Year (' . ($activeSchoolYear->school_year ?? 'Current') . ')',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range (' . ($customStartDate ?? '') . ' to ' . ($customEndDate ?? '') . ')',
            default => 'Past 3 Months',
        };

        $defaultStartDate = request('custom_start_date') ?: match ($dateFilter ?? '') {
            'school_year' => now()->subMonths(12)->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'last_7_days' => now()->subDays(6)->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            default => now()->subMonths(3)->format('Y-m-d'),
        };
        $defaultEndDate = request('custom_end_date') ?: now()->format('Y-m-d');
    @endphp

    {{-- Top Action & Analytical Context Bar --}}
    <div class="mx-3 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-xs" style="font-size: 0.76rem;">
                <i class="fa-solid fa-calendar-days text-primary me-1.5"></i>
                <span class="text-muted">Analytical Window:</span> 
                <strong class="ms-1">{{ $activeWindowLabel }}</strong>
            </span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="fa-solid fa-school me-1"></i> School-wide
            </span>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1 shadow-sm" style="font-size: 0.78rem; font-weight: 500;" data-bs-toggle="modal" data-bs-target="#downloadAnalyticsModal">
                <i class="fas fa-file-arrow-down me-1"></i> Export Analytics Report
            </button>
        </div>
    </div>

    {{-- Analytics Dashboard Section (Instant Rendering) --}}
    <div class="mx-3 mb-4">
        @include('pov.school-admin.attendance-analytics.partials.attendance-analytics')
    </div>

    {{-- Download Report Modal --}}
    <div class="modal fade" id="downloadAnalyticsModal" tabindex="-1" aria-labelledby="downloadAnalyticsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="GET" action="{{ route('school_admin.time-in-time-out-history.analytics-pdf') }}">
                    <input type="hidden" name="date_filter" value="{{ $dateFilter ?? 'last_3_months' }}">

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadAnalyticsModalLabel">
                            <i class="fas fa-file-pdf text-danger me-2"></i> Export IN and OUT Analytics Report
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-circle-info me-1"></i>
                            Exporting comprehensive school-wide IN/OUT analytics report.
                        </div>

                        <p class="text-muted small mb-3">Select the date window for the PDF export:</p>

                        <div class="row g-3">
                            <div class="col-6">
                                <label for="analytics_start_date" class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_start_date"
                                    name="start_date" value="{{ $defaultStartDate }}" required>
                            </div>

                            <div class="col-6">
                                <label for="analytics_end_date" class="form-label text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">End Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_end_date"
                                    name="end_date" value="{{ $defaultEndDate }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm text-white">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.school-admin>