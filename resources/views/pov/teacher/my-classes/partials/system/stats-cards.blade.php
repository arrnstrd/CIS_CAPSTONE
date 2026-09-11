<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <div>
                <p class="gs-stat-label">Average Grade</p>
                <p class="gs-stat-value">{{ $stats['avg_grade'] !== null ? $stats['avg_grade'] : '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card gs-stat-card-success d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-success">
                <i class="fa-solid fa-user-check"></i>
            </span>
            <div>
                <p class="gs-stat-label gs-stat-label-success">Passing Rate</p>
                <p class="gs-stat-value gs-stat-present">{{ $stats['passing_rate'] !== null ? $stats['passing_rate'].'%' : '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-danger">
                <i class="fa-solid fa-user-xmark"></i>
            </span>
            <div>
                <p class="gs-stat-label gs-stat-label-danger">Failing Rate</p>
                <p class="gs-stat-value gs-stat-danger">{{ $stats['failing_rate'] !== null ? $stats['failing_rate'].'%' : '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-calendar-check"></i>
            </span>
            <div>
                <p class="gs-stat-label">Avg Attendance</p>
                <p class="gs-stat-value">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—' }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-users"></i>
            </span>
            <div>
                <p class="gs-stat-label">Total Students</p>
                <p class="gs-stat-value">{{ $stats['total_students'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
            <div>
                <p class="gs-stat-label gs-stat-label-danger">Failing Students</p>
                <p class="gs-stat-value gs-stat-danger">{{ $stats['failing_students'] }}</p>
            </div>
        </div>
    </div>
</div>
