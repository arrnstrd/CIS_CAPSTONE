<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'level',
    ];

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
