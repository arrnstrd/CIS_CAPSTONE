<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\QrCode;
use App\Models\Guardian;
use Override;

class Student extends Model
{
    protected $fillable = [

        'lrn',
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'sex',
        'address',
        'birthdate',
        'status',
        'student_number'
    ];


    #[Override]
    protected static function booted()
    {
static::created(function($student){
            if (empty($student->student_number)) {
                $student->student_number = 'STU-' . now()->year() . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
                $student->saveQuietly();
            }
        });
        });
    }

    // Student → QR Code (1:1)
    public function qrCode()
    {
        return $this->hasOne(QrCode::class);
    }

    // Student → Guardian (1:1)
    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // for overview cards in enrollment webpage
    public function scopeWithoutCurrentEnrollment($query, $school_year_id)
    {
        return $query->whereDoesntHave('enrollments', function ($q) use ($school_year_id) {
            $q->where('school_year_id', $school_year_id);
        });
    }
}
