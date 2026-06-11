<x-layouts.admin>
<x-slot name="title">
{{ $student->first_name }}  {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }} 
</x-slot>

<x-slot name="pageName">
Student Profile
</x-slot>
<x-slot name="subtitle">

</x-slot>

<div class="container-fluid pb-4">

    {{-- ===================== HEADER ===================== --}}
    <div class="bg-white border rounded-3 p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

            {{-- Left: Avatar + Name + Badge --}}
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-user-graduate text-primary" style="font-size: 20px;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">
                        {{ $student->first_name }}
                        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}
                    </h5>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                            <i class="fa-solid fa-circle-check me-2" style="font-size: 10px;"></i>
                                {{ $student->enrollments->first()->status ?? '-'}}
                        </span>
                        <span class="text-muted small">
                            <i class="fa-solid fa-hashtag me-1" style="font-size: 10px;"></i>
                            {{ $student->student_number }}
                        </span>
                        <span class="text-muted small">
                            <i class="fa-solid fa-id-card me-1" style="font-size: 10px;"></i>
                            LRN: {{ $student->lrn }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Quick stats --}}
            <div class="d-flex align-items-center gap-2 flex-wrap  ">

                <div class="border rounded-3 px-3 py-2 text-center" style="min-width: 90px;">
                    <p class="mb-0 fw-bold text-primary">
                        {{ $student->enrollments->first()->schoolYear->school_year ?? '—' }}
                    </p>
                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">
                        School Year
                    </small>
                </div>

                <div class="border rounded-3 px-3 py-2 text-center" style="min-width: 90px;">
                    <p class="mb-0 fw-bold">
                        {{ $student->enrollments->first()->grade_level ?? '—' }}
                    </p>
                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">
                        Grade Level
                    </small>
                </div>

                <div class="border rounded-3 px-3 py-2 text-center" style="min-width: 90px;">
                    <p class="mb-0 fw-bold">
                        {{ $student->enrollments->first()->section ?? '—' }}
                    </p>
                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">
                        Section
                    </small>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== MAIN GRID ===================== --}}
    <div class="row g-3 align-items-start">

        {{-- ===== LEFT COLUMN ===== --}}
        <div class="col-xl-4 d-flex flex-column gap-3">

            {{-- Personal Info --}}
            <div class="bg-white border rounded-3 p-4">
                <h6 class="fw-semibold text-uppercase text-muted mb-3 pb-2 border-bottom"
                    style="font-size: 11px; letter-spacing: 0.07em;">
                    <i class="fa-solid fa-user me-2"></i> Personal Information
                </h6>

                <div class="mb-3">
                    <p class="text-muted mb-1 small text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.06em;">Full name</p>
                    <p class="mb-0 fw-medium">
                        {{ $student->first_name }}
                        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}
                    </p>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <p class="text-muted mb-1 small text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.06em;">Sex</p>
                        <p class="mb-0">{{ $student->sex }}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1 small text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.06em;">Birthdate</p>
                        <p class="mb-0">
                            {{ \Carbon\Carbon::parse($student->birthdate)->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.06em;">Address</p>
                    <p class="mb-0 text-secondary" style="line-height: 1.5;">{{ $student->address }}</p>
                </div>
            </div>

            {{-- Guardian Info --}}
            <div class="bg-white border rounded-3 p-4">
                <h6 class="fw-semibold text-uppercase text-muted mb-3 pb-2 border-bottom"
                    style="font-size: 11px; letter-spacing: 0.07em;">
                    <i class="fa-solid fa-people-roof me-2"></i> Guardian Information
                </h6>

                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-person text-secondary" style="font-size: 16px;"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-medium">{{ $student->guardian->name }}</p>
                        <small class="text-muted">{{ $student->guardian->relationship }}</small>
                    </div>
                </div>

                <div>
                    <p class="text-muted mb-1 small text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.06em;">Email address</p>
                    <p class="mb-0">
                        <i class="fa-regular fa-envelope me-1 text-muted" style="font-size: 12px;"></i>
                        {{ $student->guardian->email }}
                    </p>
                </div>
            </div>

        </div>

        {{-- ===== RIGHT COLUMN ===== --}}
        <div class="col-xl-8">
            <div class="bg-white border rounded-3 p-4 d-flex flex-column" style="min-height: 55vh;">

                {{-- Tabs --}}
                <ul class="nav nav-tabs border-bottom mb-0" id="studentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-3 py-2"
                                id="academic-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#academic"
                                type="button" role="tab">
                            <i class="fa-solid fa-graduation-cap me-2"></i> Academic
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-2"
                                id="attendance-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#attendance"
                                type="button" role="tab">
                            <i class="fa-solid fa-calendar-check me-2"></i> Attendance
                        </button>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content flex-grow-1 overflow-auto pt-4">

                    {{-- ACADEMIC TAB --}}
                    <div class="tab-pane fade show active" id="academic" role="tabpanel">

                        {{-- IDs row --}}
                        <p class="text-uppercase text-muted fw-semibold mb-2"
                           style="font-size: 10px; letter-spacing: 0.07em;">
                            Student IDs
                        </p>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Student number</p>
                                    <p class="mb-0 fw-medium">{{ $student->student_number }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">LRN</p>
                                    <p class="mb-0 fw-medium">{{ $student->lrn }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Enrollment row --}}
                        <p class="text-uppercase text-muted fw-semibold mb-2"
                           style="font-size: 10px; letter-spacing: 0.07em;">
                            Current enrollment
                        </p>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Status</p>
                                    @if($student->enrollments)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            {{ $student->enrollments->first()->status ?? '-' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            No active enrollment
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">School year</p>
                                    <p class="mb-0 fw-medium"> {{ $student->enrollments->first()->schoolYear->school_year ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Session type</p>
                                    <p class="mb-0 fw-medium">{{ $student->enrollments->first()->session_type ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Adviser</p>
                                    <p class="mb-0 fw-medium">{{ $student->enrollments->first()->adviser ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Grade level</p>
                                    <p class="mb-0 fw-medium">{{ $student->enrollments->first()->grade_level ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-light rounded-3 px-3 py-2">
                                    <p class="text-muted mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">Section</p>
                                    <p class="mb-0 fw-medium">{{ $student->enrollments->first()->section ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- ATTENDANCE TAB --}}
                    <div class="tab-pane fade" id="attendance" role="tabpanel">

                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size: 10px; letter-spacing: 0.07em;">
                            Attendance Summary
                        </p>

                        <x-ui.table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop attendance logs here --}}
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fa-regular fa-calendar-xmark me-2"></i>
                                        No attendance records found.
                                    </td>
                                </tr>
                            </tbody>
                        </x-ui.table>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

```

</x-layouts.admin>