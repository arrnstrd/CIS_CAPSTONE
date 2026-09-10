<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{$title ?? 'CIS'  }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <div class="teacher-page-wrapper toggled">

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

        <header class="teacher-top-nav">
            <div class="teacher-top-nav-greeting">
                <div class="teacher-top-nav-greeting-text">{{ $greeting }}, {{ $teacherFirstName }}!</div>
                <div class="teacher-top-nav-greeting-subtext">Here's what's happening in your classes today.</div>
            </div>

            <div class="teacher-top-nav-actions">
                <!-- Notification Bell Dropdown -->
                <div class="dropdown notification-dropdown">
                    <button
                        class="teacher-top-nav-bell-btn btn position-relative"
                        type="button"
                        id="teacherNotificationDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Notifications"
                        title="Notifications"
                    >
                        <i class="fa-solid fa-bell"></i>
                        @if ($unreadNotificationsCount > 0)
                            <span id="navUnreadBadge" class="teacher-top-nav-bell-badge">
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

                        <div class="notification-tabs-wrapper px-3 py-2 border-bottom bg-light">
                            <div class="notification-filter-tabs d-flex p-1">
                                <button type="button" class="notification-tab-btn btn btn-sm flex-fill border-0 active" data-filter="all">
                                    All
                                </button>
                                <button type="button" class="notification-tab-btn btn btn-sm flex-fill border-0 text-muted" data-filter="unread">
                                    Unread
                                </button>
                                <button type="button" class="notification-tab-btn btn btn-sm flex-fill border-0 text-muted" data-filter="read">
                                    Read
                                </button>
                            </div>
                        </div>

                        <div class="notification-list" id="notificationList">
                            <div id="notificationFilterEmpty" class="text-center py-4 px-3 d-none">
                                <i class="fa-regular fa-bell-slash text-muted fs-4 mb-2 d-block"></i>
                                <p class="fw-semibold text-dark mb-1" id="notificationFilterEmptyText" style="font-size: 0.82rem;">No notifications</p>
                                <small class="text-muted" style="font-size: 0.72rem;">Check other tabs or view all notifications.</small>
                            </div>
                            @forelse ($recentNotifications as $notification)
                                @php
                                    $isUnread = is_null($notification->read_at);
                                    $category = $notification->data['category'] ?? 'system';
                                    $catMeta = $getCategoryMeta($category);
                                    $title = $notification->data['title'] ?? 'Notification';
                                    $message = $notification->data['message'] ?? '';
                                    $actionUrl = $notification->data['data']['url'] ?? $notification->data['url'] ?? null;
                                    $attendanceDate = $notification->data['data']['attendance_date'] ?? $notification->data['attendance_date'] ?? null;
                                    $cleanMessage = preg_replace('/\s+for\s+(\d{4}-\d{2}-\d{2}|[A-Za-z]{3}\s+\d{1,2},\s*\d{4})\.?$/i', '.', $message);
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
                                            {{ $cleanMessage }}
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <small class="text-muted" style="font-size: 0.68rem;">
                                                    <i class="fa-regular fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                </small>
                                                @if ($attendanceDate)
                                                    <small class="text-muted" style="font-size: 0.68rem;">
                                                        <i class="fa-regular fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($attendanceDate)->format('M d, Y') }}
                                                    </small>
                                                @endif
                                            </div>
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

                <!-- Teacher Profile Dropdown -->
                <div class="dropdown profile-dropdown">
                    <button
                        class="teacher-top-nav-profile-btn btn d-flex align-items-center gap-2"
                        type="button"
                        id="teacherProfileDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Profile menu"
                        title="Profile menu"
                    >
                        <div class="teacher-top-nav-profile-avatar">
                            <i class="fa-solid fa-circle-user"></i>
                        </div>
                        <div class="teacher-top-nav-profile-details d-flex flex-column align-items-start">
                            <span class="teacher-top-nav-profile-name">{{ $teacherName }}</span>
                            <span class="teacher-top-nav-profile-email">{{ $teacherEmail }}</span>
                        </div>
                        <i class="fa-solid fa-chevron-down ms-2"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end profile-menu shadow-lg border-0" aria-labelledby="teacherProfileDropdown">
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <div class="teacher-top-nav-profile-avatar">
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $teacherName }}</div>
                                    <div class="text-muted small">{{ $teacherEmail }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-menu-items">
                            <a href="{{ route('teacher.settings.profile') }}" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="fa-solid fa-gear text-primary"></i>
                                <span>Settings</span>
                            </a>

                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#logoutModal"
                            >
                                <i class="fa-solid fa-sign-out-alt text-danger"></i>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="teacher-page-content">
            <div class="container-fluid p-0">
                @if (!request()->routeIs('teacher.dashboard'))
                    <section class="teacher-head-banner p-4 mx-3 mb-4">
                        <div class="teacher-head-banner__copy">
                            <h1 class="teacher-head-banner__title mt-1">{{ $pageName ?? 'Header' }}</h1>
                            @if ($subtitle ?? null)
                                <p class="teacher-head-banner__sub">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="teacher-head-banner__meta">
                            <div class="teacher-head-banner__date">
                                <i class="fa-regular fa-calendar-check"></i>
                                <span>
                                    <small>Today</small>
                                    <strong id="liveDatePageHeader">{{ now()->format('F d, Y') }}</strong>
                                </span>
                            </div>

                            <div class="d-flex flex-column align-items-end gap-2">
                                @unless (request()->routeIs('teacher.grading-system.*', 'room-attendance.*', 'teacher.attendance', 'teacher.student-management*', 'teacher.student-profile*'))<x-help-button />@endunless
                            </div>

                            @isset($headerActions)
                                <div class="teacher-head-banner__actions">
                                    {{ $headerActions }}
                                </div>
                            @endisset
                        </div>
                    </section>
                @endif

                <div class="teacher-page-slot mx-3">
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
                            applyNotificationFilter(currentFilter);

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
                            applyNotificationFilter(currentFilter);
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

            // Notification Tabs Filtering
            const tabButtons = document.querySelectorAll('.notification-tab-btn');
            const emptyFilterState = document.getElementById('notificationFilterEmpty');
            const emptyFilterText = document.getElementById('notificationFilterEmptyText');
            let currentFilter = 'all';

            function applyNotificationFilter(filter) {
                currentFilter = filter;
                let visibleCount = 0;

                tabButtons.forEach(btn => {
                    if (btn.dataset.filter === filter) {
                        btn.classList.add('active');
                        btn.classList.remove('text-muted');
                    } else {
                        btn.classList.remove('active');
                        btn.classList.add('text-muted');
                    }
                });

                const items = document.querySelectorAll('.notification-item');
                items.forEach(item => {
                    const isUnread = item.classList.contains('unread-item');
                    let show = false;

                    if (filter === 'all') {
                        show = true;
                    } else if (filter === 'unread') {
                        show = isUnread;
                    } else if (filter === 'read') {
                        show = !isUnread;
                    }

                    if (show) {
                        item.classList.remove('d-none');
                        item.classList.add('d-flex');
                        visibleCount++;
                    } else {
                        item.classList.remove('d-flex');
                        item.classList.add('d-none');
                    }
                });

                if (emptyFilterState) {
                    if (visibleCount === 0 && items.length > 0) {
                        emptyFilterState.classList.remove('d-none');
                        if (emptyFilterText) {
                            emptyFilterText.textContent = filter === 'unread'
                                ? 'No unread notifications'
                                : 'No read notifications';
                        }
                    } else {
                        emptyFilterState.classList.add('d-none');
                    }
                }
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    applyNotificationFilter(this.dataset.filter);
                });
            });
        });
    </script>
    <style>
        /* Profile Dropdown Styles */
        .teacher-top-nav-profile-btn {
            background: transparent;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .teacher-top-nav-profile-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .profile-menu {
            min-width: 280px;
            max-width: 320px;
        }

        .profile-menu-items {
            max-height: 300px;
            overflow-y: auto;
        }

        .profile-menu-items .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .profile-menu-items .dropdown-item:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .profile-menu-items .dropdown-item i {
            width: 20px;
            text-align: center;
        }
    </style>
    <script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>
    @stack('scripts')
</body>

</html>

