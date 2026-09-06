<x-layouts.teacher>
    <x-slot name="pageName">
        Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Teacher Dashboard
    </x-slot>
<div class="row g-3">
        <div class="col-md-4">
            <div class="card border h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-light d-flex align-items-center justify-content-center"
                            style="width: 3rem; height: 3rem; flex-shrink: 0;">
                            <i class="fa-solid fa-chalkboard-teacher fs-5"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-bold">My Classes</div>
                            <div class="fs-3 fw-bold">&mdash;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-light d-flex align-items-center justify-content-center"
                            style="width: 3rem; height: 3rem; flex-shrink: 0;">
                            <i class="fa-solid fa-user-check fs-5"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-bold">Students Today</div>
                            <div class="fs-3 fw-bold">&mdash;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-light d-flex align-items-center justify-content-center"
                            style="width: 3rem; height: 3rem; flex-shrink: 0;">
                            <i class="fa-solid fa-clock fs-5"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted small fw-bold">Attendance</div>
                            <div class="fs-3 fw-bold">&mdash;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border mt-3">
        <div class="card-body p-4">
            <p class="text-muted mb-0">Teacher dashboard &mdash; placeholder. Content will be added as part of the
                session-type / attendance refactor.</p>
        </div>
    </div>
</x-layouts.teacher>
