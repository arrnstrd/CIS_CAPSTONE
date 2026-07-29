# 03. Business Rules

This document defines all business rules enforced by the Bulk Import module. These rules govern validation, normalization, processing, enrollment creation, issue handling, and overall import behavior.

---

## 1. General Rules

### BR-001 - Administrator Access

Only authenticated users with Administrator privileges may access the Bulk Import module.

---

### BR-002 - Supported File Types

Only Microsoft Excel spreadsheets are accepted.

Accepted formats:

- .xlsx
- .xls

CSV files are not supported.

---

### BR-003 - Maximum File Size

Maximum upload size:

10 MB

Files exceeding this limit shall be rejected before validation.

---

### BR-004 - Import Source

The uploaded spreadsheet must follow the provided Bulk Import Template based on the DepEd SF-1 format.

Modified columns required by the system are included in the template.

---

## 2. Template Rules

### BR-005 - Learner Name Format

The template only requires one column:

Learner Name

Format:

Last Name, First Name Middle Name

Examples:

Santos, Juan Dela Cruz

Dela Cruz, Maria

The system automatically parses:

- Last Name
- First Name
- Middle Name

No separate name columns are required.

---

### BR-006 - Department Level

The template uses:

Department Level

Accepted values:

- elementary
- highschool
- senior_high_school

The system also accepts common aliases.

Examples

| Input | Normalized |
|--------|------------|
| Elem | elementary |
| Elementary | elementary |
| HS | highschool |
| High School | highschool |
| SHS | senior_high_school |
| Senior High | senior_high_school |

The normalized value must match the Section table enum.

---

### BR-007 - Guardian Relationship

Accepted values only:

- mother
- father
- guardian
- sibling

The template displays these accepted values as guidance.

---

### BR-008 - Email Address

Guardian Email is required.

The email must be valid.

Duplicate emails are allowed because multiple guardians may share an email.

---

### BR-009 - Full Address

The template contains one Full Address column.

If the source SF-1 contains multiple address fields, the template combines them into one.

---

## 3. Automatic Values

These values are never supplied by the spreadsheet.

---

### BR-010 - Student Status

Every imported student automatically receives:

status = active

---

### BR-011 - Enrollment Status

Every created enrollment automatically receives:

status = active

---

### BR-012 - School Year

The active School Year is automatically assigned.

The spreadsheet never contains School Year.

If no active School Year exists, the import cannot proceed.

---

### BR-013 - Student Number

Student Number is generated automatically by the existing Student model.

The spreadsheet never provides Student Number.

---

### BR-014 - QR Code

Every imported student automatically receives:

- QR Code
- Active QR status

using the existing StudentService.

---

## 4. Section Resolution

### BR-015 - Section Lookup

The spreadsheet does not provide Section ID.

The system resolves the Section using:

- Section Name
- Department Level
- Grade Level

Example

Section Name = Einstein

Department Level = highschool

Grade Level = 8

↓

Section ID = 15

---

### BR-016 - Missing Section

If no matching section exists:

Student creation continues.

Enrollment is skipped.

A Warning Issue is created.

---

### BR-017 - Inactive Section

If the section exists but is inactive:

Student creation continues.

Enrollment is skipped.

A Warning Issue is created.

---

### BR-018 - Full Section

If section capacity has been reached:

Student creation continues.

Enrollment is skipped.

A Warning Issue is created.

---

## 5. Student Creation Rules

Student creation uses the existing StudentService.

The Bulk Import module never duplicates student creation logic.

StudentService automatically performs:

- strip_tags()
- Student creation
- Guardian creation
- QR generation
- Transaction handling

---

### BR-019 - Duplicate LRN

If the LRN already exists:

Student creation fails.

Enrollment is skipped.

The issue is recorded.

Import continues with the next row.

---

### BR-020 - Duplicate LRN Inside Spreadsheet

If the spreadsheet contains duplicate LRNs:

Only the first occurrence is processed.

Remaining duplicates become Issues.

---

## 6. Enrollment Rules

Enrollment creation uses the existing EnrollmentService.

No enrollment logic is duplicated.

---

### BR-021 - Enrollment Creation

Enrollment is automatically created after successful student creation.

Administrator does not perform a second import.

---

### BR-022 - Duplicate Enrollment

If the student is already enrolled in the active School Year:

Student remains created.

Enrollment is skipped.

Issue is recorded.

---

### BR-023 - Enrollment Dependency

Enrollment is only attempted if Student creation succeeds.

---

## 7. Validation Rules

Validation occurs before processing.

Examples include:

- Missing required values
- Invalid email
- Invalid relationship
- Invalid department level
- Invalid session type
- Invalid birthdate

Validation errors prevent processing until acknowledged.

---

## 8. Processing Rules

The system processes one row at a time.

Each row is independent.

Failure of one row does not stop remaining rows.

---

### BR-024 - Transaction Boundary

Student creation remains atomic through StudentService.

Enrollment uses the existing EnrollmentService.

No global transaction exists for the entire spreadsheet.

---

### BR-025 - Progress

The system updates progress after every processed row.

---

### BR-026 - Cancellation

The Administrator may cancel before processing begins.

Processing cannot be cancelled once database insertion has started.

---

## 9. Issue Management

Every failure generates an Issue record.

Each Issue stores:

- Import Session
- Row Number
- Severity
- Message
- Original Row Data

---

### BR-027 - Warning Issues

Warnings allow processing to continue.

Examples:

- Section Full
- Section Not Found
- Duplicate Enrollment

---

### BR-028 - Error Issues

Errors prevent that specific row from completing.

Examples:

- Duplicate LRN
- Database Error
- Missing Required Data

---

### BR-029 - Issue History

Issues are permanently stored for auditing.

They remain accessible from Import History.

---

## 10. Import Completion

### BR-030 - Completed

All rows processed successfully.

Status:

Completed

---

### BR-031 - Completed with Issues

Some rows failed.

Some rows succeeded.

Status:

Completed with Issues

---

### BR-032 - Failed

A fatal system error occurred before processing could complete.

Status:

Failed

---

### BR-033 - Audit Trail

Every import permanently records:

- Administrator
- Upload Time
- Filename
- Total Rows
- Successful Rows
- Failed Rows
- Warning Count
- Error Count
- Processing Duration
- Final Status

Import History serves as the official audit log.