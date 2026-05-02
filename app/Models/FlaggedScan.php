<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlaggedScan extends Model
{
    //
    protected $fillable = [
        'flag_type',
        'description'
    ];
}
