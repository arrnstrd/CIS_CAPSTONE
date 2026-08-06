<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\QrCode;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    public function createStudent(array $studentData, array $guardianData): Student
    {
        return DB::transaction(function () use ($studentData, $guardianData) {

            $student = Student::create([
                'lrn' => $studentData['lrn'],
                'first_name' => strip_tags($studentData['first_name']),
                'last_name' => strip_tags($studentData['last_name']),
                'middle_name' => isset($studentData['middle_name'])
                    ? strip_tags($studentData['middle_name'])
                    : null,
                'sex' => $studentData['sex'],
                'address' => strip_tags($studentData['address']),
                'birthdate' => $studentData['birthdate'],
                'status' => $studentData['status'],
            ]);

            Guardian::create([
                'student_id' => $student->id,
                'name' => strip_tags($guardianData['name']),
                'relationship' => $guardianData['relationship'],
                'email' => strip_tags($guardianData['email']),
            ]);

            do {
                $qrCode = Str::upper(Str::random(32));
            } while (QrCode::where('code', $qrCode)->exists());

            QrCode::create([
                'student_id' => $student->id,
                'code' => $qrCode,
                'is_active' => true,
            ]);

            return $student;
        });
    }

    public function updateStudent(Student $student, array $studentData, array $guardianData): Student
    {
        return DB::transaction(function () use ($student, $studentData, $guardianData) {

            $student->update([
                'lrn' => $studentData['lrn'],
                'first_name' => strip_tags($studentData['first_name']),
                'last_name' => strip_tags($studentData['last_name']),
                'middle_name' => isset($studentData['middle_name'])
                    ? strip_tags($studentData['middle_name'])
                    : null,
                'sex' => $studentData['sex'],
                'address' => strip_tags($studentData['address']),
                'birthdate' => $studentData['birthdate'],
                'status' => $studentData['status'],
            ]);

            $student->guardian()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'name' => strip_tags($guardianData['name']),
                    'relationship' => $guardianData['relationship'],
                    'email' => strip_tags($guardianData['email']),
                ]
            );

            return $student;
        });
    }
}
