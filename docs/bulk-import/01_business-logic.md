# 01_Bulk_Import_Business_Rules.md

# Bulk Import Business Rules

Version: 1.0

Purpose:
This document defines the complete business rules governing the Bulk Import feature of the Centralized Academic Management System. These rules serve as the authoritative reference for implementation and must be followed consistently by all backend services, frontend interfaces, and future maintenance.

---

# 1. Overview

The Bulk Import module allows administrators to import multiple student records from a standardized DepEd SF-1 spreadsheet while automatically creating the following:

- Student
- Guardian
- QR Code
- Enrollment

The system shall reuse the existing StudentService and EnrollmentService to ensure that imported students follow the exact same business rules as manually created students.

The Bulk Import module shall never bypass existing services.

---

# 2. General Rules

BR-001

Only authenticated administrators may access the Bulk Import module.

---

BR-002

The system shall only accept spreadsheet files.

Accepted formats:

- .xlsx

Rejected formats:

- .xls
- .csv
- .ods
- pdf
- images
- executable files

---

BR-003

Maximum upload size:

10 MB

Files exceeding this size shall be rejected before upload.

---

BR-004

The uploaded spreadsheet must follow the official system template based on the DepEd SF-1 format.

The system shall reject spreadsheets with missing required columns.

---

BR-005

Every upload creates one Import Session.

Each Import Session has its own:

- history
- issues
- statistics
- uploaded file
- uploaded user

---

# 3. Import Session Lifecycle

Every import session shall follow the lifecycle below.

Pending

↓

Validating

↓

Waiting Confirmation

↓

Processing

↓

Completed

OR

Completed With Issues

OR

Failed

OR

Cancelled

Status transitions must always occur sequentially.

---

# 4. Template Rules

The import template is derived from the DepEd SF-1 format.

Only required columns are included.

Additional system-specific columns are appended after the SF-1 columns.

Example:

Guardian Email

Department Level

Session Type

The administrator must never modify the header names.

---

# 5. Data Normalization Rules

The system shall normalize imported values before validation.

## 5.1 Department Level

Accepted values

Elementary
Elem

↓

elementary

----------------

High School
HS

↓

highschool

----------------

Senior High School
SHS

↓

senior_high_school

---

## 5.2 Sex

M

↓

male

F

↓

female

Case-insensitive.

---

## 5.3 Learner Name

The spreadsheet stores only one column.

Example

CRUZ, JUAN DELA SANTOS

The system shall automatically split into

Last Name

First Name

Middle Name

The parsed values become the values stored in Students.

---

## 5.4 Address

The template stores a complete address.

No additional parsing is required.

Stored directly into

students.address

---

## 5.5 Guardian Relationship

Accepted values

Mother

Father

Guardian

Sibling

Case-insensitive.

Values shall be normalized to lowercase.

---

## 5.6 Student Status

The spreadsheet shall NOT contain Student Status.

During import,

status shall automatically become

active

---

## 5.7 Enrollment Status

The spreadsheet shall NOT contain Enrollment Status.

During import,

status shall automatically become

active

---

# 6. Validation Rules

Validation occurs before processing.

Every row is validated independently.

One invalid row must never stop validation of other rows.

The system shall collect all validation issues.

---

Required fields

LRN

Learner Name

Sex

Birthdate

Address

Guardian Name

Guardian Relationship

Department Level

Grade Level

Section

Session Type

Guardian Email

---

LRN

Required

Exactly 12 digits

Unique

---

Guardian Email

Required

Valid email format

---

Birthdate

Must be a valid date.

---

Department Level

Must match

elementary

highschool

senior_high_school

after normalization.

---

Session Type

Must match

morning

afternoon

whole_day

after normalization.

---

# 7. Student Creation Rules

Student creation shall always use

StudentService::createStudent()

The Bulk Import module must never insert directly into the students table.

The existing StudentService remains the only source of truth.

StudentService shall automatically

generate Student Number

create Guardian

generate QR Code

wrap creation inside DB transaction

sanitize text fields

---

# 8. Guardian Rules

Each imported student creates exactly one guardian.

Guardian Email is mandatory.

Relationship must be one of the supported enum values.

---

# 9. Enrollment Rules

Enrollment shall automatically be created immediately after successful Student creation.

Enrollment creation uses

EnrollmentService::createEnrollment()

The system must never directly insert into enrollments.

EnrollmentService remains the source of truth.

---

Enrollment automatically uses

Current Active School Year

Status = Active

Section Snapshot

Level Snapshot

Grade Level Snapshot

---

# 10. Section Resolution Rules

The spreadsheet provides

Department Level

Grade Level

Section Name

The system shall resolve these three values into exactly one Section.

Matching is based on

Section Name

+

Department Level

+

Grade Level

No matching section

↓

Enrollment Issue

Student remains created.

---

Inactive section

↓

Enrollment Issue

Student remains created.

---

Full section

↓

Enrollment Issue

Student remains created.

---

# 11. Processing Rules

Rows are processed individually.

The system must never wrap the entire import in one transaction.

Each student uses the existing StudentService transaction.

If row 20 fails,

Rows 1–19 remain committed.

Rows 21+ continue processing.

---

# 12. Error Handling Rules

Validation Errors

Prevent processing of that row.

---

Processing Errors

Do not stop remaining rows.

---

Database Errors

Recorded as Import Issues.

---

Unexpected Exceptions

Recorded.

Processing continues.

---

# 13. Duplicate Rules

Duplicate LRN

↓

Student creation fails

↓

Issue logged

↓

Continue next row

---

Duplicate Enrollment

↓

Enrollment skipped

↓

Student remains created

↓

Issue logged

---

Duplicate QR

Handled automatically by StudentService.

---

# 14. Import Issues

Every issue belongs to one Import Session.

Each issue stores

Row Number

Issue Type

Severity

Field

Message

Original Row Data

Timestamp

Issues cannot delete imported students.

---

# 15. History Rules

Every completed import becomes part of Import History.

History stores

Uploaded By

Upload Date

Filename

Total Rows

Successful Rows

Failed Rows

Warning Count

Current Status

History is read-only.

---

# 16. Security Rules

Only administrators may

Upload

Validate

Confirm

Cancel

View History

View Issues

Download Template

---

# 17. Cancellation Rules

An import may only be cancelled before Processing starts.

Once Processing begins,

Cancellation is no longer permitted.

---

# 18. System Responses

Successful Upload

↓

Import Session Created

---

Successful Validation

↓

Validation Summary

↓

Waiting Confirmation

---

Successful Import

↓

Completed

↓

Success Modal

↓

History Updated

---

Import With Issues

↓

Completed With Issues

↓

Issue Records Created

↓

History Updated

---

Fatal Error

↓

Failed

↓

Error Modal

↓

Issue Logged

---

# 19. Design Principles

The Bulk Import module shall

Reuse StudentService

Reuse EnrollmentService

Never duplicate business logic

Never bypass validation

Never bypass transactions

Never stop processing because of a single row failure

Always record every issue

Always maintain a complete audit history

Always preserve data integrity