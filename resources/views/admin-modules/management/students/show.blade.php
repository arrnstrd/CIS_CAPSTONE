<x-layouts.admin>
    <x-slot name="title">
        Grade {{ $grade }} Students
    </x-slot>

    <x-slot name="subtitle">
        Students currently associated with Grade {{ $grade }}.
    </x-slot>

    <x-slot name="pageName">
        Students · Grade {{ $grade }}
    </x-slot>

    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 border">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-semibold text-dark m-0 fs-5">Grade {{ $grade }} Students</h3>
                    <p class="text-muted small mb-0">Students currently associated with Grade {{ $grade }}.</p>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#addStudentModal" data-grade="{{ $grade }}">
                        <span>+ Add Student</span>
                    </button>

                    <a href="{{ route('student-management.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Grade Selection
                    </a>
                </div>
            </div>

            <form action="{{ route('student-management.grade', $grade) }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-4">
                        <div class="input-group">
                            <input type="search" name="query" class="form-control"
                                placeholder="Search by name, LRN, or student number..." value="{{ request('query') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="last_name_asc" {{ request('sort', 'last_name_asc') === 'last_name_asc' ? 'selected' : '' }}>
                                Last name A-Z
                            </option>
                            <option value="last_name_desc" {{ request('sort') === 'last_name_desc' ? 'selected' : '' }}>
                                Last name Z-A
                            </option>
                            <option value="first_name_asc" {{ request('sort') === 'first_name_asc' ? 'selected' : '' }}>
                                First name A-Z
                            </option>
                            <option value="first_name_desc" {{ request('sort') === 'first_name_desc' ? 'selected' : '' }}>
                                First name Z-A
                            </option>
                            <option value="student_number_asc" {{ request('sort') === 'student_number_asc' ? 'selected' : '' }}>
                                Student ID A-Z
                            </option>
                            <option value="student_number_desc" {{ request('sort') === 'student_number_desc' ? 'selected' : '' }}>
                                Student ID Z-A
                            </option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <select name="school_year_id" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('school_year_id', 'all') === 'all' ? 'selected' : '' }}>
                                All School Years
                            </option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ request('school_year_id') == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->school_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <select name="section" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('section', 'all') === 'all' ? 'selected' : '' }}>
                                All Sections
                            </option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section') == $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>
                                All Status
                            </option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <x-ui.table>
        <x-slot>
            <thead class="text-uppercase">
                <tr>
                    <th style="width: 30%">
                        <span class="fas fa-user me-1"></span> Student Name
                    </th>
                    <th style="width: 18%">
                        <span class="fas fa-id-card me-1"></span> LRN
                    </th>
                    <th style="width: 16%">
                        <span class="fas fa-signal me-1"></span> Grade Level
                    </th>
                    <th style="width: 16%">
                        <span class="fas fa-users me-1"></span> Section
                    </th>
                    <th style="width: 10%">
                        <span class="fas fa-circle me-1"></span> Status
                    </th>
                    <th style="width: 10%">
                        <span class="fas fa-sliders-h me-1"></span> Actions
                    </th>
                </tr>
            </thead>

            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="table-name-cell">
                            @php
                                $firstName = $student->first_name ?? '';
                                $lastName = $student->last_name ?? '';
                                $studentName = trim($firstName . ' ' . $lastName) ?: '-';
                                $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '--';
                            @endphp
                            <div class="table-name-wrap">
                                <div class="table-name-avatar">{{ $initials }}</div>
                                <div class="table-name-copy">
                                    <span class="table-name-main">{{ $studentName }}</span>
                                    <span class="table-name-sub">{{ $student->student_number ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->lrn ?? '-' }}</td>
                        <td>Grade {{ $student->grade_level }}</td>
                        <td>{{ $student->section_name }}</td>
                        <td>
                            @if ($student->enrollment_status === 'active')
                                <span class="badge-dot dot-success">Active</span>
                            @else
                                <span class="badge-dot dot-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ url('/student-profile/' . $student->id) }}"
                                            target="_blank">View</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#editStudentModal" data-bs-toggle="modal"
                                            data-bs-target="#editStudentModal" data-id="{{ $student->id }}"
                                            data-lrn="{{ $student->lrn }}" data-first_name="{{ $student->first_name }}"
                                            data-middle_name="{{ $student->middle_name }}"
                                            data-last_name="{{ $student->last_name }}" data-suffix="{{ $student->suffix }}"
                                            data-sex="{{ $student->sex }}" data-address="{{ $student->address }}"
                                            data-age="{{ $student->age }}" data-status="{{ $student->status }}"
                                            data-guardian_name="{{ $student->guardian->name ?? '' }}"
                                            data-guardian_relationship="{{ $student->guardian->relationship ?? '' }}"
                                            data-guardian_contact="{{ $student->guardian->contact_number ?? '' }}"
                                            data-guardian_email="{{ $student->guardian->email ?? '' }}"
                                            data-school_year_id="{{ $student->school_year_id ?? '' }}"
                                            data-grade_level="{{ $student->grade_level }}"
                                            data-section_id="{{ $student->section_id ?? '' }}"
                                            data-enrollment_status="{{ $student->enrollment_status ?? 'active' }}">
                                            Edit
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                            data-ajax-delete="student">
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
                        <td colspan="6" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0 fw-semibold">No students found</p>
                                <p class="mb-0">There are no students matching the selected grade level and
                                    filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-slot>
    </x-ui.table>

    <div class="px-3 py-3">
        {{ $students->links() }}
    </div>

    @include('admin-modules.management.students.partials.add-student-modal')
    @include('admin-modules.management.students.partials.edit-student-modal')

</x-layouts.admin>
