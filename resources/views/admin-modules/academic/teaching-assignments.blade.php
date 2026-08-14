<x-layouts.admin>
    <x-slot name="pageName">
        Teaching Assignments
    </x-slot>

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
                    <td>{{ ucfirst($assignment->section?->session_type ?? '—') }}</td>
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
                    <label class="form-label text-muted text-uppercase small fw-bold">Subject</label>
                    <select name="subject_id" id="field_subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">Section</label>
                    <select name="section_id" id="field_section_id" class="form-select" required>
                        <option value="">Select Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }} (Grade {{ $section->grade_level }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted text-uppercase small fw-bold">School Year</label>
                    <select name="school_year_id" id="field_school_year_id" class="form-select" required>
                        <option value="">Select School Year</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
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
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark px-4" id="formSubmitBtn">
                    <i class="fas fa-save me-2"></i> <span id="formSubmitText">Create</span>
                </button>
            </div>
        </form>
    </x-modal>

</x-layouts.admin>