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

    {{-- Attendance vs Academic Performance --}}
    <div class="gs-panel mb-3">
        <p class="gs-panel-title mb-1">Attendance vs Academic Performance</p>
        <p class="text-muted small mb-3">
            Relationship between student absences and average academic grade.
        </p>

        @php
            $correlationChartData = collect($studentPoints ?? [])->map(function ($point) {
                return [
                    'x' => $point['x'],
                    'y' => $point['y'],
                    'name' => $point['name'],
                ];
            })->values();
        @endphp

        @if ($correlationChartData->isNotEmpty())
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="attendanceGradeCorrelationChart"></canvas>
            </div>
        @else
            <div class="gs-chart-empty" style="height: 320px;">
                <i class="fa-solid fa-chart-simple gs-chart-empty-icon"></i>
                <p class="fw-medium mb-1">No correlation data</p>
                <p class="text-muted small mb-0">
                    Student attendance and grade data are not sufficient for the selected filters.
                </p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('pov.teacher.my-classes.partials.common.chart-defaults')

    <script>
        @if ($correlationChartData->isNotEmpty())
            new Chart(document.getElementById('attendanceGradeCorrelationChart'), {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Students',
                        data: @json($correlationChartData),
                        backgroundColor: '#2438b9',
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const point = context.raw;

                                    return [
                                        point.name,
                                        'Absences: ' + point.x,
                                        'Average Grade: ' + point.y
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Absences',
                                font: {
                                    weight: '600',
                                    size: 11
                                }
                            },
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(243, 244, 246, 0.8)'
                            }
                        },
                        y: {
                            beginAtZero: false,
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
        @endif
    </script>
</x-layouts.teacher>