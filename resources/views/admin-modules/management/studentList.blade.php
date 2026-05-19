<x-layouts.admin>
    <x-slot name="title">
        Student List
    </x-slot>


    <x-slot name="pageName">
        Student
    </x-slot>




    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 shadow-sm">
              <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Student Records</h3>
                    <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <span>+ Add Student</span>
                    </button>
                </div>
            <form action="{{ route('addStudent') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-10 col-md-7 col-lg-8">
                        <div class="input-group">
                            <input type="search" name="query" class="form-control"
                                placeholder="Search by name or student number..." value="{{ request('query') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-6 col-md-2.5 col-lg-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status
                            </option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2.5 col-lg-2">
                        <select name="sex" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('sex', 'all') === 'all' ? 'selected' : '' }}>All Sex</option>
                            <option value="female" {{ request('sex') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="male" {{ request('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        </select>
                    </div>
                </div>
            </form>


        </div>
    </div>


    <x-ui.table>

        <x-slot>

            <thead class="text-uppercase text-center">
                <tr>
                    <th class="ms-0">Student No.</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>LRN</th>
                    <th>Sex</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            @forelse($students as $student)
                <tbody class="text-center">

                    <tr>
                        <td>{{  $student->student_number  }} </td>
                        <td> {{  $student->last_name }}</td>
                        <td>{{  $student->first_name }} </td>
                        <td>{{  $student->middle_name }} </td>
                        <td>{{  $student->lrn }} </td>
                        <td>{{  $student->sex }} </td>
                        <td> {{  $student->status }} </td>
                        <td> </td>

                    </tr>
                </tbody>


            @empty
                <p class="small text-muted"> No student on the records yet</p>

            @endforelse

        </x-slot>

    </x-ui.table>
    <div class="pagination">
        {{ $students->links() }}

    </div>




    {{-- modal for add student --}}
    <x-modal>
        <x-slot name="id">
            addStudentModal
        </x-slot>


        <x-slot name="modalTitle">
            Add Student
        </x-slot>

        <form action=" {{ route('student.store') }}" method="POST">
            @csrf
            {{-- @method('PUT') --}}

            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label class="form-label">Student LRN</label>
                        <input type="text" class="form-control" name="lrn" placeholder="e.g. 123456789012"
                            inputmode="numeric" maxlength="12" required />

                    </div>



                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label"> First Name</label>
                            <input type="text" class="form-control" name="first_name" required />
                        </div>
                        <div class=" col mb-3">
                            <label class="form-label"> Last Name</label>
                            <input type="text" class="form-control" name="last_name" required />
                        </div>
                    </div>

                    {{-- //middle name and sex --}}
                    <div class="row">
                        <div class=" col mb-3">
                            <label class="form-label"> Middle Name <span class="text-muted fst-italic">
                                    (Optional)</span> </label>
                            <input type="text" class="form-control" name="middle_name" />
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Sex</label>
                            <select class="form-select" name="sex" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>

                            </select>
                        </div>

                    </div>

                    <div class=" mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address"
                            placeholder="House No., Street, Brgy., City" required />
                    </div>


                    <div class="row">
                        <div class=" col mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="birthdate" required />
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class=" mb-3">
                        <label class="form-label">Guardian Name</label>
                        <input type="text" class="form-control" name="name" required />
                    </div>

                    <div class=" mb-3">
                        <label class="form-label">Guardian Email</label>
                        <input type="email" class="form-control" name="email" required />
                    </div>


                    <div class="col mb-3">
                        <label class="form-label">Relationship</label>
                        <select class="form-select" name="relationship" required>
                            <option value="mother">Mother</option>
                            <option value="father">Father</option>
                            <option value="sibling">Sibling</option>
                            <option value="guardian">Guardian</option>
                        </select>
                    </div>


                </div>


            </div>



            <button type="submit" class="btn btn-dark w-100">Add Student</button>
        </form>

    </x-modal>






















</x-layouts.admin>