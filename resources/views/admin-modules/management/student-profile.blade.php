<x-layouts.admin>
    <div class="container-fluid">
        <div class="col mb-2">
            <div class="bg-white shadow-sm rounded p-3">
                <h5 class="fw-bold"> Juan Dela Cruz</h5>
                <span
                    class="badge rounded-pill text-success bg-success-subtle border border-success-subtle">Enrolled</span>
            </div>
        </div>

        <div class="row align-items-start">

            {{-- row one left- personal info --}}
            <div class="col col-xl-4 mb-3">
                <div class="bg-white shadow-sm rounded p-4">
                    <h6 class="fw-bold text-uppercase text-muted  border-bottom ">Personal Information</h6>
                    <div class="col mb-2 mt-3">
                        <label class="text-muted fw-semibold small text-uppercase"> Fullname</label>
                        <p class="fw-semibold"> Juan Dela Cruz</p>
                    </div>
                    <div class="col mb-2">
                        <label class="text-muted fw-semibold small text-uppercase"> Sex</label>
                        <p> Male</p>
                    </div>
                    <div class="col mb-2">
                        <label class="text-muted fw-semibold small text-uppercase"> birthdate</label>
                        <p> July 14 2004</p>
                    </div>
                    <div class="col mb-2">
                        <label class="text-muted fw-semibold small text-uppercase"> Address</label>
                        <p> 123 Purok Bahay Pare Candaba Pampanga</p>
                    </div>

                </div>
            </div>

            {{-- row 2 left nav and tabs --}}
            <div class="col col-xl-8">
                <div class="bg-white shadow-sm rounded p-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Academic</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Grade</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Attendance</a>
                        </li>
                       
                    </ul>

                </div>
            </div>
        </div>


        <div class="col col-xl-4">
            <div class="bg-white shadow-sm rounded p-4">
                <h6 class="fw-bold text-uppercase text-muted  border-bottom ">Personal Information</h6>
                <div class="col mb-2 mt-3">
                    <label class="text-muted fw-semibold small text-uppercase"> Fullname</label>
                    <p class="fw-semibold"> Juan Dela Cruz</p>
                </div>
                <div class="col mb-2">
                    <label class="text-muted fw-semibold small text-uppercase"> Sex</label>
                    <p> Male</p>
                </div>
                <div class="col mb-2">
                    <label class="text-muted fw-semibold small text-uppercase"> birthdate</label>
                    <p> July 14 2004</p>
                </div>
                <div class="col mb-2">
                    <label class="text-muted fw-semibold small text-uppercase"> Address</label>
                    <p> 123 Purok Bahay Pare Candaba Pampanga</p>
                </div>

            </div>
        </div>
    </div>

</x-layouts.admin>