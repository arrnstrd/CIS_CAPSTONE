{{-- Attendance Analytics Partial — Multi-Graph Clarity Workspace with Functional Directives
     Included from time-in-time-out-analytics.blade.php.
     Expects $analytics array from AttendanceLogController::buildAnalytics(). --}}

@php
    $a = $analytics;
@endphp

<style>
    /* ===== Microsoft Clarity / Multi-Graph Workspace ===== */
    .att-analytics { display: grid; gap: 1rem; }

    /* ── Metric Cards Grid ── */
    .att-kpi-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
        gap: 0.65rem;
    }
    .att-kpi {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.8rem 0.95rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .att-kpi__label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 700;
    }
    .att-kpi__val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .att-kpi__sub { font-size: 0.68rem; color: #64748b; }
    .att-kpi--success { border-left: 3.5px solid #10b981; }
    .att-kpi--warning { border-left: 3.5px solid #f59e0b; }
    .att-kpi--danger  { border-left: 3.5px solid #ef4444; }
    .att-kpi--info    { border-left: 3.5px solid #3b82f6; }

    /* ── Panel Card ── */
    .att-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.95rem 1.15rem;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .att-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .att-panel__title {
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .att-panel__meta { font-size: 0.7rem; color: #64748b; }

    /* ── Grid Layouts ── */
    .att-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }
    .att-grid-3 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 0.85rem;
    }
    @media (max-width: 900px) {
        .att-grid-2 { grid-template-columns: 1fr; }
    }

    /* ── 1. GRAPH TYPE: Donut / Ring Chart ── */
    .att-donut-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        padding: 0.5rem 0;
    }
    .att-donut-chart {
        width: 120px;
        height: 120px;
        position: relative;
        flex-shrink: 0;
    }
    .att-donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .att-donut-center__val { font-size: 1.25rem; font-weight: 900; color: #0f172a; line-height: 1; }
    .att-donut-center__lbl { font-size: 0.6rem; color: #64748b; font-weight: 700; text-transform: uppercase; }

    .att-donut-legend {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        flex-grow: 1;
    }
    .att-donut-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.73rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px dashed #f1f5f9;
    }
    .att-donut-item__name {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: #334155;
        font-weight: 600;
    }
    .att-donut-item__dot { width: 0.55rem; height: 0.55rem; border-radius: 50%; flex-shrink: 0; }
    .att-donut-item__num { font-weight: 800; color: #0f172a; }

    /* ── 2. GRAPH TYPE: Horizontal Bar Matrix ── */
    .att-hbar-list { display: flex; flex-direction: column; gap: 0.55rem; padding: 0.25rem 0; }
    .att-hbar-item { display: flex; flex-direction: column; gap: 0.2rem; }
    .att-hbar-item__head {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        font-weight: 700;
        color: #334155;
    }
    .att-hbar-item__track {
        height: 0.55rem;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        display: flex;
    }
    .att-hbar-item__fill { height: 100%; border-radius: 999px; transition: width 0.3s ease; }

    /* ── 3. GRAPH TYPE: Vertical Column Timeline ── */
    .att-vbar-wrap {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 120px;
        padding-top: 0.5rem;
        gap: 0.25rem;
    }
    .att-vbar-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        height: 100%;
        justify-content: flex-end;
        gap: 0.25rem;
    }
    .att-vbar-bar {
        width: 100%;
        max-width: 18px;
        background: #e2e8f0;
        border-radius: 3px 3px 0 0;
        transition: height 0.3s ease;
        position: relative;
    }
    .att-vbar-bar--peak { background: #3b82f6; }
    .att-vbar-bar--in   { background: #10b981; }
    .att-vbar-lbl { font-size: 0.58rem; color: #64748b; font-weight: 600; white-space: nowrap; }

    /* ── 4. Metric Pills (Row 1 Multi-Series Selector) ── */
    .att-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
    }
    .att-metric-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        border: 1.5px solid transparent;
        background: #f8fafc;
        color: #64748b;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .att-metric-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .att-metric-pill__dot {
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .att-metric-pill--in.active {
        background: #ecfdf5;
        border-color: #10b981;
        color: #065f46;
    }
    .att-metric-pill--in .att-metric-pill__dot { background: #10b981; }

    .att-metric-pill--out.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1e40af;
    }
    .att-metric-pill--out .att-metric-pill__dot { background: #3b82f6; }

    .att-metric-pill--late.active {
        background: #fffbeb;
        border-color: #f59e0b;
        color: #92400e;
    }
    .att-metric-pill--late .att-metric-pill__dot { background: #f59e0b; }

    .att-metric-pill--missing.active {
        background: #fef2f2;
        border-color: #ef4444;
        color: #991b1b;
    }
    .att-metric-pill--missing .att-metric-pill__dot { background: #ef4444; }

    .att-metric-pill:not(.active) {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #94a3b8;
        opacity: 0.6;
    }
    .att-metric-pill:not(.active) .att-metric-pill__dot {
        background: #cbd5e1 !important;
    }

    /* ── Date Filter Toolbar inside Chart Card ── */
    .att-chart-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.45rem;
        padding: 0.4rem 0.6rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.55rem;
        margin-bottom: 0.6rem;
    }

    /* ── Dynamic Chart Frame & Tooltip ── */
    .att-chart-dynamic-frame {
        position: relative;
        height: 195px;
        border-radius: 0.55rem;
        border: 1px solid #edf2f7;
        background: linear-gradient(180deg, #fbfcfe 0%, #ffffff 100%);
        padding: 0.25rem;
        overflow: hidden;
    }
    .att-chart-tooltip {
        position: absolute;
        display: none;
        z-index: 30;
        pointer-events: none;
        background: rgba(15, 23, 42, 0.96);
        color: #fff;
        border-radius: 0.45rem;
        padding: 0.45rem 0.65rem;
        font-size: 0.68rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        transform: translate(-50%, -115%);
        transition: opacity 0.08s ease;
        white-space: nowrap;
    }
    .att-chart-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: rgba(15, 23, 42, 0.96) transparent transparent transparent;
    }

    /* ── Section Visual Graph Elements ── */
    .att-section-visual-wrap {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        margin-bottom: 0.65rem;
    }
    .att-section-card-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.45rem 0.65rem;
        transition: all 0.15s ease;
    }
    .att-section-card-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .att-section-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .att-section-bar-track {
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        display: flex;
        margin-bottom: 0.25rem;
    }
    .att-section-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.3s ease;
    }
    .att-section-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.64rem;
        color: #64748b;
    }

    /* ── Section Container Fixed Height & Scroll ── */
    .att-panel--fixed-section {
        height: 355px;
        max-height: 355px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .att-section-scroll-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding-right: 0.25rem;
        min-height: 0;
    }
    .att-section-scroll-body::-webkit-scrollbar {
        width: 5px;
    }
    .att-section-scroll-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }
    .att-section-scroll-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .att-section-scroll-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* ── Row 2 Follow-Up Registries Fixed Height & Scroll ── */
    .att-panel--fixed-registry {
        height: 380px;
        max-height: 380px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .att-registry-scroll-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding-right: 0.25rem;
        min-height: 0;
    }
    .att-registry-scroll-body::-webkit-scrollbar {
        width: 5px;
    }
    .att-registry-scroll-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }
    .att-registry-scroll-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    .att-registry-scroll-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .att-in-card-nav {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 0.55rem;
        padding-bottom: 0.35rem;
    }
    .att-in-card-tab {
        background: none;
        border: none;
        padding: 0.25rem 0.55rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        border-radius: 0.35rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .att-in-card-tab.active {
        background: #eff6ff;
        color: #2563eb;
    }
    .att-in-card-tab:hover:not(.active) {
        background: #f8fafc;
        color: #334155;
    }

    /* ── Functional Action Directives ── */
    .att-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 0.65rem;
    }
    .att-action-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        padding: 0.8rem 0.95rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.5rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .att-action-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .att-action-card--danger  { border-left: 3.5px solid #ef4444; background: #fff5f5; }
    .att-action-card--warning { border-left: 3.5px solid #f59e0b; background: #fffbeb; }
    .att-action-card--info    { border-left: 3.5px solid #3b82f6; background: #eff6ff; }
    .att-action-card--success { border-left: 3.5px solid #10b981; background: #f0fdf4; }

    .att-action-card__top { display: flex; align-items: center; justify-content: space-between; }
    .att-action-card__cat { font-size: 0.62rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; color: #475569; }
    .att-action-card__title { font-size: 0.82rem; font-weight: 800; color: #0f172a; margin: 0.15rem 0; }
    .att-action-card__desc { font-size: 0.72rem; color: #334155; line-height: 1.35; margin: 0; }
    .att-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 700;
        border: none;
        background: none;
        padding: 0.2rem 0;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .att-action-btn--danger  { color: #dc2626; }
    .att-action-btn--warning { color: #d97706; }
    .att-action-btn--info    { color: #2563eb; }
    .att-action-btn--success { color: #059669; }
    .att-action-btn:hover { text-decoration: underline; }

    .att-badge { font-size: 0.62rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 999px; display: inline-block; }
    .att-badge--danger  { background: #fee2e2; color: #991b1b; }
    .att-badge--warning { background: #fef3c7; color: #92400e; }
    .att-badge--info    { background: #dbeafe; color: #1e40af; }
    .att-badge--success { background: #d1fae5; color: #065f46; }

    /* ── Tables & Show More Toggles ── */
    .att-table-wrap { border: 1px solid #edf2f7; border-radius: 0.55rem; overflow: hidden; }
    .att-table { width: 100%; font-size: 0.73rem; border-collapse: collapse; }
    .att-table th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 0.6rem; font-weight: 700; padding: 0.45rem 0.6rem; border-bottom: 1px solid #e2e8f0; }
    .att-table td { padding: 0.45rem 0.6rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .att-table tr.att-clickable-row:hover { background: #f1f5f9; cursor: pointer; }
    .att-toggle-btn { display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; color: #2563eb; background: none; border: none; padding: 0.45rem 0.2rem 0; cursor: pointer; }
    .att-toggle-btn:hover { color: #1d4ed8; text-decoration: underline; }
</style>

@if (!$a['hasData'])
    <div class="att-panel">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-chart-line fa-2x mb-2 opacity-50"></i>
            <p class="mb-0">No scan activity recorded for the selected filter parameters.</p>
        </div>
    </div>
@else
<div class="att-analytics">

    {{-- ════════════════════════════════════════════════════════════════
         1. CORE IN / OUT ATTENDANCE KPIS
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-kpi-row">
        <div class="att-kpi att-kpi--info">
            <div class="att-kpi__label">Total Time In</div>
            <div class="att-kpi__val">{{ number_format($a['totalInScans'] ?? $a['uniqueStudentsIn']) }}</div>
            <div class="att-kpi__sub">{{ number_format($a['uniqueStudentsIn']) }} unique students</div>
        </div>

        <div class="att-kpi att-kpi--success">
            <div class="att-kpi__label">Total Time Out</div>
            <div class="att-kpi__val">{{ number_format($a['totalOutScans'] ?? $a['uniqueStudentsOut']) }}</div>
            <div class="att-kpi__sub">{{ $a['checkoutIntegrityRate'] }}% scan completion</div>
        </div>

        <div class="att-kpi att-kpi--warning">
            <div class="att-kpi__label">Late Arrivals</div>
            <div class="att-kpi__val">{{ number_format($a['lateCount']) }}</div>
            <div class="att-kpi__sub">{{ number_format($a['totalLateScans']) }} late entries &bull; {{ $a['lateRate'] ?? '0' }}%</div>
        </div>

        <div class="att-kpi att-kpi--danger">
            <div class="att-kpi__label">Missing Time Out</div>
            <div class="att-kpi__val">{{ number_format($a['missingOutCount']) }}</div>
            <div class="att-kpi__sub">unrecorded departures</div>
        </div>

        @if ($a['attendanceRate'] !== null)
        <div class="att-kpi att-kpi--info">
            <div class="att-kpi__label">Attendance Rate</div>
            <div class="att-kpi__val">{{ $a['attendanceRate'] }}%</div>
            <div class="att-kpi__sub">of {{ number_format($a['expectedStudents']) }} active enrollments</div>
        </div>
        @endif

        <div class="att-kpi att-kpi--success">
            <div class="att-kpi__label">On-Time Arrivals</div>
            <div class="att-kpi__val">{{ $a['onTimeRate'] !== null ? $a['onTimeRate'] . '%' : 'N/A' }}</div>
            <div class="att-kpi__sub">{{ number_format($a['onTimeCount']) }} student(s) on time</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         2. ANALYTICS ROW 1: DAILY SCAN VELOCITY & SECTION BREAKDOWN
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-grid-2">

        {{-- LEFT COLUMN: Scan Velocity & Movement Trends --}}
        <div class="att-panel">
            <div class="att-panel__head flex-wrap gap-2">
                <div>
                    <h4 class="att-panel__title">
                        <i class="fas fa-chart-line text-success"></i>  Scan Velocity & Movement Trends
                    </h4>
                </div>

                {{-- Metric Pills: Toggleable Multi-Series Selectors --}}
                <div class="att-pill-group">
                    <button type="button" class="att-metric-pill att-metric-pill--in active" data-metric="in" onclick="toggleMetric('in')" title="Toggle Time In Series">
                        <span class="att-metric-pill__dot"></span><span>Time In</span>
                    </button>
                    <button type="button" class="att-metric-pill att-metric-pill--out active" data-metric="out" onclick="toggleMetric('out')" title="Toggle Time Out Series">
                        <span class="att-metric-pill__dot"></span><span>Time Out</span>
                    </button>
                    <button type="button" class="att-metric-pill att-metric-pill--late active" data-metric="late" onclick="toggleMetric('late')" title="Toggle Late Arrivals Series">
                        <span class="att-metric-pill__dot"></span><span>Late</span>
                    </button>
                    <button type="button" class="att-metric-pill att-metric-pill--missing active" data-metric="missing_out" onclick="toggleMetric('missing_out')" title="Toggle Missing Time Out Series">
                        <span class="att-metric-pill__dot"></span><span>Missing Out</span>
                    </button>
                </div>
            </div>

            {{-- Optional Date Filter Toolbar (Isolated to this Chart) --}}
            <div class="att-chart-filter-bar">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.04em;">
                        <i class="fas fa-calendar-days text-primary me-1"></i> Date Filter <small class="text-muted fw-normal">(Optional)</small>:
                    </span>
                    <select id="chartMonthSelect" class="form-select form-select-sm py-1 px-2" style="width: auto; font-size: 0.72rem; min-width: 175px;" onchange="onMonthChange(this.value)">
                        <option value="">Default (Past 3 Months Overview)</option>
                        @if (!empty($availableMonths))
                            @foreach ($availableMonths as $m)
                                <option value="{{ $m['key'] }}">{{ $m['range_label'] }} ({{ $m['short_label'] }})</option>
                            @endforeach
                        @endif
                    </select>
                    <select id="chartWeekSelect" class="form-select form-select-sm py-1 px-2" style="width: auto; font-size: 0.72rem; min-width: 140px;" onchange="onWeekChange(this.value)" disabled>
                        <option value="">All Weeks in Month</option>
                        <option value="1">Week 1 (Days 1–7)</option>
                        <option value="2">Week 2 (Days 8–14)</option>
                        <option value="3">Week 3 (Days 15–21)</option>
                        <option value="4">Week 4 (Days 22–28)</option>
                        <option value="5">Week 5 (Days 29–End)</option>
                    </select>
                    <button type="button" id="chartResetFilterBtn" class="btn btn-sm btn-outline-secondary py-1 px-2 d-none" style="font-size: 0.7rem;" onclick="resetChartFilters()">
                        <i class="fas fa-rotate-left me-1"></i> Clear
                    </button>
                </div>
                <div id="chartSummaryStats" class="text-muted small fw-semibold" style="font-size: 0.68rem;">
                    {{ $a['dayCount'] }} days analyzed
                </div>
            </div>

            {{-- Filter Status Notification Banner --}}
            <div id="chartActiveFilterBadge" class="alert alert-info py-1 px-2.5 small d-flex justify-content-between align-items-center mb-2 d-none" style="font-size: 0.7rem;">
                <span><i class="fas fa-filter text-primary me-1"></i> Filter active: <strong id="filterScopeText"></strong></span>
                <a href="javascript:void(0)" onclick="resetChartFilters()" class="text-decoration-none fw-bold"><i class="fas fa-times me-0.5"></i> Clear Filter</a>
            </div>

            {{-- Interactive Multi-Series SVG Chart Frame --}}
            <div class="att-chart-dynamic-frame" id="velocityChartFrame">
                <svg id="velocityChartSvg" class="w-100 h-100" viewBox="0 0 650 210" preserveAspectRatio="none"></svg>
                <div id="chartEmptyState" class="text-center py-4 text-muted d-none">
                    <i class="fas fa-calendar-xmark fa-2x mb-2 text-secondary opacity-50"></i>
                    <p class="mb-1 fw-bold" style="font-size: 0.8rem;">No scan records in this period</p>
                    <p class="small text-muted mb-2">No activity was logged during the selected month/week.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5" style="font-size: 0.72rem;" onclick="resetChartFilters()">
                        <i class="fas fa-rotate-left me-1"></i> Reset to Default Overview
                    </button>
                </div>
                <div id="chartTooltip" class="att-chart-tooltip"></div>
            </div>

            {{-- Date Axis Ticks Bar --}}
            <div id="chartTickBar" class="d-flex justify-content-between font-monospace text-muted mt-1 px-1" style="font-size: 0.65rem;"></div>
        </div>

        {{-- RIGHT COLUMN: Section Attendance & Late Entry Breakdown (Visual Chart + Table) --}}
        <div class="att-panel att-panel--fixed-section">
            <div class="att-panel__head flex-wrap gap-2 mb-2">
                <div>
                    <h4 class="att-panel__title">
                        <i class="fas fa-users-rectangle text-primary"></i> Section Attendance & Late Entry Breakdown
                    </h4>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="sectionSortSelect" class="form-select form-select-sm py-0.5 px-2" style="width: auto; font-size: 0.7rem;" onchange="sortSectionData(this.value)">
                        <option value="scans">Sort: Most Scans</option>
                        <option value="late_rate">Sort: Highest Late %</option>
                        <option value="missing">Sort: Most Missing OUT</option>
                        <option value="students">Sort: Most Students</option>
                    </select>
                    <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold" style="font-size: 0.68rem;">
                        {{ $a['sectionData']->count() }} Sections
                    </span>
                </div>
            </div>

            @if ($a['sectionData']->isNotEmpty())
                @php
                    $maxSectionScans = max($a['sectionData']->max('total_scans') ?? 1, 1);
                    $hasManySections = $a['sectionData']->count() > 2;
                @endphp

                {{-- Scrollable Container Body (Fixed Height with Custom Scrollbar) --}}
                <div class="att-section-scroll-body" id="sectionScrollBody">

                    {{-- 1. Visual Comparative Graph (Volume & Late Disparity Bars) --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.63rem; letter-spacing: 0.05em;">
                            <i class="fas fa-chart-simple text-primary me-1"></i> Visual Scan Comparison
                        </span>
                    </div>

                    <div class="att-section-visual-wrap mb-1" id="sectionVisualList">
                        @foreach ($a['sectionData'] as $sidx => $sd)
                            @php
                                $sScanPct = round(($sd['total_scans'] / $maxSectionScans) * 100, 1);
                                $isElevatedLate = $sd['late_rate'] >= 15;
                                $lateBadgeClass = $sd['late_rate'] >= 15 ? 'badge bg-danger text-white' : ($sd['late_rate'] >= 10 ? 'badge bg-warning text-dark' : 'badge bg-success-subtle text-success border border-success-subtle');
                            @endphp
                            <div class="att-section-card-item {{ $sidx >= 2 ? 'att-graph-extra d-none' : '' }}" 
                                 data-scans="{{ $sd['total_scans'] }}" 
                                 data-late-rate="{{ $sd['late_rate'] }}" 
                                 data-missing="{{ $sd['missing_out'] }}" 
                                 data-students="{{ $sd['students'] }}">
                                <div class="att-section-card-head">
                                    <span>
                                        <span class="badge bg-light text-dark border me-1" style="font-size: 0.65rem;">G{{ $sd['grade_level'] }}</span>
                                        <strong>{{ $sd['name'] }}</strong>
                                    </span>
                                    <span class="d-flex align-items-center gap-1.5">
                                        <span class="{{ $lateBadgeClass }}" style="font-size: 0.65rem;">
                                            @if ($isElevatedLate) <i class="fas fa-triangle-exclamation me-0.5"></i> @endif
                                            {{ $sd['late_rate'] }}% Late Rate
                                        </span>
                                        @if ($sd['missing_out'] > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.65rem;">
                                                {{ $sd['missing_out'] }} Missing OUT
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="att-section-bar-track">
                                    <div class="att-section-bar-fill" style="width: {{ $sScanPct }}%; background: #3b82f6;" title="{{ $sd['total_scans'] }} total scans"></div>
                                </div>
                                <div class="att-section-card-footer">
                                    <span>{{ $sd['students'] }} enrolled student(s)</span>
                                    <span><strong>{{ number_format($sd['total_scans']) }}</strong> total scans &bull; {{ $sd['late_count'] }} late entries</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- "See All" for Section Graphs (When Count > 2) --}}
                    @if ($hasManySections)
                        <button type="button" class="att-toggle-btn mb-2.5" id="toggleGraphSeeAllBtn" onclick="toggleGraphSeeAll()">
                            <i class="fas fa-chevron-down me-1"></i> See all ({{ $a['sectionData']->count() - 2 }} more sections)
                        </button>
                    @endif

                    {{-- 2. Supporting Data Table (Kept Below the Graphs) --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-1">
                        <span class="text-uppercase text-muted fw-bold" style="font-size: 0.63rem; letter-spacing: 0.05em;">
                            <i class="fas fa-table-list text-primary me-1"></i> Supporting Data Table
                        </span>
                    </div>

                    <div class="att-table-wrap mb-1">
                        <table class="att-table att-section-table" id="sectionDataTable">
                            <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2;">
                                <tr>
                                    <th>Section</th>
                                    <th style="text-align:right">Students</th>
                                    <th style="text-align:right">Total Scans</th>
                                    <th style="text-align:right">Late Count</th>
                                    <th style="text-align:right">Late %</th>
                                    <th style="text-align:right">Missing OUT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($a['sectionData'] as $sidx => $sd)
                                <tr class="{{ $sidx >= 2 ? 'att-table-extra d-none' : '' }}"
                                    data-scans="{{ $sd['total_scans'] }}" 
                                    data-late-rate="{{ $sd['late_rate'] }}" 
                                    data-missing="{{ $sd['missing_out'] }}" 
                                    data-students="{{ $sd['students'] }}">
                                    <td><strong>{{ $sd['name'] }}</strong></td>
                                    <td style="text-align:right">{{ $sd['students'] }}</td>
                                    <td style="text-align:right">{{ number_format($sd['total_scans']) }}</td>
                                    <td style="text-align:right">
                                        @if ($sd['late_count'] > 0)
                                            <span class="att-badge att-badge--warning">{{ $sd['late_count'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right" class="fw-bold {{ $sd['late_rate'] >= 15 ? 'text-danger' : '' }}">
                                        {{ $sd['late_rate'] }}%
                                    </td>
                                    <td style="text-align:right">
                                        @if ($sd['missing_out'] > 0)
                                            <span class="att-badge att-badge--danger">{{ $sd['missing_out'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- "See All" for Table Rows (When Count > 2) --}}
                    @if ($hasManySections)
                        <button type="button" class="att-toggle-btn mt-1" id="toggleTableSeeAllBtn" onclick="toggleTableSeeAll()">
                            <i class="fas fa-chevron-down me-1"></i> See all ({{ $a['sectionData']->count() - 2 }} more rows)
                        </button>
                    @endif

                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-users-slash fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0" style="font-size: 0.78rem;">No section scan activity recorded in this period.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════
         3. DIVERSE DIAGNOSTIC GRAPHS (DONUT, HORIZONTAL BARS, VERTICAL TIMELINE)
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-grid-3">

        {{-- GRAPH 1: SVG Donut / Ring Chart (Punctuality Composition) --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-chart-pie text-primary"></i> Punctuality Breakdown
                </h4>
                <span class="att-panel__meta">{{ number_format($a['uniqueStudentsIn']) }} Students</span>
            </div>

            <div class="att-donut-wrap">
                <div class="att-donut-chart">
                    <svg viewBox="0 0 36 36" class="w-100 h-100">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#f1f5f9" stroke-width="4.5" />
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#10b981" stroke-width="4.5"
                              stroke-dasharray="{{ $a['donutOnTimePct'] }}, 100" />
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#f59e0b" stroke-width="4.5"
                              stroke-dasharray="{{ $a['donutLatePct'] }}, 100"
                              stroke-dashoffset="-{{ $a['donutOnTimePct'] }}" />
                    </svg>
                    <div class="att-donut-center">
                        <div class="att-donut-center__val">{{ $a['donutOnTimePct'] }}%</div>
                        <div class="att-donut-center__lbl">On-Time</div>
                    </div>
                </div>

                <div class="att-donut-legend">
                    <div class="att-donut-item">
                        <span class="att-donut-item__name">
                            <span class="att-donut-item__dot" style="background:#10b981"></span> On-Time Arrivals
                        </span>
                        <span class="att-donut-item__num">{{ number_format($a['onTimeCount']) }} <small class="text-muted">({{ $a['donutOnTimePct'] }}%)</small></span>
                    </div>
                    <div class="att-donut-item">
                        <span class="att-donut-item__name">
                            <span class="att-donut-item__dot" style="background:#f59e0b"></span> Late Arrivals
                        </span>
                        <span class="att-donut-item__num">{{ number_format($a['lateCount']) }} <small class="text-muted">({{ $a['donutLatePct'] }}%)</small></span>
                    </div>
                    <div class="att-donut-item">
                        <span class="att-donut-item__name">
                            <span class="att-donut-item__dot" style="background:#ef4444"></span> Missing OUT
                        </span>
                        <span class="att-donut-item__num">{{ number_format($a['missingOutCount']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAPH 2: SVG Pie / Donut Chart (Attendance by Grade Level) --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-chart-pie text-info"></i> Attendance by Grade Level
                </h4>
                <span class="att-panel__meta">{{ $a['gradeLevelData']->count() }} Grade Levels</span>
            </div>

            @php
                $totalGradeStudents = max($a['gradeLevelData']->sum('students'), 1);
                $gradePalette = ['#3b82f6', '#06b6d4', '#8b5cf6', '#10b981', '#f59e0b', '#ec4899', '#6366f1', '#14b8a6', '#f97316'];
            @endphp

            @if ($a['gradeLevelData']->isNotEmpty())
                <div class="att-donut-wrap">
                    <div class="att-donut-chart">
                        <svg viewBox="0 0 36 36" class="w-100 h-100">
                            {{-- Base Background Ring --}}
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#f1f5f9" stroke-width="4.5" />
                            {{-- Slices --}}
                            @php $accumOffset = 0; @endphp
                            @foreach ($a['gradeLevelData'] as $gIdx => $g)
                                @php
                                    $slicePct = round(($g['students'] / $totalGradeStudents) * 100, 1);
                                    $color = $gradePalette[$gIdx % count($gradePalette)];
                                @endphp
                                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                      fill="none" stroke="{{ $color }}" stroke-width="4.5"
                                      stroke-dasharray="{{ $slicePct }}, 100"
                                      stroke-dashoffset="-{{ $accumOffset }}" />
                                @php $accumOffset += $slicePct; @endphp
                            @endforeach
                        </svg>
                        <div class="att-donut-center">
                            <div class="att-donut-center__val">{{ number_format($a['gradeLevelData']->sum('students')) }}</div>
                            <div class="att-donut-center__lbl">Students</div>
                        </div>
                    </div>

                    <div class="att-donut-legend" style="max-height: 140px; overflow-y: auto;">
                        @foreach ($a['gradeLevelData'] as $gIdx => $g)
                            @php
                                $slicePct = round(($g['students'] / $totalGradeStudents) * 100, 1);
                                $color = $gradePalette[$gIdx % count($gradePalette)];
                            @endphp
                            <div class="att-donut-item">
                                <span class="att-donut-item__name">
                                    <span class="att-donut-item__dot" style="background:{{ $color }}"></span> {{ $g['label'] }}
                                </span>
                                <span class="att-donut-item__num">{{ $g['students'] }} <small class="text-muted fw-normal">({{ $slicePct }}%)</small></span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Supporting Footnote --}}
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light text-muted font-monospace" style="font-size: 0.68rem;">
                    <span><i class="fas fa-users me-1 text-primary"></i> {{ number_format($a['gradeLevelData']->sum('students')) }} Total Attended</span>
                    <span>{{ number_format($a['gradeLevelData']->sum('total_scans')) }} Total Scans</span>
                </div>
            @else
                <div class="text-muted small py-4 text-center">
                    <i class="fas fa-layer-group fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No grade level data available.</p>
                </div>
            @endif
        </div>

        {{-- GRAPH 3: Vertical Column Timeline (Hourly Gate Traffic & Rush Surge) --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-chart-column text-warning"></i> Hourly Attendance Traffic Density
                </h4>
                <span class="att-panel__meta">Rush: <strong>{{ $a['rushWindow'] ?? 'Normal' }}</strong></span>
            </div>

            <div class="att-vbar-wrap">
                @foreach ($a['hourlyDistribution'] as $h)
                    @if ($h['hour'] >= 6 && $h['hour'] <= 17)
                        @php
                            $vPct = max(round(($h['total'] / $a['maxHourlyTotal']) * 100, 1), 6);
                            $isPeak = ($h['hour'] === ($a['peakEntryHour']['hour'] ?? null) || $h['hour'] === ($a['peakExitHour']['hour'] ?? null)) && $h['total'] > 0;
                        @endphp
                        <div class="att-vbar-col" title="{{ $h['label'] }}: {{ $h['total'] }} scans ({{ $h['in'] }} IN, {{ $h['out'] }} OUT)">
                            <div class="att-vbar-bar {{ $isPeak ? 'att-vbar-bar--peak' : 'att-vbar-bar--in' }}" style="height: {{ $vPct }}%;"></div>
                            <div class="att-vbar-lbl">{{ $h['label'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light font-monospace text-muted" style="font-size: 0.68rem;">
                <span><span class="badge bg-primary rounded-circle p-1"></span> Peak Hour Highlighted</span>
                <span>Max: {{ $a['maxHourlyTotal'] }} scans/hr</span>
            </div>
        </div>

    </div>


    {{-- ════════════════════════════════════════════════════════════════
         5. DUAL FOLLOW-UP REGISTRIES (SAME ROW ON DESKTOP LAYOUTS)
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-grid-2">

        {{-- LEFT PANEL: Multiple Late Arrivals Follow-Up Registry --}}
        <div class="att-panel att-panel--fixed-registry" id="lateArrivalsRegistry">
            <div class="att-panel__head flex-wrap gap-2 mb-2">
                <div>
                    <h4 class="att-panel__title">
                        <i class="fas fa-clock text-warning"></i> Multiple Late Arrivals Follow-Up Registry
                    </h4>
                </div>
                <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold" style="font-size: 0.7rem;">
                    {{ $a['frequentLateStudents']->count() }} Students (&ge; 2 Late Scans)
                </span>
            </div>

            @if ($a['frequentLateStudents']->isNotEmpty())
                <div class="att-registry-scroll-body">
                    <table class="att-table">
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2;">
                            <tr>
                                <th>Student</th>
                                <th>Grade & Section</th>
                                <th style="text-align:center">Late Count</th>
                                <th style="text-align:center">Pattern / Status</th>
                                <th style="text-align:center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($a['frequentLateStudents'] as $st)
                            <tr>
                                <td>
                                    <strong>{{ $st['student_name'] }}</strong>
                                    <span class="text-muted d-block" style="font-size:0.65rem">{{ $st['student_number'] }}</span>
                                </td>
                                <td>Grade {{ $st['grade_level'] }} — {{ $st['section_name'] }}</td>
                                <td style="text-align:center" class="fw-bold">{{ $st['late_count'] }}</td>
                                <td style="text-align:center">
                                    <span class="att-badge att-badge--{{ $st['badge_tone'] ?? 'warning' }}" title="{{ $st['pattern_desc'] ?? '' }}">
                                        {{ $st['badge_label'] ?? ($st['late_count'] . ' Late Entries') }}
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" style="font-size: 0.65rem;"
                                                data-bs-toggle="modal" data-bs-target="#studentLateModal{{ $st['enrollment_id'] }}" title="View late arrival details">
                                            <i class="fas fa-eye me-0.5"></i> Details
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5" style="font-size: 0.65rem;"
                                                onclick="initiateInOutIntervention({{ $st['enrollment_id'] }}, '{{ addslashes($st['student_name']) }}', 'late', {{ $st['late_count'] }})" title="Initiate intervention for late arrival">
                                            <i class="fas fa-paper-plane me-0.5"></i> Intervene
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted my-auto">
                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                    <p class="mb-0" style="font-size: 0.78rem;">No repeated late arrivals recorded in this timeframe.</p>
                </div>
            @endif
        </div>

        {{-- RIGHT PANEL: Multiple Missing Time Out Follow-Up Registry --}}
        <div class="att-panel att-panel--fixed-registry" id="missingOutRegistry">
            <div class="att-panel__head flex-wrap gap-2 mb-2">
                <div>
                    <h4 class="att-panel__title text-danger">
                        <i class="fas fa-right-from-bracket text-danger"></i> Multiple Missing Time Out Follow-Up Registry
                    </h4>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold" style="font-size: 0.7rem;">
                    {{ $a['frequentMissingOutStudents']->count() }} Flagged Students &bull; {{ $a['missingOutCount'] }} Total Records
                </span>
            </div>

            @if ($a['frequentMissingOutStudents']->isNotEmpty())
                <div class="att-registry-scroll-body">
                    <table class="att-table">
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2;">
                            <tr>
                                <th>Student</th>
                                <th>Grade & Section</th>
                                <th style="text-align:center">Missing OUT</th>
                                <th style="text-align:center">Pattern / Status</th>
                                <th style="text-align:center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($a['frequentMissingOutStudents'] as $mo)
                            <tr>
                                <td>
                                    <strong>{{ $mo['student_name'] }}</strong>
                                    <span class="text-muted d-block" style="font-size:0.65rem">{{ $mo['student_number'] }}</span>
                                </td>
                                <td>Grade {{ $mo['grade_level'] }} — {{ $mo['section_name'] }}</td>
                                <td style="text-align:center" class="fw-bold text-danger">{{ $mo['missing_count'] }}</td>
                                <td style="text-align:center">
                                    <span class="att-badge att-badge--{{ $mo['badge_tone'] ?? 'danger' }}" title="{{ $mo['pattern_desc'] ?? '' }}">
                                        {{ $mo['badge_label'] ?? ($mo['missing_count'] . ' Unrecorded OUTs') }}
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" style="font-size: 0.65rem;"
                                                data-bs-toggle="modal" data-bs-target="#studentMissingModal{{ $mo['enrollment_id'] }}" title="View missing out details">
                                            <i class="fas fa-eye me-0.5"></i> Details
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5" style="font-size: 0.65rem;"
                                                onclick="initiateInOutIntervention({{ $mo['enrollment_id'] }}, '{{ addslashes($mo['student_name']) }}', 'missing_out', {{ $mo['missing_count'] }})" title="Initiate intervention for missing checkout">
                                            <i class="fas fa-paper-plane me-0.5"></i> Intervene
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted my-auto">
                    <i class="fas fa-circle-check text-success fa-2x mb-2"></i>
                    <p class="mb-0" style="font-size: 0.78rem;">All student Time In scans have matching departure records.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════
         5. ADMINISTRATIVE ACTION DIRECTIVES
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-panel mb-3">
        <div class="att-panel__head">
            <h4 class="att-panel__title">
                <i class="fas fa-clipboard-check text-primary"></i> Administrative Action Directives
            </h4>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold" style="font-size: 0.7rem;">
                {{ $a['prescriptiveActions']->count() }} Actionable Directives
            </span>
        </div>

        <div class="att-actions-grid">
            @foreach ($a['prescriptiveActions'] as $action)
                <div class="att-action-card att-action-card--{{ $action['priority_tone'] }}">
                    <div>
                        <div class="att-action-card__top">
                            <span class="att-action-card__cat">{{ $action['category'] }}</span>
                            <span class="att-badge att-badge--{{ $action['priority_tone'] }}">{{ $action['priority'] }}</span>
                        </div>
                        <div class="att-action-card__title">{{ $action['title'] }}</div>
                        <p class="att-action-card__desc">{{ $action['description'] }}</p>
                    </div>
                    <div class="pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                        <span class="text-muted small" style="font-size: 0.68rem;">Action protocol</span>
                        @if (str_starts_with($action['modal_target'] ?? '', '#modalAction'))
                            <button type="button" class="att-action-btn att-action-btn--{{ $action['priority_tone'] }}"
                                    data-bs-toggle="modal" data-bs-target="{{ $action['modal_target'] }}">
                                <span>{{ $action['action_label'] }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        @elseif (!empty($action['modal_target']))
                            <a href="{{ $action['modal_target'] }}" class="att-action-btn att-action-btn--{{ $action['priority_tone'] }} text-decoration-none">
                                <span>{{ $action['action_label'] }}</span>
                                <i class="fas fa-arrow-down"></i>
                            </a>
                        @else
                            <button type="button" class="att-action-btn att-action-btn--{{ $action['priority_tone'] }}"
                                    data-bs-toggle="modal" data-bs-target="#downloadAnalyticsModal">
                                <span>{{ $action['action_label'] }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════════
     6. FUNCTIONAL ACTION DIRECTIVE MODALS
     ════════════════════════════════════════════════════════════════ --}}

{{-- DIRECTIVE 1: Section Advisory Modal --}}
@php
    $sectionAction = $a['prescriptiveActions']->firstWhere('action_key', 'section_advisory');
@endphp
@if ($sectionAction)
<div class="modal fade" id="modalAction_section_advisory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="fas fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Advisor Follow-Up: {{ $sectionAction['section_name'] }}</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">{{ $sectionAction['section_late_count'] }} late arrivals recorded &bull; {{ $sectionAction['section_late_rate'] }}% late rate</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning py-2 px-3 small mb-3">
                    <i class="fas fa-info-circle me-1"></i> {{ $sectionAction['description'] }}
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted" style="font-size: 0.7rem;">Late Arrival Students in {{ $sectionAction['section_name'] }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.72rem;" onclick="copyTableText('sectionLateTable', this)">
                        <i class="fas fa-copy me-1"></i> Copy Follow-up List
                    </button>
                </div>

                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-sm table-hover mb-0" id="sectionLateTable" style="font-size: 0.75rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Scan Date</th>
                                <th>Time In</th>
                                <th>Session</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sectionAction['late_students'] as $lst)
                                <tr>
                                    <td><strong>{{ $lst['name'] }}</strong></td>
                                    <td>{{ $lst['student_number'] }}</td>
                                    <td>{{ $lst['date'] }}</td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-dark fw-bold">{{ $lst['time'] }}</span></td>
                                    <td>{{ $lst['session'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('school_admin.time-in-time-out-history.index', ['query' => $sectionAction['section_name'], 'flag_type' => 'late_arrival']) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i> Filter in Scan History Logs
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- DIRECTIVE 2: Gate Timing Diagnostic Modal --}}
@php
    $gateAction = $a['prescriptiveActions']->firstWhere('action_key', 'gate_timing');
@endphp
@if ($gateAction)
<div class="modal fade" id="modalAction_gate_timing" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Attendance Queue & Staffing Directives</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Peak Window: {{ $gateAction['rush_window'] }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem;">Rush Hour Velocity</small>
                            <span class="fs-4 fw-bold text-dark">{{ $gateAction['rush_velocity'] }}</span>
                            <small class="text-muted d-block" style="font-size: 0.65rem;">scans / minute</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem;">Surge Timing</small>
                            <span class="fs-5 fw-bold text-primary">{{ $gateAction['rush_window'] }}</span>
                            <small class="text-muted d-block" style="font-size: 0.65rem;">high entry load</small>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 0.7rem;">Recommended Operational Protocol</h6>
                <div class="list-group list-group-flush border rounded-3 p-2 small">
                    <div class="list-group-item border-0 d-flex gap-2 align-items-start py-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>Deploy Secondary Scanner Operator:</strong> Station an additional staff member at the scanning station 10 minutes prior to {{ explode('–', $gateAction['rush_window'])[0] ?? 'surge' }}.</div>
                    </div>
                    <div class="list-group-item border-0 d-flex gap-2 align-items-start py-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>Separate Entry & Exit Lanes:</strong> Ensure clear physical stanchions to prevent outbound congestion during peak morning entry.</div>
                    </div>
                    <div class="list-group-item border-0 d-flex gap-2 align-items-start py-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <div><strong>Express QR Code Staging:</strong> Instruct student leaders to hold QR badges ready prior to stepping up to scanners.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- DIRECTIVE 3: Unrecorded Departures Modal --}}
@php
    $missingAction = $a['prescriptiveActions']->firstWhere('action_key', 'missing_checkout');
@endphp
@if ($missingAction)
<div class="modal fade" id="modalAction_missing_checkout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Unrecorded Departures Reconciliation</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">{{ $missingAction['missing_count'] }} students with IN scan but no matching OUT scan</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted" style="font-size: 0.7rem;">Unrecorded Student Departures</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.72rem;" onclick="copyTableText('missingOutTable', this)">
                        <i class="fas fa-copy me-1"></i> Copy Verification List
                    </button>
                </div>

                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-sm table-hover mb-0" id="missingOutTable" style="font-size: 0.75rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Grade & Section</th>
                                <th>Scan Date</th>
                                <th>Time In</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($missingAction['missing_sample'] as $mo)
                                <tr>
                                    <td><strong>{{ $mo['student_name'] }}</strong></td>
                                    <td>{{ $mo['student_number'] }}</td>
                                    <td>Grade {{ $mo['grade_level'] }} — {{ $mo['section_name'] }}</td>
                                    <td>{{ $mo['date'] }}</td>
                                    <td class="fw-bold">{{ $mo['in_time'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('school_admin.time-in-time-out-history.index', ['scan_type' => 'IN']) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-list me-1"></i> View Scans in History
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- DIRECTIVE 4: Late Guidance Follow-Up Modal --}}
@php
    $lateAction = $a['prescriptiveActions']->firstWhere('action_key', 'late_guidance');
@endphp
@if ($lateAction)
<div class="modal fade" id="modalAction_late_guidance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Multiple Late Arrivals Follow-Up Registry</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">{{ $lateAction['student_count'] }} students with &ge; 2 late scans</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-uppercase text-muted" style="font-size: 0.7rem;">Flagged Student List</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.72rem;" onclick="copyTableText('frequentLateActionTable', this)">
                        <i class="fas fa-copy me-1"></i> Copy Registry
                    </button>
                </div>

                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table table-sm table-hover mb-0" id="frequentLateActionTable" style="font-size: 0.75rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Grade & Section</th>
                                <th style="text-align:center">Late Count</th>
                                <th style="text-align:center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($a['frequentLateStudents'] as $st)
                                <tr>
                                    <td><strong>{{ $st['student_name'] }}</strong></td>
                                    <td>{{ $st['student_number'] }}</td>
                                    <td>Grade {{ $st['grade_level'] }} — {{ $st['section_name'] }}</td>
                                    <td style="text-align:center" class="fw-bold">{{ $st['late_count'] }}</td>
                                    <td style="text-align:center"><span class="att-badge att-badge--{{ $st['badge_tone'] }}">{{ $st['badge_label'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════
     7. COMPREHENSIVE DRILLDOWN MODALS (For Each Student)
     ════════════════════════════════════════════════════════════════ --}}
@foreach ($a['frequentLateStudents'] as $st)
    <div class="modal fade" id="studentLateModal{{ $st['enrollment_id'] }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">{{ $st['student_name'] }}</h6>
                            <span class="text-muted" style="font-size: 0.72rem;">ID: {{ $st['student_number'] }} &bull; Grade {{ $st['grade_level'] }} — {{ $st['section_name'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Pattern Diagnostic Alert --}}
                    <div class="alert alert-{{ $st['badge_tone'] }} py-2 px-3 small mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>
                            <strong>{{ $st['badge_label'] }}:</strong> {{ $st['pattern_desc'] }}
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2.5 bg-light rounded-3 text-center border">
                                <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem;">Late Entries</small>
                                <span class="fs-5 fw-bold text-dark">{{ $st['late_count'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2.5 bg-light rounded-3 text-center border">
                                <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem;">Personal Late Rate</small>
                                <span class="fs-5 fw-bold text-dark">{{ $st['personal_late_rate'] }}%</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.05em;">Recorded Late Arrival Logs</h6>
                    <div class="table-responsive border rounded-3" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.75rem;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Session</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($st['scan_details'] as $logItem)
                                    <tr>
                                        <td><strong>{{ $logItem['date'] }}</strong></td>
                                        <td><span class="badge bg-warning bg-opacity-10 text-dark fw-bold">{{ $logItem['time'] }}</span></td>
                                        <td>{{ $logItem['session_type'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm"
                            onclick="initiateInOutIntervention({{ $st['enrollment_id'] }}, '{{ addslashes($st['student_name']) }}', 'late', {{ $st['late_count'] }})">
                        <i class="fas fa-paper-plane me-1"></i> Initiate Intervention
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Drilldown Modals for Frequent Missing OUT Students --}}
@foreach ($a['frequentMissingOutStudents'] as $mo)
    <div class="modal fade" id="studentMissingModal{{ $mo['enrollment_id'] }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                            <i class="fas fa-right-from-bracket"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0">{{ $mo['student_name'] }}</h6>
                            <span class="text-muted" style="font-size: 0.72rem;">ID: {{ $mo['student_number'] }} &bull; Grade {{ $mo['grade_level'] }} — {{ $mo['section_name'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Pattern Diagnostic Alert --}}
                    <div class="alert alert-{{ $mo['badge_tone'] }} py-2 px-3 small mb-3 d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>
                            <strong>{{ $mo['badge_label'] }}:</strong> {{ $mo['pattern_desc'] }}
                        </div>
                    </div>

                    <div class="p-2.5 bg-light rounded-3 text-center border mb-3">
                        <small class="text-muted text-uppercase d-block fw-bold" style="font-size: 0.65rem;">Unrecorded Departures</small>
                        <span class="fs-5 fw-bold text-danger">{{ $mo['missing_count'] }}</span>
                        <small class="text-muted d-block" style="font-size: 0.65rem;">Scanned IN but no exit scan recorded</small>
                    </div>

                    <h6 class="fw-bold mb-2 text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.05em;">Incomplete Attendance Scans</h6>
                    <div class="table-responsive border rounded-3" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.75rem;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Session</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mo['scan_details'] as $logItem)
                                    <tr>
                                        <td><strong>{{ $logItem['date'] }}</strong></td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-dark fw-bold">{{ $logItem['time'] }}</span></td>
                                        <td>{{ $logItem['session_type'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="initiateInOutIntervention({{ $mo['enrollment_id'] }}, '{{ addslashes($mo['student_name']) }}', 'missing_out', {{ $mo['missing_count'] }})">
                        <i class="fas fa-paper-plane me-1"></i> Initiate Intervention
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
    function copyTableText(tableId, btn) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var text = "";
        for (var r = 0; r < table.rows.length; r++) {
            var row = table.rows[r];
            var rowText = [];
            for (var c = 0; c < row.cells.length; c++) {
                rowText.push(row.cells[c].innerText.trim());
            }
            text += rowText.join(" \t ") + "\n";
        }
        navigator.clipboard.writeText(text).then(function() {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied!';
            setTimeout(function() { btn.innerHTML = origHtml; }, 2000);
        });
    }

    // ── Phase 2: Dynamic Multi-Series Velocity Chart & Section Controls ────
    document.addEventListener('DOMContentLoaded', function () {
        const rawTrendData = @json($a['dailyTrend'] ?? []);
        let activeMetrics = {
            in: true,
            out: true,
            late: true,
            missing_out: true
        };
        let currentMonth = '';
        let currentWeek = '';

        const colors = {
            in: '#10b981',
            out: '#3b82f6',
            late: '#f59e0b',
            missing_out: '#ef4444'
        };

        const metricLabels = {
            in: 'Time In',
            out: 'Time Out',
            late: 'Late Arrivals',
            missing_out: 'Missing Time Out'
        };

        function renderVelocityChart() {
            const svg = document.getElementById('velocityChartSvg');
            const emptyState = document.getElementById('chartEmptyState');
            const tickBar = document.getElementById('chartTickBar');
            const tooltip = document.getElementById('chartTooltip');
            const summaryStats = document.getElementById('chartSummaryStats');
            const activeFilterBadge = document.getElementById('chartActiveFilterBadge');
            const filterScopeText = document.getElementById('filterScopeText');
            const resetBtn = document.getElementById('chartResetFilterBtn');

            if (!svg) return;

            // 1. Filter raw data based on selected month & week
            let filtered = rawTrendData.filter(function (d) {
                if (currentMonth && d.month_key !== currentMonth) {
                    return false;
                }
                if (currentWeek) {
                    const day = parseInt(d.day_of_month, 10);
                    if (currentWeek === '1' && (day < 1 || day > 7)) return false;
                    if (currentWeek === '2' && (day < 8 || day > 14)) return false;
                    if (currentWeek === '3' && (day < 15 || day > 21)) return false;
                    if (currentWeek === '4' && (day < 22 || day > 28)) return false;
                    if (currentWeek === '5' && day < 29) return false;
                }
                return true;
            });

            // Update filter badges & reset buttons
            const isFiltered = Boolean(currentMonth || currentWeek);
            if (resetBtn) {
                resetBtn.classList.toggle('d-none', !isFiltered);
            }
            if (activeFilterBadge) {
                activeFilterBadge.classList.toggle('d-none', !isFiltered);
                if (isFiltered) {
                    const monthSelect = document.getElementById('chartMonthSelect');
                    const monthText = monthSelect ? monthSelect.options[monthSelect.selectedIndex]?.text : currentMonth;
                    const weekText = currentWeek ? ' (Week ' + currentWeek + ')' : ' (Full Month)';
                    if (filterScopeText) {
                        filterScopeText.textContent = monthText + weekText;
                    }
                }
            }

            if (summaryStats) {
                summaryStats.textContent = filtered.length + ' day(s) analyzed';
            }

            // Zero-data state handling
            if (filtered.length === 0) {
                svg.innerHTML = '';
                svg.classList.add('d-none');
                if (emptyState) emptyState.classList.remove('d-none');
                if (tickBar) tickBar.innerHTML = '';
                return;
            }

            svg.classList.remove('d-none');
            if (emptyState) emptyState.classList.add('d-none');

            // Metric pills active list
            const activeKeys = Object.keys(activeMetrics).filter(function (k) { return activeMetrics[k]; });
            if (activeKeys.length === 0) {
                svg.innerHTML = '<text x="325" y="105" text-anchor="middle" fill="#94a3b8" font-size="12" font-family="sans-serif">Select at least one metric pill above to visualize data.</text>';
                if (tickBar) tickBar.innerHTML = '';
                return;
            }

            // Find maximum value across active metrics
            let maxVal = 0;
            filtered.forEach(function (d) {
                activeKeys.forEach(function (k) {
                    if (d[k] > maxVal) maxVal = d[k];
                });
            });
            if (maxVal <= 0) maxVal = 5;
            if (maxVal <= 5) maxVal = 5;
            else if (maxVal <= 10) maxVal = 10;
            else maxVal = Math.ceil(maxVal / 5) * 5;

            // Dimensions for responsive SVG layout
            const width = 650;
            const height = 210;
            const padLeft = 38;
            const padRight = 18;
            const padTop = 18;
            const padBottom = 28;
            const chartW = width - padLeft - padRight;
            const chartH = height - padTop - padBottom;

            let svgHtml = '';

            // Gradient Definitions
            svgHtml += `
            <defs>
                <linearGradient id="grad_in" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                </linearGradient>
                <linearGradient id="grad_out" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>
                </linearGradient>
                <linearGradient id="grad_late" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0"/>
                </linearGradient>
                <linearGradient id="grad_missing_out" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#ef4444" stop-opacity="0.22"/>
                    <stop offset="100%" stop-color="#ef4444" stop-opacity="0.0"/>
                </linearGradient>
            </defs>`;

            // Horizontal Grid Lines & Scale Numbers
            const gridSteps = 3;
            for (let g = 0; g <= gridSteps; g++) {
                const gy = padTop + chartH - (g / gridSteps) * chartH;
                const gVal = Math.round((g / gridSteps) * maxVal);
                svgHtml += `<line x1="${padLeft}" y1="${gy}" x2="${width - padRight}" y2="${gy}" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3 3"/>`;
                svgHtml += `<text x="${padLeft - 6}" y="${gy + 3.5}" text-anchor="end" fill="#94a3b8" font-size="9" font-family="monospace">${gVal}</text>`;
            }

            const n = filtered.length;
            const getX = function (idx) {
                return n === 1 ? padLeft + chartW / 2 : padLeft + (idx / (n - 1)) * chartW;
            };
            const getY = function (val) {
                return padTop + chartH - (val / maxVal) * chartH;
            };

            // Draw Area Shading and Lines for Active Metrics
            activeKeys.forEach(function (k) {
                let pathD = '';
                let areaD = '';
                filtered.forEach(function (d, idx) {
                    const x = getX(idx);
                    const y = getY(d[k]);
                    if (idx === 0) {
                        pathD += `M ${x} ${y}`;
                        areaD += `M ${x} ${padTop + chartH} L ${x} ${y}`;
                    } else {
                        pathD += ` L ${x} ${y}`;
                        areaD += ` L ${x} ${y}`;
                    }
                });
                areaD += ` L ${getX(n - 1)} ${padTop + chartH} Z`;

                svgHtml += `<path d="${areaD}" fill="url(#grad_${k})" pointer-events="none" />`;
                svgHtml += `<path d="${pathD}" fill="none" stroke="${colors[k]}" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />`;

                // Render circular point markers
                filtered.forEach(function (d, idx) {
                    const x = getX(idx);
                    const y = getY(d[k]);
                    svgHtml += `<circle cx="${x}" cy="${y}" r="3" fill="#ffffff" stroke="${colors[k]}" stroke-width="2" pointer-events="none" />`;
                });
            });

            // Invisible Interactive Collision Columns & Vertical Crosshairs
            const colWidth = n === 1 ? chartW : chartW / (n - 1);
            filtered.forEach(function (d, idx) {
                const x = getX(idx);
                const leftBound = idx === 0 ? padLeft : x - colWidth / 2;
                const rightBound = idx === n - 1 ? width - padRight : x + colWidth / 2;
                const rectW = Math.max(rightBound - leftBound, 10);

                svgHtml += `
                <g class="chart-col-group" data-idx="${idx}">
                    <line class="chart-hover-line" id="hover-line-${idx}" x1="${x}" y1="${padTop}" x2="${x}" y2="${padTop + chartH}" stroke="#64748b" stroke-width="1.2" stroke-dasharray="2 2" opacity="0"/>
                    <rect class="chart-col-rect" x="${leftBound}" y="${padTop}" width="${rectW}" height="${chartH}" fill="transparent" style="cursor: pointer;"
                          data-idx="${idx}"/>
                </g>`;
            });

            svg.innerHTML = svgHtml;

            // X-Axis Date Ticks
            if (tickBar) {
                tickBar.innerHTML = '';
                const step = n <= 8 ? 1 : Math.ceil(n / 7);
                for (let i = 0; i < n; i += step) {
                    const span = document.createElement('span');
                    span.textContent = filtered[i].label;
                    tickBar.appendChild(span);
                }
                if ((n - 1) % step !== 0) {
                    const lastSpan = document.createElement('span');
                    lastSpan.textContent = filtered[n - 1].label;
                    tickBar.appendChild(lastSpan);
                }
            }

            // Interactive Tooltip Event Handlers
            svg.querySelectorAll('.chart-col-rect').forEach(function (rect) {
                rect.addEventListener('mouseenter', function (e) {
                    const idx = parseInt(this.getAttribute('data-idx'), 10);
                    const item = filtered[idx];
                    if (!item || !tooltip) return;

                    const line = document.getElementById('hover-line-' + idx);
                    if (line) line.setAttribute('opacity', '1');

                    let content = `<div class="fw-bold mb-1 border-bottom pb-1" style="font-size: 0.72rem; color: #f8fafc;">${item.day_name}, ${item.date}</div>`;
                    activeKeys.forEach(function (k) {
                        content += `
                        <div class="d-flex align-items-center justify-content-between gap-3 py-0.5" style="font-size: 0.68rem;">
                            <span class="d-flex align-items-center gap-1.5">
                                <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:${colors[k]};"></span>
                                ${metricLabels[k]}:
                            </span>
                            <strong style="color: #fff;">${item[k]}</strong>
                        </div>`;
                    });
                    tooltip.innerHTML = content;
                    tooltip.style.display = 'block';

                    const frame = document.getElementById('velocityChartFrame');
                    if (frame) {
                        const frameRect = frame.getBoundingClientRect();
                        const mouseX = e.clientX - frameRect.left;
                        const mouseY = e.clientY - frameRect.top;
                        tooltip.style.left = mouseX + 'px';
                        tooltip.style.top = Math.max(mouseY - 10, 30) + 'px';
                    }
                });

                rect.addEventListener('mousemove', function (e) {
                    if (!tooltip) return;
                    const frame = document.getElementById('velocityChartFrame');
                    if (frame) {
                        const frameRect = frame.getBoundingClientRect();
                        const mouseX = e.clientX - frameRect.left;
                        const mouseY = e.clientY - frameRect.top;
                        tooltip.style.left = mouseX + 'px';
                        tooltip.style.top = Math.max(mouseY - 10, 30) + 'px';
                    }
                });

                rect.addEventListener('mouseleave', function () {
                    const idx = parseInt(this.getAttribute('data-idx'), 10);
                    const line = document.getElementById('hover-line-' + idx);
                    if (line) line.setAttribute('opacity', '0');
                    if (tooltip) tooltip.style.display = 'none';
                });
            });
        }

        // Global metric toggler
        window.toggleMetric = function (metricKey) {
            activeMetrics[metricKey] = !activeMetrics[metricKey];
            const btn = document.querySelector(`.att-metric-pill[data-metric="${metricKey}"]`);
            if (btn) {
                btn.classList.toggle('active', activeMetrics[metricKey]);
            }
            renderVelocityChart();
        };

        // Month filter changed
        window.onMonthChange = function (val) {
            currentMonth = val;
            currentWeek = '';
            const weekSelect = document.getElementById('chartWeekSelect');
            if (weekSelect) {
                weekSelect.value = '';
                weekSelect.disabled = !val;
            }
            renderVelocityChart();
        };

        // Week filter changed
        window.onWeekChange = function (val) {
            currentWeek = val;
            renderVelocityChart();
        };

        // Clear all filters back to default
        window.resetChartFilters = function () {
            currentMonth = '';
            currentWeek = '';
            const monthSelect = document.getElementById('chartMonthSelect');
            const weekSelect = document.getElementById('chartWeekSelect');
            if (monthSelect) monthSelect.value = '';
            if (weekSelect) {
                weekSelect.value = '';
                weekSelect.disabled = true;
            }
            renderVelocityChart();
        };

        // Resize listener
        window.addEventListener('resize', renderVelocityChart);

        // Initial chart render
        renderVelocityChart();
    });

    // ── Section Attendance Dual "See All" Toggles & Sorting ──────────────
    let isGraphCollapsed = true;
    let isTableCollapsed = true;

    window.toggleGraphSeeAll = function () {
        isGraphCollapsed = !isGraphCollapsed;
        const extraCards = document.querySelectorAll('.att-graph-extra');
        const btn = document.getElementById('toggleGraphSeeAllBtn');
        extraCards.forEach(c => c.classList.toggle('d-none', isGraphCollapsed));
        if (btn) {
            btn.innerHTML = isGraphCollapsed
                ? `<i class="fas fa-chevron-down me-1"></i> See all (${extraCards.length} more sections)`
                : `<i class="fas fa-chevron-up me-1"></i> Show less`;
        }
    };

    window.toggleTableSeeAll = function () {
        isTableCollapsed = !isTableCollapsed;
        const extraRows = document.querySelectorAll('.att-table-extra');
        const btn = document.getElementById('toggleTableSeeAllBtn');
        extraRows.forEach(r => r.classList.toggle('d-none', isTableCollapsed));
        if (btn) {
            btn.innerHTML = isTableCollapsed
                ? `<i class="fas fa-chevron-down me-1"></i> See all (${extraRows.length} more rows)`
                : `<i class="fas fa-chevron-up me-1"></i> Show less`;
        }
    };

    window.sortSectionData = function (sortBy) {
        const visualList = document.getElementById('sectionVisualList');
        const tableBody = document.querySelector('#sectionDataTable tbody');

        const sortFn = function (a, b) {
            let valA = 0, valB = 0;
            if (sortBy === 'scans') {
                valA = parseFloat(a.dataset.scans || 0);
                valB = parseFloat(b.dataset.scans || 0);
            } else if (sortBy === 'late_rate') {
                valA = parseFloat(a.dataset.lateRate || 0);
                valB = parseFloat(b.dataset.lateRate || 0);
            } else if (sortBy === 'missing') {
                valA = parseFloat(a.dataset.missing || 0);
                valB = parseFloat(b.dataset.missing || 0);
            } else if (sortBy === 'students') {
                valA = parseFloat(a.dataset.students || 0);
                valB = parseFloat(b.dataset.students || 0);
            }
            return valB - valA;
        };

        if (visualList) {
            const cards = Array.from(visualList.children);
            cards.sort(sortFn);
            cards.forEach(function (card, idx) {
                if (idx < 2) {
                    card.classList.remove('att-graph-extra', 'd-none');
                } else {
                    card.classList.add('att-graph-extra');
                    card.classList.toggle('d-none', isGraphCollapsed);
                }
                visualList.appendChild(card);
            });
        }

        if (tableBody) {
            const rows = Array.from(tableBody.querySelectorAll('tr'));
            rows.sort(sortFn);
            rows.forEach(function (row, idx) {
                if (idx < 2) {
                    row.classList.remove('att-table-extra', 'd-none');
                } else {
                    row.classList.add('att-table-extra');
                    row.classList.toggle('d-none', isTableCollapsed);
                }
                tableBody.appendChild(row);
            });
        }
    };

    // ── Phase 4: School Admin IN/OUT Intervention Action Hook ──
    window.initiateInOutIntervention = function (enrollmentId, studentName, reasonType, count) {
        // Dismiss open detail modal if any
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(m => {
            const bsModal = bootstrap.Modal.getInstance(m);
            if (bsModal) bsModal.hide();
        });

        if (typeof window.openInOutInterventionModal === 'function') {
            window.openInOutInterventionModal(enrollmentId, studentName, reasonType, count);
        }
    };
</script>

{{-- Phase 4: School Admin IN/OUT Intervention Modal --}}
@include('pov.school-admin.attendance-analytics.partials.in-out-intervention-modal')

@endif
