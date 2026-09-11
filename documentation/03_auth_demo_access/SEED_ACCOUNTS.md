# Local Development Seed Accounts

For local development and testing, the application database seeders populate standard test accounts corresponding to each major system role, matching the **Quick Demo Access** login flow.

> [!WARNING]
> The accounts listed below are strictly for local development and testing environments. Never use these credentials in staging or production.

---

## 1. Pre-Configured Seed Users

Running `php artisan db:seed` executes `DatabaseSeeder`, which calls `UserSeeder` and provisions/updates the following accounts matching the Quick Demo Access dropdown on `/login`:

| Role | Name | Email | Default Password | Quick Demo Role |
|---|---|---|---|---|
| **Super Admin** | Super Admin | `superadmin@cis.edu.ph` | `ProtectedAdminAccount2026#` | `super_admin` |
| **School Admin** | School Admin | `schooladmin@cis.edu.ph` | `SchoolAdminPassword2026#` | `school_admin` |
| **Teacher** | Arriane Estrada | `teacher@cis.edu.ph` | `TeacherPassword2026#` | `teacher` |
| **Teacher (Dev Alias)** | Arriane Estrada | `arriane.estrada.dev` | `TeacherPassword2026#` | `teacher` |
| **Scanner Operator** | Scanner Operator | `scanner@cis.edu.ph` | `ScannerPassword2026#` | `scanner_operator` |

*Note: All seeders use `User::updateOrCreate` matched by email. Running `php artisan db:seed` or `sail artisan db:seed` multiple times is idempotent and will never duplicate accounts.*

---

## 2. Post-Login Redirection Flow

When logging in at `/login`, `App\Http\Controllers\Shared\AuthController` evaluates the authenticated user's role and redirects them to their respective homepage:

* **`super_admin`** ➔ Redirects to `/super-admin/dashboard` (`route('super_admin.dashboard')`)
* **`admin`** ➔ Redirects to `/dashboard` (`route('admin.dashboard')`)
* **`teacher`** ➔ Redirects to `/room-attendance` (`route('room-attendance.index')`)
* **`scanner_operator`** ➔ Redirects to `/qr-station` (`route('qr-station.index')`)
