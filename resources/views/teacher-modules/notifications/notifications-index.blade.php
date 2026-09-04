<x-layouts.teacher>
    <x-slot name="pageName">
        <div class="d-flex align-items-center gap-2">
            <x-ui.backButton />
            <span class="page-title-icon">
                <i class="fa-solid fa-bell"></i>
                Notifications
            </span>
        </div>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">View and manage all your in-app notifications and alerts.</span>
    </x-slot>

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @php
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

    <div class="gs-panel">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <p class="gs-panel-title mb-0">All Notifications</p>
                @if ($unreadCount > 0)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        {{ $unreadCount }} unread
                    </span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="checkbox" id="selectAllNotifications">
                    <label class="form-check-label small" for="selectAllNotifications">Select all</label>
                </div>
                <button type="button" id="deleteSelectedBtn" class="btn btn-outline-danger btn-sm" disabled>
                    <i class="fa-solid fa-trash me-1"></i> Delete selected
                </button>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('teacher.notifications.mark-all-as-read') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-check-double me-1"></i> Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column gap-2">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $category = $notification->data['category'] ?? 'system';
                    $catMeta = $getCategoryMeta($category);
                    $title = $notification->data['title'] ?? 'Notification';
                    $message = $notification->data['message'] ?? '';
                    $actionUrl = $notification->data['data']['url'] ?? $notification->data['url'] ?? null;
                @endphp
                <div class="p-3 rounded border d-flex align-items-start gap-3 {{ $isUnread ? 'bg-light-subtle border-primary-subtle' : 'bg-white' }}">
                    <div class="form-check pt-1">
                        <input class="form-check-input notification-checkbox" type="checkbox" value="{{ $notification->id }}">
                    </div>
                    <div class="notification-icon-wrapper {{ $catMeta['bg'] }} {{ $catMeta['color'] }} p-2 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="{{ $catMeta['icon'] }} fs-6"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $catMeta['bg'] }} {{ $catMeta['color'] }} border" style="font-size: 0.7rem;">
                                    {{ $catMeta['label'] }}
                                </span>
                                <h6 class="mb-0 fw-semibold text-dark" style="font-size: 0.88rem;">
                                    {{ $title }}
                                </h6>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="fa-regular fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>

                        <p class="text-secondary mb-2" style="font-size: 0.82rem; line-height: 1.4;">
                            {{ $message }}
                        </p>

                        <div class="d-flex align-items-center gap-3">
                            @if ($actionUrl)
                                <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                    View Details <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('teacher.notifications.mark-as-read', $notification->id) }}" class="m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" style="font-size: 0.75rem;">
                                        <i class="fa-regular fa-circle-check me-1"></i> Mark as read
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-check me-1"></i> Read {{ $notification->read_at->diffForHumans() }}
                                </span>
                            @endif
                            <form method="POST" action="{{ route('teacher.notifications.destroy', $notification->id) }}" class="m-0" onsubmit="return confirm('Delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fa-regular fa-bell-slash text-muted fs-1 mb-3 d-block"></i>
                    <h5 class="fw-semibold text-dark mb-1">No notifications yet</h5>
                    <p class="text-muted small mb-0">You're all caught up with your classes.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllNotifications');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        const checkboxes = document.querySelectorAll('.notification-checkbox');

        function updateDeleteBtnState() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            deleteBtn.disabled = !anyChecked;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateDeleteBtnState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                if (!cb.checked && selectAll) selectAll.checked = false;
                updateDeleteBtnState();
            });
        });

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const selectedIds = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selectedIds.length === 0) return;
                if (!confirm('Delete ' + selectedIds.length + ' selected notification(s)?')) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch('{{ route("teacher.notifications.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids: selectedIds }),
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(() => alert('Something went wrong while deleting.'));
            });
        }
    });
    </script>
</x-layouts.teacher>
