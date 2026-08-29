<x-layouts.admin>
    <x-slot name="title">Attendance — Sections</x-slot>
    <x-slot name="subtitle">Grade {{ $grade }} — Select a section.</x-slot>
    <x-slot name="pageName">Attendance</x-slot>

    <link rel="stylesheet" href="{{ asset('css/gradeLevel.css') }}">

    <a href="{{ route('attendance.grade-level') }}" class="back-link" style="display:inline-flex;align-items:center;gap:0.5rem;color:#5a5c69;text-decoration:none;font-size:0.9rem;margin-bottom:1rem;">
        <i class="fas fa-arrow-left"></i> Back to Grade Levels
    </a>

    <div class="grade-selection-wrapper">
        <div class="grade-section">
            <p class="grade-section-title">Grade {{ $grade }}</p>
            <div class="grade-grid">
                @forelse ($sections as $section)
                    <a href="#" class="grade-card" onclick="alert('Classroom Attendance page not implemented yet.')">
                        <div class="grade-info">
                            <span class="grade-name">{{ $section->name }}</span>
                            <span class="grade-level">Adviser: {{ $section->advisor?->name ?? 'N/A' }}</span>
                            <span class="student-count-badge">
                                <i class="fas fa-user-graduate"></i>
                                {{ $section->student_count }} students
                            </span>
                        </div>
                        <i class="fas fa-chevron-right grade-chevron"></i>
                    </a>
                @empty
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:4rem 1rem;min-height:50vh;">
                        <h3 style="font-weight:700;color:#2e2f38;margin-bottom:0.5rem;">No sections found for Grade {{ $grade }}</h3>
                        <p style="color:#6e707e;margin-bottom:1.5rem;">Sections can be created from the Academic section.</p>
                        <a href="{{ route('sections.index') }}" class="grade-card" style="display:inline-flex;padding:0.75rem 1.25rem;background:#4e73df;color:#fff;border-radius:0.5rem;text-decoration:none;font-weight:600;box-shadow:0 4px 12px rgba(78,115,223,0.2);">
                            Go to Academic Sections
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
