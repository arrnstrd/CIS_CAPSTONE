# Subject Level Normalization Fix

## Summary

Fixed subject level validation failures caused by mismatched level values.
The `subjects.level` database enum accepts `elementary`, `hs`, and `shs`, while
some forms were submitting section-style values such as `highschool` and
`senior_high_school`.

## Files Modified

- `app/Models/Subject.php`
  - Added canonical level labels and normalization helpers.
- `app/Http/Requests/Academic/Subject/StoreSubjectRequest.php`
  - Normalizes incoming `level` before validation.
  - Validates against the model's canonical subject level values.
- `app/Http/Requests/Academic/Subject/UpdateSubjectRequest.php`
  - Normalizes incoming `level` before validation.
  - Validates against the model's canonical subject level values.
- `app/Services/Academic/SubjectService.php`
  - Normalizes `level` before create and update.
- `app/Http/Controllers/AcademicFeature/SubjectController.php`
  - Normalizes subject level filters before querying.
- `resources/views/admin-modules/academic/subject.blade.php`
  - Uses canonical subject level option values and display labels.
- `resources/views/academic/subjects/index.blade.php`
  - Uses canonical subject level option values and display labels.
- `tests/Feature/AcademicTabsTest.php`
  - Updated subject test data to use canonical `hs`.

## Verification

- `php -l app/Models/Subject.php` passed.
- `php -l app/Http/Requests/Academic/Subject/StoreSubjectRequest.php` passed.
- `php -l app/Http/Requests/Academic/Subject/UpdateSubjectRequest.php` passed.
- `php -l app/Services/Academic/SubjectService.php` passed.
- `php -l app/Http/Controllers/AcademicFeature/SubjectController.php` passed.
- `php -l app/Services/Grading/SubjectWeightResolver.php` passed.
- `npm run build` passed.

## Remaining Work

- Re-test creating/updating subjects in the browser.
