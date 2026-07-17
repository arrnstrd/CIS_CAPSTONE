# Academic Tabs AJAX Filtering

## Summary

Implemented scoped AJAX filtering for the Academic tabs under `/academic`.
Enrollment, Section, and Subject now use independent namespaced query
parameters, independent pagination page names, and scoped table-panel refreshes.

## Files Created

- `app/Http/Controllers/AcademicFeature/AcademicController.php`
- `resources/views/academic/subjects/index.blade.php`
- `tests/Feature/AcademicTabsTest.php`
- `docs/implementation/01_academic_tabs_ajax_filtering.md`

## Files Modified

- `routes/web.php` - routes `/academic` through `AcademicController`.
- `app/Http/Controllers/AcademicFeature/SectionController.php` - extracts reusable section query logic and whitelists standalone pagination params.
- `app/Http/Controllers/AcademicFeature/SubjectController.php` - adds filtered/paginated subject listing and AJAX JSON responses for CRUD.
- `resources/views/admin-modules/academic/academic.blade.php` - adds scoped tab pane IDs and the academic enrollment modal.
- `resources/views/admin-modules/academic/enrollment.blade.php` - adds the unenrolled scoped panel with `enrollment_*` params.
- `resources/views/admin-modules/academic/section.blade.php` - adds the section scoped panel, filters, table, pagination, and modals.
- `resources/views/admin-modules/academic/subject.blade.php` - adds the subject scoped panel, filters, table, pagination, and modals.
- `resources/js/ajax-crud.js` - adds scoped refresh, namespaced tab filter handling, scoped pagination, and generic AJAX form support.
- `resources/js/enrollment.js` - passes the enrollment scope to AJAX CRUD refreshes.
- `resources/js/section.js` - passes the section scope to AJAX CRUD refreshes.

## Verification

- `php -l app/Http/Controllers/AcademicFeature/AcademicController.php` passed.
- `php -l app/Http/Controllers/AcademicFeature/SectionController.php` passed.
- `php -l app/Http/Controllers/AcademicFeature/SubjectController.php` passed.
- `npm run build` passed.
- `php artisan test tests/Feature/AcademicTabsTest.php` could not run assertions because this PHP environment is missing the SQLite PDO driver required by `phpunit.xml`.

## Remaining Work

- Re-run `php artisan test tests/Feature/AcademicTabsTest.php` in an environment with `pdo_sqlite` enabled.
