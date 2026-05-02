<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    //
    protected $fillable = [
        'code',
        'is_active',
    ];
}
