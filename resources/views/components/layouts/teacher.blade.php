<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{$title ?? 'CIS'  }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $savedTheme = auth()->user()?->dashboardPreference?->theme ?? 'system';
    @endphp
    <script>
        (function() {
            const userTheme = "{{ $savedTheme }}";
            let isDark = false;
            if (userTheme === 'dark') {
                isDark = true;
            } else if (userTheme === 'light') {
                isDark = false;
            } else {
                isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                const userTheme = "{{ $savedTheme }}";
                if (userTheme === 'system') {
                    const isDark = e.matches;
                    document.documentElement.classList.toggle('dark', isDark);
                    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                    document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
                }
            });
        }
    </script>
</head>

<body>

    <div class="page-wrapper toggled">

        {{-- sidebar --}}
        <x-layouts.teacher.sidebar />

        @php
            $teacherUser = auth()->user();
            $teacherProfile = $teacherUser?->teacher;
            $teacherName = $teacherProfile?->full_name ?? ($teacherUser?->first_name . ' ' . $teacherUser?->last_name);
            $teacherFirstName = $teacherUser?->first_name ?? trim(explode(' ', $teacherName)[0] ?? '');
            $teacherEmail = $teacherUser?->email ?? '';

            $unreadNotificationsCount = $teacherUser ? $teacherUser->unreadNotifications()->count() : 0;
            $recentNotifications = $teacherUser ? $teacherUser->notifications()->latest()->take(8)->get() : collect();

            $hour = now()->hour;
            if ($hour < 12) {
                $greeting = 'Good morning';
            } elseif ($hour < 18) {
                $greeting = 'Good afternoon';
            } else {
                $greeting = 'Good evening';
            }

            $getCategoryMeta = function ($category) {
                return match ($category) {
                    'attendance' => ['icon' => 'fa-solid fa-clipboard-user', 'bg' => 'bg-primary-subtle', 'color' => 'text-primary', 'label' => 'Attendance'],
                    'grading' => ['icon' => 'fa-solid fa-graduation-cap', 'bg' => 'bg-success-subtle', 'color' => 'text-success', 'label' => 'Grading'],
                    'at_risk' => ['icon' => 'fa-solid fa-triangle-exclamation', 'bg' => 'bg-danger-subtle', 'color' => 'text-danger', 'label' => 'At-Risk'],
                    'analytics' => ['icon' => 'fa-solid fa-chart-line', 'bg' => 'bg-info-subtle', 'color' => 'text-info', 'label' => 'Analytics'],
                    'announcement' => ['icon' => 'fa-solid fa-bullhorn', 'bg' => 'bg-warning-subtle', 'color' => 'text-warning', 'label' => 'Announcement'],
                    'import' => ['icon' => 'fa-solid fa-file-import', 'bg' => 'bg-secondary-subtle', 'color' => 'text-secondary', 'label' => 'Import'],
                    default => ['icon' => 'fa-solid fa-bell', 'bg' => 'bg-light', 'color' => 'text-primary', 'label' => 'Notice'],
                };
            };
        @endphp

        <header class="top-nav">
            <div class="top-nav-greeting">
                <div class="top-nav-greeting-text">{{ $greeting }}, {{ $teacherFirstName }}!</div>
                <div class="top-nav-greeting-subtext">Here's what's happening in your classes today.</div>
            </div>

            <div class="top-nav-actions">
                <!-- Notification Bell Dropdown -->
                <div class="dropdown notification-dropdown">
                    <button
                        class="top-nav-bell-btn btn position-relative"
                        type="button"
                        id="teacherNotificationDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Notifications"
                        title="Notifications"
                    >
                        <i class="fa-solid fa-bell"></i>
                        @if ($unreadNotificationsCount > 0)
                            <span id="navUnreadBadge" class="top-nav-bell-badge">
                                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        @endif
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notification-menu shadow-lg border-0 p-0" aria-labelledby="teacherNotificationDropdown">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark" style="font-size: 0.88rem;">Notifications</span>
                                @if ($unreadNotificationsCount > 0)
                                    <span id="menuUnreadPill" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.7rem;">
                                        {{ $unreadNotificationsCount }} new
                                    </span>
                                @endif
                            </div>
                            @if ($unreadNotificationsCount > 0)
                                <form method="POST" action="{{ route('teacher.notifications.mark-all-as-read') }}" class="m-0 js-mark-all-read-form">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-primary fw-semibold" style="font-size: 0.75rem;">
                                        Mark all as read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="notification-list" id="notificationList">
                            @forelse ($recentNotifications as $notification)
                                @php
                                    $isUnread = is_null($notification->read_at);
                                    $category = $notification->data['category'] ?? 'system';
                                    $catMeta = $getCategoryMeta($category);
                                    $title = $notification->data['title'] ?? 'Notification';
                                    $message = $notification->data['message'] ?? '';
                                    $actionUrl = $notification->data['data']['url'] ?? $notification->data['url'] ?? null;
                                @endphp
                                <div class="notification-item d-flex align-items-start gap-2 px-3 py-2 border-bottom {{ $isUnread ? 'unread-item' : '' }}" id="notif-item-{{ $notification->id }}">
                                    <div class="notification-icon-wrapper {{ $catMeta['bg'] }} {{ $catMeta['color'] }} mt-1">
                                        <i class="{{ $catMeta['icon'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <span class="notification-title fw-semibold text-dark text-truncate" style="font-size: 0.82rem;">
                                                {{ $title }}
                                            </span>
                                            @if ($isUnread)
                                                <span class="unread-dot rounded-circle bg-primary flex-shrink-0" style="width: 6px; height: 6px;"></span>
                                            @endif
                                        </div>
                                        <p class="notification-msg text-muted mb-1 text-truncate-2" style="font-size: 0.75rem; line-height: 1.35;">
                                            {{ $message }}
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted" style="font-size: 0.68rem;">
                                                <i class="fa-regular fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                            </small>
                                            @if ($isUnread)
                                                <form method="POST" action="{{ route('teacher.notifications.mark-as-read', $notification->id) }}" class="m-0 js-mark-read-form" data-id="{{ $notification->id }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-secondary" style="font-size: 0.7rem;">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 px-3">
                                    <i class="fa-regular fa-bell-slash text-muted fs-3 mb-2 d-block"></i>
                                    <p class="fw-semibold text-dark mb-1" style="font-size: 0.85rem;">No notifications yet</p>
                                    <small class="text-muted" style="font-size: 0.75rem;">You're all caught up.</small>
                                </div>
                            @endforelse
                        </div>

                        <div class="p-2 border-top text-center bg-light">
                            <a href="{{ route('teacher.notifications.index') }}" class="btn btn-link btn-sm text-decoration-none fw-semibold text-primary p-0" style="font-size: 0.78rem;">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Teacher Profile -->
                <div class="top-nav-profile">
                    <div class="top-nav-profile-avatar">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                    <div class="top-nav-profile-details">
                        <span class="top-nav-profile-name">{{ $teacherName }}</span>
                        <span class="top-nav-profile-email">{{ $teacherEmail }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="page-content teacher-page-bg">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Handle AJAX mark as read on individual notification in dropdown
            document.querySelectorAll('.js-mark-read-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const notifId = this.dataset.id;
                    const url = this.getAttribute('action');

                    try {
                        const response = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const item = document.getElementById('notif-item-' + notifId);
                            if (item) {
                                item.classList.remove('unread-item');
                                item.querySelector('.unread-dot')?.remove();
                                form.remove();
                            }

                            // Update badges
                            updateUnreadBadges(data.unread_count);

                            if (data.action_url) {
                                window.location.href = data.action_url;
                            }
                        } else {
                            form.submit();
                        }
                    } catch (err) {
                        form.submit();
                    }
                });
            });

            // Handle AJAX mark all as read
            document.querySelectorAll('.js-mark-all-read-form').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('action');

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            document.querySelectorAll('.notification-item.unread-item').forEach(item => {
                                item.classList.remove('unread-item');
                                item.querySelector('.unread-dot')?.remove();
                                item.querySelector('.js-mark-read-form')?.remove();
                            });
                            updateUnreadBadges(0);
                            form.remove();
                        } else {
                            form.submit();
                        }
                    } catch (err) {
                        form.submit();
                    }
                });
            });

            function updateUnreadBadges(count) {
                const navBadge = document.getElementById('navUnreadBadge');
                const menuPill = document.getElementById('menuUnreadPill');

                if (count <= 0) {
                    navBadge?.remove();
                    menuPill?.remove();
                } else {
                    if (navBadge) {
                        navBadge.textContent = count > 99 ? '99+' : count;
                    }
                    if (menuPill) {
                        menuPill.textContent = count + ' new';
                    }
                }
            }
        });
    </script>
    <script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>
</body>

</html>
