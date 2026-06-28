<x-ui.table>
    <x-slot>
        <thead class="text-uppercase">
            <tr>
                <th style="width: 11%">
                    <span class="fas fa-id-card me-1"></span> Student Number
                </th>
                <th style="width: 12%">
                    <span class="fas fa-user-graduate me-1"></span> Student Name
                </th>
                <th style="width: 10%">
                    <span class="fas fa-signal me-1"></span> Level
                </th>
                <th style="width: 10%">
                    <span class="fas fa-chart-simple me-1"></span> Grade Level
                </th>
                <th style="width: 10%">
                    <span class="fas fa-users me-1"></span> Section
                </th>

                <th style="width: 10%">
                    <span class="fas fa-circle me-1"></span> Status
                </th>
                <th style="width: 8%">
                    <span class="fas fa-sliders-h me-1"></span> Actions
                </th>
            </tr>
        </thead>


        @forelse ($enrollments as $enrollment)
            <tbody>
                <tr>
                    <td> {{ $enrollment->student->student_number ?? '-'}}</td>
                    <td>{{ $enrollment->student->first_name ?? ''}}
                        {{ $enrollment->student->last_name ?? '' }}
                    </td>
                    <td> {{ $enrollment->section->level }}</td>
                    <td>{{$enrollment->section->grade_level  }}</td>
                    <td> {{ $enrollment->section->name }}</td>
                    <td> {{ $enrollment->status }}</td>
                    <td class="whitespace-nowrap">
                        <div class="dropdown position-static">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#viewEnrollmentModal" data-bs-toggle="modal"
                                        data-bs-target="#viewEnrollmentModal"
                                        data-student-name="{{ $enrollment->student->first_name ?? '' }} {{ $enrollment->student->last_name ?? '' }}"
                                        data-student-lrn="{{ $enrollment->student->lrn ?? '' }}"
                                        data-school-year="{{ $enrollment->schoolYear->school_year ?? '' }}"
                                        data-level="{{ $enrollment->level }}"
                                        data-grade-level="{{ $enrollment->grade_level }}"
                                        data-section="{{ $enrollment->section }}"
                                        data-session-type="{{ $enrollment->session_type }}"
                                        data-status="{{ $enrollment->status }}"
                                        data-created-at="{{ $enrollment->created_at?->format('Y-m-d') ?? '' }}">View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#editEnrollmentModal" data-bs-toggle="modal"
                                        data-bs-target="#editEnrollmentModal" data-id="{{ $enrollment->id }}"
                                        data-student-name="{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name ?? 'N/A'}}"
                                        data-student-lrn="{{ $enrollment->student->lrn }}"
                                        data-level="{{ $enrollment->level }}"
                                        data-grade-level="{{ $enrollment->grade_level }}"
                                        data-section="{{ $enrollment->section }}"
                                        data-session-type="{{ $enrollment->session_type }}"
                                        data-status="{{ $enrollment->status }}"
                                        data-update-url="{{ route('enrollment.update', $enrollment->id) }}">
                                        Edit
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('enrollment.destroy', $enrollment->id) }}" method="POST"
                                        data-ajax-delete="enrollment">
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
                    <td colspan="7" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                            <p class="mb-0">No student found for the selected criteria</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-slot>
</x-ui.table>

<div class="pagination">
    {{ $enrollments->links() }}
</div>
</div>



{{-- add enrollment modal --}}
<x-modal>
    <x-slot name="id">addEnrollmentModal</x-slot>
    <x-slot name="modalTitle">Add Enrollment</x-slot>

    <form id="enrollmentForm" action="{{ route('enrollment.store') }} " method="POST">
        @csrf
        @method('POST')
        {{-- Student Search --}}
        <div class="mb-3">
            <label class="form-label">Search Student</label>
            <div style="position: relative;">
                <input type="text" id="enrollmentStudentSearch" class="form-control"
                    placeholder="Search by student number or name..." autocomplete="off" />
                <div id="enrollmentSearchResults" style="
                    display: none;
                    position: absolute;
                    top: 100%; left: 0; right: 0;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 6px;
                    z-index: 9999;
                    max-height: 200px;
                    overflow-y: auto;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                "></div>
            </div>
            <input type="hidden" name="student_id" id="enrollmentStudentId" />
            <div id="enrollmentSelectedStudent" class="mt-2 px-2 py-1 bg-light rounded d-flex align-items-center gap-2"
                style="display:none!important;">
                <small class="text-muted">Selected:</small>
                <span id="enrollmentSelectedName" class="fw-semibold small"></span>
                <button type="button" id="enrollmentClearStudent"
                    class="btn btn-sm btn-link text-danger p-0 ms-auto">✕</button>
            </div>
            <div class="invalid-feedback">Please select a student from the list.</div>
        </div>

        {{-- School Year & Level --}}
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Level</label>
                <select class="form-select" name="level" required>
                    <option value="" disabled selected>Select level</option>
                    <option value="elementary">Elementary</option>
                    <option value="hs">High School</option>
                    <option value="shs">Senior High School</option>
                </select>
            </div>
        </div>

        {{-- Grade Level & Section --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Grade Level</label>
                <select class="form-select" name="grade_level" required>
                    <option value="" disabled selected>Select grade</option>
                    @foreach(['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Section</label>
                <input type="text" class="form-control" name="section" placeholder="e.g. Rizal, Section A" required />
            </div>
        </div>

        {{-- Session Type & Status --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Session Type</label>
                <select class="form-select" name="session_type" required>
                    <option value="" disabled selected>Select session</option>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="whole_day">Whole Day</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <option value="" disabled selected>Select status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-dark w-100">Add Enrollment</button>
    </form>

</x-modal>





{{-- edit enrollment modal --}}
<x-modal>
    <x-slot name="id">
        editEnrollmentModal
    </x-slot>
    <x-slot name="modalTitle">
        Edit Enrollment
    </x-slot>

    <form id="editEnrollmentForm" action="" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="_method" value="PUT">

        <div class="d-flex align-items-center gap-3 bg-light rounded-3 px-3 border py-3 mb-4">
            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-medium text-primary"
                style="width: 44px; height: 44px; font-size: 24px; flex-shrink: 0;">
                <i class="fa-solid fa-user-graduate"></i>
            </div>

            <div class="flex-grow-1">
                <p class="mb-0 fw-bold" id="editStudentName">Select an enrollment</p>
                <small class="text-muted" id="editStudentLrn">LRN</small>
            </div>
        </div>
        <div class="row">

            <div class="col-md-6 mb-3">
                <label class="form-label">Level</label>
                <select class="form-select" name="level" id="edit_level" required>
                    <option value="" disabled selected>Select level</option>
                    <option value="elementary">Elementary</option>
                    <option value="hs">High School</option>
                    <option value="shs">Senior High School</option>
                </select>
            </div>
        </div>

        {{-- Grade Level & Section --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Grade Level</label>
                <select class="form-select" name="grade_level" id="edit_grade_level" required>
                    <option value="" disabled selected>Select grade</option>
                    @foreach(['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'] as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Section</label>
                <input type="text" class="form-control" name="section" placeholder="e.g. Rizal, Section A"
                    id="edit_section" required />
            </div>
        </div>

        {{-- Session Type & Status --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Session Type</label>
                <select class="form-select" name="session_type" id="edit_session_type" required>
                    <option value="" disabled selected>Select session</option>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="whole_day">Whole Day</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" id="edit_status" required>
                    <option value="" disabled selected>Select status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark">Save Changes</button>
        </div>

    </form>

</x-modal>


<x-modal>
    <x-slot name="id"> viewEnrollmentModal</x-slot>
    <x-slot name="modalTitle">
        <span>
            <i class="fa fa-clipboard-list"></i>
        </span> Enrollment Record <br>
        <span>
            <small class="text-muted fw-normal ms-2" style="font-size:67%">View only — no changes will be
                made</small>
        </span>
    </x-slot>

    <div class="d-flex align-items-center gap-3 bg-light rounded-3 px-3 border py-3 mb-4">
        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-medium text-primary"
            style="width: 44px; height: 44px; font-size: 24px; flex-shrink: 0;">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
        <div class="flex-grow-1">
            <p class="mb-0 fw-bold" id="viewStudentName">Select an enrollment</p>
            <small class="text-muted" id="viewStudentLrn">LRN</small>
        </div>
        <span class="badge rounded-pill bg-success-subtle text-success fw-medium px-3"
            id="viewEnrollmentStatus">status</span>
    </div>

    <!-- Academic information -->
    <p class="text-uppercase text-muted fw-medium mb-2" style="font-size: 11px; letter-spacing: 0.07em;">
        Enrollment Information
    </p>
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">School
                    year</p>
                <p class="mb-0 fw-medium" style="font-size: 14px;" id="viewSchoolYear">-</p>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">Grade
                    level</p>
                <p class="mb-0 fw-medium" style="font-size: 14px;" id="viewGradeLevel">-</p>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">Section
                </p>
                <p class="mb-0 fw-medium" style="font-size: 14px;" id="viewSection">-</p>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light rounded-3 px-3 py-2">
                <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;"> Class
                    Adviser</p>
                <p class="mb-0 fw-medium" style="font-size: 14px;">Adviser here</p>
            </div>
        </div>
    </div>

    <!-- Enrollment details -->
    <p class="text-uppercase text-muted fw-medium mb-2" style="font-size: 11px; letter-spacing: 0.07em;">
        Enrollment details
    </p>
    <div class="row g-3 mb-4 mx-1">
        <div class="col-6">
            <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;"> Date Enrolled
            </p>
            <p class="mb-0" style="font-size: 14px;" id="viewCreatedAt">-</p>
        </div>
        <div class="col-6">
            <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">Department Level
            </p>
            <p class="mb-0" style="font-size: 14px;" id="viewLevel">-</p>
        </div>
        <div class="col-6">
            <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">Enrolled by
            </p>
            <p class="mb-0" style="font-size: 14px;">Admin – R. Santos</p>
        </div>
        <div class="col-6">
            <p class="text-uppercase text-muted mb-1" style="font-size: 10px; letter-spacing: 0.06em;">Status</p>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle bg-success d-inline-block" style="width: 7px; height: 7px;"></span>
                <p class="mb-0" style="font-size: 14px;" id="viewDetailStatus">-</p>
            </div>
        </div>
    </div>


    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
    </div>

    </div>




</x-modal>
