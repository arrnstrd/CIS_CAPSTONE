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
        'age',
        'birthplace',
        'mother_tongue',
        'ip_ethnic_group',
        'religion',
        'status',
        'student_number'
    ];


    #[Override]
    protected static function booted()
    {
static::created(function($student){
            if (empty($student->student_number)) {
                $student->student_number = 'STU-' . now()->year . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
                $student->saveQuietly();
            }
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

    public function getFullNameAttribute(): string
    {
        $mi = $this->middle_name ? ' ' . mb_substr($this->middle_name, 0, 1) . '.' : '';
        return trim(($this->last_name ?? '') . ', ' . ($this->first_name ?? '') . $mi);
    }
}
