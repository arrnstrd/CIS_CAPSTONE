<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-chart-line"></i>
            Attendance vs Performance
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Relationship between student attendance and academic performance.</span>
    </x-slot>

    {{-- Analytics Sub-Navigation --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('teacher.grading-system.analytics') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.grading-system.analytics*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
           style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-graduation-cap me-1"></i> Academic Analytics
        </a>

        <a href="{{ route('teacher.grading-system.attendance') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.grading-system.attendance*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
           style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-clipboard-user me-1"></i> Attendance Analytics
        </a>

        <a href="{{ route('teacher.grading-system.correlation') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.grading-system.correlation*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
           style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-chart-simple me-1"></i> Attendance vs Performance
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('teacher.grading-system.correlation') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">School Year</label>
                <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All School Years</option>
                    @foreach ($schoolYears as $schoolYear)
                        <option value="{{ $schoolYear->id }}"
                            @selected(request('school_year_id') == $schoolYear->id)>
                            {{ $schoolYear->school_year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Term</label>
                <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Terms</option>
                    @for ($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" @selected(request('term') == $t)>
                            Term {{ $t }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Grade Level</label>
                <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    @foreach ($gradeLevels as $gl)
                        <option value="{{ $gl }}" @selected(request('grade_level') == $gl)>
                            Grade {{ $gl }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Section</label>
                <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Subject</label>
                <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Student</label>
                <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>
                            {{ $student->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
            </div>

        </div>

        @if (request()->hasAny([
            'school_year_id',
            'term',
            'grade_level',
            'section_id',
            'subject_id',
            'student_id',
        ]))
            <div class="mt-2">
                <a href="{{ route('teacher.grading-system.correlation') }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>
                    Clear Filters
                </a>
            </div>
        @endif
    </form>

    {{-- Main Relationship Visualization & Integrated Correlation Analysis --}}
    <div class="gs-panel mb-3">
        {{-- Card Header --}}
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <p class="gs-panel-title mb-1">
                    <i class="fa-solid fa-chart-line text-primary me-2"></i>
                    Attendance Rate (%) vs Academic Performance
                </p>
                <p class="text-muted small mb-0">
                    Investigate whether student attendance correlates with academic grades against official DepEd reference benchmarks (85% Attendance &amp; 75 Passing Mark).
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="toggleConnectLines">
                    <label class="form-check-label small text-muted user-select-none" for="toggleConnectLines">Connect observations</label>
                </div>
                @if (!empty($correlationStats['isSufficient']))
                    <span class="badge {{ $correlationStats['r'] >= 0.4 ? 'bg-success' : ($correlationStats['r'] >= 0.2 ? 'bg-primary' : ($correlationStats['r'] > -0.2 ? 'bg-secondary' : 'bg-danger')) }} px-3 py-2 fs-6">
                        r = {{ $correlationStats['rFormatted'] }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Integrated Correlation Spectrum & Key Finding Meter --}}
        <div class="p-3 rounded border mb-3" style="background-color: #f8fafc;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark small">
                        <i class="fa-solid fa-scale-balanced text-primary me-1"></i>
                        Correlation Strength &amp; Direction:
                    </span>
                    <span class="badge {{ !empty($correlationStats['isSufficient']) ? ($correlationStats['r'] >= 0.4 ? 'bg-success' : ($correlationStats['r'] >= 0.2 ? 'bg-primary' : ($correlationStats['r'] > -0.2 ? 'bg-secondary' : 'bg-danger'))) : 'bg-secondary' }} px-2 py-1">
                        {{ $correlationStats['relationshipTitle'] ?? 'Insufficient Data' }}
                        @if(!empty($correlationStats['isSufficient']))
                            (r = {{ $correlationStats['rFormatted'] }})
                        @endif
                    </span>
                </div>
                <div class="small text-muted d-flex align-items-center gap-3">
                    <span><strong>{{ $correlationStats['sampleSize'] }}</strong> Students Analyzed</span>
                    <span>&bull;</span>
                    <span>Cohort Avg Attendance: <strong>{{ $correlationStats['avgAttendance'] !== null ? $correlationStats['avgAttendance'] . '%' : 'N/A' }}</strong></span>
                    <span>&bull;</span>
                    <span>Cohort Avg Grade: <strong>{{ $correlationStats['avgGrade'] !== null ? number_format($correlationStats['avgGrade'], 1) : 'N/A' }}</strong></span>
                </div>
            </div>

            @if(!empty($correlationStats['isSufficient']) && $correlationStats['r'] !== null)
                @php
                    $posPercent = max(2, min(98, (($correlationStats['r'] + 1) / 2) * 100));
                @endphp
                {{-- Visual Spectrum Bar --}}
                <div class="position-relative my-3" style="height: 10px; background: linear-gradient(to right, #ef4444 0%, #fca5a5 25%, #cbd5e1 50%, #93c5fd 75%, #10b981 100%); border-radius: 6px;">
                    <div class="position-absolute" style="left: {{ $posPercent }}%; top: -6px; transform: translateX(-50%); transition: left 0.3s ease;">
                        <div style="width: 22px; height: 22px; background: #0f172a; border: 3px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.35);"
                             title="Calculated Pearson r: {{ $correlationStats['rFormatted'] }}"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem; font-weight: 500;">
                    <span class="text-danger"><i class="fa-solid fa-arrow-down-left me-1"></i> -1.0 Strong Inverse</span>
                    <span>-0.5 Moderate</span>
                    <span class="text-secondary"><i class="fa-solid fa-arrows-left-right me-1"></i> 0.0 No Correlation</span>
                    <span>+0.5 Moderate</span>
                    <span class="text-success"><i class="fa-solid fa-arrow-up-right me-1"></i> +1.0 Strong Positive</span>
                </div>
            @endif

            <div class="mt-2 pt-2 border-top text-secondary small" style="line-height: 1.45;">
                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                <strong>Analytical Finding:</strong> {{ $correlationStats['teacherExplanation'] }}
                <span class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                    * Statistical correlation measures observed linear association in this dataset; individual student outcomes vary based on multifaceted academic and personal factors.
                </span>
            </div>
        </div>

        @if (empty($correlationStats['isSufficient']) && !empty($correlationStats['sampleSize']) && $correlationStats['sampleSize'] > 0)
            {{-- Insufficient data warning banner --}}
            <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                <div>
                    <span class="fw-semibold">Insufficient data for correlation calculation:</span>
                    <span>At least 5 valid paired student records are needed for statistical validity. Currently showing {{ $correlationStats['sampleSize'] }} {{ Str::plural('student', $correlationStats['sampleSize']) }}.</span>
                </div>
            </div>
        @endif

        @php
            $sortedChartPoints = collect($studentPoints ?? [])
                ->sortBy([
                    ['attendance_rate', 'asc'],
                    ['name', 'asc'],
                ])
                ->map(function ($point) {
                    return [
                        'x' => (float) $point['attendance_rate'],
                        'y' => (float) $point['avg_grade'],
                        'name' => $point['name'],
                        'section' => $point['section'] ?? '',
                        'absences' => $point['absences'] ?? 0,
                        'quadrant' => $point['quadrant'] ?? '',
                        'quadrant_label' => $point['quadrant_label'] ?? '',
                        'color' => $point['color'] ?? '#2438b9',
                    ];
                })
                ->values();

            $trendPoints = collect($correlationStats['trendPoints'] ?? [])->values();
            $totalAnalyzed = $correlationStats['sampleSize'] > 0 ? $correlationStats['sampleSize'] : 1;
        @endphp

        @if ($sortedChartPoints->isNotEmpty())
            {{-- Canvas Container --}}
            <div style="position: relative; height: 440px; width: 100%;">
                <canvas id="attendanceGradeCorrelationChart"></canvas>
            </div>

            {{-- Quadrant Distribution Legend (Directly Underneath Chart) --}}
            <div class="pt-3 mt-3 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <span class="small fw-bold text-dark">
                        <i class="fa-solid fa-layer-group text-primary me-1"></i>
                        Student Quadrant Distribution (DepEd Benchmarks: 85% Attendance &bull; 75 Passing Grade):
                    </span>
                </div>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded border" style="background-color: #f7fdf9; border-left: 3px solid #10b981 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-success small">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i> High Att &amp; Passing
                                </span>
                                <span class="badge bg-success rounded-pill">
                                    {{ $correlationStats['quadrantCounts']['high_att_high_grade'] ?? 0 }}
                                </span>
                            </div>
                            <span class="text-muted d-block" style="font-size: 0.72rem;">
                                &ge;85% Att &bull; &ge;75 Grade ({{ round((($correlationStats['quadrantCounts']['high_att_high_grade'] ?? 0) / $totalAnalyzed) * 100) }}%)
                            </span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded border" style="background-color: #f7fcff; border-left: 3px solid #6366f1 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small" style="color: #4f46e5 !important;">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem; color: #6366f1;"></i> Low Att &amp; Passing
                                </span>
                                <span class="badge rounded-pill text-white" style="background-color: #6366f1;">
                                    {{ $correlationStats['quadrantCounts']['low_att_high_grade'] ?? 0 }}
                                </span>
                            </div>
                            <span class="text-muted d-block" style="font-size: 0.72rem;">
                                &lt;85% Att &bull; &ge;75 Grade (Academic Exception)
                            </span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded border" style="background-color: #fffdfa; border-left: 3px solid #f59e0b !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small" style="color: #d97706 !important;">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem; color: #f59e0b;"></i> High Att &amp; Below 75
                                </span>
                                <span class="badge rounded-pill text-dark" style="background-color: #fbbf24;">
                                    {{ $correlationStats['quadrantCounts']['high_att_low_grade'] ?? 0 }}
                                </span>
                            </div>
                            <span class="text-muted d-block" style="font-size: 0.72rem;">
                                &ge;85% Att &bull; &lt;75 Grade (Instructional Focus)
                            </span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded border" style="background-color: #fffafb; border-left: 3px solid #ef4444 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-danger small">
                                    <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i> Low Att &amp; Below 75
                                </span>
                                <span class="badge bg-danger rounded-pill">
                                    {{ $correlationStats['quadrantCounts']['low_att_low_grade'] ?? 0 }}
                                </span>
                            </div>
                            <span class="text-muted d-block" style="font-size: 0.72rem;">
                                &lt;85% Att &bull; &lt;75 Grade (Attendance &amp; Grades)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="gs-chart-empty" style="height: 340px;">
                <i class="fa-solid fa-chart-simple gs-chart-empty-icon"></i>
                <p class="fw-medium mb-1">No correlation data available</p>
                <p class="text-muted small mb-0">
                    No matching student attendance and academic grade records found for the active filter combination.
                </p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('pov.teacher.my-classes.partials.common.chart-defaults')

    <script>
        @if ($sortedChartPoints->isNotEmpty())
            (function() {
                const observationsData = @json($sortedChartPoints);
                const trendLineData = @json($trendPoints);
                const isSufficient = @json($correlationStats['isSufficient'] ?? false);
                const rValue = @json($correlationStats['rFormatted'] ?? null);

                // Map colors for individual student points
                const pointColors = observationsData.map(p => p.color || '#2438b9');

                // Custom Chart.js plugin to draw DepEd benchmark lines and quadrant background watermarks
                const correlationQuadrantPlugin = {
                    id: 'correlationQuadrantPlugin',
                    beforeDraw(chart) {
                        const { ctx, chartArea: { top, right, bottom, left }, scales: { x, y } } = chart;
                        if (!x || !y) return;

                        const x85 = x.getPixelForValue(85);
                        const y75 = y.getPixelForValue(75);

                        ctx.save();

                        // Soft quadrant background shading
                        // Top-Right: High Attendance & Passing (soft emerald)
                        if (x85 < right && y75 > top) {
                            ctx.fillStyle = 'rgba(16, 185, 129, 0.04)';
                            ctx.fillRect(x85, top, right - x85, y75 - top);
                        }
                        // Bottom-Left: Low Attendance & Below 75 (soft rose)
                        if (x85 > left && y75 < bottom) {
                            ctx.fillStyle = 'rgba(239, 68, 68, 0.04)';
                            ctx.fillRect(left, y75, x85 - left, bottom - y75);
                        }

                        // Benchmark lines: dashed lines
                        ctx.setLineDash([5, 5]);
                        ctx.lineWidth = 1.5;

                        // Vertical reference line at 85% attendance
                        if (x85 >= left && x85 <= right) {
                            ctx.strokeStyle = 'rgba(100, 116, 139, 0.55)';
                            ctx.beginPath();
                            ctx.moveTo(x85, top);
                            ctx.lineTo(x85, bottom);
                            ctx.stroke();

                            // Label
                            ctx.fillStyle = '#475569';
                            ctx.font = '600 10px sans-serif';
                            ctx.textAlign = 'center';
                            ctx.fillText('85% Attendance Benchmark', x85, top + 14);
                        }

                        // Horizontal reference line at 75 passing grade
                        if (y75 >= top && y75 <= bottom) {
                            ctx.strokeStyle = 'rgba(100, 116, 139, 0.55)';
                            ctx.beginPath();
                            ctx.moveTo(left, y75);
                            ctx.lineTo(right, y75);
                            ctx.stroke();

                            // Label
                            ctx.fillStyle = '#475569';
                            ctx.font = '600 10px sans-serif';
                            ctx.textAlign = 'right';
                            ctx.fillText('75 Passing Grade', right - 8, y75 - 5);
                        }

                        // Quadrant corner watermarks
                        ctx.font = 'bold 10px sans-serif';

                        // Q1: Top Right (High Att & Passing)
                        ctx.fillStyle = 'rgba(16, 185, 129, 0.45)';
                        ctx.textAlign = 'right';
                        ctx.fillText('High Attendance & Passing (≥85%, ≥75)', right - 12, top + 28);

                        // Q2: Top Left (Low Att & Passing)
                        ctx.fillStyle = 'rgba(99, 102, 241, 0.45)';
                        ctx.textAlign = 'left';
                        ctx.fillText('Low Attendance & Passing (<85%, ≥75)', left + 12, top + 28);

                        // Q3: Bottom Left (Low Att & Below Passing)
                        ctx.fillStyle = 'rgba(239, 68, 68, 0.45)';
                        ctx.textAlign = 'left';
                        ctx.fillText('Low Attendance & Below Passing (<85%, <75)', left + 12, bottom - 10);

                        // Q4: Bottom Right (High Att & Below Passing)
                        ctx.fillStyle = 'rgba(245, 158, 11, 0.45)';
                        ctx.textAlign = 'right';
                        ctx.fillText('High Attendance & Below Passing (≥85%, <75)', right - 12, bottom - 10);

                        ctx.restore();
                    }
                };

                const datasets = [{
                    label: 'Student Observations',
                    data: observationsData,
                    type: 'line',
                    showLine: false,
                    borderColor: 'rgba(100, 116, 139, 0.3)',
                    borderWidth: 1.5,
                    pointRadius: 7,
                    pointHoverRadius: 10,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    tension: 0,
                    fill: false,
                    order: 2
                }];

                if (isSufficient && trendLineData.length === 2) {
                    datasets.push({
                        label: 'Correlation Trend Line (Linear Fit, r = ' + (rValue || 'N/A') + ')',
                        data: trendLineData,
                        type: 'line',
                        borderColor: '#2563eb',
                        backgroundColor: '#2563eb',
                        borderWidth: 3,
                        borderDash: [6, 4],
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        fill: false,
                        tension: 0,
                        order: 1
                    });
                }

                const chart = new Chart(document.getElementById('attendanceGradeCorrelationChart'), {
                    data: {
                        datasets: datasets
                    },
                    plugins: [correlationQuadrantPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        interaction: {
                            mode: 'nearest',
                            intersect: true
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 14,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 1) {
                                            return 'Linear Correlation Trend (r = ' + (rValue || 'N/A') + ')';
                                        }

                                        const point = context.raw;

                                        return [
                                            point.name + (point.section ? ' (' + point.section + ')' : ''),
                                            'Attendance Rate: ' + point.x + '%',
                                            'Average Academic Grade: ' + point.y,
                                            'Total Absences: ' + point.absences + ' days',
                                            'Status: ' + (point.quadrant_label || 'Observed')
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                min: 0,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Attendance Rate (%)',
                                    font: {
                                        weight: '600',
                                        size: 11
                                    }
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                grid: {
                                    color: 'rgba(243, 244, 246, 0.8)'
                                }
                            },
                            y: {
                                min: 50,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Average Academic Grade',
                                    font: {
                                        weight: '600',
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(243, 244, 246, 0.8)'
                                }
                            }
                        }
                    }
                });

                // Toggle connecting line behavior
                const toggleBtn = document.getElementById('toggleConnectLines');
                if (toggleBtn) {
                    toggleBtn.addEventListener('change', function(e) {
                        chart.data.datasets[0].showLine = e.target.checked;
                        chart.update();
                    });
                }
            })();
        @endif
    </script>
</x-layouts.teacher>