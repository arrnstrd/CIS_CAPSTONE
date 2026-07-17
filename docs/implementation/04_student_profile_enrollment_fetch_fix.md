# Student Profile Enrollment Fetch Fix

## Summary

Fixed student profile enrollment display so it reads the actual enrollment
record through `student_id`, `school_year_id`, and `section_id`, then displays
section/adviser data through relationships instead of stale or invalid fields.

## Files Modified

- `app/Http/Controllers/Student/StudentProfileController.php`
  - Loads the active-school-year enrollment first.
  - Falls back to the latest enrollment if no active-school-year match exists.
  - Loads enrollment history for the academic tab.
  - Loads section/adviser through a non-conflicting relationship name.
- `app/Models/Enrollment.php`
  - Adds `sectionModel()` as a non-conflicting relationship to `sections`.
- `resources/views/admin-modules/management/student-profile.blade.php`
  - Uses relationship-backed section data in the profile header.
  - Passes enrollment history to the academic tab component.
- `resources/views/components/student-profile/academic-tab.blade.php`
  - Fixes invalid relationship references.
  - Displays adviser, grade level, section, status, and enrollment history from real enrollment records.
- `resources/views/components/student-profile/qr-tab.blade.php`
  - Uses relationship-backed section data for QR modal display.

## Verification

- `php -l app/Http/Controllers/Student/StudentProfileController.php` passed.
- `php -l app/Models/Enrollment.php` passed.
- `npm run build` passed.

## Remaining Work

- Re-open an enrolled student's profile in the browser and confirm the header,
  Academic tab, and QR modal show the current enrollment.
