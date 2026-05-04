<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
class QrCode extends Model
{
    //
    protected $fillable = [
        'student_id',
        'code',
        'is_active',
    ];

    public function student(){
        return $this->belongsTo(Student::class , 'student_id');
    }



}
