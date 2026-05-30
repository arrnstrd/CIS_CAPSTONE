<x-layouts.admin>
    <div class="container-fluid">

        <!-- HEADER -->
        <div class="bg-white shadow-sm rounded p-3 mb-3">
            <h5 class="fw-bold mb-1">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }} </h5>
            <span class="badge rounded-pill text-success bg-success-subtle border border-success-subtle">
                Enrolled
            </span>
        </div>

        <!-- MAIN GRID -->
        <div class="row g-3 align-items-start">

            <!-- LEFT COLUMN (FIXED STACK) -->
            <div class="col-xl-4 d-flex flex-column gap-3">

                <!-- PERSONAL INFO -->
                <div class="bg-white shadow-sm rounded p-4">
                    <h6 class="fw-bold text-uppercase text-muted border-bottom pb-2">
                        Personal Information
                    </h6>

                    <div class="mt-3">
                        <label class="text-muted fw-semibold small text-uppercase">Fullname</label>
                        <p class="fw-semibold">{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }} </p>
                    </div>

                    <div class="mb-2">
                        <label class="text-muted fw-semibold small text-uppercase">Sex</label>
                        <p>{{$student->sex}}</p>
                    </div>

                    <div class="mb-2">
                        <label class="text-muted fw-semibold small text-uppercase">Birthdate</label>
                        <p>{{ $student->birthdate }}</p>
                    </div>

                    <div class="mb-2">
                        <label class="text-muted fw-semibold small text-uppercase">Address</label>
                        <p> {{ $student->address }}</p>
                    </div>
                </div>

                <!-- GUARDIAN INFO -->
                <div class="bg-white shadow-sm rounded p-4">
                    <h6 class="fw-bold text-uppercase text-muted border-bottom pb-2">
                        Guardian Information
                    </h6>

                    <div class="mt-3">
                        <label class="text-muted fw-semibold small text-uppercase">Fullname</label>
                        <p class="fw-semibold">{{ $student->guardian->name }}</p>
                    </div>

                    <div class="mb-2">
                        <label class="text-muted fw-semibold small text-uppercase">Relationship</label>
                        <p>{{ $student->guardian->relationship }}</p>
                    </div>

                    <div class="mb-2">
                        <label class="text-muted fw-semibold small text-uppercase">Email Address</label>
                        <p>{{ $student->guardian->email }}</p>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (FIXED HEIGHT TABS) -->
            <div class="col-xl-8">

                <div class="bg-white shadow-sm rounded p-4 d-flex flex-column"
                     style="height: 50vh;">

                    <!-- TABS -->
                    <ul class="nav nav-tabs" id="studentTabs" role="tablist">

                        <li class="nav-item" role="presentation">
                            <button class="nav-link active"
                                    id="academic-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#academic"
                                    type="button">
                                Academic
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link"
                                    id="attendance-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#attendance"
                                    type="button">
                                Attendance Overview
                            </button>
                        </li>

                    </ul>

                    <!-- TAB CONTENT (SCROLLABLE) -->
                    <div class="tab-content flex-grow-1 overflow-auto mt-3">

                        <!-- ACADEMIC -->
                        <div class="tab-pane mt-3 fade show active" id="academic">

                            <div class="row mx-2">
                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">Student Number</label>
                                    <p>{{ $student->student_number }}</p>
                                </div>

                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">LRN Number</label>
                                    <p>{{ $student->lrn }}</p>
                                </div>
                            </div>

                            <div class="row mx-2">
                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">Enrollment Status</label><br>
                                    <span class="badge text-success bg-success-subtle border border-success-subtle text-uppercase">
                                     {{ $student->enrollment->status ?? "No active enrollment"}}

                                     @php
                                  
                                     @endphp
                                    </span>
                                </div>

                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">School Year</label>
                                    <p> {{ $student->enrollment->school_year ??"no active enrollment"}}</p>
                                </div>
                            </div>

                            <div class="row mx-2">
                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">Session Type</label>
                                    <p> {{ $student->enrollment->session_type ?? "no active enrollment" }}</p>
                                </div>

                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">adviser</label>
                                    <p>Mrs/ Ana Reyes</p>
                                </div>
                            </div>

                               <div class="row mx-2">
                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">Grade Level</label>
                                    <p>Grade 7</p>
                                </div>

                                <div class="col mb-3">
                                    <label class="text-muted fw-semibold small text-uppercase">Section</label>
                                    <p>Anahaw</p>
                                </div>
                            </div>

                        </div>

                        <!-- ATTENDANCE -->
                        <div class="tab-pane fade" id="attendance">

                            <div class="card shadow-sm p-3">
                                <h6 class="fw-bold mb-3">Attendance Summary</h6>
                                <x-ui.table>
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Date</th>
                                            <th>Time </th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </x-ui.table>
                             
                            </div>

                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</x-layouts.admin>