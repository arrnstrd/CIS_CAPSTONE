<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\QrCode;
use App\Models\Student;
use App\Services\QrSystem\QRCodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    public function __construct(
        private readonly QRCodeService $qrCodeService,
    ) {}

    /**
     * Ensure a student has an active QR code record AND its generated image.
     *
     * Idempotent: reuses an existing QR row (queried straight from the DB to
     * avoid stale relation caching), so it never creates duplicates. Also
     * backfills students that predate QR generation and students whose QR row
     * exists but has no image yet.
     */
    private function ensureQrCode(Student $student): QrCode
    {
        $qrCode = QrCode::where('student_id', $student->id)->first();

        if ($qrCode === null) {
            do {
                $code = Str::upper(Str::random(32));
            } while (QrCode::where('code', $code)->exists());

            $qrCode = QrCode::create([
                'student_id' => $student->id,
                'code' => $code,
                'is_active' => true,
            ]);
        }

        // Generate the QR PNG eagerly so the code is usable right away.
        return $this->qrCodeService->ensureImage($qrCode);
    }

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

            $this->ensureQrCode($student);

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

            // Backfill QR code + image for students who predate QR generation.
            $this->ensureQrCode($student);

            return $student;
        });
    }
}
