<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Section Management</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addSectionModal" data-ajax-scope="#section-table-pane">
                <span>+ Add Section</span>
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel shadow-sm">
        <div class="p-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <input type="search" class="form-control" name="section_search"
                        value="{{ request('section_search') }}" placeholder="Search by section name or grade level..."
                        data-tab-filter data-tab-scope="#section-table-pane" data-page-param="section_page">
                </div>

                <div class="col-lg-3">
                    <select name="section_grade_level" class="form-select" data-tab-filter
                        data-tab-scope="#section-table-pane" data-page-param="section_page">
                        <option value="">All Grade Levels</option>
                        @for ($grade = 1; $grade <= 12; $grade++)
                            <option value="{{ $grade }}" {{ request('section_grade_level') == $grade ? 'selected' : '' }}>
                                Grade {{ $grade }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-3">
                    <select name="section_status" class="form-select" data-tab-filter
                        data-tab-scope="#section-table-pane" data-page-param="section_page">
                        <option value="">All Status</option>
                        <option value="active" {{ request('section_status') === 'active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="inactive" {{ request('section_status') === 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="12%">Section</th>
                    <th width="10%">Grade</th>
                    <th width="15%">Level</th>
                    <th width="22%">Adviser</th>
                    <th width="10%">Capacity</th>
                    <th width="10%">Status</th>
                    <th width="8%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($sections as $section)
                    <tr>
                        <td>{{ $section->name }}</td>
                        <td>Grade {{ $section->grade_level }}</td>
                        <td>{{ Str::headline(str_replace('_', ' ', $section->level)) }}</td>
                        <td>{{ $section->advisor?->full_name ?? 'Not Assigned' }}</td>
                        <td>{{ $section->students_count }} / {{ $section->capacity }}</td>
                        <td>
                            @if ($section->status === 'active')
                                <span class="badge-dot dot-success">Active</span>
                            @else
                                <span class="badge-dot dot-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <button type="button" class="dropdown-item js-edit-section" data-bs-toggle="modal"
                                            data-bs-target="#editSectionModal" data-id="{{ $section->id }}"
                                            data-name="{{ $section->name }}" data-level="{{ $section->level }}"
                                            data-grade-level="{{ $section->grade_level }}"
                                            data-advisor-id="{{ $section->advisor_id ?? '' }}"
                                            data-capacity="{{ $section->capacity }}" data-status="{{ $section->status }}"
                                            data-ajax-scope="#section-table-pane">
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    @if ($section->status === 'active')
                                        <li>
                                            <form action="{{ route('sections.destroy', $section->id) }}" method="POST"
                                                data-ajax-delete="section" data-ajax-scope="#section-table-pane">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Archive</button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form action="{{ route('sections.restore', $section->id) }}" method="POST"
                                                data-ajax-restore="section" data-ajax-scope="#section-table-pane">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item text-success">Restore</button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">No sections found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-3">
            {{ $sections->links() }}
        </div>
    </div>
</div>

<x-modal id="addSectionModal" modalTitle="Add Section" size="modal-md">
    <form id="addSectionForm" action="{{ route('sections.store') }}" method="POST"
        data-ajax-scope="#section-table-pane">
        @csrf
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Section Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Level</label>
                <select name="level" class="form-select" id="add_section_level" required>
                    <option value="" selected disabled>Select level</option>
                    <option value="elementary">Elementary</option>
                    <option value="highschool">High School</option>
                    <option value="senior_high_school">Senior High School</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Grade Level</label>
                <select name="grade_level" class="form-select" id="add_section_grade_level" required>
                    <option value="" selected disabled>Select grade level</option>
                    @for ($grade = 1; $grade <= 12; $grade++)
                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Adviser</label>
            <select name="advisor_id" class="form-select">
                <option value="">Not Assigned</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Capacity</label>
                <input type="number" name="capacity" class="form-control" min="1" max="100" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Creating...">Create Section</button>
        </div>
    </form>
</x-modal>

<x-modal id="editSectionModal" modalTitle="Edit Section" size="modal-md">
    <form id="editSectionForm" method="POST" data-update-url="{{ route('sections.update', ':id') }}"
        data-ajax-scope="#section-table-pane">
        @csrf
        @method('PUT')
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Section Name</label>
            <input type="text" name="name" id="edit_section_name" class="form-control" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Level</label>
                <select name="level" id="edit_section_level" class="form-select" required>
                    <option value="elementary">Elementary</option>
                    <option value="highschool">High School</option>
                    <option value="senior_high_school">Senior High School</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Grade Level</label>
                <select name="grade_level" id="edit_section_grade_level" class="form-select" required>
                    @for ($grade = 1; $grade <= 12; $grade++)
                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Adviser</label>
            <select name="advisor_id" id="edit_section_advisor_id" class="form-select">
                <option value="">Not Assigned</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Capacity</label>
                <input type="number" name="capacity" id="edit_section_capacity" class="form-control" min="1" max="100"
                    required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" id="edit_section_status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Saving...">Save Changes</button>
        </div>
    </form>
</x-modal>