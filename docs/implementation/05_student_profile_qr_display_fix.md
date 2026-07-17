# Student Profile QR Display Fix

## Summary

Fixed student profile QR display so the generated QR image exists before the
profile tab renders. Previously, QR PNG files were only generated when using
QR show/download routes, so profiles could have a `qr_codes` record but no
displayable image file.

## Files Created

- `app/Services/QrSystem/QRCodeService.php`
- `docs/implementation/05_student_profile_qr_display_fix.md`

## Files Modified

- `app/Http/Controllers/QrSystemFeature/QrCode/QrCodeController.php`
  - Reuses `QRCodeService` for image generation and Base64 output.
- `app/Http/Controllers/Student/StudentProfileController.php`
  - Ensures the student's QR image exists before rendering the profile.
  - Passes a resolved public QR image URL to the QR tab.
- `app/Models/QrCode.php`
  - Makes `hasImage()` verify that the stored image file exists.
- `resources/views/admin-modules/management/student-profile.blade.php`
  - Passes the resolved QR URL into the QR tab component.
- `resources/views/components/student-profile/qr-tab.blade.php`
  - Uses the resolved QR URL instead of manually building `asset('storage/...')`.

## Verification

- `php -l app/Services/QrSystem/QRCodeService.php` passed.
- `php -l app/Http/Controllers/QrSystemFeature/QrCode/QrCodeController.php` passed.
- `php -l app/Http/Controllers/Student/StudentProfileController.php` passed.
- `php -l app/Models/QrCode.php` passed.
- `npm run build` passed.

## Remaining Work

- Re-open a student profile with a QR code record and confirm the QR image appears.
