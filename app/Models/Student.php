<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'sex',
        'address',
        'status'
    ];

    protected $casts = [
        'birthdate'
    ];
    
    
}
