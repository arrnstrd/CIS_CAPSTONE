<x-layouts.admin>
    <x-slot name="title">
        Dashboard
    </x-slot>

    <x-slot name="pageName">
        Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Monitor and view recent entry exit scans
    </x-slot>

    {{-- contents inside eontainer fluid --}}
    <div class="row g-3 mb-3 justify-content-center">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                            ENTRY
                        </h6>
                        <h4 class="fw-bold mb-0 text-primary">0</h4>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                            EXIT
                        </h6>
                        <h3 class="fw-bold mb-0 text-success">0</h3>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                            FLAGGED SCANS
                        </h6>
                        <h4 class="fw-bold mb-0 text-danger">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <x-ui.table>
        <x-slot name="thead">
            <th>Date</th>
            <th>Student Name</th>
            <th>Grade</th>
            <th>Section</th>
            <th>Gate Time</th>
            <th>Type</th>
            <th>Session</th>
            <th>Status</th>
        </x-slot>


        <x-slot name="tbody">
            <td> </td>
            <td> </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </x-slot>
    </x-ui.table>



</x-layouts.admin>