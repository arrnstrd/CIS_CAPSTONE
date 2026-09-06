<x-layouts.school-admin>
    <x-slot name="title">
        Grade {{ $grade }} — {{ $section->name }} Students
    </x-slot>

    <x-slot name="subtitle">
        Grade {{ $grade }} · Section {{ $section->name }} · Adviser: {{ $section->advisor?->full_name ?? 'Not Assigned' }}
    </x-slot>

    <x-slot name="pageName">
        Students · {{ $section->name }}
    </x-slot>

    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 border">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h3 class="fw-semibold text-dark m-0 fs-5">Section {{ $section->name }}</h3>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                            <i class="fas fa-chalkboard-user me-1"></i> Adviser: {{ $section->advisor?->full_name ?? 'Not Assigned' }}
                        </span>
                        <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill">
                            Grade {{ $grade }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Students currently enrolled in Section {{ $section->name }}.
                    </p>
                </div>

                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <a href="{{ route('student-management.grade', $grade) }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5">
                        <i class="fas fa-arrow-left fa-sm"></i>
                        <span>Back to Sections</span>
                    </a>

                    <a href="{{ route('bulk-import') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium">
                        <i class="fas fa-file-import fa-sm"></i>
                        <span>Bulk Import</span>
                    </a>

                    <button type="button" class="btn btn-outline-success px-3 py-2 rounded-3 d-inline-flex align-items-center gap-1.5 fw-medium"
                        data-bs-toggle="modal" data-bs-target="#exportFormatModal"
                        title="Download XLSX file for this section">
                        <i class="fas fa-file-excel fa-sm"></i>
                        <span>Download XLSX</span>
                    </button>

                    <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center gap-1.5"
                        data-bs-toggle="offcanvas" data-bs-target="#addStudentSidePanel" data-grade="{{ $grade }}"
                        data-default-section="{{ $section->id }}">
                        <i class="fas fa-plus fa-sm"></i>
                        <span>Add Student</span>
                    </button>
                </div>
            </div>

            {{-- Icon-Driven Filter Toolbar --}}
            <div class="row g-2.5 align-items-center">
                
                {{-- Search Box --}}
                <div class="col-12 col-md-5 col-lg-4">
                    <form action="{{ route('student-management.section', ['grade' => $grade, 'section' => $section->id]) }}" method="GET" class="d-flex">
                        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                        @if(request('school_year_id')) <input type="hidden" name="school_year_id" value="{{ request('school_year_id') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fas fa-search fa-sm"></i>
                            </span>
                            <input type="search" name="query" class="form-control border-start-0 ps-0"
                                placeholder="Search name, LRN, student #..." value="{{ request('query') }}">
                        </div>
                    </form>
                </div>

                {{-- Full Icon Filter Dropdowns --}}
                <div class="col-12 col-md-7 col-lg-8">
                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
                        
                        {{-- Sort Icon Filter --}}
                        @php
                            $sort = request('sort', 'last_name_asc');
                            $sortLabels = [
                                'last_name_asc' => 'Last name: A to Z',
                                'last_name_desc' => 'Last name: Z to A',
                                'first_name_asc' => 'First name: A to Z',
                                'first_name_desc' => 'First name: Z to A',
                                'student_number_asc' => 'Student ID: Low to High',
                                'student_number_desc' => 'Student ID: High to Low',
                            ];
                            $currentSortLabel = $sortLabels[$sort] ?? 'Sort';
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark bg-white"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Sort students">
                                <i class="fas fa-arrow-down-a-z text-secondary"></i>
                                <span class="small fw-medium">{{ $currentSortLabel }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                @foreach ($sortLabels as $val => $label)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 small {{ $sort === $val ? 'active fw-semibold' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['sort' => $val]) }}">
                                            <span>{{ $label }}</span>
                                            @if($sort === $val)
                                                <i class="fas fa-check fa-xs"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- School Year Icon Filter --}}
                        @php
                            $selectedSyId = request('school_year_id', 'all');
                            $currentSy = $schoolYears->firstWhere('id', $selectedSyId);
                            $syLabel = $currentSy ? 'S.Y. ' . $currentSy->school_year : 'All School Years';
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark bg-white"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Filter by School Year">
                                <i class="fa-regular fa-calendar text-secondary"></i>
                                <span class="small fw-medium">{{ $syLabel }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 small {{ $selectedSyId === 'all' ? 'active fw-semibold' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['school_year_id' => 'all']) }}">
                                        <span>All School Years</span>
                                        @if($selectedSyId === 'all')
                                            <i class="fas fa-check fa-xs"></i>
                                        @endif
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach ($schoolYears as $sy)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 small {{ (string)$selectedSyId === (string)$sy->id ? 'active fw-semibold' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['school_year_id' => $sy->id]) }}">
                                            <span>S.Y. {{ $sy->school_year }}</span>
                                            @if((string)$selectedSyId === (string)$sy->id)
                                                <i class="fas fa-check fa-xs"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Status Icon Filter --}}
                        @php
                            $statusFilter = request('status', 'all');
                            $statusLabels = [
                                'all' => 'All Status',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ];
                            $currentStatusLabel = $statusLabels[$statusFilter] ?? 'Status';
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 text-dark bg-white"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Filter by Status">
                                <i class="fas fa-filter text-secondary"></i>
                                <span class="small fw-medium">{{ $currentStatusLabel }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                @foreach ($statusLabels as $val => $label)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center justify-content-between gap-2 small {{ $statusFilter === $val ? 'active fw-semibold' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['status' => $val]) }}">
                                            <span>{{ $label }}</span>
                                            @if($statusFilter === $val)
                                                <i class="fas fa-check fa-xs"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Clear Filters Button --}}
                        @if(request('query') || (request('sort') && request('sort') !== 'last_name_asc') || (request('school_year_id') && request('school_year_id') !== 'all') || (request('status') && request('status') !== 'all'))
                            <a href="{{ route('student-management.section', ['grade' => $grade, 'section' => $section->id]) }}"
                                class="btn btn-outline-secondary px-2.5 py-2 rounded-3 d-inline-flex align-items-center" title="Clear Filters">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    <x-ui.table>
        <x-slot>
            <thead class="text-uppercase">
                <tr>
                    <th style="width: 32%">
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
                    <th style="width: 8%">
                        <span class="fas fa-circle me-1"></span> Status
                    </th>
                    <th style="width: 10%" class="text-end">
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
                        <td class="text-end whitespace-nowrap">
                            <div class="d-inline-flex align-items-center gap-2 pe-1">
                                {{-- View Action Icon --}}
                                <a href="{{ url('/student-profile/' . $student->id) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-2 d-inline-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;" title="View Profile">
                                    <i class="fas fa-eye fa-xs"></i>
                                </a>

                                {{-- Edit Action Icon --}}
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-2 d-inline-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;"
                                    data-bs-toggle="modal" data-bs-target="#editStudentModal"
                                    data-id="{{ $student->id }}"
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
                                    data-enrollment_status="{{ $student->enrollment_status ?? 'active' }}"
                                    title="Edit Student">
                                    <i class="fas fa-pen fa-xs"></i>
                                </button>

                                {{-- Delete Action Icon --}}
                                <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                    data-ajax-delete="student" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger rounded-2 d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px;"
                                        onclick="return confirm('Are you sure you want to delete this student?');"
                                        title="Delete Student">
                                        <i class="fas fa-trash fa-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0 fw-semibold">No students found in Section {{ $section->name }}</p>
                                <p class="mb-0">There are no students enrolled in this section matching your filters.</p>
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

    @include('pov.school-admin.students.partials.add-student-modal')
    @include('pov.school-admin.students.partials.edit-student-modal')
    @include('pov.school-admin.students.partials.export-format-modal', ['exportRoute' => route('student-management.section.export', ['grade' => $grade, 'section' => $section->id])])

</x-layouts.school-admin>
