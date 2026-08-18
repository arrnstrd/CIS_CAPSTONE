# 02_SF1_Template_Specification.md

# SF-1 Bulk Import Template Specification

Version: 1.0

Purpose:

This document defines the official spreadsheet template accepted by the Bulk Import module. The template is based on the DepEd SF-1 format with additional system-specific columns required by the Centralized Academic Management System.

---

# 1. Template Overview

The import template shall follow the structure of the DepEd SF-1 Learner Information Sheet.

Only the columns required by the system shall be included.

The administrator must not:

- Rename headers
- Remove headers
- Rearrange headers
- Insert additional columns between existing headers

The first row shall always contain the column headers.

Data begins on Row 2.

---

# 2. Column Mapping

| Excel Header          | System Field                                   | Required        | Notes                                                                                                                                                                                                |
| --------------------- | ---------------------------------------------- | --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| LRN                   | students.lrn                                   | Yes             | 12 digits                                                                                                                                                                                            |
| Learner Name          | Parsed into first_name, middle_name, last_name | Yes             | Format: LASTNAME, FIRSTNAME MIDDLENAME                                                                                                                                                               |
| Sex                   | students.sex                                   | Yes             | M/F                                                                                                                                                                                                  |
| Birth Date            | students.birthdate                             | Yes             | Valid date                                                                                                                                                                                           |
| Complete Address      | students.address                               | Yes             | Full address                                                                                                                                                                                         |
| Guardian Name         | guardians.name                                 | Yes             | Full name                                                                                                                                                                                            |
| Guardian Relationship | guardians.relationship                         | Yes             | Must match supported values                                                                                                                                                                          |
| Guardian Email        | guardians.email                                | Yes             | Valid email                                                                                                                                                                                          |
| Department Level      | sections.level                                 | Yes             | Elementary / High School / Senior High School                                                                                                                                                        |
| Grade Level           | sections.grade_level                           | Yes             | Numeric                                                                                                                                                                                              |
| Section               | sections.name                                  | Yes             | Existing section                                                                                                                                                                                     |
| Session Type          | —                                              | No (deprecated) | Accepted for backward compatibility only. Session type is now derived from the resolved Section (`sections.session_type`). If provided, the value is parsed but no longer written to the enrollment. |

---

# 3. Automatic Fields

The following values are NOT included in the spreadsheet.

The system generates them automatically.

| Field             | Value                       |
| ----------------- | --------------------------- |
| Student Number    | Generated by StudentService |
| Student Status    | active                      |
| Enrollment Status | active                      |
| QR Code           | Generated automatically     |
| School Year       | Current Active School Year  |
| Import Date       | Current Timestamp           |
| Uploaded By       | Current Administrator       |

---

# 4. Learner Name Format

Accepted Format

LASTNAME, FIRSTNAME MIDDLENAME

Example

CRUZ, JUAN DELA SANTOS

The parser automatically extracts

Last Name

CRUZ

First Name

JUAN

Middle Name

DELA SANTOS

If no middle name exists

Middle Name = NULL

Examples

SANTOS, JUAN

↓

Last Name

SANTOS

First Name

JUAN

Middle Name

NULL

---

# 5. Sex Normalization

Accepted Values

M

Male

m

male

↓

male

---

F

Female

f

female

↓

female

Case-insensitive.

---

# 6. Department Level Normalization

The spreadsheet uses a human-friendly value.

The system converts it into the database enum.

| Accepted Value     | Stored Value       |
| ------------------ | ------------------ |
| Elementary         | elementary         |
| Elem               | elementary         |
| ELEM               | elementary         |
| High School        | highschool         |
| HS                 | highschool         |
| Junior High        | highschool         |
| Senior High School | senior_high_school |
| SHS                | senior_high_school |

Matching is case-insensitive.

---

# 7. Session Type Normalization

| Accepted Value    | Stored Value |
| ----------------- | ------------ |
| Morning           | morning      |
| AM                | morning      |
| Afternoon         | afternoon    |
| PM                | afternoon    |
| Whole Day         | whole_day    |
| WholeDay          | whole_day    |
| Whole Day Session | whole_day    |

Matching is case-insensitive.

---

# 8. Guardian Relationship

Accepted Values

Mother

Father

Guardian

Sibling

Stored Values

mother

father

guardian

sibling

Matching is case-insensitive.

---

# 9. Grade Level

Accepted Values

1

2

3

...

12

Stored as Integer.

---

# 10. Section Resolution

The spreadsheet does NOT provide Section ID.

Instead, the system resolves the section using

Department Level

-

Grade Level

-

Section Name

Example

Department Level

Elementary

Grade Level

3

Section

Rizal

↓

System finds

Section

Name = Rizal

Level = elementary

Grade Level = 3

↓

Returns Section ID

If multiple sections match

↓

Configuration Error

If no section matches

↓

Enrollment Issue

---

# 11. Required Columns

The following columns are mandatory.

- LRN
- Learner Name
- Sex
- Birth Date
- Complete Address
- Guardian Name
- Guardian Relationship
- Guardian Email
- Department Level
- Grade Level
- Section
- Session Type

Missing headers immediately reject the spreadsheet.

---

# 12. Blank Values

Blank required values

↓

Validation Error

↓

Issue Logged

↓

Row skipped

Blank optional values

↓

NULL

Current template contains no optional columns.

---

# 13. Invalid Values

Examples

Invalid Sex

X

↓

Validation Error

---

Department Level

College

↓

Validation Error

---

Guardian Relationship

Aunt

↓

Validation Error

---

Session Type

Night

↓

Validation Error

---

# 14. Duplicate Rows

Duplicate LRN within the uploaded spreadsheet

↓

Validation Error

↓

Only the first occurrence is processed.

Remaining duplicate rows are skipped.

---

# 15. Header Validation

The system validates every required header before parsing.

Missing Header

↓

Upload Rejected

Incorrect Header Name

↓

Upload Rejected

Extra Columns

↓

Ignored

as long as all required headers exist.

---

# 16. File Requirements

Accepted Format

.xlsx

Maximum File Size

10 MB

Maximum Rows

5,000 rows

First Worksheet only

The system ignores additional worksheets.

---

# 17. Import Summary

After validation the administrator receives

Total Rows

Valid Rows

Invalid Rows

Warnings

Detected Sections

Detected Department Levels

Duplicate LRNs

Missing Sections

The administrator must confirm before processing begins.

---

# 18. Template Notes

The provided template is the only supported import format.

Any modification to:

- Header Names
- Header Order
- Required Columns

may result in validation failure.

Administrators are encouraged to always download the latest template directly from the system before performing an import.
