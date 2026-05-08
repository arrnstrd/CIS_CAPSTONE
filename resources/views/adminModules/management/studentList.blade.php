<x-layouts.admin>
    <x-slot name="title">
        Student List
    </x-slot>

    <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        + Add Student
    </button>
    

    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        
                    </div>
                    <form action=" {{ route('students.store') }}" method="POST">
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
                                    <input type="text" class="form-control" name="name"
                                        required />
                                </div>

                                 <div class=" mb-3">
                                    <label class="form-label">Guardian Email</label>
                                    <input type="email" class="form-control" name="email"
                                        required />
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
                </div>
            </div>
        </div>
    </div>

















    


</x-layouts.admin>