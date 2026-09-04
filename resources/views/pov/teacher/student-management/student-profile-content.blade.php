<style>
    .student-profile {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ---- Header ---- */
    .student-profile .sp-hero {
        background: #fff;
        border: 1px solid #e5e9f2;
        border-radius: 1.2rem;
        padding: 1.25rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        margin-bottom: 1rem;
    }

    .student-profile .sp-hero__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .student-profile .sp-identity {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .student-profile .sp-avatar {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #4f46e5, #2442a6);
        color: #fff;
        flex-shrink: 0;
    }

    .student-profile .sp-name {
        margin: 0;
        font-weight: 800;
        color: #111827;
    }

    .student-profile .sp-meta {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-top: 0.4rem;
    }

    .student-profile .sp-meta-item {
        font-size: 0.78rem;
        color: #6b7280;
    }

    /* ---- Stats ---- */
    .student-profile .sp-stats {
        display: flex;
        align-items: stretch;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .student-profile .sp-stat {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 0.9rem;
        padding: 0.6rem 0.9rem;
        min-width: 96px;
        text-align: center;
    }

    .student-profile .sp-stat__value {
        font-weight: 800;
        color: #111827;
        margin: 0;
    }

    .student-profile .sp-stat__label {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 800;
        margin-top: 0.1rem;
    }

    /* ---- Panels ---- */
    .student-profile .sp-card {
        background: #fff;
        border: 1px solid #e5e9f2;
        border-radius: 1.2rem;
        padding: 1.15rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .student-profile .sp-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px solid #eef2f7;
    }

    .student-profile .sp-card__title {
        margin: 0;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ---- Inline edit ---- */
    .student-profile .sp-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid #d7deea;
        background: #fff;
        color: #4f46e5;
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .student-profile .sp-edit-btn:hover {
        border-color: #4f46e5;
        background: #eef2ff;
    }

    .student-profile .sp-form-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.2rem;
    }

    .student-profile .sp-edit-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .student-profile .sp-edit-error {
        margin-right: auto;
    }

    /* ---- Fields ---- */
    .student-profile .sp-field__label {
        font-size: 0.66rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .student-profile .sp-field__value {
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .student-profile .sp-field__value--muted {
        color: #374151;
        font-weight: 500;
        line-height: 1.5;
    }

    /* ---- Tabs ---- */
    .student-profile .sp-tabs {
        border-bottom: 1px solid #eef2f7;
        gap: 0.35rem;
    }

    .student-profile .sp-tabs .nav-link {
        border: none;
        border-radius: 0.75rem 0.75rem 0 0;
        color: #6b7280;
        font-weight: 600;
        padding: 0.6rem 1rem;
    }

    .student-profile .sp-tabs .nav-link:hover {
        color: #4f46e5;
    }

    .student-profile .sp-tabs .nav-link.active {
        color: #4f46e5;
        background: #eef2ff;
    }

    .student-profile .sp-tabpanel {
        padding-top: 1.25rem;
    }
</style>

<div class="container-fluid pb-4 student-profile">

    {{-- ===================== HEADER ===================== --}}
    <div class="sp-hero">
        <div class="sp-hero__inner">

            {{-- Left: Avatar + Name + Badge --}}
            <div class="sp-identity">
                <div class="sp-avatar">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <h5 class="sp-name" id="sp-name">
                        {{ $student->first_name }}
                        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}{{ $student->suffix ? ', ' . $student->suffix : '' }}
                    </h5>
                    <div class="sp-meta">
                        <span
                            class="badge rounded-pill {{ $currentEnrollment?->status === 'active' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border">
                            <i class="fa-solid fa-circle-check me-1" style="font-size: 10px;"></i>
                            {{ $currentEnrollment?->status ? Str::headline($currentEnrollment->status) : 'Not Enrolled' }}
                        </span>
                        <span class="sp-meta-item">
                            <i class="fa-solid fa-hashtag me-1" style="font-size: 10px;"></i>
                            {{ $student->student_number }}
                        </span>
                        <span class="sp-meta-item">
                            <i class="fa-solid fa-id-card me-1" style="font-size: 10px;"></i>
                            LRN: {{ $student->lrn }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Quick stats --}}
            <div class="sp-stats">
                <div class="sp-stat">
                    <p class="sp-stat__value">{{ $currentEnrollment?->schoolYear?->school_year ?? '—' }}</p>
                    <div class="sp-stat__label">School Year</div>
                </div>
                <div class="sp-stat">
                    <p class="sp-stat__value">
                        {{ $currentEnrollment?->sectionModel?->grade_level ?? $currentEnrollment?->grade_level ?? '—' }}
                    </p>
                    <div class="sp-stat__label">Grade Level</div>
                </div>
                <div class="sp-stat">
                    <p class="sp-stat__value">
                        {{ $currentEnrollment?->sectionModel?->name ?? $currentEnrollment?->getAttribute('section') ?? '—' }}
                    </p>
                    <div class="sp-stat__label">Section</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MAIN GRID ===================== --}}
    <div class="row g-3 align-items-start">

        {{-- ===== LEFT COLUMN ===== --}}
        <div class="col-xl-4 d-flex flex-column gap-3">

            {{-- Personal Info --}}
            <div class="sp-card">
                <div class="sp-card__head">
                    <h6 class="sp-card__title"><i class="fa-solid fa-user me-1"></i> Personal Information</h6>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="sp-field__label">Full name</div>
                        <p class="sp-field__value" id="sp-info-fullname">
                            {{ $student->first_name }}
                            {{ $student->middle_name ? $student->middle_name . ' ' : '' }}{{ $student->last_name }}{{ $student->suffix ? ', ' . $student->suffix : '' }}
                        </p>
                    </div>
                    <div class="col-6">
                        <div class="sp-field__label">Sex</div>
                        <p class="sp-field__value" id="sp-info-sex">{{ $student->sex }}</p>
                    </div>
                    <div class="col-6">
                        <div class="sp-field__label">Age</div>
                        <p class="sp-field__value" id="sp-info-age">{{ $student->age }}</p>
                    </div>
                    <div class="col-12">
                        <div class="sp-field__label">Birthplace</div>
                        <p class="sp-field__value" id="sp-info-birthplace">{{ $student->birthplace ?? '—' }}</p>
                    </div>
                    <div class="col-6">
                        <div class="sp-field__label">Mother tongue</div>
                        <p class="sp-field__value" id="sp-info-mother-tongue">{{ $student->mother_tongue ?? '—' }}
                        </p>
                    </div>
                    <div class="col-6">
                        <div class="sp-field__label">IP / Ethnic group</div>
                        <p class="sp-field__value" id="sp-info-ip">{{ $student->ip_ethnic_group ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <div class="sp-field__label">Religion</div>
                        <p class="sp-field__value" id="sp-info-religion">{{ $student->religion ?? '—' }}</p>
                    </div>
                    <div class="col-12">
                        <div class="sp-field__label">Address</div>
                        <p class="sp-field__value sp-field__value--muted" id="sp-info-address">
                            {{ $student->address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Guardian Info --}}
            <div class="sp-card">
                <div class="sp-card__head">
                    <h6 class="sp-card__title"><i class="fa-solid fa-people-roof me-1"></i> Guardian Information
                    </h6>
                </div>

                <div>
                    <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-person text-secondary" style="font-size: 16px;"></i>
                        </div>
                        <div>
                            <p class="mb-0 fw-semibold" id="sp-guardian-name">{{ $student->guardian?->name ?? '—' }}
                            </p>
                            <small class="text-muted"
                                id="sp-guardian-relationship">{{ $student->guardian?->relationship ? Str::headline($student->guardian->relationship) : '—' }}</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="sp-field__label">Email address</div>
                            <p class="sp-field__value" id="sp-guardian-email">
                                <i class="fa-regular fa-envelope me-1 text-muted" style="font-size: 12px;"></i>
                                {{ $student->guardian?->email ?? '—' }}
                            </p>
                        </div>
                        <div class="col-12">
                            <div class="sp-field__label">Contact number</div>
                            <p class="sp-field__value" id="sp-guardian-contact">
                                <i class="fa-solid fa-phone me-1 text-muted" style="font-size: 12px;"></i>
                                {{ $student->guardian?->contact_number ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== RIGHT COLUMN ===== --}}
        <div class="col-xl-8">
            <div class="sp-card d-flex flex-column" style="min-height: 55vh;">

                {{-- Tabs --}}
                <ul class="nav sp-tabs" id="studentTabs" role="tablist">

                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-3 py-2" id="academic-tab" data-bs-toggle="tab"
                            data-bs-target="#academic" type="button">

                            <i class="fa-solid fa-graduation-cap me-2"></i>
                            Academic
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-2" id="attendance-tab" data-bs-toggle="tab"
                            data-bs-target="#attendance" type="button">

                            <i class="fa-solid fa-calendar-check me-2"></i>
                            Attendance
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-2" id="qr-tab" data-bs-toggle="tab" data-bs-target="#qr"
                            type="button">

                            <i class="fa-solid fa-qrcode me-2"></i>
                            QR Code
                        </button>
                    </li>

                </ul>

                {{-- Tab Content --}}
                <div class="tab-content flex-grow-1 overflow-auto sp-tabpanel">

                    @include('pov.teacher.student-management.components.academic-tab', [
                        'student' => $student,
                        'currentEnrollment' => $currentEnrollment,
                        'enrollmentHistory' => $enrollmentHistory,
                    ])

                    @include('pov.teacher.student-management.components.attendance-tab', [
                        'student' => $student,
                    ])

                    @include('pov.teacher.student-management.components.qr-tab', [
                        'student' => $student,
                        'currentEnrollment' => $currentEnrollment,
                        'qrCodeUrl' => $qrCodeUrl,
                    ])

                </div>

            </div>
        </div>

    </div>
</div>



