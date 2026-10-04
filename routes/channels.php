<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('attendance.monitoring', function (User $user): bool {
    return $user->hasRole(User::ROLE_ADMIN, User::ROLE_SCANNER_OPERATOR, User::ROLE_SUPER_ADMIN);
});

Broadcast::channel('room-attendance.{sectionId}', function (User $user, int $sectionId): bool {
    if ($user->hasRole(User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN)) {
        return true;
    }

    if ($user->hasRole(User::ROLE_TEACHER)) {
        $teacher = $user->teacher;
        if (! $teacher) {
            return false;
        }

        return \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('section_id', $sectionId)
            ->where('status', 'active')
            ->exists();
    }

    return false;
});

