<?php

namespace App\Services;

use App\Models\Enrollment;
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
        private readonly EnrollmentService $enrollmentService,
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
                $code = Str::upper(Str::random(64));
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

    public function createStudent(array $studentData, array $guardianData, ?array $enrollmentData = null): Student
    {
        return DB::transaction(function () use ($studentData, $guardianData, $enrollmentData) {

            $student = Student::create([
                'lrn' => $studentData['lrn'],
                'first_name' => strip_tags($studentData['first_name']),
                'last_name' => strip_tags($studentData['last_name']),
                'middle_name' => isset($studentData['middle_name'])
                    ? strip_tags($studentData['middle_name'])
                    : null,
                'suffix' => isset($studentData['suffix'])
                    ? strip_tags($studentData['suffix'])
                    : null,
                'sex' => $studentData['sex'],
                'address' => strip_tags($studentData['address']),
                'age' => $studentData['age'] ?? null,
                'birthplace' => isset($studentData['birthplace'])
                    ? strip_tags($studentData['birthplace'])
                    : null,
                'mother_tongue' => isset($studentData['mother_tongue'])
                    ? strip_tags($studentData['mother_tongue'])
                    : null,
                'ip_ethnic_group' => isset($studentData['ip_ethnic_group'])
                    ? strip_tags($studentData['ip_ethnic_group'])
                    : null,
                'religion' => isset($studentData['religion'])
                    ? strip_tags($studentData['religion'])
                    : null,
                'status' => $studentData['status'],
            ]);

            // Primary guardian only when a name is present — a blank name
            // means the row has no guardian (father/mother are created by
            // ImportProcessor). Email is nullable-safe.
            if (!empty(trim((string) ($guardianData['name'] ?? '')))) {
                Guardian::create([
                    'student_id' => $student->id,
                    'name' => strip_tags($guardianData['name']),
                    'relationship' => $guardianData['relationship'] ?? 'guardian',
                    'contact_number' => isset($guardianData['contact_number'])
                        ? strip_tags($guardianData['contact_number'])
                        : null,
                    'email' => isset($guardianData['email'])
                        ? strip_tags($guardianData['email'])
                        : null,
                ]);
            }

            $this->ensureQrCode($student);

            if ($enrollmentData) {
                $this->enrollmentService->createEnrollment(
                    $this->enrollmentPayload($student->id, $enrollmentData),
                    $enrollmentData['school_year_id'],
                );
            }

            return $student;
        });
    }

    public function updateStudent(Student $student, array $studentData, array $guardianData, ?array $enrollmentData = null): Student
    {
        return DB::transaction(function () use ($student, $studentData, $guardianData, $enrollmentData) {

            $student->update([
                'lrn' => $studentData['lrn'],
                'first_name' => strip_tags($studentData['first_name']),
                'last_name' => strip_tags($studentData['last_name']),
                'middle_name' => isset($studentData['middle_name'])
                    ? strip_tags($studentData['middle_name'])
                    : null,
                'suffix' => isset($studentData['suffix'])
                    ? strip_tags($studentData['suffix'])
                    : null,
                'sex' => $studentData['sex'],
                'address' => strip_tags($studentData['address']),
                'age' => $studentData['age'] ?? null,
                'status' => $studentData['status'],
            ]);

            $student->guardian()->updateOrCreate(
                ['student_id' => $student->id],
                [
                    'name' => strip_tags($guardianData['name']),
                    'relationship' => $guardianData['relationship'],
                    'contact_number' => isset($guardianData['contact_number'])
                        ? strip_tags($guardianData['contact_number'])
                        : null,
                    'email' => strip_tags($guardianData['email']),
                ]
            );

            // Backfill QR code + image for students who predate QR generation.
            $this->ensureQrCode($student);

            if ($enrollmentData) {
                $this->upsertEnrollment($student, $enrollmentData);
            }

            return $student;
        });
    }

    private function enrollmentPayload(int $studentId, array $data): array
    {
        return [
            'student_id' => $studentId,
            'section_id' => $data['section_id'],
            'status' => $data['status'],
        ];
    }

    private function upsertEnrollment(Student $student, array $data): void
    {
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('school_year_id', $data['school_year_id'])
            ->first();

        if ($enrollment) {
            $this->enrollmentService->updateEnrollment($enrollment, [
                'section_id' => $data['section_id'],
                'status' => $data['status'],
            ]);

            return;
        }

        $this->enrollmentService->createEnrollment(
            $this->enrollmentPayload($student->id, $data),
            $data['school_year_id'],
        );
    }
}
