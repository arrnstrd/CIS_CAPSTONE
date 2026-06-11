<x-layouts.admin>
    <x-slot name="title">
        Enrollment
    </x-slot>
    <x-slot name="pageName">
        Enrollment
    </x-slot>


    <div class="container-fluid">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 mb-3 g-3">
            <x-card title="total active enrollments" value="{{ $statusCounts['total'] ?? 0 }}" icon="fa-solid fa-user-check" variants="primary" />
            <x-card title="total unenrolled students" value="{{ $studentWithoutEnrollment ?? 0 }}" icon="fa fa-solid fa-user-xmark" variants="primary" />
            <x-card title="elementary students" value="{{ $statusCounts['elementary'] ?? 0 }}" icon="fa-solid fa-child" variants="primary" />
            <x-card title="high school students" value="{{ $statusCounts['hs'] ?? 0 }}" icon="fa-solid fa-user-graduate" variants="primary" />
            <x-card title="senior high students" value="{{ $statusCounts['shs'] ?? 0 }}" icon="fa-solid fa-graduation-cap" variants="primary" />
        </div>


       
        <div class="col mb-3 mx-2">
            <div class="bg-white rounded p-4 border">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Enrollment Records</h3>
                    <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
                        <span>+ Add Enrollment</span>
                    </button>
                </div>

                <div class="row g-3 align-items-center">
                    <form action="{{ route('enrollment.index') }}" method="GET">
                        <div class="row g-3 align-items-center">
                            <div class="col-10 col-md-7 col-lg-8">
                                <div class="input-group">
                                    <input type="search" name="query" class="form-control"
                                        placeholder="Search student's number or name..." value="{{ request('query') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>

                            <div class="col-6 col-md-2.5 col-lg-2">
                                <select name="school_year_id" class="form-select rounded-3 py-2 border-light-subtle"
                                    onchange="this.form.submit()">
                                    <option value="{{ $activeSchoolYear->id }}" selected>
                                        {{ $activeSchoolYear->school_year }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-6 col-md-2.5 col-lg-2">
                                <select class="form-select" name="grade_level" onchange="this.form.submit()">
                                    <option value="all" {{ request('grade_level', 'all') === 'all' ? 'selected' : '' }}>
                                        All Grade Levels
                                    </option>

                                    @foreach(['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'] as $grade)
                                        <option value="{{ $grade }}" {{ request('grade_level') === $grade ? 'selected' : '' }}>
                                            {{ $grade }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>



        
        {{-- Tabs --}}
        <ul class="nav nav-tabs mx-2" id="enrollmentTabs" role="tablist">

            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#enrolled-tab">
                    Enrolled
                </button>
            </li>

            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#not-enrolled-tab">
                    Unenrolled
                </button>
            </li>

        </ul>

        <div class="tab-content mt-2">

            <div class="tab-pane fade show active" id="enrolled-tab">
                @include('admin-modules.management.enrollment.partials.enrolled-table')
            </div>

            <div class="tab-pane fade" id="not-enrolled-tab">
               @include('admin-modules.management.enrollment.partials.not-enrolled-table')
            </div>

        </div>







</x-layouts.admin>