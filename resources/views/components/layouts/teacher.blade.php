<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{$title ?? 'CIS'  }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body>

    <div class="page-wrapper toggled">

        {{-- sidebar --}}
        <x-layouts.teacher.sidebar />

        <header class="top-nav">
    @php
        $teacherUser = auth()->user();
        $teacherProfile = $teacherUser?->teacher;
        $teacherName = $teacherProfile?->full_name ?? ($teacherUser?->first_name . ' ' . $teacherUser?->last_name);
        $teacherFirstName = $teacherUser?->first_name ?? trim(explode(' ', $teacherName)[0] ?? '');
        $teacherEmail = $teacherUser?->email ?? '';

        $hour = now()->hour;
        if ($hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour < 18) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }
    @endphp
    <div class="top-nav-greeting">
        <div class="top-nav-greeting-text">{{ $greeting }}, {{ $teacherFirstName }}!</div>
        <div class="top-nav-greeting-subtext">Here's what's happening in your classes today.</div>
    </div>
    <div class="top-nav-profile">
        <div class="top-nav-profile-avatar">
            <i class="fa-solid fa-circle-user"></i>
        </div>
        <div class="top-nav-profile-details">
            <span class="top-nav-profile-name">{{ $teacherName }}</span>
            <span class="top-nav-profile-email">{{ $teacherEmail }}</span>
        </div>
    </div>
</header>



        <main class="page-content">
            <div class="container-fluid p-6">
                <section class="admin-head-banner p-4 mx-3 mt-3">
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

                <div class="admin-page-slot mx-3">
                    {{ $slot }}
                </div>
            </div>

        </main>

    </div>

    @include('components.logout-modal')

</body>

</html>