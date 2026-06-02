<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
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

    protected $hidden = [
        'password',
        'remember_token'
    ];


    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'role',
        'status',
        'email',
        'password'
    ];

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    
    #[Override]
    protected static function booted()
    {
          static::created(function($user){
            $user->user_id = 'STU-' . now()->year . '-' . str_pad($user->id, 4, '0' , STR_PAD_LEFT);
            $user->save();
        });
    }
     
      
    
}
