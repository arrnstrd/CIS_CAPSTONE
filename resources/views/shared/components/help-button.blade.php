{{--
Reusable "Help & FAQ" button + modal for admin pages.

Pulls its content from config/admin-help.php, keyed by the current route
name (or the URI path as a fallback for routes without names). If no
content exists for the current page, a friendly general help guide is shown.
--}}
@php
    $route = request()->route();
    $helpKey = $route?->getName();

    if (!$helpKey || config('admin-help.' . $helpKey) === null) {
        $helpKey = request()->path();
    }

    $help = $helpKey ? config('admin-help.' . $helpKey) : null;

    if (!$help) {
        $help = [
            'title' => 'System Guide & Help',
            'intro' => 'Welcome to the CIS Administrative Portal. Use this guide for assistance with system operations, navigation, and administrative workflows.',
            'steps' => [
                ['title' => 'Navigation', 'body' => 'Use the sidebar menu to quickly access management modules, analytics, and settings.'],
                ['title' => 'Actions', 'body' => 'Use the primary buttons and tables to create, modify, or audit records.'],
                ['title' => 'Support', 'body' => 'For security access, permission changes, or technical inquiries, contact the Super Administrator.'],
            ],
            'faqs' => [
                ['q' => 'How do I return to the Dashboard?', 'a' => 'Click on "Dashboard" in the sidebar menu on the left.'],
                ['q' => 'How do I log out safely?', 'a' => 'Click the logout button at the bottom of the sidebar.'],
            ],
        ];
    }

    $hasSteps = !empty($help['steps']);
    $hasFaqs = !empty($help['faqs']);
@endphp

<button type="button" class="admin-head-banner__help-btn d-inline-flex align-items-center gap-1.5"
    data-bs-toggle="modal" data-bs-target="#adminHelpModal" title="Help & Page Guide">
    <i class="fa-solid fa-circle-question text-warning"></i>
    <span>Help & Guide</span>
</button>

<x-modal size="modal-lg">
    <x-slot name="id">adminHelpModal</x-slot>
    <x-slot name="modalTitle">
        <i class="fa-solid fa-circle-info text-primary me-2"></i>{{ $help['title'] ?? 'Help & FAQ' }}
    </x-slot>

    <div class="modal-body px-0 pb-3">
        {{-- Tabs --}}
        <ul class="nav nav-tabs nav-fill mb-0 px-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="adminHelpHowTab" data-bs-toggle="tab"
                    data-bs-target="#adminHelpHowPane" type="button" role="tab" aria-controls="adminHelpHowPane"
                    aria-selected="true">
                    <i class="fa-solid fa-book-open me-1.5 text-primary"></i> Instructions
                </button>
            </li>
            @if ($hasFaqs)
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="adminHelpFaqTab" data-bs-toggle="tab" data-bs-target="#adminHelpFaqPane"
                        type="button" role="tab" aria-controls="adminHelpFaqPane" aria-selected="false">
                        <i class="fa-solid fa-circle-question me-1.5 text-warning"></i> Frequently Asked Questions
                    </button>
                </li>
            @endif
        </ul>

        <div class="tab-content pt-3 px-3">
            {{-- Help Tab --}}
            <div class="tab-pane fade show active" id="adminHelpHowPane" role="tabpanel"
                aria-labelledby="adminHelpHowTab" tabindex="0">
                <div class="small text-secondary">
                    @if(!empty($help['intro']))
                        <div class="p-3 bg-light rounded-3 border mb-3 text-dark fw-medium">
                            {{ $help['intro'] }}
                        </div>
                    @endif

                    @if ($hasSteps)
                        <h6 class="fw-bold text-dark mb-2.5">
                            <i class="fas fa-list-ol text-primary me-1.5"></i>How It Works
                        </h6>
                        <div class="row g-2.5">
                            @foreach ($help['steps'] as $i => $step)
                                <div class="col-sm-6">
                                    <div class="border rounded-3 p-3 h-100 bg-white shadow-xs">
                                        <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                                            <span class="badge bg-primary-subtle text-primary rounded-circle" style="width: 1.35rem; height: 1.35rem; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">{{ $i + 1 }}</span>
                                            {{ $step['title'] }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem;">{{ $step['body'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- FAQ Tab --}}
            @if ($hasFaqs)
                <div class="tab-pane fade" id="adminHelpFaqPane" role="tabpanel" aria-labelledby="adminHelpFaqTab"
                    tabindex="0">
                    <div class="small text-muted">
                        <div class="accordion accordion-flush border rounded-3 overflow-hidden" id="adminHelpFaqAccordion">
                            @foreach ($help['faqs'] as $i => $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="adminHelpFaqHeading-{{ $i }}">
                                        <button class="accordion-button collapsed fw-semibold text-dark py-2.5" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#adminHelpFaqCollapse-{{ $i }}" aria-expanded="false"
                                            aria-controls="adminHelpFaqCollapse-{{ $i }}" style="font-size: 0.82rem;">
                                            <i class="far fa-question-circle text-primary me-2"></i>{{ $faq['q'] }}
                                        </button>
                                    </h2>
                                    <div id="adminHelpFaqCollapse-{{ $i }}" class="accordion-collapse collapse"
                                        aria-labelledby="adminHelpFaqHeading-{{ $i }}" data-bs-parent="#adminHelpFaqAccordion">
                                        <div class="accordion-body text-secondary" style="font-size: 0.8rem; background-color: #f8fafc;">
                                            {{ $faq['a'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-modal>