<x-layouts.school-admin>

    <x-slot name="title">{{ $teacher->full_name }} — Teacher Workspace</x-slot>
    <x-slot name="pageName">Teacher Workspace</x-slot>
    <x-slot name="subtitle">Manage academic assignments, advisory duties, and teacher profile records.</x-slot>

    @php
        $teacherUser = $teacher->user;
        $teacherFirst = trim($teacherUser?->first_name ?? '');
        $teacherLast = trim($teacherUser?->last_name ?? '');
        $teacherName = trim($teacherFirst . ' ' . $teacherLast) ?: ($teacher->full_name ?? 'Unnamed Teacher');
        
        $teacherInitials = collect(preg_split('/\s+/', $teacherName, -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'T';

        $totalAssignments = $teacher->teachingAssignments->count();
        $status = strtolower($teacher->status ?? 'inactive');
    @endphp

    <style>
        /* Flat, Clean Card Styling */
        .tw-panel {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .tw-panel-header {
            padding: 1.15rem 1.35rem;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .tw-panel-body {
            padding: 1.35rem;
        }

        /* Top Profile Banner */
        .tw-banner {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .tw-avatar {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: #1e3a8a;
            color: #ffffff;
            font-size: 1.3rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Prominent Advisory Spotlight */
        .tw-advisory-spotlight {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.95rem 1.25rem;
            min-width: 290px;
        }

        /* Status Dot Pill */
        .status-dot-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
            text-transform: capitalize;
        }

        .status-dot-pill .dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 50%;
        }

        .status-active {
            background-color: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .status-active .dot {
            background-color: #10b981;
        }

        .status-pending {
            background-color: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }

        .status-pending .dot {
            background-color: #f59e0b;
        }

        .status-inactive {
            background-color: #f8fafc;
            color: #475569;
            border-color: #e2e8f0;
        }

        .status-inactive .dot {
            background-color: #94a3b8;
        }

        /* Info Item Row */
        .tw-info-item {
            padding: 0.85rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .tw-info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .tw-info-label {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tw-info-value {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        /* Clean Table */
        .tw-table th {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.15rem;
        }

        .tw-table td {
            padding: 0.95rem 1.15rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .tw-table tbody tr:hover {
            background-color: #fcfcfd;
        }

        .hover-primary:hover {
            color: #2563eb !important;
        }
    </style>

    <div class="container-fluid px-2 px-md-3">

        {{-- Top Navigation & Action Toolbar --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="{{ route('teachers.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-3 d-inline-flex align-items-center gap-2 fw-medium">
                <i class="fas fa-arrow-left fa-sm"></i>
                <span>Back to Teachers Directory</span>
            </a>

            <div class="d-flex align-items-center gap-2">
                @if ($status === 'active')
                    <button class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                        data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                        <i class="fas fa-user-pen fa-sm"></i>
                        <span>Edit Profile</span>
                    </button>
                @endif

                @if ($status === 'active')
                    <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST"
                        data-ajax-delete="teacher" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                            onclick="return confirm('Are you sure you want to archive this teacher?');">
                            <i class="fas fa-box-archive fa-sm"></i>
                            <span>Archive Teacher</span>
                        </button>
                    </form>
                @else
                    <form action="{{ route('teachers.restore', $teacher->id) }}" method="POST"
                        data-ajax-restore="teacher" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-success px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5">
                            <i class="fas fa-rotate-left fa-sm"></i>
                            <span>Restore Teacher</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- Unified Profile & Section Advisory Header                                 --}}
        {{-- ========================================================================= --}}
        @php
            $advisedSection = $teacher->advisedSections->first();
        @endphp
        <div class="tw-banner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                
                {{-- Left: Teacher Info --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="tw-avatar">
                        {{ $teacherInitials }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold text-dark m-0 fs-5">
                                {{ $teacherName }}
                            </h4>
                            <span class="status-dot-pill status-{{ $status }}">
                                <span class="dot"></span>
                                {{ $status }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap text-muted small mt-1">
                            <span>
                                <i class="fa-regular fa-envelope me-1 text-muted"></i>
                                {{ $teacherUser?->email ?? 'No email provided' }}
                            </span>
                            <span>•</span>
                            <span>
                                <i class="fas fa-id-badge me-1 text-muted"></i>
                                {{ $teacherUser?->employee_id ? 'Employee ID: ' . $teacherUser->employee_id : 'No Employee ID' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Clean Advisory Class Presentation (Unstacked & Streamlined) --}}
                @if ($advisedSection)
                    <div class="d-flex align-items-center gap-3 flex-wrap ms-md-auto">
                        <div class="text-md-end">
                            <div class="text-muted small d-flex align-items-center justify-content-md-end gap-1 mb-0.5">
                                <i class="fas fa-chalkboard-user text-primary fa-xs"></i>
                                <span class="fw-semibold text-secondary text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Class Adviser</span>
                            </div>
                            <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-1.5">
                                <span class="me-2">{{ $advisedSection->name }}</span>
                                <span class="badge bg-light text-secondary border fw-medium px-2 py-0.5" style="font-size: 0.72rem;">Grade {{ $advisedSection->grade_level }}</span>
                            </div>
                        </div>
                        <a href="{{ route('student-management.section', ['grade' => $advisedSection->grade_level, 'section' => $advisedSection->id]) }}"
                            class="btn btn-sm btn-outline-primary px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium"
                            title="View student roster for Section {{ $advisedSection->name }}">
                            <i class="fas fa-users fa-xs"></i>
                            <span>View Class</span>
                        </a>
                    </div>
                @else
                    <div class="d-flex align-items-center gap-2 text-muted small ms-md-auto px-3 py-2 bg-light rounded-3 border">
                        <i class="fas fa-chalkboard-user text-secondary"></i>
                        <span>No advisory class assigned</span>
                    </div>
                @endif

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- Main Workspace Grid (Left: Details, Right: Teaching Assignments)          --}}
        {{-- ========================================================================= --}}
        <div class="row g-4">

            {{-- --------------------------------------------------------------------- --}}
            {{-- LEFT COLUMN: Profile Details                                          --}}
            {{-- --------------------------------------------------------------------- --}}
            <div class="col-lg-4">
                <div class="tw-panel">
                    <div class="tw-panel-header">
                        <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                            <i class="fas fa-address-card text-muted"></i>
                            <span>Profile Details</span>
                        </h6>
                        @if ($status === 'active')
                            <button class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-2"
                                data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                                <i class="fas fa-pen fa-xs me-1"></i> Edit
                            </button>
                        @endif
                    </div>
                    <div class="tw-panel-body">
                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-user text-muted fa-fw"></i> First Name
                            </span>
                            <span class="tw-info-value">{{ $teacherUser?->first_name ?? '—' }}</span>
                        </div>

                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-user text-muted fa-fw"></i> Last Name
                            </span>
                            <span class="tw-info-value">{{ $teacherUser?->last_name ?? '—' }}</span>
                        </div>

                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-envelope text-muted fa-fw"></i> Email
                            </span>
                            <span class="tw-info-value text-break">{{ $teacherUser?->email ?? '—' }}</span>
                        </div>

                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-id-badge text-muted fa-fw"></i> Employee ID
                            </span>
                            <span class="tw-info-value">
                                @if($teacherUser?->employee_id)
                                    <span class="badge bg-light text-dark border">{{ $teacherUser->employee_id }}</span>
                                @else
                                    <span class="text-muted fw-normal">Not Assigned</span>
                                @endif
                            </span>
                        </div>

                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-chalkboard-user text-muted fa-fw"></i> Advisory Class
                            </span>
                            <span class="tw-info-value">
                                @if($advisedSection)
                                    <span class="fw-semibold text-dark">{{ $advisedSection->name }}</span>
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.7rem;">Grade {{ $advisedSection->grade_level }}</span>
                                @else
                                    <span class="text-muted fw-normal">None</span>
                                @endif
                            </span>
                        </div>

                        <div class="tw-info-item">
                            <span class="tw-info-label">
                                <i class="fas fa-circle-check text-muted fa-fw"></i> Status
                            </span>
                            <span class="tw-info-value">
                                <span class="status-dot-pill status-{{ $status }}">
                                    <span class="dot"></span>
                                    {{ $status }}
                                </span>
                            </span>
                        </div>

                        @if ($teacher->created_at)
                            <div class="tw-info-item">
                                <span class="tw-info-label">
                                    <i class="fas fa-calendar-day text-muted fa-fw"></i> Registered On
                                </span>
                                <span class="tw-info-value text-muted fw-normal">{{ $teacher->created_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- --------------------------------------------------------------------- --}}
            {{-- RIGHT COLUMN: Teaching Assignments Table                              --}}
            {{-- --------------------------------------------------------------------- --}}
            <div class="col-lg-8">
                <div class="tw-panel">
                    
                    {{-- Card Header with Action Button --}}
                    <div class="tw-panel-header">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                <i class="fas fa-book-bookmark text-muted"></i>
                                <span>Teaching Assignments</span>
                            </h6>
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-0.5 ms-1" style="font-size: 0.72rem;">
                                {{ $totalAssignments }}
                            </span>
                        </div>

                        <button class="btn btn-dark btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                            data-bs-toggle="modal" data-bs-target="#assignmentModal"
                            onclick="openCreateAssignmentModal()">
                            <i class="fas fa-plus fa-xs"></i>
                            <span>Add Assignment</span>
                        </button>
                    </div>

                    {{-- Table or Empty State --}}
                    @if ($teacher->teachingAssignments->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="mb-2 text-muted opacity-50" style="font-size: 2rem;">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h6 class="fw-semibold text-dark mb-1">No Teaching Assignments Found</h6>
                            <p class="text-muted small mb-3">
                                This teacher currently has no subjects or sections assigned.
                            </p>
                            <button class="btn btn-dark btn-sm px-3 py-2 rounded-3"
                                data-bs-toggle="modal" data-bs-target="#assignmentModal"
                                onclick="openCreateAssignmentModal()">
                                <i class="fas fa-plus fa-xs me-1"></i> Add Assignment
                            </button>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table tw-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="32%">Subject</th>
                                        <th width="30%">Section & Grade</th>
                                        <th width="18%">School Year</th>
                                        <th width="10%">Status</th>
                                        <th width="10%" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teacher->teachingAssignments as $assignment)
                                        @php
                                            $assignStatus = strtolower($assignment->status ?? 'active');
                                        @endphp
                                        <tr data-assignment-id="{{ $assignment->id }}">
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $assignment->subject?->name ?? '—' }}</div>
                                                @if($assignment->subject?->code)
                                                    <div class="text-muted small" style="font-size: 0.72rem;">Code: {{ $assignment->subject->code }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student-management.section', ['grade' => $assignment->section?->grade_level ?? 1, 'section' => $assignment->section_id]) }}"
                                                    class="text-decoration-none text-dark fw-semibold hover-primary d-inline-flex align-items-center gap-1.5"
                                                    title="View students in {{ $assignment->section?->name }}">
                                                    <span>{{ $assignment->section?->name ?? '—' }}</span>
                                                    <i class="fas fa-arrow-up-right-from-square fa-2xs text-muted"></i>
                                                </a>
                                                <div class="mt-0.5">
                                                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 0.7rem;">
                                                        Grade {{ $assignment->section?->grade_level ?? '—' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-secondary small fw-medium">
                                                    {{ $assignment->schoolYear?->school_year ?? '—' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-dot-pill status-{{ $assignStatus }}">
                                                    <span class="dot"></span>
                                                    {{ $assignStatus }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <button class="btn btn-sm btn-outline-secondary rounded-2 d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px;"
                                                        data-bs-toggle="modal" data-bs-target="#assignmentModal"
                                                        onclick="openEditAssignmentModal({{ $assignment->id }}, {{ $assignment->subject_id }}, {{ $assignment->section_id }}, {{ $assignment->school_year_id }}, '{{ $assignment->status }}')"
                                                        title="Edit assignment">
                                                        <i class="fas fa-pen fa-xs"></i>
                                                    </button>
                                                    
                                                    <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}"
                                                        method="POST" data-ajax-delete="assignment" class="d-inline m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger rounded-2 d-inline-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;"
                                                            onclick="return confirm('Are you sure you want to delete this teaching assignment?');"
                                                            title="Delete assignment">
                                                            <i class="fas fa-trash fa-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>

    </div>


    {{-- ========================================================================= --}}
    {{-- Edit Teacher Profile Modal                                                --}}
    {{-- ========================================================================= --}}
    <x-modal id="editTeacherModal" modalTitle="Edit Teacher Information" size="modal-md">
        <form id="editTeacherForm" method="POST" data-ajax-form="teacher"
            data-update-url="{{ route('teachers.update', $teacher->id) }}">

            @csrf
            @method('PUT')

            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" id="edit_first_name" class="form-control rounded-3" required
                    value="{{ $teacherUser?->first_name ?? '' }}" placeholder="e.g. Maria">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" id="edit_last_name" class="form-control rounded-3" required
                    value="{{ $teacherUser?->last_name ?? '' }}" placeholder="e.g. Santos">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary small">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="edit_email" class="form-control rounded-3" required
                    value="{{ $teacherUser?->email ?? '' }}" placeholder="e.g. maria.santos@school.edu">
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium" data-loading-text="Saving...">
                    Save Changes
                </button>
            </div>

        </form>
    </x-modal>


    {{-- ========================================================================= --}}
    {{-- Add / Edit Teaching Assignment Modal                                      --}}
    {{-- ========================================================================= --}}
    <x-modal id="assignmentModal" modalTitle="Teaching Assignment" size="modal-lg">
        <form id="assignmentForm" method="POST" data-ajax-form>
            @csrf
            <input type="hidden" name="_method" value="POST" id="assignmentFormMethod">
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">

            <div data-ajax-errors></div>

            {{-- Informational Notice --}}
            <div class="p-3 mb-3 rounded-3 bg-light border d-flex align-items-center gap-2.5">
                <div class="tw-avatar" style="width: 38px; height: 38px; font-size: 0.85rem; border-radius: 8px;">
                    {{ $teacherInitials }}
                </div>
                <div class="min-w-0">
                    <div class="fw-bold text-dark small">{{ $teacherName }}</div>
                    <div class="text-muted small" style="font-size: 0.78rem;">Teacher is pre-selected for this assignment</div>
                </div>
            </div>

            <div class="row g-3">
                
                {{-- Subject Selection --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" id="field_subject_id" class="form-select rounded-3" required>
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section Selection --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small">Section & Grade Level <span class="text-danger">*</span></label>
                    <select name="section_id" id="field_section_id" class="form-select rounded-3" required>
                        <option value="">Select Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }} (Grade {{ $section->grade_level }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- School Year Selection --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small">Academic Year <span class="text-danger">*</span></label>
                    <select name="school_year_id" id="field_school_year_id" class="form-select rounded-3" required>
                        <option value="">Select School Year</option>
                        @foreach ($schoolYears as $sy)
                            <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Selection --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-secondary small">Assignment Status <span class="text-danger">*</span></label>
                    <select name="status" id="field_status" class="form-select rounded-3" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="d-flex gap-2 justify-content-end mt-4 pt-2 border-top">
                <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="btn btn-dark px-4 py-2 rounded-3 fw-medium" id="assignmentSubmitBtn">
                    <span id="assignmentSubmitText">Save Assignment</span>
                </button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
        const storeUrl = @json(route('teaching-assignments.store'));
        const updateUrlTemplate = @json(route('teaching-assignments.update', ':id'));
        const teacherWorkspaceUrl = @json(route('teachers.show', $teacher->id));

        function openCreateAssignmentModal() {
            const form = document.getElementById('assignmentForm');
            const method = document.getElementById('assignmentFormMethod');
            const submitText = document.getElementById('assignmentSubmitText');

            form.action = storeUrl;
            method.value = 'POST';
            submitText.textContent = 'Save Assignment';

            // Reset selects (teacher_id hidden field stays untouched)
            ['field_subject_id', 'field_section_id', 'field_school_year_id', 'field_status'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.selectedIndex = 0;
            });
        }

        function openEditAssignmentModal(assignmentId, subjectId, sectionId, schoolYearId, status) {
            const form = document.getElementById('assignmentForm');
            const method = document.getElementById('assignmentFormMethod');
            const submitText = document.getElementById('assignmentSubmitText');

            form.action = updateUrlTemplate.replace(':id', assignmentId);
            method.value = 'PUT';
            submitText.textContent = 'Save Changes';

            setSelectValue('field_subject_id', subjectId);
            setSelectValue('field_section_id', sectionId);
            setSelectValue('field_school_year_id', schoolYearId);
            setSelectValue('field_status', status);
        }

        function setSelectValue(id, value) {
            const el = document.getElementById(id);
            if (el) el.value = value;
        }

        // Auto-reload on successful assignment AJAX creation/update/deletion
        document.addEventListener('ajax-success', function (e) {
            const type = e.detail?.type;
            if (type === 'assignment' || type === 'teacher') {
                const assignModalEl = document.getElementById('assignmentModal');
                if (assignModalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(assignModalEl);
                    if (modal) modal.hide();
                }
                const editTeacherModalEl = document.getElementById('editTeacherModal');
                if (editTeacherModalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(editTeacherModalEl);
                    if (modal) modal.hide();
                }
                window.location.reload();
            }
        });
    </script>
    @endpush

</x-layouts.school-admin>
