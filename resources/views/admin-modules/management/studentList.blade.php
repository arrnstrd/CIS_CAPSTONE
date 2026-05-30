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

            <thead class="text-uppercase">
                <tr>
                    <th style="width: 10%">Student No.</th>
                    <th style="width: 11%">Full Name</th>

                    <th style="width: 13%">LRN</th>
                    <th style="width: 14%">Sex</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 8%">Actions</th>
                </tr>
            </thead>
            @forelse($students as $student)
                <tbody>

                    <tr>
                        <td>{{  $student->student_number  }} </td>
                        <td> {{  $student->last_name }},
                            {{  $student->first_name }}
                            {{  $student->middle_name }}
                        </td>
                        <td>{{  $student->lrn }} </td>
                        <td>{{  $student->sex }} </td>
                        <td> {{  $student->status }} </td>
                        <td class="whitespace-nowrap">
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ url('/student-profile/' . $student->id) }}">View</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#editStudentModal" data-bs-toggle="modal"
                                            data-bs-target="#editStudentModal"
                                            data-id="{{ $student->id }}"
                                            data-lrn="{{ $student->lrn }}" 
                                            data-first_name="{{ $student->first_name }}"
                                            data-last_name="{{ $student->last_name }}"
                                            data-middle_name="{{ $student->middle_name }}" 
                                            data-sex="{{ $student->sex }}"
                                            data-address="{{ $student->address }}"
                                            data-birthdate="{{ $student->birthdate }}" 
                                            data-status="{{ $student->status }}"
                                            data-name="{{ $student->guardian->name ?? '' }}"
                                            data-relationship="{{ $student->guardian->relationship ?? '' }}"
                                            data-email="{{ $student->guardian->email ?? '' }}">
                                            Edit
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>

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


            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark">Add Student</button>
            </div>
        </form>

    </x-modal>



    {{-- edit modal --}}
    <x-modal>
        <x-slot name="id">
            editStudentModal
        </x-slot>
        <x-slot name="modalTitle">
            Edit Student
        </x-slot>

        <form id="editStudentForm" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col">
                    <div class="mb-3">
                        <label class="form-label">Student LRN</label>
                        <input type="text" class="form-control" id="edit_lrn" name="lrn" placeholder="e.g. 123456789012"
                            inputmode="numeric" maxlength="12" required />
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required />
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Middle Name <span
                                    class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" class="form-control" id="edit_middle_name" name="middle_name" />
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Sex</label>
                            <select class="form-select" id="edit_sex" name="sex" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address"
                            placeholder="House No., Street, Brgy., City" required />
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_birthdate" name="birthdate" required />
                        </div>

                        <div class="col mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="mb-3">
                        <label class="form-label">Guardian Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Guardian Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required />
                    </div>

                    <div class="col mb-3">
                        <label class="form-label">Relationship</label>
                        <select class="form-select" id="edit_relationship" name="relationship" required>
                            <option value="mother">Mother</option>
                            <option value="father">Father</option>
                            <option value="sibling">Sibling</option>
                            <option value="guardian">Guardian</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark">Save Changes</button>
            </div>
        </form>

    </x-modal>


















</x-layouts.admin>