<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
