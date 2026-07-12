<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
            $user->updateQuietly([
                'employee_id' => sprintf(
                    'EMP-%s-%04d',
                    now()->year,
                    $user->id
                ),
            ]);
        });
    }
}