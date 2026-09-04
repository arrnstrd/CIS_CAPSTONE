<x-layouts.admin>
    <x-slot name="title">Attendance — Grade Level</x-slot>
    <x-slot name="subtitle">Select a grade level to view attendance.</x-slot>
    <x-slot name="pageName">Attendance</x-slot>

    <link rel="stylesheet" href="{{ asset('css/gradeLevel.css') }}">

    <div class="grade-selection-wrapper">
        @foreach ($grades as $level => $gradeRange)
            <div class="grade-section">
                <p class="grade-section-title">{{ $level }}</p>
                <div class="grade-grid">
                    @foreach ($gradeRange as $g)
                        <a href="{{ route('attendance.section', $g) }}" class="grade-card">
                            <div class="grade-info">
                                <span class="grade-name">Grade {{ $g }}</span>
                                <span class="grade-level">{{ $level }}</span>
                                <span class="student-count-badge">
                                    <i class="fas fa-user-graduate"></i>
                                    {{ $gradeCounts[(string) $g] ?? 0 }} students
                                </span>
                            </div>
                            <i class="fas fa-chevron-right grade-chevron"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
