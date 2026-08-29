{{--
Reusable "Help & FAQ" button + modal for admin pages.

Pulls its content from config/admin-help.php, keyed by the current route
name (or the URI path as a fallback for routes without names). If no
content exists for the current page, nothing is rendered.

Each config entry supports:
title → modal heading
intro → one plain-language paragraph explaining the page
steps → ordered "how it works" cards: [['title' =>, 'body' =>], ...]
faqs → accordion items: [['q' =>, 'a' =>], ...]
--}}
@php
    $route = request()->route();
    $helpKey = $route?->getName();

    if (!$helpKey || config('admin-help.' . $helpKey) === null) {
        $helpKey = request()->path();
    }

    $help = $helpKey ? config('admin-help.' . $helpKey) : null;
@endphp

@if ($help)
    @php
        $hasSteps = !empty($help['steps']);
        $hasFaqs = !empty($help['faqs']);
    @endphp

    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 admin-help-btn"
        data-bs-toggle="modal" data-bs-target="#adminHelpModal" title="Help & FAQ">
        <i class="fa-solid fa-circle-question"></i>
        <span class="d-none d-sm-inline">Help</span>
    </button>

    <x-modal size="modal-lg">
        <x-slot name="id">adminHelpModal</x-slot>
        <x-slot name="modalTitle">{{ $help['title'] ?? 'Help & FAQ' }}</x-slot>

        <div class="modal-body px-0 pb-3">
            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-fill mb-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="adminHelpHowTab" data-bs-toggle="tab"
                        data-bs-target="#adminHelpHowPane" type="button" role="tab" aria-controls="adminHelpHowPane"
                        aria-selected="true">
                        <i class="fa-solid fa-book-open me-1"></i> Help
                    </button>
                </li>
                @if ($hasFaqs)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="adminHelpFaqTab" data-bs-toggle="tab" data-bs-target="#adminHelpFaqPane"
                            type="button" role="tab" aria-controls="adminHelpFaqPane" aria-selected="false">
                            <i class="fa-solid fa-circle-question me-1"></i> FAQ
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content pt-3">
                {{-- Help Tab --}}
                <div class="tab-pane fade show active" id="adminHelpHowPane" role="tabpanel"
                    aria-labelledby="adminHelpHowTab" tabindex="0">
                    <div class="px-2 small text-secondary">
                        <p class="fw-semibold text-dark mb-2">{{ $help['intro'] }}</p>

                        @if ($hasSteps)
                            <div class="row g-2 mt-1">
                                @foreach ($help['steps'] as $i => $step)
                                    <div class="col-sm-6">
                                        <div class="border rounded-2 p-2 h-100">
                                            <div class="fw-semibold text-dark mb-1">
                                                {{ $i + 1 }}. {{ $step['title'] }}
                                            </div>
                                            <div class="text-muted">{{ $step['body'] }}</div>
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
                        <div class="px-2 small text-muted">
                            <div class="accordion accordion-flush" id="adminHelpFaqAccordion">
                                @foreach ($help['faqs'] as $i => $faq)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="adminHelpFaqHeading-{{ $i }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#adminHelpFaqCollapse-{{ $i }}" aria-expanded="false"
                                                aria-controls="adminHelpFaqCollapse-{{ $i }}">
                                                {{ $faq['q'] }}
                                            </button>
                                        </h2>
                                        <div id="adminHelpFaqCollapse-{{ $i }}" class="accordion-collapse collapse"
                                            aria-labelledby="adminHelpFaqHeading-{{ $i }}" data-bs-parent="#adminHelpFaqAccordion">
                                            <div class="accordion-body">{{ $faq['a'] }}</div>
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
@endif