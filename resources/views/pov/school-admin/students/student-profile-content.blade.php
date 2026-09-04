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
            <div class="sp-card" data-edit-container="info">
                <div class="sp-card__head">
                    <h6 class="sp-card__title"><i class="fa-solid fa-user me-1"></i> Personal Information</h6>
                    @if(!auth()->user()?->isTeacher())
                        <button type="button" class="sp-edit-btn" data-edit-toggle="info">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>
                    @endif
                </div>

                <div class="row g-3" data-edit-view="info">
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

                @if(!auth()->user()?->isTeacher())
                    <form class="sp-edit-form d-none" data-edit-form="info"
                        action="{{ route('student.profile.update-info', $student) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="sp-form-label">First name</label>
                                <input type="text" name="first_name" class="form-control"
                                    value="{{ $student->first_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Middle name</label>
                                <input type="text" name="middle_name" class="form-control"
                                    value="{{ $student->middle_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Last name</label>
                                <input type="text" name="last_name" class="form-control"
                                    value="{{ $student->last_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Suffix</label>
                                <input type="text" name="suffix" class="form-control" value="{{ $student->suffix }}">
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Sex</label>
                                <select name="sex" class="form-select" required>
                                    <option value="female" {{ $student->sex === 'female' ? 'selected' : '' }}>Female
                                    </option>
                                    <option value="male" {{ $student->sex === 'male' ? 'selected' : '' }}>Male</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Age</label>
                                <input type="number" name="age" class="form-control" min="1" max="100"
                                    value="{{ $student->age }}">
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Birthplace</label>
                                <input type="text" name="birthplace" class="form-control"
                                    value="{{ $student->birthplace }}">
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">Mother tongue</label>
                                <input type="text" name="mother_tongue" class="form-control"
                                    value="{{ $student->mother_tongue }}">
                            </div>
                            <div class="col-md-6">
                                <label class="sp-form-label">IP / Ethnic group</label>
                                <input type="text" name="ip_ethnic_group" class="form-control"
                                    value="{{ $student->ip_ethnic_group }}">
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Religion</label>
                                <input type="text" name="religion" class="form-control"
                                    value="{{ $student->religion }}">
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ $student->address }}">
                            </div>
                        </div>
                        <div class="sp-edit-actions">
                            <span class="sp-edit-error text-danger small" data-edit-error="info"></span>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-edit-cancel="info">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Guardian Info --}}
            <div class="sp-card" data-edit-container="guardian">
                <div class="sp-card__head">
                    <h6 class="sp-card__title"><i class="fa-solid fa-people-roof me-1"></i> Guardian Information
                    </h6>
                    @if(!auth()->user()?->isTeacher())
                        <button type="button" class="sp-edit-btn" data-edit-toggle="guardian">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>
                    @endif
                </div>

                <div data-edit-view="guardian">
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

                @if(!auth()->user()?->isTeacher())
                    <form class="sp-edit-form d-none" data-edit-form="guardian"
                        action="{{ route('student.profile.update-guardian', $student) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="sp-form-label">Guardian name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ $student->guardian?->name }}" required>
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Relationship</label>
                                <select name="relationship" class="form-select" required>
                                    @foreach(['mother' => 'Mother', 'father' => 'Father', 'sibling' => 'Sibling', 'guardian' => 'Guardian'] as $relValue => $relLabel)
                                        <option value="{{ $relValue }}" {{ ($student->guardian?->relationship ?? '') === $relValue ? 'selected' : '' }}>{{ $relLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Contact number</label>
                                <input type="text" name="contact_number" class="form-control" maxlength="20"
                                    value="{{ $student->guardian?->contact_number }}">
                            </div>
                            <div class="col-12">
                                <label class="sp-form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ $student->guardian?->email }}">
                            </div>
                        </div>
                        <div class="sp-edit-actions">
                            <span class="sp-edit-error text-danger small" data-edit-error="guardian"></span>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-edit-cancel="guardian">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </form>
                @endif
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

                    @include('pov.school-admin.students.components.academic-tab', [
                        'student' => $student,
                        'currentEnrollment' => $currentEnrollment,
                        'enrollmentHistory' => $enrollmentHistory,
                    ])

                    @include('pov.school-admin.students.components.attendance-tab', [
                        'student' => $student,
                    ])

                    @include('pov.school-admin.students.components.qr-tab', [
                        'student' => $student,
                        'currentEnrollment' => $currentEnrollment,
                        'qrCodeUrl' => $qrCodeUrl,
                    ])

                </div>

            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function toggleEdit(container, showForm) {
            const view = container.querySelector('[data-edit-view]');
            const form = container.querySelector('[data-edit-form]');
            if (!view || !form) return;
            view.classList.toggle('d-none', showForm);
            form.classList.toggle('d-none', !showForm);
            const err = container.querySelector('[data-edit-error]');
            if (err) err.textContent = '';
        }

        document.querySelectorAll('[data-edit-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                toggleEdit(btn.closest('[data-edit-container]'), true);
            });
        });

        document.querySelectorAll('[data-edit-cancel]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                toggleEdit(btn.closest('[data-edit-container]'), false);
            });
        });

        function cap(s) {
            return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—';
        }

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        function applyUpdatedValues(container, student) {
            const scope = container.dataset.editContainer;
            const form = container.querySelector('[data-edit-form]');
            const source = scope === 'guardian' ? (student.guardian || {}) : student;

            if (scope === 'info') {
                const fullName = [student.first_name, student.middle_name, student.last_name]
                    .filter(Boolean).join(' ') + (student.suffix ? ', ' + student.suffix : '');
                setText('sp-info-fullname', fullName);
                setText('sp-info-sex', student.sex || '—');
                setText('sp-info-age', student.age ?? '—');
                setText('sp-info-birthplace', student.birthplace || '—');
                setText('sp-info-mother-tongue', student.mother_tongue || '—');
                setText('sp-info-ip', student.ip_ethnic_group || '—');
                setText('sp-info-religion', student.religion || '—');
                setText('sp-info-address', student.address || '—');
                setText('sp-name', fullName);
            } else {
                setText('sp-guardian-name', source.name || '—');
                setText('sp-guardian-relationship', cap(source.relationship));
                setText('sp-guardian-email', source.email || '—');
                setText('sp-guardian-contact', source.contact_number || '—');
            }

            // Keep the edit form inputs in sync so the next edit shows fresh values.
            if (form) {
                Array.from(form.elements).forEach(function (el) {
                    if (!el.name || el.name === '_token' || el.name === '_method') return;
                    if (el.type === 'hidden' || el.type === 'submit' || el.type === 'button') return;
                    const value = source[el.name];
                    if (value !== undefined) el.value = value ?? '';
                });
            }
        }

        document.querySelectorAll('[data-edit-form]').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const container = form.closest('[data-edit-container]');
                const errEl = container.querySelector('[data-edit-error]');
                if (errEl) errEl.textContent = '';
                const submitBtn = form.querySelector('[type="submit"]');
                const original = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Saving...';

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        const firstErr = data.errors ? Object.values(data.errors)[0] : null;
                        const msg = Array.isArray(firstErr) && firstErr.length
                            ? firstErr[0]
                            : (data.message || 'Could not save changes.');
                        if (errEl) errEl.textContent = msg;
                        return;
                    }

                    applyUpdatedValues(container, data.student || {});
                    toggleEdit(container, false);
                } catch (err) {
                    if (errEl) errEl.textContent = 'Network error. Please try again.';
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = original;
                }
            });
        });
    })();
</script>

