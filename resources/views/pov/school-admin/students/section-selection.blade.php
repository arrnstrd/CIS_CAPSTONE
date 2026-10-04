<x-layouts.school-admin>
    <x-slot name="title">Student Management — Grade {{ $grade }} Sections</x-slot>
    <x-slot name="subtitle">Grade {{ $grade }} — Select a section to view student records.</x-slot>
    <x-slot name="pageName">Student Management</x-slot>

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
        </div>
    </div>

    <div class="grade-selection-wrapper">
        <div class="grade-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <p class="grade-section-title mb-0">Grade {{ $grade }} Sections</p>
                    <small class="text-muted">Select a section below to view class roster, advisor, and teacher assignments.</small>
                </div>
                {{-- ONLY ONE Add Section button on this page --}}
                <button type="button" class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                    data-bs-toggle="offcanvas" data-bs-target="#createSectionDrawer" data-grade="{{ $grade }}">
                    <i class="fas fa-plus fa-sm"></i>
                    <span>+ Add Section</span>
                </button>
            </div>

            @if ($sections->isNotEmpty())
                <div class="grade-grid">
                    @foreach ($sections as $section)
                        <div class="grade-card position-relative d-flex align-items-center justify-content-between p-3 bg-white border rounded-3 shadow-sm">
                            <a href="{{ route('student-management.section', ['grade' => $grade, 'section' => $section->id]) }}" class="text-decoration-none text-dark flex-grow-1">
                                <div class="grade-info">
                                    <span class="grade-name fw-bold fs-6 d-block text-dark mb-1">{{ $section->name }}</span>
                                    <span class="grade-level text-secondary small d-block mb-1">
                                        <i class="fas fa-chalkboard-user me-1 text-muted"></i>
                                        Adviser: {{ $section->advisor?->full_name ?? 'Not Assigned' }}
                                    </span>
                                    <span class="student-count-badge badge bg-light text-secondary border">
                                        <i class="fas fa-user-graduate me-1"></i>
                                        {{ $section->student_count }} students
                                    </span>
                                </div>
                            </a>

                            <div class="d-flex align-items-center gap-2">
                                <div class="dropdown position-relative z-3" onclick="event.stopPropagation()">
                                    <button class="btn btn-sm btn-light border-0 text-secondary rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Section Options">
                                        <i class="fa-solid fa-ellipsis-vertical fs-6"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button type="button" class="dropdown-item js-edit-section"
                                                data-bs-toggle="modal" data-bs-target="#editSectionModal"
                                                data-id="{{ $section->id }}"
                                                data-name="{{ $section->name }}"
                                                data-level="{{ $section->level }}"
                                                data-grade-level="{{ $section->grade_level }}"
                                                data-advisor-id="{{ $section->advisor_id ?? '' }}"
                                                data-capacity="{{ $section->capacity }}"
                                                data-status="{{ $section->status }}"
                                                data-session-type="{{ $section->session_type }}">
                                                <i class="fas fa-pen fa-xs me-2 text-muted"></i> Edit Section
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                data-bs-toggle="offcanvas" data-bs-target="#assignAdvisorDrawer"
                                                data-section-id="{{ $section->id }}"
                                                data-section-name="{{ $section->name }}"
                                                data-grade="{{ $grade }}">
                                                <i class="fas fa-user-tie fa-xs me-2 text-muted"></i> Assign Adviser
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <a href="{{ route('student-management.section', ['grade' => $grade, 'section' => $section->id]) }}" class="text-secondary ms-1">
                                    <i class="fas fa-chevron-right grade-chevron"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white border rounded-3 p-5 text-center">
                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="fa-solid fa-folder-open text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                        <h5 class="fw-bold text-dark mb-1">No sections found for Grade {{ $grade }}</h5>
                        <p class="text-muted mb-0">Use the <strong>+ Add Section</strong> button above to create the first section under Grade {{ $grade }}.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('pov.school-admin.students.drawers.create-section-drawer', ['selectedGrade' => $grade])
    @include('pov.school-admin.academic.modals.edit-section-modal')
    @include('pov.school-admin.students.drawers.assign-advisor-drawer', ['selectedGrade' => $grade])
</x-layouts.school-admin>
