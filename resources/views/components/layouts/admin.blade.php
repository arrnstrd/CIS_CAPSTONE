<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{$title ?? 'CIS Management System'  }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
    @stack('styles')
</head>

<body>

    <div class="page-wrapper toggled">

        {{-- sidebar --}}
        <x-layouts.admin.sidebar />

        <header class="top-nav">
        </header>



        <main class="page-content">
            <div class="container-fluid p-6">
                <section class="admin-head-banner p-4  mx-3 mt-3">
                    <div class="admin-head-banner__copy">
                        <h1 class="admin-head-banner__title mt-3">{{ $pageName ?? 'Header' }}</h1>
                        @if ($subtitle ?? null)
                            <p class="admin-head-banner__sub">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <div class="admin-head-banner__meta">
                        <div class="admin-head-banner__date">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>
                                <small>Today</small>
                                <strong id="liveDate"></strong>
                            </span>
                        </div>

                        <x-help-button />

                        @isset($headerActions)
                            <div class="admin-head-banner__actions">
                                {{ $headerActions }}
                            </div>
                        @endisset
                    </div>
                </section>

                <div class="admin-page-slot mx-2">
                    {{ $slot }}
                </div>
            </div>
        </main>

    </div>


    @include('components.logout-modal')

</body>

</html>