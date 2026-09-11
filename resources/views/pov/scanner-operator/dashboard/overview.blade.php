<x-layouts.scanner-operator>
    <x-slot name="title">Dashboard</x-slot>

    <x-slot name="subtitle">Today’s attendance and school activity at a glance.</x-slot>

    <x-slot name="pageName">Dashboard</x-slot>

    @php
        $peakLabel = $peakBucket['range'] ?? 'No scans yet';
        $peakTotal = $peakBucket['total'] ?? 0;
        $chartBucketCount = max(count($scanBuckets ?? []), 1);

        // Catmull-Rom -> Bezier smooth curve generator
        $toSmoothPath = function (array $pts): string {
            $n = count($pts);
            if ($n === 0) return '';
            if ($n === 1) return 'M ' . $pts[0]['x'] . ',' . $pts[0]['y'];
            $d = 'M ' . $pts[0]['x'] . ',' . $pts[0]['y'];
            for ($i = 0; $i < $n - 1; $i++) {
                $p0 = $pts[$i - 1] ?? $pts[$i];
                $p1 = $pts[$i];
                $p2 = $pts[$i + 1];
                $p3 = $pts[$i + 2] ?? $p2;
                $c1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
                $c1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
                $c2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
                $c2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;
                $d .= ' C ' . round($c1x, 2) . ',' . round($c1y, 2)
                    . ' ' . round($c2x, 2) . ',' . round($c2y, 2)
                    . ' ' . $p2['x'] . ',' . $p2['y'];
            }
            return $d;
        };

        // Time-In and Time-Out lines for daily attendance
        $timelineSeries = [
            'in' => [
                'key' => 'in',
                'label' => 'Time-In',
                'color' => '#10b981',
                'field' => 'time_in',
                'total' => $timeInToday,
            ],
            'out' => [
                'key' => 'out',
                'label' => 'Time-Out',
                'color' => '#3b82f6',
                'field' => 'time_out',
                'total' => $timeOutToday,
            ],
        ];

        $dailyTimelineLines = collect($timelineSeries)->map(function ($series) use ($scanBuckets, $chartBucketCount, $maxBucketTotal, $toSmoothPath) {
            $span = max($chartBucketCount - 1, 1);
            $points = [];
            foreach (($scanBuckets ?? []) as $i => $b) {
                $x = 3 + ($i / $span) * 94;
                $count = $b[$series['field']] ?? 0;
                $ratio = $maxBucketTotal > 0 ? ($count / $maxBucketTotal) : 0;
                $y = 8 + (1 - $ratio) * 84;
                $points[] = ['x' => round($x, 2), 'y' => round($y, 2)];
            }
            $path = $toSmoothPath($points);
            $area = $path !== '' ? $path . ' L 97 92 L 3 92 Z' : '';
            return [
                'key' => $series['key'],
                'label' => $series['label'],
                'color' => $series['color'],
                'path' => $path,
                'area' => $area,
                'total' => $series['total'],
            ];
        })->values();

        $weeklyChartBuckets = collect($weeklyScanBuckets ?? [])->values();
        $weeklyChartBucketCount = max($weeklyChartBuckets->count(), 1);

        // Weekly department lines
        $weeklyLevelLines = collect($departmentLevels ?? [])
            ->filter(fn($meta, $level) => $level !== 'unknown')
            ->map(function ($meta, $level) use ($weeklyChartBuckets, $weeklyChartBucketCount, $weeklyMaxBucketTotal, $toSmoothPath) {
                $gradeKeys = array_map('strval', match ($level) {
                    'elementary' => [1, 2, 3, 4, 5, 6],
                    'highschool' => [7, 8, 9, 10],
                    'senior_high_school' => [11, 12],
                    default => [],
                });
                $span = max($weeklyChartBucketCount - 1, 1);
                $points = [];
                $totalForLevel = 0;
                foreach ($weeklyChartBuckets as $i => $week) {
                    $grades = $week['grades'] ?? [];
                    $count = 0;
                    foreach ($gradeKeys as $gk) {
                        $count += $grades[$gk] ?? 0;
                    }
                    $totalForLevel += $count;
                    $x = 4 + ($i / $span) * 92;
                    $ratio = $weeklyMaxBucketTotal > 0 ? ($count / $weeklyMaxBucketTotal) : 0;
                    $y = 8 + (1 - $ratio) * 84;
                    $points[] = ['x' => round($x, 2), 'y' => round($y, 2)];
                }
                $path = $toSmoothPath($points);
                $area = $path !== '' ? $path . ' L 96 92 L 4 92 Z' : '';
                return [
                    'level' => $level,
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'path' => $path,
                    'area' => $area,
                    'total' => $totalForLevel,
                ];
            })
            ->values();

        $weeklyChartPeakBucket = $weeklyPeakBucket ?? $weeklyChartBuckets->sortByDesc('total')->first();
        $weeklyChartPeakLabel = $weeklyChartPeakBucket['range'] ?? 'No scans yet';
        $weeklyChartPeakTotal = $weeklyChartPeakBucket['total'] ?? 0;
        $weeklyChartAverage = $weeklyAverage ?? round($weeklyChartBuckets->avg('total') ?? 0, 1);
        $weeklyChartTotal = $weeklyTotalScans ?? $weeklyChartBuckets->sum('total');
        $weeklyTrend = $weeklyTrend ?? null;
        if ($weeklyTrend === null && $weeklyChartBuckets->count() >= 2) {
            $prevWeek = $weeklyChartBuckets->get($weeklyChartBuckets->count() - 2)['total'] ?? 0;
            $currWeek = $weeklyChartBuckets->last()['total'] ?? 0;
            if ($prevWeek > 0) {
                $weeklyTrend = round((($currWeek - $prevWeek) / $prevWeek) * 100, 1);
            } elseif ($currWeek > 0) {
                $weeklyTrend = 100.0;
            }
        }

        // Select 5 evenly spaced milestone labels for daily timeline ticks to prevent overlap
        $dailyTickMilestones = collect($scanBuckets ?? []);
        $tickIndices = [];
        if ($dailyTickMilestones->count() > 1) {
            $totalB = $dailyTickMilestones->count();
            $tickIndices = [
                0,
                (int) round($totalB * 0.25),
                (int) round($totalB * 0.5),
                (int) round($totalB * 0.75),
                $totalB - 1,
            ];
            $tickIndices = array_unique($tickIndices);
        }
    @endphp

    <style>
        .dashboard-shell {
            display: grid;
            gap: 1rem;
        }

        /* ===== Metric Stat Cards ===== */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
        }

        @media (max-width: 1199px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-card {
            display: block;
            text-decoration: none;
            color: inherit;
            background: #fff;
            border: 1px solid #e9eef5;
            border-radius: 1rem;
            padding: 0.85rem 1rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            border-color: #cbd5e1;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            color: inherit;
        }

        .dashboard-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .dashboard-card__icon {
            width: 2.65rem;
            height: 2.65rem;
            border-radius: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .card-tone-primary .dashboard-card__icon {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .card-tone-success .dashboard-card__icon {
            background: linear-gradient(135deg, #10b981, #047857);
        }

        .card-tone-dark .dashboard-card__icon {
            background: linear-gradient(135deg, #1e293b, #0f172a);
        }

        .card-tone-indigo .dashboard-card__icon {
            background: linear-gradient(135deg, #6366f1, #4338ca);
        }

        .dashboard-card__label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 0.2rem;
            font-weight: 700;
        }

        .dashboard-card__value {
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .dashboard-card__hint {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== Analytics & Layout Grid ===== */
        .dashboard-analytics-grid,
        .dashboard-lower-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
            gap: 1rem;
        }

        @media (max-width: 1024px) {
            .dashboard-analytics-grid,
            .dashboard-lower-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: #fff;
            border: 1px solid #e9eef5;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
            min-width: 0;
        }

        .panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
        }

        .panel__title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .panel__meta {
            margin: 0.1rem 0 0;
            font-size: 0.75rem;
            color: #64748b;
        }

        /* ===== Refined, Compact Metric Chips ===== */
        .chart-stat-strip {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            margin-bottom: 0.75rem;
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.55rem;
            border-radius: 0.55rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            font-size: 0.72rem;
            color: #475569;
            line-height: 1.2;
        }

        .stat-pill strong {
            font-weight: 800;
            color: #0f172a;
        }

        .stat-pill--success {
            background: #ecfdf5;
            border-color: #d1fae5;
            color: #065f46;
        }
        .stat-pill--success strong {
            color: #047857;
        }

        .stat-pill--warning {
            background: #fffbeb;
            border-color: #fef3c7;
            color: #92400e;
        }
        .stat-pill--warning strong {
            color: #b45309;
        }

        .stat-pill--info {
            background: #eff6ff;
            border-color: #dbeafe;
            color: #1e40af;
        }
        .stat-pill--info strong {
            color: #1d4ed8;
        }

        /* ===== Chart style toggle (Bars / Line / Table) ===== */
        .chart-view-toggle {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .chart-view-toggle__btn {
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .chart-view-toggle__btn:hover {
            color: #0f172a;
        }

        .chart-view-toggle__btn.active {
            background: #fff;
            color: #4338ca;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.1);
        }

        /* ===== Sleek Chart Legend ===== */
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.5rem;
        }

        .chart-legend__item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.25rem 0.55rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
        }

        .chart-legend__swatch {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .chart-legend__value {
            font-weight: 700;
            color: #0f172a;
        }

        /* ===== Line Chart (Crisp, Thin, Clean, No Circles) ===== */
        .line-chart__frame {
            position: relative;
            height: 175px;
            border-radius: 0.75rem;
            border: 1px solid #edf2f7;
            background: linear-gradient(180deg, #fbfcfe 0%, #ffffff 100%);
            padding: 0.35rem;
            overflow: hidden;
        }

        .line-chart__svg {
            width: 100%;
            height: 100%;
            display: block;
            overflow: visible;
        }

        .line-chart__grid-line {
            stroke: #f1f5f9;
            stroke-width: 1;
            stroke-dasharray: 3 3;
        }

        .line-chart__guide-text {
            fill: #94a3b8;
            font-size: 3.2px;
            font-weight: 600;
            font-family: inherit;
        }

        .sp-line__stroke {
            fill: none;
            stroke-width: 1.25px;
            stroke-linecap: round;
            stroke-linejoin: round;
            vector-effect: non-scaling-stroke;
            transition: stroke-width 0.15s ease;
        }

        .sp-line__stroke:hover {
            stroke-width: 2px;
        }

        .sp-line__area {
            stroke: none;
            opacity: 0.06;
            pointer-events: none;
        }

        /* ===== Horizontal Ticks without Overlap ===== */
        .chart-tick-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.25rem 0.2rem 0;
            font-size: 0.65rem;
            color: #64748b;
            font-weight: 600;
            line-height: 1;
        }

        /* ===== Clean Stacked Bar Chart ===== */
        .scan-chart {
            overflow: visible;
            padding-bottom: 0.1rem;
        }

        .scan-chart__inner {
            width: 100%;
            display: grid;
            align-items: end;
            gap: 0.25rem;
            height: 175px;
            padding-top: 0.25rem;
        }

        .scan-chart__col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            min-width: 0;
            position: relative;
        }

        .scan-chart__bar-wrap {
            position: relative;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            height: 155px;
        }

        .scan-chart__bar {
            width: 100%;
            max-width: 32px;
            border-radius: 4px;
            background: #edf2f7;
            overflow: hidden;
            display: flex;
            flex-direction: column-reverse;
            justify-content: flex-start;
            transition: opacity 0.15s ease;
        }

        .scan-chart__col:hover .scan-chart__bar {
            opacity: 0.85;
        }

        .scan-chart__segment {
            width: 100%;
        }

        .scan-chart__tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 8px);
            transform: translate(-50%, 4px);
            min-width: 170px;
            max-width: 220px;
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            border-radius: 0.65rem;
            padding: 0.6rem 0.75rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease, transform 0.15s ease;
            z-index: 30;
            text-align: left;
            white-space: normal;
        }

        .scan-chart__col:hover .scan-chart__tooltip,
        .scan-chart__bar-wrap:hover .scan-chart__tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .scan-chart__tooltip-title {
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 0.25rem;
        }

        .scan-chart__tooltip-total {
            font-size: 0.85rem;
            font-weight: 800;
            color: #38bdf8;
            margin-bottom: 0.35rem;
        }

        .scan-chart__tooltip-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 0.2rem;
        }

        .scan-chart__tooltip-key {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .scan-chart__tooltip-swatch {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        /* ===== Table View ===== */
        .chart-table-wrap {
            max-height: 220px;
            overflow: auto;
            border: 1px solid #edf2f7;
            border-radius: 0.65rem;
        }

        .chart-table {
            width: 100%;
            font-size: 0.75rem;
            border-collapse: collapse;
        }

        .chart-table th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 0.45rem 0.65rem;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }

        .chart-table td {
            padding: 0.35rem 0.65rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            text-align: right;
        }

        .chart-table td:first-child {
            text-align: left;
            font-weight: 600;
        }

        .chart-table tbody tr:hover {
            background: #f8fafc;
        }

        .chart-table__total {
            font-weight: 700;
            color: #0f172a;
        }

        /* ===== Grade Level Distribution Panel (Compact & Modern) ===== */
        .grade-distribution-container {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: 1rem;
            align-items: center;
        }

        @media (max-width: 575px) {
            .grade-distribution-container {
                grid-template-columns: 1fr;
                justify-items: center;
            }
        }

        .grade-donut__wrap {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto;
            border-radius: 50%;
            display: grid;
            place-items: center;
        }

        .grade-donut__ring {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
        }

        .grade-donut__ring::after {
            content: "";
            position: absolute;
            inset: 22%;
            border-radius: 50%;
            background: #fff;
            box-shadow: inset 0 0 0 1px #f1f5f9;
        }

        .grade-donut__center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            z-index: 1;
            pointer-events: none;
        }

        .grade-donut__center strong {
            font-size: 1.25rem;
            line-height: 1;
            font-weight: 800;
            color: #0f172a;
        }

        .grade-donut__center span {
            margin-top: 0.15rem;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            font-weight: 700;
        }

        .grade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 0.4rem;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 0.2rem;
        }

        .grade-chip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.35rem 0.55rem;
            border-radius: 0.6rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            font-size: 0.72rem;
        }

        .grade-chip__left {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-width: 0;
            color: #334155;
            font-weight: 600;
        }

        .grade-chip__swatch {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .grade-chip__right {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.7rem;
            white-space: nowrap;
        }

        /* ===== Recent Scans Table ===== */
        .table-card .table {
            font-size: 0.78rem;
            margin-bottom: 0;
        }

        .table-card .table th {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 0.5rem 0.65rem;
            border-bottom: 1px solid #edf2f7;
        }

        .table-card .table td {
            padding: 0.45rem 0.65rem;
            vertical-align: middle;
        }

        .table-name-avatar {
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.55rem;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .table-name-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-name-main {
            font-weight: 700;
            color: #0f172a;
            display: block;
            line-height: 1.2;
        }

        .table-name-sub {
            font-size: 0.68rem;
            color: #64748b;
            display: block;
        }

        .badge-dot {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
        }

        .badge-dot::before {
            content: "";
            width: 0.38rem;
            height: 0.38rem;
            border-radius: 50%;
        }

        .dot-success {
            background: #ecfdf5;
            color: #065f46;
        }
        .dot-success::before {
            background: #10b981;
        }

        .dot-primary {
            background: #eff6ff;
            color: #1e40af;
        }
        .dot-primary::before {
            background: #3b82f6;
        }

        .dot-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .dot-secondary::before {
            background: #94a3b8;
        }
    </style>

    <div class="dashboard-shell mx-2 mb-3" data-attendance-realtime
        data-resync-url="{{ route('scanner.attendance.monitoring.resync') }}">
        {{-- Top KPI Cards --}}
        <section class="dashboard-cards">
            @foreach ($dashboardCards as $card)
                <a href="{{ $card['href'] }}" class="dashboard-card card-tone-{{ $card['tone'] }}">
                    <div class="dashboard-card__top">
                        <div>
                            <div class="dashboard-card__label">{{ $card['label'] }}</div>
                            <div class="dashboard-card__value"
                                data-realtime-card="{{ strtolower(str_replace(' ', '-', $card['label'])) }}">{{ $card['value'] }}</div>
                        </div>
                        <div class="dashboard-card__icon">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                    </div>
                    <div class="dashboard-card__hint">{{ $card['hint'] }}</div>
                </a>
            @endforeach
        </section>        {{-- Main Attendance Analytics Grid --}}
        <section class="dashboard-analytics-grid">
            {{-- Panel 1: Today's IN & OUT Timeline --}}
            <div class="panel" data-chart-timeline data-timeline-buckets='@json($scanBuckets)'
                data-timeline-interval="{{ $intervalMinutes ?? 15 }}"
                data-timeline-start="{{ isset($chartStart) ? $chartStart->format('H:i') : '06:00' }}"
                data-max-total="{{ $maxBucketTotal }}">
                <div class="panel__header">
                    <div>
                        <h2 class="panel__title">Today's Attendance Timeline
                            <span data-realtime-live-dot class="realtime-live-dot" title="Realtime updates active">&#9679; LIVE</span>
                        </h2>
                        <p class="panel__meta">Today's check-in and check-out activity grouped by time.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" style="font-size: 0.72rem; border-radius: 999px;" data-bs-toggle="modal" data-bs-target="#timelineScannedStudentsModal">
                            <i class="fas fa-list-check me-1"></i> View Scanned Students
                        </button>
                        <div class="chart-view-toggle" data-chart-toggle="daily" role="group" aria-label="Chart style">
                            <button type="button" class="chart-view-toggle__btn active" data-chart-view="line">Line</button>
                            <button type="button" class="chart-view-toggle__btn" data-chart-view="bars">Bars</button>
                            <button type="button" class="chart-view-toggle__btn" data-chart-view="table">Table</button>
                        </div>
                    </div>
                </div>

                {{-- Key Quick Insights --}}
                <div class="chart-stat-strip">
                    <span class="stat-pill stat-pill--info">
                        Today: <strong data-realtime-card="scans-today">{{ number_format($totalScansToday) }} scans</strong>
                    </span>
                    <span class="stat-pill stat-pill--success">
                        In: <strong data-realtime-card="time-in">{{ number_format($timeInToday) }}</strong> · Out: <strong data-realtime-card="time-out">{{ number_format($timeOutToday) }}</strong>
                    </span>
                    @if ($lateArrivalsToday > 0)
                        <span class="stat-pill stat-pill--warning">
                            Late: <strong data-realtime-card="late">{{ $lateArrivalsToday }}</strong> ({{ round(100 - $onTimePercentage, 1) }}%)
                        </span>
                    @else
                        <span class="stat-pill stat-pill--success">
                            On-time: <strong>100%</strong>
                        </span>
                    @endif
                    <span class="stat-pill">
                        Peak: <strong data-timeline-peak>{{ $peakLabel }} ({{ $peakTotal }})</strong>
                    </span>
                </div>

                {{-- Interactive Views --}}
                <div class="chart-views" data-chart-views="daily">
                    {{-- Line View (Default) --}}
                    <div class="chart-view" data-view="line">
                        <div class="chart-legend">
                            @foreach ($dailyTimelineLines as $ll)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $ll['color'] }}"></span>
                                    <span>{{ $ll['label'] }}:</span>
                                    <span class="chart-legend__value" data-timeline-legend="{{ $ll['key'] }}">{{ $ll['total'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="line-chart__frame">
                            <svg class="line-chart__svg" viewBox="0 0 100 100" preserveAspectRatio="none" role="img"
                                aria-label="Today's scan volume trend by Time-In and Time-Out">
                                {{-- Subtle Horizontal Grid Guides --}}
                                <line x1="0" y1="29" x2="100" y2="29" class="line-chart__grid-line" />
                                <line x1="0" y1="50" x2="100" y2="50" class="line-chart__grid-line" />
                                <line x1="0" y1="71" x2="100" y2="71" class="line-chart__grid-line" />
                                <line x1="0" y1="92" x2="100" y2="92" stroke="#e2e8f0" stroke-width="1" />

                                <text x="1" y="27" class="line-chart__guide-text" data-timeline-guide="75">{{ (int) round($maxBucketTotal * 0.75) }}</text>
                                <text x="1" y="48" class="line-chart__guide-text" data-timeline-guide="50">{{ (int) round($maxBucketTotal * 0.5) }}</text>
                                <text x="1" y="69" class="line-chart__guide-text" data-timeline-guide="25">{{ (int) round($maxBucketTotal * 0.25) }}</text>

                                {{-- Render line paths --}}
                                @foreach ($dailyTimelineLines as $ll)
                                    @if (!empty($ll['area']))
                                        <path d="{{ $ll['area'] }}" class="sp-line__area" data-timeline-area="{{ $ll['key'] }}"
                                            style="fill: {{ $ll['color'] }}"></path>
                                    @endif
                                    <path d="{{ $ll['path'] }}" class="sp-line__stroke" data-timeline-stroke="{{ $ll['key'] }}"
                                        style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke">
                                        <title>{{ $ll['label'] }} ({{ $ll['total'] }} total)</title>
                                    </path>
                                @endforeach
                            </svg>
                        </div>

                        {{-- Horizontal Milestone Ticks (Evenly Spaced, No Overlap) --}}
                        <div class="chart-tick-bar">
                            @foreach ($tickIndices as $idx)
                                @if (isset($scanBuckets[$idx]))
                                    <span>{{ $scanBuckets[$idx]['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Bars View --}}
                    <div class="chart-view d-none" data-view="bars">
                        <div class="chart-legend">
                            <div class="chart-legend__item">
                                <span class="chart-legend__swatch" style="background: #10b981"></span>
                                <span>Time-In:</span>
                                <span class="chart-legend__value" data-timeline-legend="in">{{ $timeInToday }}</span>
                            </div>
                            <div class="chart-legend__item">
                                <span class="chart-legend__swatch" style="background: #3b82f6"></span>
                                <span>Time-Out:</span>
                                <span class="chart-legend__value" data-timeline-legend="out">{{ $timeOutToday }}</span>
                            </div>
                        </div>

                        <div class="scan-chart">
                            <div class="scan-chart__inner"
                                style="grid-template-columns: repeat({{ $chartBucketCount }}, minmax(0, 1fr));">
                                @foreach ($scanBuckets as $i => $bucket)
                                    @php
                                        $bucketTotal = $bucket['total'] ?? 0;
                                        $bucketIn = $bucket['time_in'] ?? 0;
                                        $bucketOut = $bucket['time_out'] ?? 0;
                                        $bucketHeight = $bucketTotal > 0
                                            ? max((int) round(($bucketTotal / $maxBucketTotal) * 145), 4)
                                            : 2;
                                        $inHeight = $bucketTotal > 0 ? max((int) round(($bucketIn / $bucketTotal) * $bucketHeight), $bucketIn > 0 ? 2 : 0) : 0;
                                        $outHeight = $bucketTotal > 0 ? max((int) round(($bucketOut / $bucketTotal) * $bucketHeight), $bucketOut > 0 ? 2 : 0) : 0;
                                    @endphp
                                    <div class="scan-chart__col" data-bucket-idx="{{ $i }}">
                                        <div class="scan-chart__bar-wrap" tabindex="0" aria-label="{{ $bucket['range'] }}">
                                            <div class="scan-chart__tooltip">
                                                <div class="scan-chart__tooltip-title">{{ $bucket['range'] }}</div>
                                                <div class="scan-chart__tooltip-total" data-tooltip-total>{{ $bucketTotal }} scans</div>
                                                <div class="scan-chart__tooltip-row">
                                                    <span class="scan-chart__tooltip-key">
                                                        <span class="scan-chart__tooltip-swatch" style="background: #10b981"></span>
                                                        <span>Time-In</span>
                                                    </span>
                                                    <strong data-tooltip-in>{{ $bucketIn }}</strong>
                                                </div>
                                                <div class="scan-chart__tooltip-row">
                                                    <span class="scan-chart__tooltip-key">
                                                        <span class="scan-chart__tooltip-swatch" style="background: #3b82f6"></span>
                                                        <span>Time-Out</span>
                                                    </span>
                                                    <strong data-tooltip-out>{{ $bucketOut }}</strong>
                                                </div>
                                            </div>

                                            <div class="scan-chart__bar" style="height: {{ $bucketHeight }}px;" data-bar-wrap>
                                                @if ($inHeight > 0)
                                                    <div class="scan-chart__segment" data-segment-in
                                                        style="height: {{ $inHeight }}px; background: #10b981;"
                                                        title="Time-In: {{ $bucketIn }}"></div>
                                                @endif
                                                @if ($outHeight > 0)
                                                    <div class="scan-chart__segment" data-segment-out
                                                        style="height: {{ $outHeight }}px; background: #3b82f6;"
                                                        title="Time-Out: {{ $bucketOut }}"></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Milestone Ticks for Bars --}}
                        <div class="chart-tick-bar">
                            @foreach ($tickIndices as $idx)
                                @if (isset($scanBuckets[$idx]))
                                    <span>{{ $scanBuckets[$idx]['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Table View --}}
                    <div class="chart-view d-none" data-view="table">
                        <div class="chart-table-wrap">
                            <table class="chart-table">
                                <thead>
                                    <tr>
                                        <th>Time window</th>
                                        <th>In</th>
                                        <th>Out</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($scanBuckets as $i => $bucket)
                                        <tr data-bucket-idx="{{ $i }}">
                                            <td>{{ $bucket['label'] }}</td>
                                            <td data-table-in>{{ $bucket['time_in'] }}</td>
                                            <td data-table-out>{{ $bucket['time_out'] }}</td>
                                            <td class="chart-table__total" data-table-total>{{ $bucket['total'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel 2: Weekly Attendance Trend --}}
            <div class="panel" data-chart-weekly data-weekly-buckets='@json($weeklyScanBuckets)' data-weekly-max="{{ $weeklyMaxBucketTotal }}">
                <div class="panel__header">
                    <div>
                        <h2 class="panel__title">Attendance by week</h2>
                        <p class="panel__meta">6-week historical scan totals.</p>
                    </div>
                    <div class="chart-view-toggle" data-chart-toggle="weekly" role="group" aria-label="Chart style">
                        <button type="button" class="chart-view-toggle__btn active" data-chart-view="line">Line</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="bars">Bars</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="table">Table</button>
                    </div>
                </div>

                {{-- Key Weekly Stats --}}
                <div class="chart-stat-strip">
                    <span class="stat-pill stat-pill--info">
                        6-Wk Total: <strong data-weekly-stat-total>{{ number_format($weeklyChartTotal) }}</strong>
                    </span>
                    <span class="stat-pill">
                        Avg: <strong data-weekly-stat-avg>{{ $weeklyChartAverage }}/wk</strong>
                    </span>
                    <span class="stat-pill stat-pill--success">
                        Peak: <strong>{{ $weeklyChartPeakLabel }}</strong> (<span data-weekly-stat-peak>{{ $weeklyChartPeakTotal }}</span>)
                    </span>
                    @if ($weeklyTrend !== null)
                        <span class="stat-pill {{ $weeklyTrend >= 0 ? 'stat-pill--success' : 'stat-pill--warning' }}">
                            {{ $weeklyTrend >= 0 ? '▲ +' : '▼ ' }}{{ $weeklyTrend }}% vs prev week
                        </span>
                    @endif
                </div>

                <div class="chart-views" data-chart-views="weekly">
                    {{-- Line View (Default) --}}
                    <div class="chart-view" data-view="line">
                        <div class="chart-legend">
                            @foreach ($weeklyLevelLines as $ll)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $ll['color'] }}"></span>
                                    <span>{{ $ll['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="line-chart__frame">
                            <svg class="line-chart__svg" viewBox="0 0 100 100" preserveAspectRatio="none" role="img"
                                aria-label="Weekly scan volume trend by department level">
                                {{-- Subtle Horizontal Grid Guides --}}
                                <line x1="0" y1="29" x2="100" y2="29" class="line-chart__grid-line" />
                                <line x1="0" y1="50" x2="100" y2="50" class="line-chart__grid-line" />
                                <line x1="0" y1="71" x2="100" y2="71" class="line-chart__grid-line" />
                                <line x1="0" y1="92" x2="100" y2="92" stroke="#e2e8f0" stroke-width="1" />

                                <text x="1" y="27" class="line-chart__guide-text" data-weekly-guide="75">{{ (int) round($weeklyMaxBucketTotal * 0.75) }}</text>
                                <text x="1" y="48" class="line-chart__guide-text" data-weekly-guide="50">{{ (int) round($weeklyMaxBucketTotal * 0.5) }}</text>
                                <text x="1" y="69" class="line-chart__guide-text" data-weekly-guide="25">{{ (int) round($weeklyMaxBucketTotal * 0.25) }}</text>

                                {{-- Render line paths --}}
                                @foreach ($weeklyLevelLines as $ll)
                                    @if (!empty($ll['area']))
                                        <path d="{{ $ll['area'] }}" class="sp-line__area" data-weekly-area="{{ $ll['level'] }}"
                                            style="fill: {{ $ll['color'] }}"></path>
                                    @endif
                                    <path d="{{ $ll['path'] }}" class="sp-line__stroke" data-weekly-stroke="{{ $ll['level'] }}"
                                        style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke">
                                        <title>{{ $ll['label'] }}</title>
                                    </path>
                                @endforeach
                            </svg>
                        </div>

                        {{-- Week labels without overlap --}}
                        <div class="chart-tick-bar">
                            @foreach ($weeklyChartBuckets as $week)
                                <span>{{ $week['label'] }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Bars View --}}
                    <div class="chart-view d-none" data-view="bars">
                        <div class="chart-legend">
                            @foreach ($weeklyGradeLevels as $gradeKey => $gradeMeta)
                                @if (in_array($gradeKey, ['1', '7', '11']))
                                    <div class="chart-legend__item">
                                        <span class="chart-legend__swatch" style="background: {{ $gradeMeta['color'] }}"></span>
                                        <span>{{ ['1' => 'Elem (G1-6)', '7' => 'JHS (G7-10)', '11' => 'SHS (G11-12)'][$gradeKey] ?? $gradeMeta['label'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="scan-chart">
                            <div class="scan-chart__inner"
                                style="grid-template-columns: repeat({{ $weeklyChartBucketCount }}, minmax(0, 1fr));">
                                @foreach ($weeklyChartBuckets as $i => $week)
                                    @php
                                        $weekTotal = $week['total'] ?? 0;
                                        $weekGrades = $week['grades'] ?? [];
                                        $weekHeight = $weekTotal > 0
                                            ? max((int) round(($weekTotal / $weeklyMaxBucketTotal) * 145), 4)
                                            : 2;
                                    @endphp
                                    <div class="scan-chart__col" data-weekly-idx="{{ $i }}">
                                        <div class="scan-chart__bar-wrap" tabindex="0" aria-label="{{ $week['range'] }}">
                                            <div class="scan-chart__tooltip">
                                                <div class="scan-chart__tooltip-title">{{ $week['range'] }}</div>
                                                <div class="scan-chart__tooltip-total" data-weekly-tooltip-total>{{ $weekTotal }} scans</div>
                                                @foreach ($weeklyGradeLevels as $gradeKey => $gradeMeta)
                                                    @if (($weekGrades[$gradeKey] ?? 0) > 0)
                                                        <div class="scan-chart__tooltip-row">
                                                            <span class="scan-chart__tooltip-key">
                                                                <span class="scan-chart__tooltip-swatch"
                                                                    style="background: {{ $gradeMeta['color'] }}"></span>
                                                                <span>{{ $gradeMeta['label'] }}</span>
                                                            </span>
                                                            <strong>{{ $weekGrades[$gradeKey] ?? 0 }}</strong>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <div class="scan-chart__bar" style="height: {{ $weekHeight }}px;" data-weekly-bar>
                                                @foreach ($weeklyGradeLevels as $gradeKey => $gradeMeta)
                                                    @php
                                                        $gradeCount = $weekGrades[$gradeKey] ?? 0;
                                                        $gradeHeight = $weekTotal > 0
                                                            ? max((int) round(($gradeCount / $weekTotal) * $weekHeight), $gradeCount > 0 ? 2 : 0)
                                                            : 0;
                                                    @endphp
                                                    @if ($gradeHeight > 0)
                                                        <div class="scan-chart__segment"
                                                            style="height: {{ $gradeHeight }}px; background: {{ $gradeMeta['color'] }};"
                                                            title="{{ $gradeMeta['label'] }}: {{ $gradeCount }}"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chart-tick-bar">
                            @foreach ($weeklyChartBuckets as $week)
                                <span>{{ $week['label'] }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Table View --}}
                    <div class="chart-view d-none" data-view="table">
                        <div class="chart-table-wrap">
                            <table class="chart-table">
                                <thead>
                                    <tr>
                                        <th>Week Range</th>
                                        <th>Total Scans</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($weeklyChartBuckets as $i => $week)
                                        <tr data-weekly-idx="{{ $i }}">
                                            <td>{{ $week['range'] }}</td>
                                            <td class="chart-table__total" data-weekly-table-total>{{ number_format($week['total']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Lower Grid: Recent Activity & Compact Enrollment Breakdown --}}
        <section class="dashboard-lower-grid">
            {{-- Latest Scans Table --}}
            <section class="panel table-card">
                <div class="panel__header">
                    <div>
                        <h2 class="panel__title">Recent scan activity</h2>
                        <p class="panel__meta">Real-time attendance checkpoints today.</p>
                    </div>
                    <a href="{{ route('time-in-time-out-history.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.72rem; border-radius: 999px;">
                        View all
                    </a>
                </div>

                @if ($latestScans->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 32%">Student</th>
                                    <th scope="col" style="width: 26%">Grade & Section</th>
                                    <th scope="col" style="width: 18%">Type</th>
                                    <th scope="col" style="width: 24%">Time & Status</th>
                                </tr>
                            </thead>
                            <tbody data-realtime-recent-scans>
                                @foreach ($latestScans as $scan)
                                    @php
                                        $dotScanType = ['IN' => 'success', 'OUT' => 'primary'][$scan['scan_type']] ?? 'secondary';
                                        $studentName = $scan['student_name'] ?? 'Unknown';
                                        $nameParts = array_values(array_filter(explode(' ', $studentName)));
                                        $firstPart = $nameParts[0] ?? 'U';
                                        $lastPart = $nameParts ? $nameParts[count($nameParts) - 1] : 'K';
                                        $initials = strtoupper(substr($firstPart, 0, 1) . substr($lastPart, 0, 1));
                                        $remarks = collect(explode(',', $scan['flag_types'] ?? ''))
                                            ->map(fn($flagType) => trim($flagType))
                                            ->filter()
                                            ->map(fn($flagType) => [
                                                'late_arrival' => 'Late',
                                                'invalid_checkout' => 'Invalid Out',
                                            ][$flagType] ?? 'Flagged')
                                            ->join(', ');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="table-name-wrap">
                                                <div class="table-name-avatar">{{ $initials }}</div>
                                                <div>
                                                    <span class="table-name-main">{{ $studentName }}</span>
                                                    <span class="table-name-sub">{{ $scan['student_number'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">Grade {{ $scan['grade_level'] ?? '-' }}</span>
                                            <span class="text-muted d-block" style="font-size: 0.68rem;">{{ $scan['section_name'] ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-dot dot-{{ $dotScanType }}">
                                                {{ ['IN' => 'Time In', 'OUT' => 'Time Out'][$scan['scan_type']] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $scan['scan_time'] ?? '-' }}</div>
                                            @if ($remarks !== '')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1" style="font-size: 0.62rem;">
                                                    {{ $remarks }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-4 text-muted">
                        <i class="fas fa-qrcode fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">No scans recorded yet today.</p>
                    </div>
                @endif
            </section>

            {{-- Compact Grade Level Distribution --}}
            <section class="panel">
                <div class="panel__header mb-2">
                    <div>
                        <h2 class="panel__title">Enrollment distribution</h2>
                        <p class="panel__meta">Active student distribution across grade levels.</p>
                    </div>
                </div>

                @php
                    $gradeDistributionSegments = [];
                    $segmentStart = 0;

                    foreach ($gradeDistribution as $item) {
                        $segmentEnd = $segmentStart + $item['percentage'];
                        $gradeDistributionSegments[] = $item['color'] . ' ' . $segmentStart . '% ' . $segmentEnd . '%';
                        $segmentStart = $segmentEnd;
                    }

                    $gradeDistributionGradient = !empty($gradeDistributionSegments)
                        ? implode(', ', $gradeDistributionSegments)
                        : '#e5e7eb 0 100%';
                @endphp

                <div class="grade-distribution-container">
                    <div class="grade-donut__wrap">
                        <div class="grade-donut__ring"
                            style="background: conic-gradient({{ $gradeDistributionGradient }});"></div>
                        <div class="grade-donut__center">
                            <strong>{{ number_format($activeEnrollments) }}</strong>
                            <span>Enrolled</span>
                        </div>
                    </div>

                    <div class="grade-grid">
                        @foreach ($gradeDistribution as $grade)
                            <div class="grade-chip" title="{{ $grade['label'] }}: {{ $grade['count'] }} ({{ $grade['percentage'] }}%)">
                                <div class="grade-chip__left">
                                    <span class="grade-chip__swatch" style="background: {{ $grade['color'] }}"></span>
                                    <span>{{ $grade['label'] }}</span>
                                </div>
                                <div class="grade-chip__right">
                                    {{ $grade['count'] }} <span class="text-muted fw-normal">({{ $grade['percentage'] }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </section>
    </div>

    @push('scripts')
        <script>
            // Chart style toggles (Bars / Line / Table)
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-chart-toggle]').forEach(function (toggle) {
                    const scope = toggle.dataset.chartToggle;
                    const views = document.querySelector('[data-chart-views="' + scope + '"]');
                    if (!views) return;

                    toggle.querySelectorAll('[data-chart-view]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            toggle.querySelectorAll('[data-chart-view]').forEach(function (b) {
                                b.classList.toggle('active', b === btn);
                            });
                            const target = btn.dataset.chartView;
                            views.querySelectorAll('[data-view]').forEach(function (v) {
                                v.classList.toggle('d-none', v.dataset.view !== target);
                            });
                        });
                    });
                });
            });

            // Echo listener for Today's Scanned Students modal
            window.addEventListener('attendance:recorded', (e) => {
                const payload = e.detail;
                const tbody = document.querySelector('[data-timeline-modal-tbody]');
                if (!tbody) return;

                const emptyTd = tbody.querySelector('td[colspan]');
                if (emptyTd) {
                    emptyTd.closest('tr')?.remove();
                }

                const student = payload.student || {};
                const name = student.name || 'Unknown';
                const nameParts = name.split(/\s+/).filter(Boolean);
                const firstPart = nameParts[0] || 'U';
                const lastPart = nameParts.length > 1 ? nameParts[nameParts.length - 1] : 'K';
                const initials = (firstPart[0] + lastPart[0]).toUpperCase();
                const dotType = payload.scan_type === 'IN' ? 'success' : 'primary';
                const scanLabel = payload.scan_type === 'IN' ? 'Time In' : 'Time Out';
                const timeStr = payload.formatted_time || (payload.scan_time ? new Date(payload.scan_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '-');

                const tr = document.createElement('tr');
                tr.className = 'table-success-subtle transition-all';
                tr.style.backgroundColor = '#ecfdf5';
                tr.innerHTML = `
                    <td>
                        <div class="table-name-wrap">
                            <div class="table-name-avatar">${initials}</div>
                            <div>
                                <span class="table-name-main">${name}</span>
                                <span class="table-name-sub">${student.student_number || '-'}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">Grade ${student.grade || '-'}</span>
                        <span class="text-muted d-block small">${student.section || '-'}</span>
                    </td>
                    <td><span class="badge-dot dot-${dotType}">${scanLabel}</span></td>
                    <td><div class="fw-semibold text-dark">${timeStr}</div></td>
                `;

                tbody.insertBefore(tr, tbody.firstChild);

                setTimeout(() => {
                    tr.style.backgroundColor = '';
                }, 2500);
            });
        </script>

        <!-- Today Attendance Timeline Scanned Students Modal -->
        <div class="modal fade" id="timelineScannedStudentsModal" tabindex="-1" aria-labelledby="timelineScannedStudentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom px-4 py-3 bg-light">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="timelineScannedStudentsModalLabel">
                                <i class="fas fa-user-clock me-2 text-primary"></i>Today's Scanned Students
                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2" style="font-size: 0.68rem;">LIVE</span>
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Chronological roster of students scanned today.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 32%">Student</th>
                                        <th style="width: 25%">Grade & Section</th>
                                        <th style="width: 18%">Scan Type</th>
                                        <th style="width: 25%">Scan Time</th>
                                    </tr>
                                </thead>
                                <tbody data-timeline-modal-tbody>
                                    @forelse($latestScans as $scan)
                                        @php
                                            $studentName = $scan['student_name'] ?? 'Unknown';
                                            $nameParts = array_values(array_filter(explode(' ', $studentName)));
                                            $firstPart = $nameParts[0] ?? 'U';
                                            $lastPart = $nameParts ? $nameParts[count($nameParts) - 1] : 'K';
                                            $initials = strtoupper(substr($firstPart, 0, 1) . substr($lastPart, 0, 1));
                                            $dotScanType = ['IN' => 'success', 'OUT' => 'primary'][$scan['scan_type']] ?? 'secondary';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="table-name-wrap">
                                                    <div class="table-name-avatar">{{ $initials }}</div>
                                                    <div>
                                                        <span class="table-name-main">{{ $studentName }}</span>
                                                        <span class="table-name-sub">{{ $scan['student_number'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark">Grade {{ $scan['grade_level'] ?? '-' }}</span>
                                                <span class="text-muted d-block small">{{ $scan['section_name'] ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge-dot dot-{{ $dotScanType }}">
                                                    {{ ['IN' => 'Time In', 'OUT' => 'Time Out'][$scan['scan_type']] ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $scan['scan_time'] ?? '-' }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No scans recorded yet today.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-2 bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endpush
</x-layouts.scanner-operator>
