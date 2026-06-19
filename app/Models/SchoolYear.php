<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast;

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

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
