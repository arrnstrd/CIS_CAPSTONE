<x-layouts.school-admin>
    <x-slot name="pageName">
        Teaching Assignments
    </x-slot>

    <x-slot name="subtitle">Assign teachers to sections and subjects.</x-slot>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-dark px-4 py-2 rounded-3 fw-medium" data-bs-toggle="modal"
            data-bs-target="#assignmentModal" onclick="openCreateModal()">
            <i class="fas fa-plus me-2"></i> Add New
        </button>
    </div>

    <x-ui.table>
        <thead>
            <tr>
                <th>Teacher</th>
                <th>Subject</th>
                <th>Section</th>
                <th>School Year</th>
                <th>Session Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($teachingAssignments as $assignment)
                <tr data-assignment-id="{{ $assignment->id }}" data-teacher-id="{{ $assignment->teacher_id }}"
                    data-subject-id="{{ $assignment->subject_id }}" data-section-id="{{ $assignment->section_id }}"
                    data-school-year-id="{{ $assignment->school_year_id }}" data-status="{{ $assignment->status }}">
                    <td>{{ $assignment->teacher?->full_name ?? '—' }}</td>
                    <td>{{ $assignment->subject?->name ?? '—' }}</td>
                    <td>{{ $assignment->section?->name ?? '—' }}</td>
                    <td>{{ $assignment->schoolYear?->school_year ?? '—' }}</td>
                    <td>{{ $assignment->section?->session_type ? Str::headline(str_replace('_', ' ', $assignment->section->session_type)) : '—' }}
                    </td>
                    <td>
                        <span class="badge-dot dot-{{ $assignment->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($assignment->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                            data-bs-target="#assignmentModal" onclick="openEditModal({{ $assignment->id }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}" method="POST"
                            data-ajax-delete="assignment" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No teaching assignments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>

    <!-- Pagination -->
    <div class="px-3 py-3">
        {{ $teachingAssignments->links() }}
    </div>

    {{-- Teaching Assignment Modal --}}
    <x-modal id="assignmentModal" modalTitle="Teaching Assignment">
        <form id="assignmentForm" method="POST" data-ajax-form>
            @csrf
            <input type="hidden" name="_method" value="POST" id="formMethod">
            <input type="hidden" name="assignment_id" id="assignmentId">

            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">Teacher</label>
                    <select name="teacher_id" id="field_teacher_id" class="form-select" required>
                        <option value="">Select Teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">School Year</label>
                    <select name="school_year_id" id="field_school_year_id" class="form-select" required>
                        <option value="">Select School Year</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy->id }}" data-active="{{ $sy->is_active ? '1' : '0' }}">{{ $sy->school_year }}{{ $sy->is_active ? ' (Active)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">Section</label>
                    <select name="section_id" id="field_section_id" class="form-select" required>
                        <option value="">Select Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}" data-level="{{ $section->level }}">{{ $section->name }} (Grade {{ $section->grade_level }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">Status</label>
                    <select name="status" id="field_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {{-- Single Subject Field (Grade 4+ and Edit mode) --}}
                <div class="col-12" id="single_subject_container">
                    <label class="form-label text-muted text-uppercase small fw-bold">Subject</label>
                    <select name="subject_id" id="field_subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" data-level="{{ $subject->level }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Mass Subject Field (Grade 1-3) --}}
                <div class="col-12 d-none" id="mass_subject_container">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <label class="form-label text-muted text-uppercase small fw-bold mb-0">Grade 1–3 Subjects</label>
                            <div class="text-muted small" style="font-size: 0.75rem;">Batch assign core elementary subjects for this section.</div>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnSelectAllSubjects" style="font-size: 0.75rem;">Select All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnDeselectAllSubjects" style="font-size: 0.75rem;">Deselect All</button>
                        </div>
                    </div>
                    <div class="border rounded-3 p-3 bg-light" id="mass_subjects_list" style="max-height: 220px; overflow-y: auto;">
                        <div class="text-muted small text-center py-2" id="mass_subjects_empty">
                            Select a section and school year to load grade-specific subjects.
                        </div>
                    </div>
                    <div class="form-text small text-muted mt-1" style="font-size: 0.75rem;">
                        <i class="fas fa-info-circle me-1"></i> Subjects already assigned to another teacher are disabled.
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark px-4" id="formSubmitBtn">
                    <i class="fas fa-save me-2"></i> <span id="formSubmitText">Create</span>
                </button>
            </div>
        </form>
    </x-modal>

</x-layouts.school-admin>