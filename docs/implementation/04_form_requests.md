# Implementation Report — Step 4: Add Form Requests

## Objective
The objective of this phase is to create Laravel Form Request classes for the new grading and teaching schema tables to ensure clean, structured input validation separated from controller and model layers.

## Summary
Created 16 Form Request classes organized under `app/Http/Requests` using a feature-first structure matching the project conventions defined in `BACKEND_STANDARDS.md`. Every class contains correct namespaces and validation rules mapped strictly to the database column constraints from `DATABASE.md` and `CURRENT_DATABASE.json`.

## Files Created
1. `app/Http/Requests/Academic/Subject/StoreSubjectRequest.php`
2. `app/Http/Requests/Academic/Subject/UpdateSubjectRequest.php`
3. `app/Http/Requests/Teacher/StoreTeachingAssignmentRequest.php`
4. `app/Http/Requests/Teacher/UpdateTeachingAssignmentRequest.php`
5. `app/Http/Requests/QrSystem/Attendance/StoreRoomAttendanceRequest.php`
6. `app/Http/Requests/QrSystem/Attendance/UpdateRoomAttendanceRequest.php`
7. `app/Http/Requests/Grading/GradingPeriod/StoreGradingPeriodRequest.php`
8. `app/Http/Requests/Grading/GradingPeriod/UpdateGradingPeriodRequest.php`
9. `app/Http/Requests/Grading/AssessmentCategory/StoreAssessmentCategoryRequest.php`
10. `app/Http/Requests/Grading/AssessmentCategory/UpdateAssessmentCategoryRequest.php`
11. `app/Http/Requests/Grading/Assessment/StoreAssessmentRequest.php`
12. `app/Http/Requests/Grading/Assessment/UpdateAssessmentRequest.php`
13. `app/Http/Requests/Grading/StudentScore/StoreStudentAssessmentScoreRequest.php`
14. `app/Http/Requests/Grading/StudentScore/UpdateStudentAssessmentScoreRequest.php`
15. `app/Http/Requests/Grading/QuarterlyGrade/StoreQuarterlyGradeRequest.php`
16. `app/Http/Requests/Grading/QuarterlyGrade/UpdateQuarterlyGradeRequest.php`

## Files Modified
None. No controllers, routes, models, or frontend files were modified, strictly sticking to the scope of Step 4.

## Notes & Design Rationale
- **Composite Unique Constraint Note:**
  - The unique constraint `teaching_assignments_unique` on `(teacher_id, subject_id, section_id, school_year_id)` is noted as a Service-layer concern to be implemented in Step 5.
- **Score Validation Closure:**
  - `StoreStudentAssessmentScoreRequest` and `UpdateStudentAssessmentScoreRequest` include a custom closure checking that the input `score` does not exceed the related assessment's `total_items`.
- **Exclusion of Calculated Fields:**
  - Grade fields (`written_work_grade`, `performance_task_grade`, `quarterly_assessment_grade`, `initial_grade`, `transmuted_grade`) are system-computed and excluded from validation input rules in `StoreQuarterlyGradeRequest` and `UpdateQuarterlyGradeRequest`.

## Remaining Work
- Implement validation migration in existing controllers by replacing inline `$request->validate([...])` calls with these new Form Request classes (to be done separately).
- Proceed to Step 5 (Services) to implement business logic, transactions, and composite constraints checks.
