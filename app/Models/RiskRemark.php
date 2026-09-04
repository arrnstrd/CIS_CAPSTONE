<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskRemark extends Model
{
    /**
     * This table only has created_at (no updated_at column).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'enrollment_id',
        'teacher_id',
        'remark',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}