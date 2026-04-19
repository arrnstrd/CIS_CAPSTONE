<x-layouts.admin>
    <x-slot name="pageName">
        Student Entry and Exit Monitoring
    </x-slot>

    <x-slot name="subtitle">
        School entry/exit gate scans.
    </x-slot>

    <div class="row g-3 mb-3 mx-2">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                            Entry
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
                        <h4 class="fw-bold mb-0 text-success">0</h4>
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
                            early out
                        </h6>
                        <h4 class="fw-bold mb-0 text-danger">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle bg-warning text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                            late
                        </h6>
                        <h4 class="fw-bold mb-0 text-warning">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <x-ui.table>
        <x-slot name="thead">
            <th> Date</th>
        </x-slot>

        <x-slot name="tbody">

        </x-slot>
    </x-ui.table>


</x-layouts.admin>