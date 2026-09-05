<x-layouts.admin>
    <x-slot name="title">Student Management — Grade {{ $grade }} Sections</x-slot>
    <x-slot name="subtitle">Grade {{ $grade }} — Select a section to view student records.</x-slot>
    <x-slot name="pageName">Student Management</x-slot>

    <link rel="stylesheet" href="{{ asset('css/gradeLevel.css') }}">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('student-management.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Grade Levels</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bulk-import') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium">
                <i class="fas fa-file-import fa-sm"></i>
                <span>Bulk Import</span>
            </a>

            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                data-bs-toggle="offcanvas" data-bs-target="#addStudentSidePanel" data-grade="{{ $grade }}">
                <i class="fas fa-plus fa-sm"></i>
                <span>Add Student</span>
            </button>
        </div>
    </div>

    <div class="grade-selection-wrapper">
        <div class="grade-section">
            <p class="grade-section-title">Grade {{ $grade }} Sections</p>
            @if ($sections->isNotEmpty())
                <div class="grade-grid">
                    @foreach ($sections as $section)
                        <a href="{{ route('student-management.section', ['grade' => $grade, 'section' => $section->id]) }}" class="grade-card">
                            <div class="grade-info">
                                <span class="grade-name">{{ $section->name }}</span>
                                <span class="grade-level">
                                    <i class="fas fa-chalkboard-user me-1 text-muted"></i>
                                    Adviser: {{ $section->advisor?->full_name ?? 'Not Assigned' }}
                                </span>
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
                <div class="bg-white border rounded-3 p-5 text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fa-solid fa-folder-open text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark mb-1">No sections found for Grade {{ $grade }}</h5>
                        <p class="text-muted mb-4">Sections can be created and configured in the Academic Setup module.</p>
                        <a href="{{ route('academic.index') }}" class="btn btn-primary px-4 py-2 rounded-3 d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Go to Academic Setup</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('pov.school-admin.students.partials.add-student-modal')
</x-layouts.admin>
