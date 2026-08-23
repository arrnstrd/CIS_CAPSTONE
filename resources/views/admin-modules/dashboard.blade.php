<x-layouts.admin>
    <x-slot name="title">Dashboard</x-slot>

    <x-slot name="subtitle">Today’s attendance and school activity at a glance.</x-slot>

    <x-slot name="pageName">Dashboard</x-slot>

    @php
        $peakLabel = $peakBucket['range'] ?? 'No scans yet';
        $peakTotal = $peakBucket['total'] ?? 0;
        $chartBucketCount = max(count($scanBuckets ?? []), 1);

        // Daily line-chart points (for the Line view). Points are padded inside
        // the 0-100 viewBox so the stroke never clips at the frame edges.
        $dailyLinePoints = collect($scanBuckets ?? [])->map(function ($b, $i) use ($chartBucketCount, $maxBucketTotal) {
            $span = max($chartBucketCount - 1, 1);
            $x = 4 + ($i / $span) * 92;
            $ratio = $maxBucketTotal > 0 ? (($b['total'] ?? 0) / $maxBucketTotal) : 0;
            $y = 6 + (1 - $ratio) * 88;
            return ['x' => round($x, 2), 'y' => round($y, 2), 'label' => $b['label'] ?? '', 'total' => $b['total'] ?? 0];
        })->values();

        // Smooth (Catmull-Rom -> Bezier) path builder for the minimal line chart.
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

        $dailyLinePath = $toSmoothPath($dailyLinePoints->all());
        $dailyLineArea = $dailyLinePath !== '' ? $dailyLinePath . ' L 96 94 L 4 94 Z' : '';

        // Per-department-level line data (Line view shows 3 colored lines).
        $dailyLevelLines = collect($departmentLevels)
            ->filter(fn($meta, $level) => $level !== 'unknown')
            ->map(function ($meta, $level) use ($scanBuckets, $chartBucketCount, $maxBucketTotal, $toSmoothPath) {
                $span = max($chartBucketCount - 1, 1);
                $points = [];
                foreach (($scanBuckets ?? []) as $i => $b) {
                    $x = 4 + ($i / $span) * 92;
                    $ratio = $maxBucketTotal > 0 ? (($b['levels'][$level] ?? 0) / $maxBucketTotal) : 0;
                    $y = 6 + (1 - $ratio) * 88;
                    $points[] = ['x' => round($x, 2), 'y' => round($y, 2)];
                }
                $path = $toSmoothPath($points);
                $area = $path !== '' ? $path . ' L 96 94 L 4 94 Z' : '';
                return [
                    'level' => $level,
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'path' => $path,
                    'area' => $area,
                    'last' => $points ? $points[count($points) - 1] : null,
                ];
            })
            ->values();

        $weeklyChartBuckets = collect($weeklyScanBuckets ?? [])->values();
        $weeklyChartBucketCount = max($weeklyChartBuckets->count(), 1);

        // Weekly line-chart points (for the Line view). Points are padded inside
        // the 0-100 viewBox so the stroke never clips at the frame edges.
        $weeklyLinePoints = $weeklyChartBuckets->map(function ($b, $i) use ($weeklyChartBucketCount, $weeklyMaxBucketTotal) {
            $span = max($weeklyChartBucketCount - 1, 1);
            $x = 4 + ($i / $span) * 92;
            $ratio = $weeklyMaxBucketTotal > 0 ? (($b['total'] ?? 0) / $weeklyMaxBucketTotal) : 0;
            $y = 6 + (1 - $ratio) * 88;
            return ['x' => round($x, 2), 'y' => round($y, 2), 'label' => $b['label'] ?? '', 'total' => $b['total'] ?? 0];
        })->values();
        $weeklyLinePath = $toSmoothPath($weeklyLinePoints->all());
        $weeklyLineArea = $weeklyLinePath !== '' ? $weeklyLinePath . ' L 96 94 L 4 94 Z' : '';

        // Per-department-level line data for the weekly chart (grouped grades).
        $weeklyLevelLines = collect($departmentLevels)
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
                foreach ($weeklyChartBuckets as $i => $week) {
                    $grades = $week['grades'] ?? [];
                    $count = 0;
                    foreach ($gradeKeys as $gk) {
                        $count += $grades[$gk] ?? 0;
                    }
                    $x = 4 + ($i / $span) * 92;
                    $ratio = $weeklyMaxBucketTotal > 0 ? ($count / $weeklyMaxBucketTotal) : 0;
                    $y = 6 + (1 - $ratio) * 88;
                    $points[] = ['x' => round($x, 2), 'y' => round($y, 2)];
                }
                $path = $toSmoothPath($points);
                $area = $path !== '' ? $path . ' L 96 94 L 4 94 Z' : '';
                return [
                    'level' => $level,
                    'label' => $meta['label'],
                    'color' => $meta['color'],
                    'path' => $path,
                    'area' => $area,
                    'last' => $points ? $points[count($points) - 1] : null,
                ];
            })
            ->values();

        $weeklyChartPeakBucket = $weeklyPeakBucket ?? $weeklyChartBuckets->sortByDesc('total')->first();
        $weeklyChartPeakLabel = $weeklyChartPeakBucket['range'] ?? 'No scans yet';
        $weeklyChartPeakTotal = $weeklyChartPeakBucket['total'] ?? 0;
        $weeklyChartAverage = $weeklyAverage ?? round($weeklyChartBuckets->avg('total') ?? 0, 1);
        $weeklyChartTotal = $weeklyTotalScans ?? $weeklyChartBuckets->sum('total');
        $departmentLevels = $departmentLevels ?? [];
        $levelTotals = $levelTotals ?? [];
    @endphp

    <style>
        .dashboard-shell {
            display: grid;
            gap: 1rem;
        }

        .dashboard-hero {
            background: linear-gradient(135deg, #172554 0%, #233f9e 52%, #0f766e 100%);
            color: #fff;
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        }

        .dashboard-hero__wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dashboard-hero__eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.7rem;
            opacity: 0.8;
            margin-bottom: 0.35rem;
        }

        .dashboard-hero__title {
            font-size: clamp(1.7rem, 3vw, 2.6rem);
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.45rem;
        }

        .dashboard-hero__sub {
            max-width: 44rem;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 0;
        }

        .dashboard-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .dashboard-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            border-radius: 999px;
            padding: 0.75rem 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .dashboard-btn:hover {
            transform: translateY(-1px);
        }

        .dashboard-btn--light {
            background: #fff;
            color: #172554;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        }

        .dashboard-btn--ghost {
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

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
            border: 1px solid #e5e9f2;
            border-radius: 1.2rem;
            padding: 0.85rem 0.9rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            border-color: #cfd8e8;
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.1);
            color: inherit;
        }

        .dashboard-card__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .dashboard-card__icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .card-tone-primary .dashboard-card__icon {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .card-tone-success .dashboard-card__icon {
            background: linear-gradient(135deg, #16a34a, #0f766e);
        }

        .card-tone-dark .dashboard-card__icon {
            background: linear-gradient(135deg, #111827, #374151);
        }

        .card-tone-indigo .dashboard-card__icon {
            background: linear-gradient(135deg, #4f46e5, #233f9e);
        }

        .dashboard-card__label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #667085;
            margin-bottom: 0.25rem;
            font-weight: 800;
        }

        .dashboard-card__value {
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1;
            color: #111827;
        }

        .dashboard-card__hint {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            color: #6b7280;
        }

        .dashboard-analytics-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 0.85rem;
        }

        @media (max-width: 991px) {
            .dashboard-analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-lower-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 0.85rem;
        }

        @media (max-width: 991px) {
            .dashboard-lower-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: #fff;
            border: 1px solid #e5e9f2;
            border-radius: 1.2rem;
            padding: 0.9rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            min-width: 0;
            overflow: visible;
        }

        .chart-card {
            display: grid;
            gap: 0.75rem;
        }

        .chart-card__summary {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .chart-card__summary-item {
            padding: 0.55rem 0.7rem;
            border-radius: 0.9rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            min-width: 0;
        }

        .chart-card__summary-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 800;
        }

        .chart-card__summary-value {
            margin-top: 0.15rem;
            font-size: 0.95rem;
            font-weight: 800;
            color: #111827;
        }

        .panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .panel__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
        }

        .panel__meta {
            margin: 0.15rem 0 0;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .scan-chart {
            overflow: visible;
            padding-bottom: 0.25rem;
        }

        .scan-chart__inner {
            width: 100%;
            display: grid;
            align-items: end;
            gap: 0.3rem;
            padding-top: 0.5rem;
            min-height: 180px;
        }

        .scan-chart__col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            min-width: 0;
            position: relative;
            isolation: isolate;
        }

        .scan-chart__count {
            font-size: 0.7rem;
            font-weight: 800;
            color: #0f172a;
            min-height: 0.8rem;
            line-height: 1;
        }

        .scan-chart__bar-wrap {
            position: relative;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            min-height: 160px;
            z-index: 1;
        }

        .scan-chart__tooltip {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 12px);
            transform: translate(-50%, 6px);
            min-width: 180px;
            max-width: 240px;
            background: rgba(15, 23, 42, 0.96);
            color: #fff;
            border-radius: 0.85rem;
            padding: 0.75rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease;
            z-index: 20;
            text-align: left;
            white-space: normal;
        }

        .scan-chart__bar-wrap:hover .scan-chart__tooltip,
        .scan-chart__bar-wrap:focus-within .scan-chart__tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .scan-chart__col:hover .scan-chart__tooltip,
        .scan-chart__col:focus-within .scan-chart__tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .scan-chart__tooltip-title {
            font-size: 0.78rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .scan-chart__tooltip-total {
            font-size: 0.95rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .scan-chart__tooltip-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.82);
            margin-top: 0.35rem;
        }

        .scan-chart__tooltip-row:first-of-type {
            margin-top: 0;
        }

        .scan-chart__tooltip-key {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .scan-chart__tooltip-swatch {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .scan-chart__bar {
            width: 100%;
            height: 140px;
            border-radius: 999px;
            background: #edf2f7;
            overflow: hidden;
            display: flex;
            flex-direction: column-reverse;
            justify-content: flex-start;
            align-self: flex-end;
        }

        .scan-chart__segment {
            width: 100%;
        }

        .scan-chart__segment--elementary {
            background: linear-gradient(180deg, #34d399, #16a34a);
        }

        .scan-chart__segment--highschool {
            background: linear-gradient(180deg, #60a5fa, #2563eb);
        }

        .scan-chart__segment--senior_high_school {
            background: linear-gradient(180deg, #fbbf24, #f59e0b);
        }

        .scan-chart__segment--unknown {
            background: linear-gradient(180deg, #9ca3af, #6b7280);
        }

        .scan-chart__label {
            font-size: 0.58rem;
            color: #6b7280;
            white-space: nowrap;
            transform: rotate(-45deg);
            transform-origin: center;
            margin-top: 0.35rem;
            max-width: 100%;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .chart-legend__item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.65rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            font-size: 0.72rem;
            font-weight: 700;
            color: #334155;
        }

        .chart-legend__swatch {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 999px;
        }

        .chart-legend__value {
            color: #111827;
        }

        .line-chart {
            display: grid;
            gap: 0.55rem;
        }

        .line-chart__frame {
            position: relative;
            height: 214px;
            border-radius: 1rem;
            border: 1px solid #edf2f7;
            background:
                linear-gradient(180deg, rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0)),
                repeating-linear-gradient(to top,
                    rgba(148, 163, 184, 0.12) 0,
                    rgba(148, 163, 184, 0.12) 1px,
                    transparent 1px,
                    transparent 20%);
            overflow: hidden;
        }

        .line-chart__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .line-chart__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.65rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.12);
            color: #166534;
            font-size: 0.7rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .line-chart__meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .line-chart__meta-card {
            padding: 0.55rem 0.65rem;
            border-radius: 0.9rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            min-width: 0;
        }

        .line-chart__meta-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 800;
        }

        .line-chart__meta-value {
            margin-top: 0.15rem;
            font-size: 0.92rem;
            font-weight: 800;
            color: #111827;
        }

        .line-chart__svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .line-chart__svg .axis-line {
            stroke: rgba(148, 163, 184, 0.3);
            stroke-width: 1;
            stroke-dasharray: 2.6 2.4;
        }

        .line-chart__svg .trend-line {
            fill: none;
            stroke: #2563eb;
            stroke-width: 1.75px;
            stroke-linecap: round;
            stroke-linejoin: round;
            vector-effect: non-scaling-stroke;
        }

        .line-chart__svg .trend-area {
            fill: url(#trendAreaFill);
        }

        .line-chart__svg .trend-dot {
            fill: #fff;
            stroke: #2563eb;
            stroke-width: 1.75px;
            vector-effect: non-scaling-stroke;
        }

        .line-chart__svg .axis-label {
            fill: #8a97b8;
            font-size: 3px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .line-chart__empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .line-chart__ticks {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
            gap: 0.25rem;
            font-size: 0.62rem;
            color: #64748b;
            text-align: center;
            white-space: nowrap;
            margin-top: -0.1rem;
        }

        .line-chart__ticks span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .line-chart__timeline {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
            gap: 0.35rem;
            align-items: end;
            margin-top: 0.1rem;
        }

        .line-chart__timeline-item {
            min-width: 0;
            display: grid;
            justify-items: center;
            gap: 0.3rem;
        }

        .line-chart__timeline-bar {
            width: 100%;
            height: 0.3rem;
            border-radius: 999px;
            background: #c7d2fe;
            opacity: 0.95;
        }

        .line-chart__timeline-label {
            font-size: 0.6rem;
            color: #8090b8;
            line-height: 1;
            white-space: nowrap;
        }

        .line-chart__timeline-item--highlight .line-chart__timeline-bar {
            background: linear-gradient(90deg, #34d399, #22c55e);
        }

        .grade-donut {
            display: grid;
            gap: 0.8rem;
        }

        .grade-donut__wrap {
            position: relative;
            width: min(100%, 220px);
            aspect-ratio: 1;
            margin: 0 auto;
            border-radius: 50%;
            background: #f8fafc;
            display: grid;
            place-items: center;
        }

        .grade-donut__ring {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(#e5e7eb 0 100%);
            position: relative;
            overflow: hidden;
        }

        .grade-donut__ring::after {
            content: "";
            position: absolute;
            inset: 18%;
            border-radius: 50%;
            background: #fff;
            box-shadow: inset 0 0 0 1px #edf2f7;
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
            font-size: 1.7rem;
            line-height: 1;
            font-weight: 800;
            color: #111827;
        }

        .grade-donut__center span {
            margin-top: 0.2rem;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            font-weight: 800;
        }

        .grade-distribution {
            display: grid;
            gap: 0.5rem;
        }

        .grade-distribution__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem;
            padding: 0.5rem 0.6rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #edf2f7;
        }

        .grade-distribution__label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            font-size: 0.75rem;
            font-weight: 700;
            color: #334155;
        }

        .grade-distribution__swatch {
            width: 0.68rem;
            height: 0.68rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .grade-distribution__value {
            font-size: 0.75rem;
            font-weight: 800;
            color: #111827;
            white-space: nowrap;
        }

        .table-card .table td {
            white-space: nowrap;
        }

        /* ===== Chart style toggle (Bars / Line / Table) ===== */
        .chart-view-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }
        .chart-view-toggle__btn {
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 999px;
            padding: 0.3rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .chart-view-toggle__btn:hover {
            color: #334155;
        }
        .chart-view-toggle__btn.active {
            background: #fff;
            color: #4f46e5;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
        }
        .chart-views {
            min-width: 0;
        }
        .chart-view {
            min-width: 0;
        }

        /* ===== Slimmer, cleaner stacked bars ===== */
        .scan-chart__inner {
            gap: 0.35rem;
            padding-top: 0.25rem;
        }
        .scan-chart__col {
            gap: 0.25rem;
        }
        .scan-chart__bar {
            width: 100%;
            max-width: 44px;
            margin-inline: auto;
            border-radius: 8px;
        }
        .scan-chart__segment {
            border-radius: 2px;
        }
        .scan-chart__label {
            font-size: 0.56rem;
            margin-top: 0.2rem;
        }

        /* ===== Table view ===== */
        .chart-table-wrap {
            max-height: 260px;
            overflow: auto;
            border: 1px solid #edf2f7;
            border-radius: 0.75rem;
        }
        .chart-table {
            width: 100%;
            font-size: 0.8rem;
            border-collapse: collapse;
        }
        .chart-table th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.62rem;
            letter-spacing: 0.06em;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        .chart-table td {
            padding: 0.4rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            text-align: right;
        }
        .chart-table td:first-child {
            text-align: left;
        }
        .chart-table tbody tr:hover {
            background: #f8fafc;
        }
        .chart-table__total {
            font-weight: 800;
            color: #111827;
        }

        /* ===== Minimal line chart (Line view) ===== */
        .line-chart__frame.sp-line {
            height: 180px;
            border: 1px solid #edf2f7;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            padding: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        .sp-line__stroke {
            fill: none;
            stroke-width: 1.75px;
            stroke-linecap: round;
            stroke-linejoin: round;
            vector-effect: non-scaling-stroke;
            filter: drop-shadow(0 2px 4px rgba(37, 99, 235, 0.12));
        }
        .sp-line__stroke--multi {
            stroke-width: 1.5px;
            vector-effect: non-scaling-stroke;
            filter: none;
        }
        .sp-line__area {
            stroke: none;
            opacity: 0.08;
        }
        .sp-line__dot {
            fill: #fff;
            stroke: #6366f1;
            stroke-width: 1.75px;
            vector-effect: non-scaling-stroke;
        }
    </style>

    <div class="dashboard-shell mx-3 mb-2">
        <section class="dashboard-cards">
            @foreach ($dashboardCards as $card)
                <a href="{{ $card['href'] }}" class="dashboard-card card-tone-{{ $card['tone'] }}">
                    <div class="dashboard-card__top">
                        <div>
                            <div class="dashboard-card__label">{{ $card['label'] }}</div>
                            <div class="dashboard-card__value">{{ $card['value'] }}</div>
                        </div>
                        <div class="dashboard-card__icon">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                    </div>
                    <div class="dashboard-card__hint">{{ $card['hint'] }}</div>
                </a>
            @endforeach
        </section>

        <section class="dashboard-analytics-grid">
            <div class="panel chart-card">
                <div class="panel__header mb-0">
                    <div>
                        <h2 class="panel__title">Attendance by department level</h2>
                        <p class="panel__meta">Today's scan totals by time window.</p>
                    </div>
                    <div class="chart-view-toggle" data-chart-toggle="daily" role="group" aria-label="Chart style">
                        <button type="button" class="chart-view-toggle__btn active" data-chart-view="bars">Bars</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="line">Line</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="table">Table</button>
                    </div>
                </div>

                <div class="chart-card__summary">
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Today</div>
                        <div class="chart-card__summary-value">{{ $totalScansToday }} scans</div>
                    </div>
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Peak window</div>
                        <div class="chart-card__summary-value">{{ $peakLabel }}</div>
                    </div>
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Peak total</div>
                        <div class="chart-card__summary-value">{{ $peakTotal }}</div>
                    </div>
                </div>

                <div class="chart-views" data-chart-views="daily">
                    {{-- Bars view --}}
                    <div class="chart-view" data-view="bars">
                        <div class="chart-legend">
                            @foreach ($departmentLevels as $levelKey => $levelMeta)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $levelMeta['color'] }}"></span>
                                    <span>{{ $levelMeta['label'] }}</span>
                                    <span class="chart-legend__value">{{ $levelTotals[$levelKey] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="scan-chart">
                            <div class="scan-chart__inner"
                                style="grid-template-columns: repeat({{ $chartBucketCount }}, minmax(0, 1fr));">
                                @foreach ($scanBuckets as $bucket)
                                    @php
                                        $bucketTotal = $bucket['total'] ?? 0;
                                        $bucketLevels = $bucket['levels'] ?? [];
                                        $bucketHeight = $bucketTotal > 0
                                            ? max((int) round(($bucketTotal / $maxBucketTotal) * 120), 6)
                                            : 4;
                                        $showLabel = $loop->iteration % 4 === 1;
                                    @endphp
                                    <div class="scan-chart__col">
                                        <div class="scan-chart__bar-wrap" tabindex="0" aria-label="{{ $bucket['range'] }}">
                                            <div class="scan-chart__tooltip">
                                                <div class="scan-chart__tooltip-title">{{ $bucket['range'] }}</div>
                                                <div class="scan-chart__tooltip-total">{{ $bucketTotal }} scans</div>
                                                @foreach ($departmentLevels as $levelKey => $levelMeta)
                                                    <div class="scan-chart__tooltip-row">
                                                        <span class="scan-chart__tooltip-key">
                                                            <span class="scan-chart__tooltip-swatch"
                                                                style="background: {{ $levelMeta['color'] }}"></span>
                                                            <span>{{ $levelMeta['label'] }}</span>
                                                        </span>
                                                        <span>{{ $bucketLevels[$levelKey] ?? 0 }}</span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="scan-chart__bar" style="height: {{ $bucketHeight }}px;">
                                                @foreach ($departmentLevels as $levelKey => $levelMeta)
                                                    @php
                                                        $levelCount = $bucketLevels[$levelKey] ?? 0;
                                                        $levelHeight = $bucketTotal > 0
                                                            ? max((int) round(($levelCount / $bucketTotal) * $bucketHeight), $levelCount > 0 ? 2 : 0)
                                                            : 0;
                                                    @endphp
                                                    @if ($levelHeight > 0)
                                                        <div class="scan-chart__segment scan-chart__segment--{{ $levelKey }}"
                                                            style="height: {{ $levelHeight }}px;"
                                                            title="{{ $levelMeta['label'] }}: {{ $levelCount }}"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="scan-chart__label">{{ $showLabel ? $bucket['label'] : '·' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Line view --}}
                    <div class="chart-view d-none" data-view="line">
                        <div class="line-chart__frame sp-line">
                            <svg class="line-chart__svg" viewBox="0 0 100 100" preserveAspectRatio="none" role="img"
                                aria-label="Scan volume trend by department level">
                                @foreach ($dailyLevelLines as $ll)
                                    @if (!empty($ll['area']))
                                        <path d="{{ $ll['area'] }}" class="sp-line__area"
                                            style="fill: {{ $ll['color'] }}"></path>
                                    @endif
                                    <path d="{{ $ll['path'] }}" class="sp-line__stroke sp-line__stroke--multi"
                                        style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke"></path>
                                    @if ($ll['last'])
                                        <circle cx="{{ $ll['last']['x'] }}" cy="{{ $ll['last']['y'] }}" r="1.2"
                                            class="sp-line__dot" style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke">
                                            <title>{{ $ll['label'] }}</title>
                                        </circle>
                                    @endif
                                @endforeach
                            </svg>
                        </div>
                        <div class="line-chart__ticks mt-1">
                            @foreach ($scanBuckets as $bucket)
                                @if ($loop->iteration % 4 === 1 || $loop->last)
                                    <span>{{ $bucket['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                        <div class="chart-legend mt-2">
                            @foreach ($dailyLevelLines as $ll)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $ll['color'] }}"></span>
                                    <span>{{ $ll['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Table view --}}
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
                                    @foreach ($scanBuckets as $bucket)
                                        <tr>
                                            <td>{{ $bucket['label'] }}</td>
                                            <td>{{ $bucket['time_in'] }}</td>
                                            <td>{{ $bucket['time_out'] }}</td>
                                            <td class="chart-table__total">{{ $bucket['total'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel chart-card">
                <div class="panel__header mb-0">
                    <div>
                        <h2 class="panel__title">Attendance by week</h2>
                        <p class="panel__meta">Weekly scan totals over the last 6 weeks.</p>
                    </div>
                    <div class="chart-view-toggle" data-chart-toggle="weekly" role="group" aria-label="Chart style">
                        <button type="button" class="chart-view-toggle__btn active" data-chart-view="bars">Bars</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="line">Line</button>
                        <button type="button" class="chart-view-toggle__btn" data-chart-view="table">Table</button>
                    </div>
                </div>

                <div class="chart-card__summary">
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Last 6 weeks</div>
                        <div class="chart-card__summary-value">{{ $weeklyChartTotal }} scans</div>
                    </div>
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Peak week</div>
                        <div class="chart-card__summary-value">{{ $weeklyChartPeakLabel }}</div>
                    </div>
                    <div class="chart-card__summary-item">
                        <div class="chart-card__summary-label">Weekly avg</div>
                        <div class="chart-card__summary-value">{{ $weeklyChartAverage }}</div>
                    </div>
                </div>

                <div class="chart-views" data-chart-views="weekly">
                    {{-- Bars view --}}
                    <div class="chart-view" data-view="bars">
                        <div class="chart-legend">
                            @foreach ($weeklyGradeLevels as $gradeKey => $gradeMeta)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $gradeMeta['color'] }}"></span>
                                    <span>{{ $gradeMeta['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="scan-chart">
                            <div class="scan-chart__inner"
                                style="grid-template-columns: repeat({{ $weeklyChartBucketCount }}, minmax(0, 1fr));">
                                @foreach ($weeklyChartBuckets as $week)
                                    @php
                                        $weekTotal = $week['total'] ?? 0;
                                        $weekGrades = $week['grades'] ?? [];
                                        $weekHeight = $weekTotal > 0
                                            ? max((int) round(($weekTotal / $weeklyMaxBucketTotal) * 120), 6)
                                            : 4;
                                    @endphp
                                    <div class="scan-chart__col">
                                        <div class="scan-chart__bar-wrap" tabindex="0" aria-label="{{ $week['range'] }}">
                                            <div class="scan-chart__tooltip">
                                                <div class="scan-chart__tooltip-title">{{ $week['range'] }}</div>
                                                <div class="scan-chart__tooltip-total">{{ $weekTotal }} scans</div>
                                                @foreach ($weeklyGradeLevels as $gradeKey => $gradeMeta)
                                                    <div class="scan-chart__tooltip-row">
                                                        <span class="scan-chart__tooltip-key">
                                                            <span class="scan-chart__tooltip-swatch"
                                                                style="background: {{ $gradeMeta['color'] }}"></span>
                                                            <span>{{ $gradeMeta['label'] }}</span>
                                                        </span>
                                                        <span>{{ $weekGrades[$gradeKey] ?? 0 }}</span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="scan-chart__bar" style="height: {{ $weekHeight }}px;">
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
                                        <div class="scan-chart__label">{{ $week['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Line view --}}
                    <div class="chart-view d-none" data-view="line">
                        <div class="line-chart__frame sp-line">
                            <svg class="line-chart__svg" viewBox="0 0 100 100" preserveAspectRatio="none" role="img"
                                aria-label="Weekly scan volume trend by department level">
                                @foreach ($weeklyLevelLines as $ll)
                                    @if (!empty($ll['area']))
                                        <path d="{{ $ll['area'] }}" class="sp-line__area"
                                            style="fill: {{ $ll['color'] }}"></path>
                                    @endif
                                    <path d="{{ $ll['path'] }}" class="sp-line__stroke sp-line__stroke--multi"
                                        style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke"></path>
                                    @if ($ll['last'])
                                        <circle cx="{{ $ll['last']['x'] }}" cy="{{ $ll['last']['y'] }}" r="1.2"
                                            class="sp-line__dot" style="stroke: {{ $ll['color'] }}" vector-effect="non-scaling-stroke">
                                            <title>{{ $ll['label'] }}</title>
                                        </circle>
                                    @endif
                                @endforeach
                            </svg>
                        </div>
                        <div class="line-chart__ticks mt-1">
                            @foreach ($weeklyChartBuckets as $week)
                                <span>{{ $week['label'] }}</span>
                            @endforeach
                        </div>
                        <div class="chart-legend mt-2">
                            @foreach ($weeklyLevelLines as $ll)
                                <div class="chart-legend__item">
                                    <span class="chart-legend__swatch" style="background: {{ $ll['color'] }}"></span>
                                    <span>{{ $ll['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Table view --}}
                    <div class="chart-view d-none" data-view="table">
                        <div class="chart-table-wrap">
                            <table class="chart-table">
                                <thead>
                                    <tr>
                                        <th>Week</th>
                                        <th>Scans</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($weeklyChartBuckets as $week)
                                        <tr>
                                            <td>{{ $week['range'] }}</td>
                                            <td class="chart-table__total">{{ $week['total'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-lower-grid">
            <section class="panel table-card">
                <div class="panel__header">
                    <div>
                        <h2 class="panel__title">Latest scans</h2>
                        <p class="panel__meta">Recent attendance records with grade and section context.</p>
                    </div>
                    <a href="{{ route('time-in-time-out-history.index') }}" class="btn btn-sm btn-outline-primary">
                        View all
                    </a>
                </div>

                @if ($latestScans->isNotEmpty())
                    <div class="table-responsive mx-4">
                        <table class="table table-hover align-middle table-striped mb-0">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th scope="col" style="width: 20%">Student</th>

                                    <th scope="col" style="width: 11%">Grade & Section</th>

                                    <th scope="col" style="width: 10%">Scan Type</th>
                                    <th scope="col" style="width: 10%">Session</th>
                                    <th scope="col" style="width: 9%">Time</th>
                                    <th scope="col" style="width: 6%">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
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
                                                'late_arrival' => 'Late arrival',
                                                'invalid_checkout' => 'Checkout issue',
                                            ][$flagType] ?? 'Needs review')
                                            ->join(', ');
                                    @endphp
                                    <tr>
                                        <td class="table-name-cell">
                                            <div class="table-name-wrap">
                                                <div class="table-name-avatar">{{ $initials }}</div>
                                                <div class="table-name-copy">
                                                    <span class="table-name-main">{{ $studentName }}</span>
                                                    <span class="table-name-sub">{{ $scan['student_number'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>Grade {{ $scan['grade_level'] ?? '-' }} - {{ $scan['section_name'] ?? '-' }}</td>

                                        <td>
                                            <span class="badge-dot dot-{{ $dotScanType }}">
                                                {{ ['IN' => 'Time In', 'OUT' => 'Time Out'][$scan['scan_type']] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>{{ $scan['session_type'] ? Str::headline(str_replace('_', ' ', $scan['session_type'])) : '-' }}</td>
                                        <td>{{ $scan['scan_time'] ?? '-' }}</td>
                                        <td class="fw-semibold text-danger">
                                            {{ $remarks !== '' ? $remarks : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                        <i class="fas fa-qrcode fa-2x mb-3 opacity-50"></i>
                        <p class="mb-0">No scans recorded yet today.</p>
                    </div>
                @endif
            </section>

            <section class="panel grade-donut">
                <div class="panel__header mb-0">
                    <div>
                        <h2 class="panel__title">Grade level distribution</h2>
                        <p class="panel__meta">Percentage of active enrollments by grade level.</p>
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

                <div class="grade-donut__wrap">
                    <div class="grade-donut__ring"
                        style="background: conic-gradient({{ $gradeDistributionGradient }});"></div>
                    <div class="grade-donut__center">
                        <strong>{{ $activeEnrollments }}</strong>
                        <span>Active enrollments</span>
                    </div>
                </div>

                <div class="grade-distribution">
                    @foreach ($gradeDistribution as $grade)
                        <div class="grade-distribution__item">
                            <div class="grade-distribution__label">
                                <span class="grade-distribution__swatch" style="background: {{ $grade['color'] }}"></span>
                                <span>{{ $grade['label'] }}</span>
                            </div>
                            <div class="grade-distribution__value">
                                {{ $grade['count'] }} - {{ $grade['percentage'] }}%
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    </div>

    @push('scripts')
        <script>
            // Chart style toggles (Bars / Line / Table). The admin layout renders
            // @stack('scripts') in <head>, before the body exists, so bind after
            // the DOM is ready or the toggles never attach.
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

            setInterval(function () {
                window.location.reload();
            }, 60000);
        </script>
    @endpush
</x-layouts.admin>
