<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\QrCode;
use App\Models\Student;

class Guardian extends Model
{
    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'sex',
        'address',
        'birthdate',
        'status'
    ];

    // Student → QR Code (1:1)
    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }

    // Student → Guardian (1:1)
    public function student()
    {
        return $this->hasOne(Student::class);
    }
}