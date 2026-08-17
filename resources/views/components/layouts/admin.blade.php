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



        <main class="page-content mx-5">
            <div class="mx-3">
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <!-- Left: Page title and subtitle -->
                        <div>
                            <h4 class="fw-bold text-dark mb-0">{{$pageName ?? 'Header' }} </h4>
                            <p class="text-muted small mb-0">{{ $subtitle ?? 'subtile here' }}</p>
                        </div>

                        <!-- Right: Current date badge -->
                        <div
                            class="current-date shadow-sm px-2 py-1 bg-white rounded-3 border d-flex align-items-center">
                            <i class="fa-regular fa-calendar-check text-primary me-2"></i>
                            <span class="fw-bold small text-uppercase" id="liveDate"> </span>
                        </div>
                    </div>
                </div>



                <div class="container-fluid">
                    {{$slot}}
                </div>
            </div>

        </main>

    </div>


    @include('components.logout-modal')

</body>

</html>