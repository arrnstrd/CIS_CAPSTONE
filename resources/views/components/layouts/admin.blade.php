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
            <div class="container-fluid px-4">
                <section class="admin-head-banner mx-3 mt-3">
                    <div class="admin-head-banner__copy">
                        <div class="admin-head-banner__eyebrow">{{ $pageName ?? 'Header' }}</div>
                        <h1 class="admin-head-banner__title">{{ $pageName ?? 'Header' }}</h1>
                        <p class="admin-head-banner__sub">{{ $subtitle ?? 'subtile here' }}</p>
                    </div>

                    <div class="admin-head-banner__meta">
                        <div class="admin-head-banner__date">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>
                                <small>Today</small>
                                <strong id="liveDate"></strong>
                            </span>
                        </div>

                        @isset($headerActions)
                            <div class="admin-head-banner__actions">
                                {{ $headerActions }}
                            </div>
                        @endisset
                    </div>
                </section>

                <div class="admin-page-slot">
                    {{ $slot }}
                </div>
            </div>
        </main>

    </div>


    @include('components.logout-modal')

</body>

</html>
