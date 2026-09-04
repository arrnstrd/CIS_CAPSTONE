# Local Development Seed Accounts

For local development and testing, the application database seeders populate standard test accounts corresponding to each major system role.

> [!WARNING]
> The accounts listed below are strictly for local development and testing environments. Never use these credentials in staging or production.

---

## 1. Pre-Configured Seed Users

Running `php artisan db:seed` executes `DatabaseSeeder`, which provisions the following accounts:

| Role | Name | Email | Default Password | Provisioning Seeder |
|---|---|---|---|---|
| **Super Admin** | CIS Admin | `superadmin@cis.edu.ph` | `password` (default factory password) | `DatabaseSeeder.php` |
| **Teacher** | Arriane Estrada | `arriane.estrada.dev` | `Password123` | `TeacherSeeder.php` |

---

## 2. Additional Admin Provisioning

The `database/seeders/AdminSeeder.php` provides a dedicated template for local school administrators:

| Role | Name | Email | Default Password | Provisioning Seeder |
|---|---|---|---|---|
| **School Admin** | Test Admin | `admin@example.com` | `password123` | `AdminSeeder.php` |

To execute this specific seeder:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## 3. Post-Login Redirection Flow

When logging in at `/login`, `App\Http\Controllers\Shared\AuthController` evaluates the authenticated user's role and redirects them to their respective homepage:

* **`super_admin`** ➔ Redirects to `/super-admin/dashboard` (`route('super_admin.dashboard')`)
* **`admin`** ➔ Redirects to `/dashboard` (`route('admin.dashboard')`)
* **`teacher`** ➔ Redirects to `/room-attendance` (`route('room-attendance.index')`)
* **`scanner_operator`** ➔ Redirects to `/qr-station` (`route('qr-station.index')`)
