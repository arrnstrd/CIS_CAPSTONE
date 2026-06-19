<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'level',
        'grade_level',
        'advisor_id',
        'capacity',
        'status'
    ];

    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'advisor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
    
}
