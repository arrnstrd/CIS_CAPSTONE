<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Notifications\TeacherSystemNotification;

class NotificationService
{
    public const CATEGORY_ATTENDANCE = 'attendance';
    public const CATEGORY_GRADING = 'grading';
    public const CATEGORY_AT_RISK = 'at_risk';
    public const CATEGORY_ANALYTICS = 'analytics';
    public const CATEGORY_IMPORT = 'import';

    public const CATEGORIES = [
        self::CATEGORY_ATTENDANCE => 'Attendance',
        self::CATEGORY_GRADING => 'Grading',
        self::CATEGORY_AT_RISK => 'At-Risk Students',
        self::CATEGORY_ANALYTICS => 'Analytics & Class Performance',
        self::CATEGORY_IMPORT => 'Data Import',
    ];

    /**
     * Send a notification to a specific user if their preferences allow it.
     */
    public function sendToUser(
        User $user,
        string $category,
        string $title,
        string $message,
        array $data = []
    ): ?object {
        if (! $this->isCategoryEnabledForUser($user, $category)) {
            return null;
        }

        $notification = new TeacherSystemNotification($category, $title, $message, $data);
        $user->notify($notification);

        return $user->notifications()->latest()->first();
    }

    /**
     * Static helper for sending a notification.
     */
    public static function send(
        User $user,
        string $category,
        string $title,
        string $message,
        array $data = []
    ): ?object {
        return app(self::class)->sendToUser($user, $category, $title, $message, $data);
    }

    /**
     * Check if a category is enabled for a given user.
     */
    public function isCategoryEnabledForUser(User $user, string $category): bool
    {
        $preferences = $user->notificationPreference;
        if (! $preferences) {
            return true;
        }

        return $preferences->isEnabled($category);
    }
}