<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_SCANNER_OPERATOR = 'scanner_operator';

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'role',
        'status',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function getOrCreateNotificationPreference(): NotificationPreference
    {
        if ($this->relationLoaded('notificationPreference') && $this->notificationPreference !== null) {
            return $this->notificationPreference;
        }

        $preference = $this->notificationPreference()->firstOrCreate([], [
            'attendance_enabled' => true,
            'grading_enabled' => true,
            'at_risk_enabled' => true,
            'analytics_enabled' => true,
            'announcement_enabled' => true,
            'import_enabled' => true,
        ]);

        $this->setRelation('notificationPreference', $preference);

        return $preference;
    }

    public function dashboardPreference()
    {
        return $this->hasOne(TeacherDashboardPreference::class);
    }

    public function getOrCreateDashboardPreference(): TeacherDashboardPreference
    {
        $preference = $this->dashboardPreference()->firstOrCreate([], [
            'default_view' => 'overview',
            'dashboard_density' => 'comfortable',
            'show_quick_actions' => true,
            'show_grading_progress' => true,
            'show_class_health' => true,
            'show_at_risk' => true,
            'show_recent_activity' => true,
            'show_summary_cards' => true,
            'show_student_counts' => true,
            'show_progress_indicators' => true,
            'default_class_id' => null,
            'default_term' => 'current',
            'theme' => 'system',
        ]);

        $this->setRelation('dashboardPreference', $preference);

        return $preference;
    }

    /**
     * Get the invitation tokens for this user.
     */
    public function invitationTokens()
    {
        return $this->hasMany(InvitationToken::class);
    }

    /**
     * Get the most recent unused invitation token for this user.
     */
    public function latestInvitation()
    {
        return $this->hasOne(InvitationToken::class)
            ->whereNull('used_at')
            ->latest('created_at');
    }

    /**
     * Check if the user has an active (unused, non-expired) invitation.
     */
    public function hasActiveInvitation(): bool
    {
        return $this->latestInvitation !== null
            && $this->latestInvitation->isValid();
    }

    /**
     * Get the invitation expiration time, if an active invitation exists.
     */
    public function getInvitationExpiresAtAttribute()
    {
        $invitation = $this->latestInvitation;
        return $invitation ? $invitation->expires_at : null;
    }

    /**
     * Check if the user has any invitation token (used or unused).
     */
    public function getInvitationTokenExistsAttribute(): bool
    {
        return $this->invitationTokens()->exists();
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isProtectedAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isTeacher(): bool
    {
        return $this->hasRole(self::ROLE_TEACHER);
    }

    public function isScannerOperator(): bool
    {
        return $this->hasRole(self::ROLE_SCANNER_OPERATOR);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'School Admin',
            self::ROLE_TEACHER => 'Teacher',
            self::ROLE_SCANNER_OPERATOR => 'Scanner Operator',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    // for future expansion on scanner operator
    // public function scannerOperator()
    // {
    //     return $this->hasOne(ScannerOperator::class);
    // }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    #[Override]
    protected static function booted()
    {
        static::created(function (User $user) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'employee_id')) {
                    $user->updateQuietly([
                        'employee_id' => sprintf(
                            'EMP-%s-%04d',
                            now()->year,
                            $user->id
                        ),
                    ]);
                }
            } catch (\Exception $e) {
                // In some test DB drivers (sqlite in-memory) migrations that
                // rename/add columns may be skipped. Silently ignore update
                // failures here to keep tests running in those environments.
            }
        });
    }
}
