<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Student;

class Guardian extends Model
{
    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'email'
    ];

  
    // Student → Guardian (1:1)
    public function student()
    {
        return $this->hasOne(Student::class);
    }
}