# Enrollment Duplicate Message Fix

## Summary

Fixed an enrollment create failure that could incorrectly show
"Student is already enrolled for this school year" even when no duplicate
enrollment existed.

## Files Modified

- `app/Http/Controllers/AcademicFeature/EnrollmentController.php`
  - Derives and saves `enrollments.level` from the selected section.
  - Fills legacy `grade_level` and `section` columns from the selected section when those columns still exist in the live database.
  - Updates `level` when an enrollment is moved to another section.
  - Returns the duplicate-enrollment message only for the actual
    `enrollments_student_school_year_unique` constraint.
- `app/Models/Enrollment.php`
  - Allows `level`, `grade_level`, and `section` to be mass assigned.
- `resources/js/enrollment.js`
  - Makes enrollment modal setup idempotent after AJAX refresh events.
  - Clears stale selected student state every time the Add Enrollment modal opens.
- `resources/views/admin-modules/management/enrollment/partials/not-enrolled-table.blade.php`
  - Adds the AJAX refresh scope to row-level Enroll buttons.
- `resources/views/admin-modules/academic/enrollment.blade.php`
  - Keeps the academic tab Enroll buttons scoped to the academic enrollment panel.

## Verification

- `php -l app/Http/Controllers/AcademicFeature/EnrollmentController.php` passed.
- `php -l app/Models/Enrollment.php` passed.
- `npm run build` passed.
- Confirmed the reported generic enrollment error was caused by the live database error `Field 'grade_level' doesn't have a default value`.

## Remaining Work

- Re-run the enrollment flow in the browser against the project database.
