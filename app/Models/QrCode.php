<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    //
    protected $fillable = [
        'student_id',
        'code',
        'image_path',
        'is_active',
    ];

    public function student(){
        return $this->belongsTo(Student::class , 'student_id');
    }



    
    public function hasImage(): bool
    {
        return !empty($this->image_path)
            && Storage::disk('public')->exists($this->image_path);
    }



}
