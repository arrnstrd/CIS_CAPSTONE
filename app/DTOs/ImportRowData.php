<?php

namespace App\DTOs;

/**
 * Holds normalized data for a single row from the SF-1 import template.
 *
 * This DTO represents the imported spreadsheet data only.
 * Parsing, normalization, and validation are handled by the import pipeline.
 */
class ImportRowData
{
    public function __construct(
        public readonly int $rowNumber,

        // Student
        public readonly ?string $lrn = null,
        public readonly ?string $learnerName = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $middleName = null,
        public readonly ?string $sex = null,
        public readonly ?string $birthdate = null,
        public readonly ?string $address = null,

        // Guardian
        public readonly ?string $guardianName = null,
        public readonly ?string $guardianRelationship = null,
        public readonly ?string $guardianEmail = null,

        // Enrollment
        public readonly ?string $departmentLevel = null,
        public readonly ?string $gradeLevel = null,
        public readonly ?string $sectionName = null,
        public readonly ?string $sessionType = null,
    ) {}

    /**
     * Returns the raw imported values.
     * Used for validation, processing, and issue logging.
     *
     * @return array<string, string|int|null>
     */
    public function toRawData(): array
    {
        return [
            'LRN'                   => $this->lrn,
            'Learner Name'          => $this->learnerName,
            'Sex'                   => $this->sex,
            'Birth Date'            => $this->birthdate,
            'Complete Address'      => $this->address,
            'Guardian Name'         => $this->guardianName,
            'Guardian Relationship' => $this->guardianRelationship,
            'Guardian Email'        => $this->guardianEmail,
            'Department Level'      => $this->departmentLevel,
            'Grade Level'           => $this->gradeLevel,
            'Section'               => $this->sectionName,
            'Session Type'          => $this->sessionType,
        ];
    }

    /**
     * Build the student data array in the exact shape expected by
     * StudentService::createStudent().
     *
     * @return array{lrn: ?string, first_name: ?string, last_name: ?string,
     *               middle_name: ?string, sex: ?string, address: ?string,
     *               birthdate: ?string, status: string}
     */
    public function toStudentData(): array
    {
        return [
            'lrn'         => $this->lrn,
            'first_name'  => $this->firstName,
            'last_name'   => $this->lastName,
            'middle_name' => $this->middleName,
            'sex'         => $this->sex,
            'address'     => $this->address,
            'birthdate'   => $this->birthdate,
            'status'      => 'active',
        ];
    }

    /**
     * Build the guardian data array in the exact shape expected by
     * StudentService::createStudent().
     *
     * @return array{name: ?string, relationship: ?string, email: ?string}
     */
    public function toGuardianData(): array
    {
        return [
            'name'         => $this->guardianName,
            'relationship' => $this->guardianRelationship,
            'email'        => $this->guardianEmail,
        ];
    }

    /**
     * Build the enrollment data array for EnrollmentService::createEnrollment().
     *
     * The 'section_id' key is intentionally left null here — it will be set by
     * SectionResolver after lookup. Callers must set section_id before passing
     * this array to EnrollmentService.
     *
     * @return array{section_id: null, session_type: ?string, status: string}
     */
    public function toEnrollmentData(): array
    {
        return [
            'section_id'   => null, // Set by SectionResolver
            'session_type' => $this->sessionType,
            'status'       => 'active',
        ];
    }

    /**
     * Determines whether enrollment data is sufficient
     * to attempt enrollment creation.
     */
    public function hasEnrollmentData(): bool
    {
        return $this->departmentLevel !== null
            && $this->gradeLevel !== null
            && $this->sectionName !== null
            && $this->sessionType !== null;
    }
}
