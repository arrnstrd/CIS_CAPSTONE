<x-layouts.admin>
    <x-slot name="title">
        Enrollment
    </x-slot>
    <x-slot name="pageName">
        Enrollment
    </x-slot>

    <div class="container-fluid">
        <div class="row mb-3 mx-4">
            <div class="d-flex justify-content-center align-items-center gap-3">
                <x-card title="total active enrollments" value="0" icon="fa-solid fa-user-check" variants="primary" />
                <x-card title="elementary students" value="0" icon="fa-solid fa-child" variants="primary" />
                <x-card title="high school students" value="0" icon="fa-solid fa-user-graduate" variants="primary" />
                <x-card title="senior high students" value="0" icon="fa-solid fa-graduation-cap" variants="primary" />
            </div>
        </div>

        <div class="col mb-3 mx-2">
            <div class="bg-white rounded p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Enrollment Records</h3>
                    <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                        <span>+ Add Enrollment</span>
                    </button>
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-10 col-md-7 col-lg-8">
                        <form action=" " method="GET" class="d-flex w-100 max-width-md">

                            <div class="input-group">
                                <!-- Search Input -->
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search student's number or name..." value=" "
                                    aria-label="Search student's number or name..." required>

                                <!-- Search Button -->
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>

                            </div>

                        </form>
                    </div>

                    <div class="col-6 col-md-2.5 col-lg-2">
                        <select name="school_year" id="schoolYearFilter"
                            class="form-select rounded-3 py-2 border-light-subtle" aria-label="Filter by School Year">
                            <option value="all" selected>All School Years</option>
                            <option value="2025-2026">2025 - 2026</option>
                            <option value="2024-2025">2024 - 2025</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2.5 col-lg-2">
                        <select name="level" id="levelFilter" class="form-select rounded-3 py-2 border-light-subtle"
                            aria-label="Filter by Level">
                            <option value="all" selected>All Levels</option>
                            <option value="elementary">Elementary</option>
                            <option value="high-school">High School</option>
                            <option value="senior-high">Senior High School</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <x-ui.table>
            <x-slot>
                <thead class="text-uppercase text-center">
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>School Year</th>
                        <th>Level</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Session Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                @forelse ($enrollments as $enrollment)

                    <tbody class="text-center">
                        <tr>
                            <td> {{ $enrollment->student->student_number ?? '-'}}</td>
                            <td>{{ $enrollment->student->first_name ?? ''}}
                                {{ $enrollment->student->last_name ?? '' }}
                            </td>
                            <td> {{ $enrollment->school_year }}</td>
                            <td> {{ $enrollment->level }}</td>
                            <td>{{$enrollment->grade_level  }}</td>
                            <td> {{ $enrollment->section }}</td>
                            <td> {{ $enrollment->session_type }}</td>
                            <td> {{ $enrollment->status }}</td>
                            <td> </td>
                        </tr>
                    </tbody>

                @empty
                    <p class="small text-muted"> No enrollment for students on the records yet</p>

                @endforelse
            </x-slot>
        </x-ui.table>

        <div class="pagination">
            {{ $enrollments->links() }}
        </div>
    </div>


    <x-modal>
        <x-slot name="id">addEnrollmentModal</x-slot>
        <x-slot name="modalTitle">Add Enrollment</x-slot>

        <form id="enrollmentForm" action="{{ route('enrollments.store') }}" method="POST">
            @csrf

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
                <div id="enrollmentSelectedStudent"
                    class="mt-2 px-2 py-1 bg-light rounded d-flex align-items-center gap-2"
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
                    <label class="form-label">School Year</label>
                    <input type="text" class="form-control" name="school_year" placeholder="e.g. 2026-2027" required />
                </div>
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

    @push('scripts')
        <script src="{{ asset('js/enrollment.js') }}"></script>
    @endpush

</x-layouts.admin>