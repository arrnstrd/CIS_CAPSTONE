<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Overview of your sections' grading performance.
    </x-slot>

    @include('pov.teacher.my-classes.partials.common.grading-tabs', ['activeTab' => 'overview'])

    @include('pov.teacher.my-classes.partials.system.filter-bar')

    @include('pov.teacher.my-classes.partials.system.stats-cards')

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Average Grade by Section</p>
                <canvas id="avgGradeChart" height="180"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Attendance Rate by Section</p>
                <canvas id="attendanceChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Passing vs Failing Students</p>
                @if (($chartData['totalPassing'] + $chartData['totalFailing']) > 0)
                    <canvas id="passFailChart" height="180"></canvas>
                @else
                    <div class="gs-chart-empty">
                        <i class="fa-solid fa-chart-pie gs-chart-empty-icon"></i>
                        <p class="mb-0">No grades recorded yet for this term.</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Performance Trend (All Terms)</p>
                <canvas id="trendChart" height="180"></canvas>
            </div>
        </div>
    </div>

    @include('pov.teacher.my-classes.partials.system.section-breakdown-table')

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('pov.teacher.my-classes.partials.common.chart-defaults')
    <script>
        const avgGradeCtx = document.getElementById('avgGradeChart');
        new Chart(avgGradeCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['sectionLabels']),
                datasets: [{
                    label: 'Average Grade',
                    data: @json($chartData['sectionAvgGrades']),
                    backgroundColor: '#2438b9',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.90)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });

        const trendCtx = document.getElementById('trendChart');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['trendLabels']),
                datasets: [{
                    label: 'Average Grade',
                    data: @json($chartData['trendData']),
                    borderColor: '#f5a623',
                    backgroundColor: 'rgba(245, 166, 35, 0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.90)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });

        const passFailCtx = document.getElementById('passFailChart');
        if (passFailCtx) {
            new Chart(passFailCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Passing', 'Failing'],
                    datasets: [{
                        data: [@json($chartData['totalPassing']), @json($chartData['totalFailing'])],
                        backgroundColor: ['#085041', '#791F1F'],
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        const attendanceCtx = document.getElementById('attendanceChart');
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['attendanceLabels']),
                datasets: [{
                    label: 'Attendance Rate',
                    data: @json($chartData['attendanceRates']),
                    backgroundColor: '#6c63ff',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.90)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });
    </script>

</x-layouts.teacher>