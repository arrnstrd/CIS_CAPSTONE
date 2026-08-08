<x-layouts.admin>

    <x-slot name="pageName">Settings</x-slot>

    <div class="container-fluid py-4">
        <div class="row">

            <div class="col-12 col-md-5 col-lg-4 mb-4">
                <div class="d-flex flex-column align-items-center">

                    <div class="mb-4 d-flex align-items-center justify-content-center text-center"
                        style="width: 250px; height: 250px; border-radius: 50%; background-color: #d1d5db; overflow: hidden;">
                        <img src="{{ asset('images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo"
                            class="img-fluid">
                    </div>

                    <div class="w-100 p-2 mb-3 fs-5 text-center fw-bold">
                        CONCEPCION INTEGRATED SCHOOL
                    </div>

                    <div class="w-100 p-3 text-center">
                        [Short Description Placeholder Text]
                    </div>

                </div>
            </div>

            <div class="col-12 col-md-8 col-lg-8">

                <div class="border rounded-2 p-4 mb-5 bg-white">
                    <div class="mb-2">
                        <span class="fw-bold me-2" style="font-size: 0.85rem;">NAME</span>
                        <span class="text-muted">[Sample Admin Name]</span>
                    </div>
                    <div>
                        <span class="fw-bold me-2" style="font-size: 0.85rem;">EMAIL</span>
                        <span class="text-muted">[sample.admin@example.com]</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-end mb-2">
                    <div>
                        <h6 class="mb-1 text-uppercase fw-bold" style="font-size: 0.9rem;">School Year</h6>
                        <span class="text-muted" style="font-size: 0.85rem;">short description for adding and managing
                            school year</span>
                    </div>
                    <button class="btn btn-sm btn-dark rounded px-3" data-bs-toggle="modal"
                        data-bs-target="#addSchoolYearModal" data-ajax-scope="#school-year-table-pane">
                        + Add New
                    </button>
                </div>

                <div id="school-year-table-pane">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>School year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schoolYears as $schoolYear)
                                <tr>
                                    <td>{{ $schoolYear->school_year }}</td>
                                    <td>
                                        @if ($schoolYear->is_active)
                                            <span class="badge-dot dot-success">Active</span>
                                        @else
                                            <span class="badge-dot dot-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown position-static">
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>

                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button type="button" class="dropdown-item js-edit-school-year"
                                                        data-bs-toggle="modal" data-bs-target="#editSchoolYearModal"
                                                        data-id="{{ $schoolYear->id }}"
                                                        data-school-year="{{ $schoolYear->school_year }}"
                                                        data-is-active="{{ $schoolYear->is_active ? '1' : '0' }}"
                                                        data-ajax-scope="#school-year-table-pane">
                                                        Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                @if ($schoolYear->is_active)
                                                    <li>
                                                        <form action="{{ route('school-years.destroy', $schoolYear->id) }}"
                                                            method="POST" data-ajax-delete="school-year"
                                                            data-ajax-scope="#school-year-table-pane">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="dropdown-item text-danger">Archive</button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form action="{{ route('school-years.restore', $schoolYear->id) }}"
                                                            method="POST" data-ajax-restore="school-year"
                                                            data-ajax-scope="#school-year-table-pane">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                class="dropdown-item text-success">Restore</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">No school years yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>

            </div>
        </div>
    </div>

    {{-- Add School Year Modal --}}
    <x-modal id="addSchoolYearModal" modalTitle="Add School Year" size="modal-md">
        <form id="addSchoolYearForm" action="{{ route('school-years.store') }}" method="POST"
            data-ajax-scope="#school-year-table-pane">
            @csrf
            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label fw-semibold">School Year</label>
                <input type="text" name="school_year" class="form-control" placeholder="e.g. 2026-2027" required>
            </div>

            <div class="form-check form-switch mb-3">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="add_is_active" checked>
                <label class="form-check-label" for="add_is_active">Set as active school year</label>
            </div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" data-loading-text="Creating...">Create School Year</button>
            </div>
        </form>
    </x-modal>

    {{-- Edit School Year Modal --}}
    <x-modal id="editSchoolYearModal" modalTitle="Edit School Year" size="modal-md">
        <form id="editSchoolYearForm" method="POST" data-update-url="{{ route('school-years.update', ':id') }}"
            data-ajax-scope="#school-year-table-pane">
            @csrf
            @method('PUT')
            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label fw-semibold">School Year</label>
                <input type="text" name="school_year" id="edit_school_year" class="form-control" required>
            </div>

            <div class="form-check form-switch mb-3">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                <label class="form-check-label" for="edit_is_active">Set as active school year</label>
            </div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" data-loading-text="Saving...">Save Changes</button>
            </div>
        </form>
    </x-modal>

</x-layouts.admin>