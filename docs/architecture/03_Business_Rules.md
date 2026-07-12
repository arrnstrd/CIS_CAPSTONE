# Business Rules

## Students

- A student may have multiple enrollments throughout their academic history.
- Only one enrollment may be active per school year.
- Students cannot be scanned if inactive.

---

## QR Codes

- Each student has one active QR code.
- QR codes must be unique.
- Inactive QR codes cannot be used.

---

## Teaching Assignments

A teaching assignment represents:

- Teacher
- Subject
- Section
- School Year

It is the primary entity of the Teacher Portal.

One teacher may have multiple teaching assignments.

---

## Classroom Attendance

Room attendance is based on Teaching Assignments.

Attendance uses:

- session_type
- in_start
- late_threshold
- out_end

---

## Assessments

Assessments belong to one Teaching Assignment.

Assessment Categories include:

- Written Works
- Performance Tasks
- Quarterly Assessment

---

## Student Assessment Scores

One student has one score per assessment.

Score cannot exceed total_items.

---

## Quarterly Grades

Quarterly grades are automatically computed.

Teachers cannot manually encode final grades.

The system computes:

- Written Works Grade
- Performance Tasks Grade
- Quarterly Assessment Grade
- Initial Grade
- Transmuted Grade

---

## Reports

Consolidated grades are generated from Quarterly Grades.

No duplicated grade storage.