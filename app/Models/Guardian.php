<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
class Guardian extends Model
{
    //
    protected $fillable = [
        'student_id',
        'name',
        'relationship',
        'email'
    ];


    public function student(){
        return $this->belongsTo(Student::class , 'student_id');
    }
}
