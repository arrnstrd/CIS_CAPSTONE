<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Teacher extends Model
{
    //

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
    ];

    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }
}
