<x-layouts.school-admin>

    <x-slot name="pageName">
        QR Code Generation
    </x-slot>

    <x-slot name="subtitle">
        Generate and download printable QR pass cards by section or for individual students.
    </x-slot>

    <div class="qr-checkout-wrapper w-100 mb-4 px-2 px-md-3">

        {{-- Quick Guide & Tutorial Banner --}}
        <div class="bg-primary bg-opacity-10 border border-primary-subtle rounded-3 p-3 mb-3 shadow-2sm">
            <div class="d-flex align-items-start gap-2">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 28px; height: 28px;">
                    <i class="fas fa-lightbulb" style="font-size: 0.8rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold text-primary mb-0" style="font-size: 0.88rem;"><i class="fas fa-circle-info me-1"></i> How to Generate & Download QR Passes</h6>
                        <span class="badge bg-primary bg-opacity-20 text-primary rounded-pill px-2 py-1" style="font-size: 0.68rem; text-transform: uppercase;">Quick Tutorial</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-2 p-2 border border-primary-subtle d-flex align-items-center gap-2">
                                <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 20px; height: 20px; font-size: 0.7rem;">1</span>
                                <span class="small text-dark" style="font-size: 0.8rem;"><strong>By Section:</strong> Click <span class="text-success fw-bold">Direct Download Section PDF</span> on any section card for instant full-class passes.</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-2 p-2 border border-primary-subtle d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 20px; height: 20px; font-size: 0.7rem;">2</span>
                                <span class="small text-dark" style="font-size: 0.8rem;"><strong>Individual Students:</strong> Select students to add to your custom basket, then click <span class="text-primary fw-bold">Download Selected Passes PDF</span>.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Summary Metric Chips --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-4">
                <div class="qr-gen-stat-card d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-2sm">
                    <div class="qr-gen-stat-icon bg-primary bg-opacity-10 text-primary p-2 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="fas fa-layer-group fs-6"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;">Total Sections</span>
                        <h4 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">{{ number_format($totalSections) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="qr-gen-stat-card d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-2sm">
                    <div class="qr-gen-stat-icon bg-success bg-opacity-10 text-success p-2 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="fas fa-user-graduate fs-6"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;">Enrolled Students</span>
                        <h4 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">{{ number_format($totalStudents) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="qr-gen-stat-card d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-2sm">
                    <div class="qr-gen-stat-icon p-2 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; color: #4f46e5; background: rgba(79, 70, 229, 0.1);">
                        <i class="fas fa-qrcode fs-6"></i>
                    </div>
                    <div>
                        <span class="text-muted fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;">Generated Passes</span>
                        <h4 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">{{ number_format($totalQrCount) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main 2-Container Grid Layout --}}
        <div class="row g-3">

            {{-- LEFT CONTAINER: Selection Workspace --}}
            <div class="col-12 col-xl-7">
                <div class="bg-white rounded-3 p-3 border shadow-2sm mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <ul class="nav nav-pills qr-gen-tabs gap-2" id="qrSelectionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold rounded-pill px-3 py-2" id="sections-tab" data-bs-toggle="pill" data-bs-target="#sections-pane" type="button" role="tab" aria-controls="sections-pane" aria-selected="true" style="font-size: 0.82rem;">
                                    <i class="fas fa-folder-tree me-2"></i>By Section
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold rounded-pill px-3 py-2" id="students-tab" data-bs-toggle="pill" data-bs-target="#students-pane" type="button" role="tab" aria-controls="students-pane" aria-selected="false" style="font-size: 0.82rem;">
                                    <i class="fas fa-users-viewfinder me-2"></i>Individual Students
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="qrSelectionTabContent">

                    {{-- TAB 1: SELECT BY SECTION --}}
                    <div class="tab-pane fade show active" id="sections-pane" role="tabpanel" aria-labelledby="sections-tab">
                        <div class="bg-white rounded-3 p-3 border shadow-2sm mb-3">
                            <form method="GET" action="{{ route('qr.index') }}">
                                <input type="hidden" name="tab" value="section">
                                <div class="row g-2 align-items-center">
                                    <div class="col-7">
                                        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search section name..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-5">
                                        <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Grade Levels</option>
                                            @for ($grade = 1; $grade <= 12; $grade++)
                                                <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                                    Grade {{ $grade }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex flex-column gap-3 mb-3">
                            @forelse($sections as $section)
                                <div class="bg-white rounded-3 border p-3 shadow-2sm d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-bold" style="font-size: 0.7rem;">
                                                Grade {{ $section->grade_level }}
                                            </span>
                                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">{{ $section->name }}</h6>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.78rem;">
                                            <span class="me-3"><i class="fas fa-user-tie me-1 opacity-75"></i> {{ $section->advisor?->full_name ?? 'No Adviser' }}</span>
                                            <span><i class="fas fa-users me-1 opacity-75"></i> {{ $section->students_count }} Students</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        @if($section->students_count > 0)
                                            <a href="{{ route('sections.qr.download', $section) }}" 
                                               class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-2sm btn-section-download" 
                                               data-section-name="{{ $section->name }}" 
                                               data-download-url="{{ route('sections.qr.download', $section) }}"
                                               style="font-size: 0.78rem;">
                                                <i class="fas fa-file-arrow-down me-2"></i>Direct Download Section PDF
                                            </a>
                                        @else
                                            <button class="btn btn-light btn-sm text-muted rounded-pill px-3" style="font-size: 0.75rem;" disabled>No Students</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-3 border p-4 text-center text-muted">
                                    <i class="fas fa-folder-open fs-3 mb-2 d-block opacity-50"></i>
                                    <span class="small">No sections found matching your search query.</span>
                                </div>
                            @endforelse
                        </div>

                        <div class="d-flex justify-content-end mb-3">
                            {{ $sections->links() }}
                        </div>
                    </div>

                    {{-- TAB 2: SELECT INDIVIDUAL STUDENTS --}}
                    <div class="tab-pane fade" id="students-pane" role="tabpanel" aria-labelledby="students-tab">
                        <div class="bg-white rounded-3 p-3 border shadow-2sm mb-3">
                            <form method="GET" action="{{ route('qr.index') }}">
                                <input type="hidden" name="tab" value="directory">
                                <div class="row g-2 align-items-center">
                                    <div class="col-7">
                                        <input type="search" name="student_search" class="form-control form-control-sm" placeholder="Search student name, LRN, student no..." value="{{ request('student_search') }}">
                                    </div>
                                    <div class="col-5">
                                        <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">All Sections</option>
                                            @foreach($allSections as $sec)
                                                <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                                    Grade {{ $sec->grade_level }} - {{ $sec->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase small text-muted">
                                    <tr>
                                        <th style="font-size: 0.72rem; padding: 0.5rem 0.75rem;">Student Information</th>
                                        <th style="font-size: 0.72rem; padding: 0.5rem 0.75rem;">Grade & Section</th>
                                        <th class="text-end" style="font-size: 0.72rem; padding: 0.5rem 0.75rem;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($directoryStudents as $std)
                                        @php $stdSection = $std->enrollments->first()?->section; @endphp
                                        <tr>
                                            <td style="padding: 0.5rem 0.75rem;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                        {{ strtoupper(substr($std->first_name, 0, 1) . substr($std->last_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-dark d-block" style="font-size: 0.82rem;">{{ $std->last_name }}, {{ $std->first_name }}</span>
                                                        <span class="text-muted small" style="font-size: 0.72rem;">ID: {{ $std->student_number }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="padding: 0.5rem 0.75rem;">
                                                @if($stdSection)
                                                    <span class="badge rounded-pill px-2 py-1 border" style="color: #4f46e5; background: #e0e7ff; border-color: #c7d2fe; font-size: 0.7rem;">
                                                        Grade {{ $stdSection->grade_level }} - {{ $stdSection->name }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border rounded-pill px-2 py-1" style="font-size: 0.7rem;">Unassigned</span>
                                                @endif
                                            </td>
                                            <td class="text-end" style="padding: 0.5rem 0.75rem;">
                                                @php $inBasket = isset($basketItems[$std->id]); @endphp
                                                <button type="button"
                                                    class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-add-student-basket {{ $inBasket ? 'btn-success' : 'btn-primary' }}"
                                                    style="font-size: 0.75rem;"
                                                    data-student-id="{{ $std->id }}"
                                                    data-student-name="{{ $std->last_name }}, {{ $std->first_name }}"
                                                    data-student-number="{{ $std->student_number }}"
                                                    data-section-name="{{ $stdSection ? 'Grade ' . $stdSection->grade_level . ' - ' . $stdSection->name : 'Unassigned' }}"
                                                    data-in-basket="{{ $inBasket ? 'true' : 'false' }}">
                                                    @if($inBasket)
                                                        <i class="fas fa-check me-1"></i> In Basket
                                                    @else
                                                        <i class="fas fa-plus me-1"></i> Add to Basket
                                                    @endif
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">No students found matching query.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mb-3">
                            {{ $directoryStudents->links() }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT CONTAINER: Checkout Basket Panel --}}
            <div class="col-12 col-xl-5">
                <div class="qr-basket-panel bg-white rounded-3 border shadow-2sm p-3 sticky-top" style="top: 1rem;">

                    {{-- Basket Header --}}
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="fas fa-shopping-basket fs-6"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0" style="font-size: 0.92rem;">QR Pass Basket</h5>
                                <span class="text-muted small" style="font-size: 0.74rem;">Custom batch download list</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="basketCountBadge" class="badge bg-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                                {{ count($basketItems) }} Selected
                            </span>
                            <button type="button" id="btnClearBasket" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" title="Clear Basket">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable Live Basket Roster --}}
                    <div id="basketRosterContainer" class="mb-3" style="max-height: 380px; overflow-y: auto;">
                        <div id="basketEmptyState" class="text-center py-4 px-3 bg-light rounded-3 border border-dashed" style="{{ count($basketItems) > 0 ? 'display:none;' : '' }}">
                            <i class="fas fa-basket-shopping fs-2 text-secondary opacity-25 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Your Basket is Empty</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Select individual students from the left container to add them to your custom batch download package.</p>
                        </div>
                        <div id="basketItemsList" class="d-flex flex-column gap-2" style="{{ count($basketItems) === 0 ? 'display:none !important;' : '' }}">
                            @foreach($basketItems as $item)
                                <div class="bg-white p-2 rounded-3 border d-flex align-items-center justify-content-between gap-2 shadow-2sm basket-item" data-student-id="{{ $item['id'] }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($item['full_name'], 0, 1)) }}
                                        </div>
                                        <div class="text-truncate">
                                            <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.8rem;">{{ $item['full_name'] }}</span>
                                            <span class="text-muted d-block" style="font-size: 0.7rem;">{{ $item['section_name'] }} &bull; ID: {{ $item['student_number'] }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle btn-remove-basket p-1" data-student-id="{{ $item['id'] }}" title="Remove">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Download Form & Summary CTA --}}
                    <form id="basketCheckoutForm" method="POST" action="{{ route('sections.qr.download-batch') }}">
                        @csrf
                        {{-- Hidden inputs are rendered server-side from session --}}
                        @php $basketIds = array_keys($basketItems); @endphp
                        @foreach($basketIds as $sid)
                            <input type="hidden" name="student_ids[]" value="{{ $sid }}">
                        @endforeach

                        <div class="bg-light p-3 rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-semibold" style="font-size: 0.78rem;">Total Pass Cards to Print:</span>
                                <span id="summaryTotalCount" class="fw-bold text-dark small" style="font-size: 0.82rem;">{{ count($basketItems) }} Card{{ count($basketItems) === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-semibold" style="font-size: 0.78rem;">Document Format:</span>
                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill" style="font-size: 0.68rem;">PDF Printable Cards</span>
                            </div>
                        </div>

                        <button type="submit" id="btnCheckoutDownload" class="btn btn-success btn-md w-100 fw-bold shadow-2sm rounded-pill py-2" style="font-size: 0.88rem;" {{ count($basketItems) === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-file-pdf me-2"></i>Download Selected Passes PDF
                        </button>
                    </form>

                </div>
            </div>

        </div>

    </div>

    {{-- PDF Generation Feedback Modal --}}
    <div class="modal fade" id="pdfFeedbackModal" tabindex="-1" aria-labelledby="pdfFeedbackModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-4 text-center">

                    {{-- STATE 1: GENERATING / PROCESSING --}}
                    <div id="pdfModalStateGenerating">
                        <div class="position-relative d-inline-flex align-items-center justify-content-center mb-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                <i class="fas fa-file-pdf fs-2"></i>
                            </div>
                            <div class="spinner-border text-primary position-absolute" style="width: 86px; height: 86px; border-width: 3px;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-1" id="pdfFeedbackModalLabel">Generating QR Pass Cards</h5>
                        <p id="pdfModalStatusText" class="text-muted small mb-3">Preparing printable document layout...</p>

                        <div class="progress rounded-pill mb-2" style="height: 10px; background-color: #e2e8f0;">
                            <div id="pdfProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 15%; transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                            <span id="pdfProgressPercent" class="fw-semibold">15%</span>
                            <span>Compiling passes in real-time</span>
                        </div>
                    </div>

                    {{-- STATE 2: SUCCESS / COMPLETE --}}
                    <div id="pdfModalStateSuccess" style="display: none;">
                        <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-circle-check fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Download Complete!</h5>
                        <p id="pdfModalSuccessText" class="text-muted small mb-4">Your printable student QR pass cards have been successfully compiled and downloaded to your device.</p>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-2sm" data-bs-dismiss="modal">
                            <i class="fas fa-check me-1"></i>Done
                        </button>
                    </div>

                    {{-- STATE 3: ERROR / WARNING --}}
                    <div id="pdfModalStateError" style="display: none;">
                        <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-circle-exclamation fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Download Failed</h5>
                        <p id="pdfModalErrorMessage" class="text-muted small mb-4">We were unable to generate the PDF pass cards. Please try again.</p>
                        <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold shadow-2sm" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // ── Tab Restore from URL ──────────────────────────────────────────────
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'directory') {
            const dirTab = document.getElementById('students-tab');
            if (dirTab) new bootstrap.Tab(dirTab).show();
        }

        // ── Route URLs injected from Blade ───────────────────────────────────
        const ROUTES = {
            add:   '{{ route('qr.basket.add') }}',
            remove:'{{ route('qr.basket.remove') }}',
            clear: '{{ route('qr.basket.clear') }}',
        };
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ── DOM References ───────────────────────────────────────────────────
        const emptyState     = document.getElementById('basketEmptyState');
        const itemsList      = document.getElementById('basketItemsList');
        const countBadge     = document.getElementById('basketCountBadge');
        const summaryTotal   = document.getElementById('summaryTotalCount');
        const checkoutBtn    = document.getElementById('btnCheckoutDownload');
        const checkoutForm   = document.getElementById('basketCheckoutForm');
        const btnClear       = document.getElementById('btnClearBasket');

        // ── AJAX Helper ──────────────────────────────────────────────────────
        async function apiPost(url, body) {
            const res = await fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(body),
            });
            if (!res.ok) throw new Error('Request failed: ' + res.status);
            return res.json();
        }

        // ── Count current basket items from DOM ──────────────────────────────
        function getBasketCount() {
            return itemsList.querySelectorAll('.basket-item').length;
        }

        // ── Sync badge, summary, and button state ────────────────────────────
        function syncUI() {
            const count = getBasketCount();
            countBadge.textContent   = `${count} Selected`;
            summaryTotal.textContent = `${count} Card${count === 1 ? '' : 's'}`;
            checkoutBtn.disabled     = count === 0;

            if (count === 0) {
                emptyState.style.display = 'block';
                itemsList.style.setProperty('display', 'none', 'important');
            } else {
                emptyState.style.display = 'none';
                itemsList.style.setProperty('display', 'flex', 'important');
            }
        }

        // ── Add basket item card to DOM ───────────────────────────────────────
        function addItemToDOM(studentId, fullName, studentNumber, sectionName) {
            // Avoid duplicates in DOM
            if (itemsList.querySelector(`.basket-item[data-student-id="${studentId}"]`)) return;

            const initial = fullName.charAt(0).toUpperCase();
            const div = document.createElement('div');
            div.className = 'bg-white p-2 rounded-3 border d-flex align-items-center justify-content-between gap-2 shadow-2sm basket-item';
            div.dataset.studentId = studentId;
            div.innerHTML = `
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:26px;height:26px;font-size:0.7rem;">${initial}</div>
                    <div class="text-truncate">
                        <span class="fw-bold text-dark d-block text-truncate" style="font-size:0.8rem;">${fullName}</span>
                        <span class="text-muted d-block" style="font-size:0.7rem;">${sectionName} &bull; ID: ${studentNumber}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle btn-remove-basket p-1" data-student-id="${studentId}" title="Remove">
                    <i class="fas fa-xmark"></i>
                </button>
            `;

            // Add hidden input to the form
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'student_ids[]';
            input.value = studentId;
            input.id    = `basket-input-${studentId}`;
            checkoutForm.appendChild(input);

            itemsList.appendChild(div);
            attachRemoveListener(div.querySelector('.btn-remove-basket'));
        }

        // ── Remove basket item card from DOM ─────────────────────────────────
        function removeItemFromDOM(studentId) {
            const card = itemsList.querySelector(`.basket-item[data-student-id="${studentId}"]`);
            if (card) card.remove();
            const inp = document.getElementById(`basket-input-${studentId}`);
            if (inp) inp.remove();
        }

        // ── Mark add-button state ────────────────────────────────────────────
        function setButtonInBasket(btn, inBasket) {
            if (inBasket) {
                btn.classList.replace('btn-primary', 'btn-success');
                btn.innerHTML = '<i class="fas fa-check me-1"></i> In Basket';
                btn.dataset.inBasket = 'true';
            } else {
                btn.classList.replace('btn-success', 'btn-primary');
                btn.innerHTML = '<i class="fas fa-plus me-1"></i> Add to Basket';
                btn.dataset.inBasket = 'false';
            }
        }

        // ── Attach listener to a single remove button ─────────────────────────
        function attachRemoveListener(btn) {
            btn.addEventListener('click', async () => {
                const sid = parseInt(btn.dataset.studentId, 10);
                btn.disabled = true;
                try {
                    await apiPost(ROUTES.remove, { student_id: sid });
                    removeItemFromDOM(sid);
                    syncUI();

                    // Update matching add-button in the table if visible
                    const addBtn = document.querySelector(`.btn-add-student-basket[data-student-id="${sid}"]`);
                    if (addBtn) setButtonInBasket(addBtn, false);
                } catch (e) {
                    console.error('Failed to remove from basket', e);
                    btn.disabled = false;
                }
            });
        }

        // ── Wire existing server-rendered remove buttons ──────────────────────
        document.querySelectorAll('.btn-remove-basket').forEach(attachRemoveListener);

        // ── Wire existing server-rendered basket items' hidden inputs ─────────
        // (They are already in the form via server-rendered Blade loop — fix id attr for JS)
        document.querySelectorAll('.basket-item').forEach(item => {
            const sid = item.dataset.studentId;
            // Ensure matching hidden input has the id so removeItemFromDOM can find it
            const inp = checkoutForm.querySelector(`input[name="student_ids[]"][value="${sid}"]`);
            if (inp && !inp.id) inp.id = `basket-input-${sid}`;
        });

        // ── Add-to-basket button clicks ───────────────────────────────────────
        document.querySelectorAll('.btn-add-student-basket').forEach(btn => {
            btn.addEventListener('click', async () => {
                const sid         = parseInt(btn.dataset.studentId, 10);
                const fullName    = btn.dataset.studentName;
                const studentNum  = btn.dataset.studentNumber;
                const sectionName = btn.dataset.sectionName;
                const inBasket    = btn.dataset.inBasket === 'true';

                btn.disabled = true;

                try {
                    if (inBasket) {
                        await apiPost(ROUTES.remove, { student_id: sid });
                        removeItemFromDOM(sid);
                        setButtonInBasket(btn, false);
                    } else {
                        await apiPost(ROUTES.add, { student_id: sid });
                        addItemToDOM(sid, fullName, studentNum, sectionName);
                        setButtonInBasket(btn, true);
                    }
                    syncUI();
                } catch (e) {
                    console.error('Basket AJAX error', e);
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // ── Clear basket ──────────────────────────────────────────────────────
        if (btnClear) {
            btnClear.addEventListener('click', async () => {
                btnClear.disabled = true;
                try {
                    await apiPost(ROUTES.clear, {});
                    itemsList.querySelectorAll('.basket-item').forEach(el => el.remove());
                    // Remove all hidden inputs for student_ids
                    checkoutForm.querySelectorAll('input[name="student_ids[]"]').forEach(el => el.remove());
                    syncUI();

                    // Reset all add-buttons
                    document.querySelectorAll('.btn-add-student-basket').forEach(b => setButtonInBasket(b, false));
                } catch (e) {
                    console.error('Failed to clear basket', e);
                } finally {
                    btnClear.disabled = false;
                }
            });
        }

        // ── PDF Generation Feedback Modal & Download Handler ──────────────────
        const pdfModalEl        = document.getElementById('pdfFeedbackModal');
        const pdfModal          = pdfModalEl ? new bootstrap.Modal(pdfModalEl) : null;
        const modalStateGen     = document.getElementById('pdfModalStateGenerating');
        const modalStateSuccess = document.getElementById('pdfModalStateSuccess');
        const modalStateError   = document.getElementById('pdfModalStateError');
        const modalStatusText   = document.getElementById('pdfModalStatusText');
        const modalProgressBar  = document.getElementById('pdfProgressBar');
        const modalProgressPct  = document.getElementById('pdfProgressPercent');
        const modalErrorMsg     = document.getElementById('pdfModalErrorMessage');
        const modalSuccessMsg   = document.getElementById('pdfModalSuccessText');

        let progressTimer = null;

        function setModalState(state) {
            if (!pdfModalEl) return;
            modalStateGen.style.display     = state === 'generating' ? 'block' : 'none';
            modalStateSuccess.style.display = state === 'success'    ? 'block' : 'none';
            modalStateError.style.display   = state === 'error'      ? 'block' : 'none';
        }

        function updateProgress(percent, text) {
            if (modalProgressBar) modalProgressBar.style.width = percent + '%';
            if (modalProgressPct) modalProgressPct.textContent = percent + '%';
            if (modalStatusText && text) modalStatusText.textContent = text;
        }

        function startProgressSimulation() {
            clearInterval(progressTimer);
            let currentPct = 15;
            updateProgress(currentPct, 'Initializing pass cards and QR codes…');

            const stages = [
                { target: 35, step: 2, text: 'Fetching student records & QR assets…' },
                { target: 65, step: 2, text: 'Formatting printable grid cards layout…' },
                { target: 88, step: 1, text: 'Compiling high-resolution PDF document…' },
                { target: 95, step: 0.5, text: 'Finalizing file stream…' },
            ];

            let stageIdx = 0;
            progressTimer = setInterval(() => {
                if (stageIdx >= stages.length) return;
                const stage = stages[stageIdx];
                if (currentPct < stage.target) {
                    currentPct = Math.min(stage.target, currentPct + stage.step);
                    updateProgress(Math.floor(currentPct), stage.text);
                } else {
                    stageIdx++;
                }
            }, 80);
        }

        function stopProgressSimulation() {
            clearInterval(progressTimer);
        }

        async function executePdfDownload({ url, method = 'GET', body = null, defaultFilename = 'Student-QR-Passes.pdf', isBatch = false, triggerBtn = null }) {
            if (!pdfModal) return;

            setModalState('generating');
            startProgressSimulation();
            pdfModal.show();

            const origBtnHtml = triggerBtn ? triggerBtn.innerHTML : '';
            if (triggerBtn) {
                triggerBtn.disabled = true;
            }

            try {
                const fetchOptions = {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/pdf, application/json, text/html',
                    }
                };
                if (body) {
                    fetchOptions.body = body;
                }

                const response = await fetch(url, fetchOptions);
                const contentType = response.headers.get('content-type') || '';

                if (response.ok && (contentType.includes('application/pdf') || contentType.includes('octet-stream'))) {
                    // Fast zoom to 100%
                    stopProgressSimulation();
                    updateProgress(100, 'Download ready! Saving file…');

                    const blob = await response.blob();

                    // Determine filename from Content-Disposition header if available
                    let filename = defaultFilename;
                    const disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (match && match[1]) {
                            filename = match[1].replace(/['"]/g, '').trim();
                        }
                    }

                    // Trigger browser file download
                    const blobUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => window.URL.revokeObjectURL(blobUrl), 2000);

                    // Transition to Success state
                    setTimeout(() => {
                        if (modalSuccessMsg) {
                            modalSuccessMsg.textContent = isBatch 
                                ? 'Selected student QR pass cards were compiled and downloaded successfully.' 
                                : `The section QR passes (${filename}) were compiled and downloaded successfully.`;
                        }
                        setModalState('success');

                        // If batch, clear the basket since backend cleared session basket
                        if (isBatch) {
                            itemsList.querySelectorAll('.basket-item').forEach(el => el.remove());
                            checkoutForm.querySelectorAll('input[name="student_ids[]"]').forEach(el => el.remove());
                            syncUI();
                            document.querySelectorAll('.btn-add-student-basket').forEach(b => setButtonInBasket(b, false));
                        }
                    }, 400);

                } else {
                    // Error response (e.g. 422 JSON warning or error HTML)
                    stopProgressSimulation();
                    let errMsg = 'We were unable to generate the PDF pass cards. Please try again.';
                    try {
                        const json = await response.json();
                        if (json && json.message) errMsg = json.message;
                    } catch (_) {
                        // ignore JSON parse fail
                    }
                    if (modalErrorMsg) modalErrorMsg.textContent = errMsg;
                    setModalState('error');
                }
            } catch (err) {
                console.error('PDF Generation Error:', err);
                stopProgressSimulation();
                if (modalErrorMsg) {
                    modalErrorMsg.textContent = 'A connection error occurred while requesting the PDF. Please check your network and try again.';
                }
                setModalState('error');
            } finally {
                if (triggerBtn) {
                    triggerBtn.disabled = false;
                    triggerBtn.innerHTML = origBtnHtml;
                }
                syncUI();
            }
        }

        // ── Intercept Batch Checkout Form Submit ──────────────────────────────
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (checkoutBtn.disabled) return;

                executePdfDownload({
                    url:             checkoutForm.action,
                    method:          'POST',
                    body:            new FormData(checkoutForm),
                    defaultFilename: 'Batch-Selected-Students-QR.pdf',
                    isBatch:         true,
                    triggerBtn:      checkoutBtn,
                });
            });
        }

        // ── Intercept Direct Section Download Links ───────────────────────────
        document.querySelectorAll('.btn-section-download').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const url = btn.dataset.downloadUrl || btn.href;
                const secName = btn.dataset.sectionName || 'Section';
                executePdfDownload({
                    url:             url,
                    method:          'GET',
                    defaultFilename: `QR-${secName}.pdf`,
                    isBatch:         false,
                    triggerBtn:      btn,
                });
            });
        });

        // Initial sync (page may load with basket items from session)
        syncUI();
    });
    </script>
    @endpush

</x-layouts.school-admin>
