<x-layouts.school-admin>
    <x-slot name="title">Attendance — Sections</x-slot>
    <x-slot name="subtitle">Grade {{ $grade }} — Monitor attendance records and verification status by section.</x-slot>
    <x-slot name="pageName">Attendance</x-slot>

    <div class="mb-3">
        <a href="{{ route('attendance.grade-level') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Grade Levels</span>
        </a>
    </div>

    <div class="grade-selection-wrapper">
        <div class="grade-section">
            <p class="grade-section-title">Grade {{ $grade }} Sections</p>
            @if ($sections->isNotEmpty())
                <div class="grade-grid">
                    @foreach ($sections as $section)
                        <a href="{{ route('attendance.section.show', ['grade' => $grade, 'section' => $section->id]) }}" class="grade-card">
                            <div class="grade-info">
                                <span class="grade-name">{{ $section->name }}</span>
                                <span class="grade-level">Adviser: {{ $section->advisor?->full_name ?? ($section->advisor?->name ?? 'Not Assigned') }}</span>
                                <span class="student-count-badge">
                                    <i class="fas fa-user-graduate"></i>
                                    {{ $section->student_count }} students
                                </span>
                            </div>
                            <i class="fas fa-chevron-right grade-chevron"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-3 p-5 text-center" style="background: transparent;">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <i class="fa-solid fa-folder-open text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold text-dark mb-1">No sections found for Grade {{ $grade }}</h4>
                        <p class="text-muted mb-0">No active sections are currently configured for this grade level.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.school-admin>
