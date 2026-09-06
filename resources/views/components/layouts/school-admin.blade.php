<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'School Admin — CIS Management System' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <div class="sa-page-wrapper toggled">

        {{-- School Admin Sidebar --}}
        <x-layouts.school-admin.sidebar />

        {{-- Main Shell beside the full-height Sidebar --}}
        <div class="sa-main-shell">
            {{-- Thin Blue Top Navigation --}}
            <x-layouts.school-admin.top-nav />

            <main class="sa-page-content">
                <div class="container-fluid p-6 d-flex flex-column flex-grow-1">
                    <section class="sa-head-banner p-4 mx-3 mt-1.5">
                        <div class="sa-head-banner__copy">
                            <h1 class="sa-head-banner__title mt-3">{{ $pageName ?? 'Header' }}</h1>
                            @if ($subtitle ?? null)
                                <p class="sa-head-banner__sub">{{ $subtitle }}</p>
                            @endif
                        </div>

                        <div class="sa-head-banner__meta">
                            <div class="d-flex flex-column align-items-end gap-2">
                                <div class="sa-head-banner__date">
                                    <i class="fa-regular fa-calendar-check"></i>
                                    <span>
                                        <small>Today</small>
                                        <strong id="liveDate"></strong>
                                    </span>
                                </div>
                            </div>

                            @isset($headerActions)
                                <div class="sa-head-banner__actions">
                                    {{ $headerActions }}
                                </div>
                            @endisset
                        </div>
                    </section>

                    <div class="sa-page-slot mx-3">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>

    </div>

    @include('components.logout-modal')

    @stack('scripts')

</body>

</html>