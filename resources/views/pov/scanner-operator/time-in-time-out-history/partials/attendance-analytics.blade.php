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

    /* ── 4. GRAPH TYPE: Dual Line Chart with Area Shading ── */
    .att-chart-frame {
        position: relative;
        height: 135px;
        border-radius: 0.55rem;
        border: 1px solid #edf2f7;
        background: linear-gradient(180deg, #fbfcfe, #fff);
        padding: 0.3rem;
        overflow: hidden;
    }
    .att-chart-svg { width: 100%; height: 100%; display: block; }
    .att-grid-line { stroke: #f1f5f9; stroke-width: 1; stroke-dasharray: 3 3; }
    .att-line { fill: none; stroke-width: 1.75px; stroke-linecap: round; stroke-linejoin: round; }
    .att-area { stroke: none; opacity: 0.08; pointer-events: none; }
    .att-tick-bar { display: flex; justify-content: space-between; font-size: 0.62rem; color: #64748b; padding-top: 0.2rem; }

    /* ── Functional Action Directives ── */
    .att-actions-grid {
        display: grid;
        grid-template-columns: 1fr;
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
         1. CORE ATTENDANCE KPIS
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-kpi-row">
        <div class="att-kpi att-kpi--info">
            <div class="att-kpi__label">Students Attending</div>
            <div class="att-kpi__val">{{ number_format($a['uniqueStudentsIn']) }}</div>
            <div class="att-kpi__sub">{{ number_format($a['totalScans']) }} total gate scans</div>
        </div>

        @if ($a['attendanceRate'] !== null)
        <div class="att-kpi att-kpi--success">
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

        <div class="att-kpi att-kpi--warning">
            <div class="att-kpi__label">Late Arrivals</div>
            <div class="att-kpi__val">{{ number_format($a['lateCount']) }}</div>
            <div class="att-kpi__sub">{{ $a['lateRate'] !== null ? $a['lateRate'] . '% of arrivals' : '0%' }}</div>
        </div>

        <div class="att-kpi att-kpi--info">
            <div class="att-kpi__label">Avg Arrival Time</div>
            <div class="att-kpi__val">{{ $a['avgArrival'] ?? '—' }}</div>
            <div class="att-kpi__sub">Velocity: {{ $a['rushVelocity'] ?? '0' }} scans/min</div>
        </div>

        <div class="att-kpi att-kpi--danger">
            <div class="att-kpi__label">Missing OUT</div>
            <div class="att-kpi__val">{{ number_format($a['missingOutCount']) }}</div>
            <div class="att-kpi__sub">{{ $a['checkoutIntegrityRate'] }}% exit completion</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         2. DIVERSE VISUAL GRAPHS (DONUT, HORIZONTAL BARS, VERTICAL COLUMNS)
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

        {{-- GRAPH 2: Horizontal Bar Progress Metrics (Grade Level Breakdown) --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-layer-group text-info"></i> Attendance by Grade Level
                </h4>
                <span class="att-panel__meta">{{ $a['gradeLevelData']->count() }} Grade Levels</span>
            </div>

            @php
                $maxGradeStudents = max($a['gradeLevelData']->max('students') ?? 1, 1);
            @endphp

            <div class="att-hbar-list">
                @forelse ($a['gradeLevelData'] as $g)
                    @php
                        $gPct = round(($g['students'] / $maxGradeStudents) * 100, 1);
                    @endphp
                    <div class="att-hbar-item">
                        <div class="att-hbar-item__head">
                            <span>{{ $g['label'] }}</span>
                            <span>{{ $g['students'] }} students <small class="text-muted fw-normal">({{ $g['late_count'] }} late &bull; {{ $g['late_rate'] }}%)</small></span>
                        </div>
                        <div class="att-hbar-item__track">
                            <div class="att-hbar-item__fill" style="width: {{ $gPct }}%; background: #3b82f6;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small py-3 text-center">No grade level data available.</div>
                @endforelse
            </div>
        </div>

        {{-- GRAPH 3: Vertical Column Timeline (Hourly Gate Traffic & Rush Surge) --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-chart-column text-warning"></i> Hourly Gate Traffic Density
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
         3. DAILY MOVEMENT LINE CHART & FUNCTIONAL ACTION DIRECTIVES
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-grid-2">
        {{-- GRAPH 4: Daily IN/OUT Movement Dual-Line Graph --}}
        @if ($a['dailyTrend']->count() > 0)
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-chart-line text-success"></i> Daily Scan Velocity & Movement Trends
                </h4>
                <span class="att-panel__meta">{{ $a['dayCount'] }} days analyzed</span>
            </div>

            <div class="d-flex gap-3 mb-2" style="font-size: 0.7rem; font-weight: 600;">
                <span><span class="d-inline-block rounded-circle" style="width: 0.5rem; height: 0.5rem; background:#10b981"></span> Time In Volume</span>
                <span><span class="d-inline-block rounded-circle" style="width: 0.5rem; height: 0.5rem; background:#3b82f6"></span> Time Out Volume</span>
            </div>

            <div class="att-chart-frame">
                <svg class="att-chart-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <line x1="0" y1="30" x2="100" y2="30" class="att-grid-line" />
                    <line x1="0" y1="50" x2="100" y2="50" class="att-grid-line" />
                    <line x1="0" y1="70" x2="100" y2="70" class="att-grid-line" />
                    <line x1="0" y1="90" x2="100" y2="90" stroke="#e2e8f0" stroke-width="1" />

                    @if ($a['inArea'])
                        <path d="{{ $a['inArea'] }}" class="att-area" style="fill:#10b981"></path>
                    @endif
                    @if ($a['outArea'])
                        <path d="{{ $a['outArea'] }}" class="att-area" style="fill:#3b82f6"></path>
                    @endif
                    @if ($a['inPath'])
                        <path d="{{ $a['inPath'] }}" class="att-line" style="stroke:#10b981"></path>
                    @endif
                    @if ($a['outPath'])
                        <path d="{{ $a['outPath'] }}" class="att-line" style="stroke:#3b82f6"></path>
                    @endif
                </svg>
            </div>

            <div class="att-tick-bar">
                @foreach ($a['trendTicks'] as $tick)
                    <span>{{ $tick }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Functional Administrative Action Directives --}}
        <div class="att-panel">
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
                            <span class="text-muted small" style="font-size: 0.68rem;">Click to execute action</span>
                            <button type="button" class="att-action-btn att-action-btn--{{ $action['priority_tone'] }}"
                                    data-bs-toggle="modal" data-bs-target="{{ $action['modal_target'] ?? '#downloadAnalyticsModal' }}">
                                <span>{{ $action['action_label'] }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         4. DETAILED DATA MATRICES & DRILLDOWN MODALS
         ════════════════════════════════════════════════════════════════ --}}
    <div class="att-grid-2">
        {{-- Section Disparities Matrix with Clarity Show More --}}
        @if ($a['sectionData']->isNotEmpty())
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-users text-primary"></i> Section Attendance & Late Entry Breakdown
                </h4>
                <span class="text-muted" style="font-size: 0.7rem;">{{ $a['sectionData']->count() }} Sections</span>
            </div>

            <div class="att-table-wrap">
                <table class="att-table">
                    <thead>
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
                        <tr class="{{ $sidx >= 5 ? 'att-section-extra-row d-none' : '' }}">
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

            @if ($a['sectionData']->count() > 5)
                <button type="button" class="att-toggle-btn" id="toggleSectionRowsBtn" onclick="
                    var srows = document.querySelectorAll('.att-section-extra-row');
                    var isHidden = srows[0].classList.contains('d-none');
                    srows.forEach(r => r.classList.toggle('d-none'));
                    this.innerHTML = isHidden ? '<i class=\'fas fa-chevron-up me-1\'></i> Show less' : '<i class=\'fas fa-chevron-down me-1\'></i> Show more ({{ $a['sectionData']->count() - 5 }} more sections)';
                ">
                    <i class="fas fa-chevron-down me-1"></i> Show more ({{ $a['sectionData']->count() - 5 }} more sections)
                </button>
            @endif
        </div>
        @endif

        {{-- Multiple Late Arrivals Table with Clarity Show More & Modal Drilldown --}}
        <div class="att-panel">
            <div class="att-panel__head">
                <h4 class="att-panel__title">
                    <i class="fas fa-clock text-warning"></i> Students with Multiple Late Arrivals
                </h4>
                <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold" style="font-size: 0.7rem;">
                    {{ $a['frequentLateStudents']->count() }} Students (&ge; 2 Late Scans)
                </span>
            </div>

            @if ($a['frequentLateStudents']->isNotEmpty())
                <div class="att-table-wrap">
                    <table class="att-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Grade & Section</th>
                                <th style="text-align:center">Late Count</th>
                                <th style="text-align:center">Status</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($a['frequentLateStudents'] as $idx => $st)
                            <tr class="att-clickable-row {{ $idx >= 5 ? 'att-late-extra-row d-none' : '' }}"
                                data-bs-toggle="modal" data-bs-target="#studentLateModal{{ $st['enrollment_id'] }}">
                                <td>
                                    <strong>{{ $st['student_name'] }}</strong>
                                    <span class="text-muted d-block" style="font-size:0.65rem">{{ $st['student_number'] }}</span>
                                </td>
                                <td>Grade {{ $st['grade_level'] }} — {{ $st['section_name'] }}</td>
                                <td style="text-align:center" class="fw-bold">{{ $st['late_count'] }}</td>
                                <td style="text-align:center">
                                    <span class="att-badge att-badge--{{ $st['badge_tone'] ?? 'warning' }}">
                                        {{ $st['badge_label'] ?? ($st['late_count'] . ' Late Entries') }}
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5" style="font-size: 0.65rem;"
                                            data-bs-toggle="modal" data-bs-target="#studentLateModal{{ $st['enrollment_id'] }}">
                                        <i class="fas fa-eye me-1"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($a['frequentLateStudents']->count() > 5)
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <button type="button" class="att-toggle-btn" id="toggleLateRowsBtn" onclick="
                            var rows = document.querySelectorAll('.att-late-extra-row');
                            var isHidden = rows[0].classList.contains('d-none');
                            rows.forEach(r => r.classList.toggle('d-none'));
                            this.innerHTML = isHidden ? '<i class=\'fas fa-chevron-up me-1\'></i> Show less' : '<i class=\'fas fa-chevron-down me-1\'></i> Show more ({{ $a['frequentLateStudents']->count() - 5 }} more)';
                        ">
                            <i class="fas fa-chevron-down me-1"></i> Show more ({{ $a['frequentLateStudents']->count() - 5 }} more)
                        </button>
                        <span class="text-muted" style="font-size: 0.68rem;">Click row for details</span>
                    </div>
                @endif
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                    <p class="mb-0" style="font-size: 0.78rem;">No repeated late arrivals recorded in this timeframe.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         5. UNRECORDED DEPARTURES (MISSING OUT RECONCILIATION)
         ════════════════════════════════════════════════════════════════ --}}
    @if ($a['missingOutCount'] > 0)
    <div class="att-panel">
        <div class="att-panel__head">
            <h4 class="att-panel__title text-danger">
                <i class="fas fa-exclamation-triangle"></i> Unrecorded Departures (Missing OUT Scans)
            </h4>
            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold" style="font-size: 0.7rem;">
                {{ $a['missingOutCount'] }} Incomplete Records
            </span>
        </div>

        <div class="att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Grade & Section</th>
                        <th>Scan Date</th>
                        <th>IN Timestamp</th>
                        <th>Session</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($a['missingOutSample'] as $midx => $mo)
                    <tr class="{{ $midx >= 5 ? 'att-missing-extra-row d-none' : '' }}">
                        <td>
                            <strong>{{ $mo['student_name'] }}</strong>
                            <span class="text-muted d-block" style="font-size:0.65rem">{{ $mo['student_number'] }}</span>
                        </td>
                        <td>Grade {{ $mo['grade_level'] }} — {{ $mo['section_name'] }}</td>
                        <td>{{ $mo['date'] }}</td>
                        <td class="fw-bold">{{ $mo['in_time'] }}</td>
                        <td>{{ $mo['session_type'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($a['missingOutSample']->count() > 5)
            <button type="button" class="att-toggle-btn" id="toggleMissingRowsBtn" onclick="
                var mrows = document.querySelectorAll('.att-missing-extra-row');
                var isHidden = mrows[0].classList.contains('d-none');
                mrows.forEach(r => r.classList.toggle('d-none'));
                this.innerHTML = isHidden ? '<i class=\'fas fa-chevron-up me-1\'></i> Show less' : '<i class=\'fas fa-chevron-down me-1\'></i> Show more ({{ $a['missingOutSample']->count() - 5 }} more)';
            ">
                <i class="fas fa-chevron-down me-1"></i> Show more ({{ $a['missingOutSample']->count() - 5 }} more)
            </button>
        @endif
    </div>
    @endif

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
                <a href="{{ route('time-in-time-out-history.index', ['query' => $sectionAction['section_name'], 'flag_type' => 'late_arrival']) }}" class="btn btn-primary btn-sm">
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
                        <h6 class="modal-title fw-bold mb-0">Gate Queue & Staffing Directives</h6>
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
                        <div><strong>Deploy Secondary Scanner Operator:</strong> Station an additional staff member at the gate 10 minutes prior to {{ explode('–', $gateAction['rush_window'])[0] ?? 'surge' }}.</div>
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
                <a href="{{ route('time-in-time-out-history.index', ['scan_type' => 'IN']) }}" class="btn btn-primary btn-sm">
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
                    <div class="table-responsive border rounded-3">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.75rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Gate In Time</th>
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
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
</script>

@endif
