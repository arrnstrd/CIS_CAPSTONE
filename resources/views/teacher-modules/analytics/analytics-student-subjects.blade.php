<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }} — subject performance trend.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'analytics'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'School-wide', 'url' => route('teacher.grading-system.analytics')],
        ['label' => 'Grade ' . $enrollment->section->grade_level, 'url' => route('teacher.grading-system.analytics.show', $enrollment->section->grade_level)],
        ['label' => $enrollment->section->name, 'url' => route('teacher.grading-system.analytics.students', $enrollment->section->id)],
        ['label' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name, 'url' => '#'],
    ]])

    <div class="row g-3">
        @forelse ($subjects as $subject)
            <div class="col-12 col-md-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">{{ $subject->subject_name }}</p>
                    @if ($subject->trend->isNotEmpty())
                        <canvas id="trendChart{{ $subject->teaching_assignment_id }}" height="140"></canvas>
                    @else
                        <div class="gs-chart-empty" style="height: 140px;">
                            <i class="fa-solid fa-chart-line gs-chart-empty-icon"></i>
                            <p class="mb-0">No grades recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No subjects found for this student.
                </div>
            </div>
        @endforelse
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('teacher-modules.partials.chart-defaults')
    <script>
        @foreach ($subjects as $subject)
            @if ($subject->trend->isNotEmpty())
                new Chart(document.getElementById('trendChart{{ $subject->teaching_assignment_id }}'), {
                    type: 'line',
                    data: {
                        labels: @json($subject->trend->pluck('term_label')),
                        datasets: [{
                            label: '{{ $subject->subject_name }}',
                            data: @json($subject->trend->pluck('grade')),
                            borderColor: '#2438b9',
                            backgroundColor: 'rgba(36, 56, 185, 0.1)',
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
            @endif
        @endforeach
    </script>

</x-layouts.teacher>