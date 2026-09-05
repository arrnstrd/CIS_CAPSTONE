<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'QR Station Operator — CIS Management System' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <div class="scanner-page-wrapper toggled">

        {{-- Scanner Operator Sidebar --}}
        <x-layouts.scanner-operator.sidebar />

        <main class="scanner-page-content">
            <div class="container-fluid p-6 d-flex flex-column flex-grow-1">
                <section class="scanner-head-banner p-4 mx-3 mt-1.5">
                    <div class="scanner-head-banner__copy">
                        <h1 class="scanner-head-banner__title mt-3">{{ $pageName ?? 'QR Station' }}</h1>
                        @if ($subtitle ?? null)
                            <p class="scanner-head-banner__sub">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <div class="scanner-head-banner__meta">
                        <div class="d-flex flex-column align-items-end gap-2">
                            <div class="scanner-head-banner__date">
                                <i class="fa-regular fa-calendar-check"></i>
                                <span>
                                    <small>Today</small>
                                    <strong id="liveDate"></strong>
                                </span>
                            </div>
                            <x-help-button />
                        </div>

                        @isset($headerActions)
                            <div class="scanner-head-banner__actions">
                                {{ $headerActions }}
                            </div>
                        @endisset
                    </div>
                </section>

                <div class="scanner-page-slot mx-3">
                    {{ $slot }}
                </div>
            </div>
        </main>

    </div>

    @include('components.logout-modal')

    @stack('scripts')

</body>

</html>
