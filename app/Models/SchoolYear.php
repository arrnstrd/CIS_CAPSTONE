<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = [
        'school_year' ,
        'is_active'
    ];

    public function enrollments(){
        return $this->hasMany(Enrollment::class, 'school_year_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
