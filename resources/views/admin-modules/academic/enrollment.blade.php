<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Unenrolled Students</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addEnrollmentModal"
                data-ajax-scope="#enrollment-table-pane"
                @disabled(! $activeSchoolYear)>
                <span>+ Add Enrollment</span>
            </button>
        </div>

        @if (! $activeSchoolYear)
            <div class="alert alert-warning mt-4 mb-0">
                No active school year found. Create or activate a school year before adding enrollments.
            </div>
        @endif
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel shadow-sm">
        <div class="p-3 border-bottom">
            <input type="search"
                class="form-control"
                name="enrollment_search"
                value="{{ request('enrollment_search') }}"
                placeholder="Search student number, LRN, or name..."
                data-tab-filter
                data-tab-scope="#enrollment-table-pane"
                data-page-param="enrollment_page">
        </div>

        <table class="table table-hover align-middle table-striped mb-0">
            <thead>
                <tr>
                    <th style="width: 15%">Student No.</th>
                    <th style="width: 20%">LRN</th>
                    <th style="width: 50%">Full name</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($notEnrolledStudents as $student)
                    <tr>
                        <td>{{ $student->student_number ?? '-' }}</td>
                        <td>{{ $student->lrn ?? '-' }}</td>
                        <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-dark"
                                data-bs-toggle="modal"
                                data-bs-target="#addEnrollmentModal"
                                data-ajax-scope="#enrollment-table-pane"
                                data-student-id="{{ $student->id }}"
                                data-student-number="{{ $student->student_number ?? '' }}"
                                data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                            >
                                Enroll
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No unenrolled students found for the active school year.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination px-3 py-3">
            {{ $notEnrolledStudents->links() }}
        </div>
    </div>
</div>
