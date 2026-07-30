<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">QR Code Generation</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addQRModal" data-ajax-scope="#qr-table-pane">
                <span>+ Generate QR</span>
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel shadow-sm">
        <div class="p-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <input type="search" class="form-control" name="qr_search" value="{{ request('qr_search') }}"
                        placeholder="Search by student name or LRN..." data-tab-filter data-tab-scope="#qr-table-pane"
                        data-page-param="qr_page">
                </div>

                <div class="col-lg-3">
                    <select name="qr_grade_level" class="form-select" data-tab-filter data-tab-scope="#qr-table-pane"
                        data-page-param="qr_page">
                        <option value="">All Grade Levels</option>
                        @for ($grade = 1; $grade <= 12; $grade++)
                            <option value="{{ $grade }}" {{ request('qr_grade_level') == $grade ? 'selected' : '' }}>
                                Grade {{ $grade }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-3">
                    <select name="qr_status" class="form-select" data-tab-filter data-tab-scope="#qr-table-pane"
                        data-page-param="qr_page">
                        <option value="">All Status</option>
                        <option value="active" {{ request('qr_status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('qr_status') === 'inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="20%">Student</th>
                    <th width="10%">Grade</th>
                    <th width="15%">Section</th>
                    <th width="10%">Status</th>
                    <th width="15%">QR Code</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td>
                            <div>
                                <div class="fw-semibold">{{ $student->full_name }}</div>
                                <small class="text-muted">{{ $student->lrn }}</small>
                            </div>
                        </td>
                        <td>Grade {{ $student->grade_level }}</td>
                        <td>{{ $student->section?->name ?? 'Not Enrolled' }}</td>
                        <td>
                            @if ($student->status === 'active')
                                <span class="badge-dot dot-success">Active</span>
                            @else
                                <span class="badge-dot dot-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if ($student->qr_code)
                                <div class="text-center">
                                    <img src="{{ asset('storage/' . $student->qr_code) }}" alt="QR Code"
                                        class="qr-code-thumbnail">
                                    <div class="mt-1">
                                        <a href="{{ asset('storage/' . $student->qr_code) }}" download
                                            class="btn btn-sm btn-outline-primary" title="Download QR">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Not generated</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <button type="button" class="dropdown-item js-generate-qr" data-bs-toggle="modal"
                                            data-bs-target="#generateQRModal" data-id="{{ $student->id }}"
                                            data-name="{{ $student->full_name }}" data-ajax-scope="#qr-table-pane">
                                            Generate QR Code
                                        </button>
                                    </li>
                                    @if ($student->qr_code)
                                        <li>
                                            <form action="{{ route('qr.destroy', $student->id) }}" method="POST"
                                                data-ajax-delete="qr" data-ajax-scope="#qr-table-pane">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Delete QR Code</button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No students found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-3">
            {{ $students->links() }}
        </div>
    </div>
</div>

<x-modal id="addQRModal" modalTitle="Generate QR Code for Multiple Students" size="modal-lg">
    <form id="bulkQRForm" action="{{ route('qr.bulkGenerate') }}" method="POST" data-ajax-scope="#qr-table-pane">
        @csrf
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Select Students</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll" value="">
                <label class="form-check-label" for="selectAll">Select All</label>
            </div>
            <div class="mt-2" id="studentList">
                @foreach ($students as $student)
                    <div class="form-check">
                        <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]"
                            value="{{ $student->id }}" id="student{{ $student->id }}">
                        <label class="form-check-label" for="student{{ $student->id }}">
                            {{ $student->full_name }} (Grade {{ $student->grade_level }})
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Generating...">Generate QR Codes</button>
        </div>
    </form>
</x-modal>

<x-modal id="generateQRModal" modalTitle="Generate QR Code" size="modal-md">
    <form id="singleQRForm" method="POST" data-update-url="{{ route('qr.generate', ':id') }}"
        data-ajax-scope="#qr-table-pane">
        @csrf
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Student</label>
            <input type="text" class="form-control" id="qrStudentName" readonly>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Generating...">Generate QR Code</button>
        </div>
    </form>
</x-modal>

<style>
    .qr-code-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 4px;
    }
</style>