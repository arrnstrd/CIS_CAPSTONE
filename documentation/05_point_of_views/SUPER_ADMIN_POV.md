# Point of View: Super Admin

The **Super Admin** represents the apex administrative authority responsible for system governance, institutional user provisioning, and security compliance auditing.

---

## 1. Sidebar Navigation Mapping

The Super Admin UI is rendered via `resources/views/components/layouts/admin.blade.php` with sidebar navigation driven by `resources/views/components/layouts/admin/sidebar.blade.php`.

```text
SUPER ADMIN SIDEBAR NAVIGATION
├── Dashboard                     ➔ route('super_admin.dashboard')    ➔ /super-admin/dashboard
├── ACCESS CONTROL & AUDITING
│   ├── User Management           ➔ route('users.index')              ➔ /users
│   ├── Security Audit Log        ➔ route('security-audit-log.index') ➔ /security-audit-log
│   └── Recent Activity           ➔ route('recent-activity.index')   ➔ /recent-activity
```

---

## 2. Controller & Route Directory Structure

| Sidebar Item | HTTP Route | Controller & Method | Active Blade View |
|---|---|---|---|
| **Dashboard** | `GET /super-admin/dashboard` | `SuperAdmin\Dashboard\DashboardController@index` | `super-admin/dashboard/index.blade.php` |
| **User Management** | `GET /users` | `SuperAdmin\UserManagement\UserManagementController@index` | `super-admin/user-management/index.blade.php` |
| **Teacher Provisioning** | `POST /teachers` | `SuperAdmin\UserManagement\TeacherProvisioningController@store` | Redirects to `users.index` |
| **Security Audit Log** | `GET /security-audit-log` | `SuperAdmin\Security\SecurityAuditLogController@index` | `super-admin/security/security-audit-log.blade.php` |
| **Export Audit Excel** | `GET /security-audit-log/download` | `SuperAdmin\Security\SecurityAuditLogController@downloadExcel` | Binary stream (`.xlsx`) |
| **Export Audit PDF** | `GET /security-audit-log/download-pdf` | `SuperAdmin\Security\SecurityAuditLogController@downloadPdf` | Binary stream (`.pdf`) |
| **Recent Activity** | `GET /recent-activity` | `SuperAdmin\Security\RecentActivityController@index` | `super-admin/security/recent-activity.blade.php` |

---

## 3. Core Responsibilities & Boundaries

1. **Sole Authority on Teacher Account Creation:**
   In compliance with institutional security rules, School Admins cannot provision new teacher login accounts. Only Super Admins have permission to hit `POST /teachers` or manage user state in `UserManagementController`.
2. **Security & Login Audit Logs:**
   Tracks failed logins, IP addresses, user agent strings, and role transitions (`LoginLog` & `AdminActivityLog`).
3. **Downloadable Audit Trails:**
   Renders exportable records formatted with school headers for DepEd or institutional compliance audits.
