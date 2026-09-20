<x-layouts.school-admin>
    <x-slot name="title">
        Student Management
    </x-slot>

    <x-slot name="subtitle">
        Select a grade level to view, edit, or organize student records.
    </x-slot>

    <x-slot name="pageName">
        Student Management
    </x-slot>

    <div class="grade-selection-wrapper" data-tour="students-grade-grid">
        @foreach ($grades as $level => $gradeRange)
            <div class="grade-section">
                <p class="grade-section-title">{{ $level }}</p>

                <div class="grade-grid">
                    @foreach ($gradeRange as $g)
                        <a href="{{ route('student-management.grade', $g) }}" class="grade-card">
                            <div class="grade-info">
                                <span class="grade-name">Grade {{ $g }}</span>
                                <span class="grade-level">
                                    @if (str_contains(strtolower($level), 'primary'))
                                        Primary Level
                                    @elseif (str_contains(strtolower($level), 'junior') || str_contains(strtolower($level), 'secondary'))
                                        Junior High
                                    @elseif (str_contains(strtolower($level), 'senior'))
                                        Senior High
                                    @else
                                        {{ $level }}
                                    @endif
                                </span>
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
</x-layouts.school-admin>