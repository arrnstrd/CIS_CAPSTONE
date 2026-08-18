<?php

namespace App\DTOs;

/**
 * Holds normalized data for a single student row from the official
 * DepEd School Form 1 (SF1) — School Register.
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
        public readonly ?string $age = null,
        public readonly ?string $birthplace = null,
        public readonly ?string $motherTongue = null,
        public readonly ?string $ipEthnicGroup = null,
        public readonly ?string $religion = null,
        public readonly ?string $address = null,

        // Parents / Guardian
        public readonly ?string $fatherName = null,
        public readonly ?string $motherMaidenName = null,
        public readonly ?string $guardianName = null,
        public readonly ?string $guardianRelationship = null,
        public readonly ?string $guardianContactNumber = null,
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
     * @return array<string, string|int|bool|null>
     */
    public function toRawData(): array
    {
        return [
            'LRN'                   => $this->lrn,
            'Learner Name'          => $this->learnerName,
            'Sex'                   => $this->sex,
            'Age'                   => $this->age,
            'Birthplace'            => $this->birthplace,
            'Mother Tongue'         => $this->motherTongue,
            'IP/Ethnic Group'       => $this->ipEthnicGroup,
            'Religion'              => $this->religion,
            'Complete Address'      => $this->address,
            "Father's Name"         => $this->fatherName,
            "Mother's Maiden Name"  => $this->motherMaidenName,
            'Guardian Name'         => $this->guardianName,
            'Guardian Relationship' => $this->guardianRelationship,
            'Guardian Contact'      => $this->guardianContactNumber,
            'Guardian Email'        => $this->guardianEmail,
            'Department Level'      => $this->departmentLevel,
            'Grade Level'           => $this->gradeLevel,
            'Section'               => $this->sectionName,
        ];
    }

    /**
     * Build the student data array in the exact shape expected by
     * StudentService::createStudent().
     *
     * @return array{lrn: ?string, first_name: ?string, last_name: ?string,
     *               middle_name: ?string, sex: ?string, address: ?string,
     *               age: ?int, birthplace: ?string, mother_tongue: ?string,
     *               ip_ethnic_group: ?string, religion: ?string, status: string}
     */
    public function toStudentData(): array
    {
        return [
            'lrn'             => $this->lrn,
            'first_name'      => $this->firstName,
            'last_name'       => $this->lastName,
            'middle_name'     => $this->middleName,
            'sex'             => $this->sex,
            'address'         => $this->address ?? '',
            'age'             => ($this->age !== null && is_numeric($this->age)) ? (int) $this->age : null,
            'birthplace'      => $this->birthplace,
            'mother_tongue'   => $this->motherTongue,
            'ip_ethnic_group' => $this->ipEthnicGroup,
            'religion'        => $this->religion,
            'status'          => 'active',
        ];
    }

    /**
     * Build the guardian data array in the exact shape expected by
     * StudentService::createStudent().
     *
     * The primary guardian record uses relationship 'guardian'; father and
     * mother records are created separately by ImportProcessor using the
     * shared contact/email from this row.
     *
     * @return array{name: ?string, relationship: string, contact_number: ?string, email: ?string}
     */
    public function toGuardianData(): array
    {
        return [
            'name'           => $this->guardianName,
            'relationship'   => 'guardian',
            'contact_number' => $this->guardianContactNumber,
            'email'          => $this->guardianEmail,
        ];
    }

    /**
     * Build the enrollment data array for EnrollmentService::createEnrollment().
     *
     * The 'section_id' key is intentionally left null here — it will be set by
     * SectionResolver after lookup. Callers must set section_id before passing
     * this array to EnrollmentService. Session type is derived from the
     * resolved Section by EnrollmentService, not set here.
     *
     * @return array{section_id: null, status: string}
     */
    public function toEnrollmentData(): array
    {
        return [
            'section_id'   => null, // Set by SectionResolver
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
            && $this->sectionName !== null;
    }
}
