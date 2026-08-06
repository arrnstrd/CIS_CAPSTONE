<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Subject Records</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addSubjectModal" data-ajax-scope="#subject-table-pane">
                <span>+ Add Subject</span>
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel shadow-sm">
        <div class="p-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <input type="search" class="form-control" name="subject_search"
                        value="{{ request('subject_search') }}" placeholder="Search subject code, name, or level..."
                        data-tab-filter data-tab-scope="#subject-table-pane" data-page-param="subject_page">
                </div>

                <div class="col-lg-4">
                    <select name="subject_level" class="form-select" data-tab-filter
                        data-tab-scope="#subject-table-pane" data-page-param="subject_page">
                        <option value="">All Levels</option>
                        @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                            <option value="{{ $value }}" {{ \App\Models\Subject::normalizeLevel(request('subject_level')) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="20%">Code</th>
                    <th width="45%">Subject</th>
                    <th width="25%">Level</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($subjects as $subject)
                    <tr>
                        <td>{{ $subject->code }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>{{ $subject->level_label }}</td>
                        <td>
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <button type="button" class="dropdown-item js-edit-subject" data-bs-toggle="modal"
                                            data-bs-target="#editSubjectModal" data-id="{{ $subject->id }}"
                                            data-code="{{ $subject->code }}" data-name="{{ $subject->name }}"
                                            data-level="{{ $subject->level }}"
                                            data-update-url="{{ route('subjects.update', $subject) }}"
                                            data-ajax-scope="#subject-table-pane">
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('subjects.destroy', $subject) }}" method="POST"
                                            data-ajax-delete="subject" data-ajax-scope="#subject-table-pane">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">No subjects found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-3">
            {{ $subjects->links() }}
        </div>
    </div>
</div>

<x-modal id="addSubjectModal" modalTitle="Add Subject" size="modal-md">
    <form id="addSubjectForm" action="{{ route('subjects.store') }}" method="POST" data-ajax-form="subject"
        data-ajax-scope="#subject-table-pane">
        @csrf
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Code</label>
            <input type="text" name="code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Subject Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Level</label>
            <select name="level" class="form-select" required>
                <option value="" selected disabled>Select level</option>
                @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Creating...">Create Subject</button>
        </div>
    </form>
</x-modal>

<x-modal id="editSubjectModal" modalTitle="Edit Subject" size="modal-md">
    <form id="editSubjectForm" method="POST" data-ajax-form="subject" data-ajax-scope="#subject-table-pane">
        @csrf
        @method('PUT')
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Code</label>
            <input type="text" name="code" id="edit_subject_code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Subject Name</label>
            <input type="text" name="name" id="edit_subject_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Level</label>
            <select name="level" id="edit_subject_level" class="form-select" required>
                @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Saving...">Save Changes</button>
        </div>
    </form>
</x-modal>