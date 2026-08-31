<x-layouts.teacher>
    <x-slot name="pageName">
        Student Management
    </x-slot>

    <x-slot name="subtitle">
        <span class="ra-page-subtitle">View and manage students in your classes.</span>
    </x-slot>


                    @if (!$sectionId)
                        <div class="card border mx-3 mb-3">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-4">Select a class to view students</h2>
                                <label class="form-label text-muted text-uppercase small fw-bold">My Classes</label>
                                <div class="row g-3">
                                    @forelse ($classCards as $classCard)
                                        @php
                                            $classQuery = request()->except('section_id');
                                            $classQuery['section_id'] = $classCard->id;
                                        @endphp
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <a href="{{ route('teacher.student-management') . '?' . http_build_query($classQuery) }}"
                                                class="text-decoration-none">
                                                <div class="gs-panel">
                                                    <p class="gs-panel-title mb-2">
                                                        Grade {{ $classCard->grade_level }} - {{ $classCard->name }}
                                                    </p>
                                                    <div class="gs-row-subtext">
                                                        <span>
                                                            <i class="fa-solid fa-users me-1"></i>
                                                            {{ $classCard->student_count }} students
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="gs-chart-empty">
                                                <i class="fa-solid fa-chalkboard gs-chart-empty-icon"></i>
                                                <p class="mb-0">No active classes found yet.</p>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        @php
                            $selectedClass = $classCards->firstWhere('id', $sectionId);
                        @endphp

                        <div class="mx-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <a href="{{ route('teacher.student-management') }}" class="btn btn-outline-primary mb-2">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back to my classes
                                    </a>
                                    <p class="ra-section-card-name mb-0">
                                        Grade {{ $selectedClass?->grade_level }} - {{ $selectedClass?->name }}
                                    </p>
                                </div>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#addStudentModal"
                                    data-grade="{{ $selectedClass?->grade_level }}">
                                    <i class="fa-solid fa-plus me-1"></i> Add Student
                                </button>
                            </div>

                        <div class="card border mb-3">
                            <div class="card-body p-5" style="padding: 1.25rem 1.5rem !important;">
                                <form action="{{ route('teacher.student-management') }}" method="GET">
                                    <input type="hidden" name="section_id" value="{{ $sectionId }}">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                                        <div class="input-group">
                                            <input type="search" name="query" class="form-control" value="{{ request('query') }}"
                                                placeholder="Student number, name or LRN">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <x-ui.table>
                        <thead>
                            <tr>
                                <th style="width: 30%">Student</th>
                                <th style="width: 22%">LRN</th>
                    <th style="width: 28%">Grade &amp; Section</th>
                    <th style="width: 20%">Actions</th>
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
                        <td>{{ $student->lrn }}</td>
                        <td>
                            @if ($student->section_name)
                                Grade {{ $student->grade_level }} - {{ $student->section_name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('teacher.student-profile', $student->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No students found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if (method_exists($students, 'links'))
            <div class="px-3 py-3">
                {{ $students->links() }}
            </div>
        @endif
        </div>
    @endif

    @include('admin-modules.management.students.partials.add-student-modal')

</x-layouts.teacher>
