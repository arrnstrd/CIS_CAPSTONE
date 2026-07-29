# Bulk Import System Architecture

## Purpose

The Bulk Import module allows administrators to import multiple students from the official DepEd SF-1 template while automatically creating the following records:

- Student
- Guardian
- QR Code
- Enrollment

The goal is to reuse existing business logic instead of creating duplicate implementations.

---

# Existing Services

StudentService

Responsible for:

- Creating Student
- Creating Guardian
- Generating QR Code
- Student number generation
- Database transaction

EnrollmentService

Responsible for:

- Creating Enrollment
- Section validation
- Capacity checking
- Section snapshot
- Enrollment validation

Bulk Import must reuse these services.

No duplicate student or enrollment creation logic should exist inside Bulk Import.

---

# Main Components

BulkImportController

Responsibilities

- Upload file
- Start validation
- Confirm import
- View history
- View issues
- Download template

No business logic.

---

BulkImportService

Acts as the orchestrator.

Responsibilities

- Receive uploaded file
- Store file
- Create BulkImport session
- Call SpreadsheetParser
- Call ImportRowValidator
- Call ImportProcessor
- Update import status

---

SpreadsheetParser

Responsibilities

- Read Excel
- Ignore formatting
- Read only expected columns
- Convert rows into normalized data objects

Does not perform validation.

---

ImportRowValidator

Responsibilities

- Required fields
- Enum validation
- Email validation
- Date validation
- Duplicate rows inside uploaded file
- Collect validation errors

Does not create database records.

---

ImportProcessor

Responsibilities

Processes validated rows one by one.

For every row:

StudentService

↓

EnrollmentService

↓

Update counters

↓

Log issues if necessary

---

BulkImport

Stores import session information.

Contains

- filename
- uploader
- status
- counters
- timestamps

---

BulkImportIssue

Stores every warning or error.

Contains

- row number
- learner name
- issue type
- severity
- message
- raw row data

---

# Database Flow

Upload

↓

BulkImport record

↓

Validation

↓

Admin Confirmation

↓

Processing

↓

Student

↓

Guardian

↓

QR

↓

Enrollment

↓

BulkImport counters

↓

History

---

# Transaction Strategy

Validation stage

No database transaction.

Processing stage

Every row is processed independently.

Student creation already uses StudentService transaction.

Enrollment creation uses EnrollmentService.

If enrollment fails after student creation:

Student remains created.

Issue is logged.

Import continues.

---

# Failure Strategy

Validation failures

- stored as issues
- processing blocked until confirmation

Processing failures

- logged
- next row continues

Fatal failures

- import status becomes Failed

---

# Import Status

Pending

↓

Validating

↓

Waiting Confirmation

↓

Processing

↓

Completed

Completed With Issues

Cancelled

Failed

---

# Performance Strategy

Active School Year

Resolved once.

Sections

Loaded once.

Capacity counts

Cached during processing.

Duplicate LRNs inside uploaded file

Tracked in memory.

Database uniqueness

Still enforced by existing constraints.

---

# Reused Business Logic

Student creation

StudentService::createStudent()

Enrollment creation

EnrollmentService::createEnrollment()

No duplicate implementation should exist inside Bulk Import.