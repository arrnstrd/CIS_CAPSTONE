<x-layouts.teacher>
    <x-slot name="pageName">
        Student Management
    </x-slot>


    <div class="card border mx-3 mb-3">
        <div class="card-body p-4">
            <form action="{{ route('teacher.student-management') }}" method="GET">

                <div class="row g-3 align-items-end">
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

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Sort</label>
                        <select class="form-select" name="sort" onchange="this.form.submit()">
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
                        <label class="form-label text-muted text-uppercase small fw-bold">My Classes</label>
                        <select class="form-select" name="section_id" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                    Grade {{ $section->grade_level }} - {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">Grade Level</label>
                        <select class="form-select" name="grade_level" onchange="this.form.submit()">
                            <option value="">All Grades</option>
                            @foreach ($gradeLevels as $grade)
                                <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                    Grade {{ $grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label text-muted text-uppercase small fw-bold">School Year</label>
                        <select class="form-select" name="school_year_id" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach ($schoolYears as $sy)
                                <option value="{{ $sy->id }}" {{ request('school_year_id') == $sy->id ? 'selected' : '' }}>
                                    {{ $sy->school_year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-auto ms-lg-auto d-flex gap-2">
                        <a href="{{ route('teacher.student-management') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
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
                <th style="width: 28%">Grade & Section</th>
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
                        <a href="{{ route('teacher.student-profile', $student->id) }}" target="_blank"
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

    <div class="px-3 py-3">
        {{ $students->links() }}
    </div>



</x-layouts.teacher>
