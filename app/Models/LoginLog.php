<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'email_attempted',
        'status',
        'ip_address',
        'user_agent',
        'attempted_at',
    ];

    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
