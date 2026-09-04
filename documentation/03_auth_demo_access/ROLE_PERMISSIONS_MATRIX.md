# Role Permissions & Access Control Matrix

The **CIS Capstone** system enforces a strict Role-Based Access Control (RBAC) model implemented via `App\Http\Middleware\EnsureUserHasRole`.

---

## 1. System Roles

The application defines 4 primary roles as constants on `App\Models\User`:

| Role Constant | Database Value | UI Title | Purpose |
|---|---|---|---|
| `User::ROLE_SUPER_ADMIN` | `'super_admin'` | **Super Admin** | System governance, security audit logs, admin activity tracking, and user account creation. |
| `User::ROLE_ADMIN` | `'admin'` | **School Admin** | School-level academic operations: sections, subjects, teachers, schedules, student imports, and monitoring. |
| `User::ROLE_TEACHER` | `'teacher'` | **Teacher** | Classroom period verification, DepEd trimester grading, at-risk student monitoring, and classroom reports. |
| `User::ROLE_SCANNER_OPERATOR` | `'scanner_operator'` | **Scanner Operator** | Dedicated account for running the self-service QR scan station kiosk and monitoring daily time-in/time-out logs. |

---

## 2. Permissions & Route Access Matrix

| Feature / URL Pattern | Route Middleware Guard | Super Admin | School Admin | Teacher | Scanner Operator |
|---|---|:---:|:---:|:---:|:---:|
| **Super Admin Dashboard** (`/super-admin/dashboard`) | `role:super_admin` | ✅ | ❌ | ❌ | ❌ |
| **User Account Provisioning** (`/users`, `POST /teachers`) | `role:super_admin` | ✅ | ❌ | ❌ | ❌ |
| **Security Audit Logs** (`/security-audit-log`) | `role:super_admin` | ✅ | ❌ | ❌ | ❌ |
| **Admin Activity Log** (`/recent-activity`) | `role:super_admin` | ✅ | ❌ | ❌ | ❌ |
| **School Admin Dashboard** (`/dashboard`) | `role:admin` | ❌ | ✅ | ❌ | ❌ |
| **Academic Setup** (`/academic`, `/sections`, `/subjects`) | `role:admin` | ❌ | ✅ | ❌ | ❌ |
| **Teaching Assignments** (`/teaching-assignments`) | `role:admin` | ❌ | ✅ | ❌ | ❌ |
| **Bulk Student Import** (`/bulk-import`) | `role:admin` | ❌ | ✅ | ❌ | ❌ |
| **Schedule Configuration** (`/schedule-configuration`) | `role:admin` | ❌ | ✅ | ❌ | ❌ |
| **QR Station Kiosk** (`/qr-station`, `/scan`) | `role:scanner_operator` OR `role:admin` | ❌ | ✅ | ❌ | ✅ |
| **Time-In / Time-Out Logs** (`/time-in-time-out-history`) | `role:scanner_operator` OR `role:admin` | ❌ | ✅ | ❌ | ✅ |
| **Teacher Portal** (`/teacher/student-management`) | `role:teacher` | ❌ | ❌ | ✅ | ❌ |
| **Classroom Attendance** (`/room-attendance`) | `role:teacher` | ❌ | ❌ | ✅ | ❌ |
| **Trimester Grading Engine** (`/teacher/grading-system/*`) | `role:teacher` | ❌ | ❌ | ✅ | ❌ |
| **At-Risk Analytics** (`/teacher/grading-system/at-risk`) | `role:teacher` | ❌ | ❌ | ✅ | ❌ |

---

## 3. Enforcement Architecture

### Middleware Execution (`EnsureUserHasRole.php`)

```php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    $normalizedRoles = array_map(
        fn (string $role) => $role === 'protected_admin' ? 'super_admin' : $role,
        $roles
    );

    if (! $user->hasRole(...$normalizedRoles)) {
        abort(403, 'Unauthorized. You do not have permission to access this resource.');
    }

    return $next($request);
}
```

### Unauthorized Request Handling
* Unauthenticated requests are redirected immediately to `/login` via `route('login')`.
* Authenticated users attempting to access endpoints outside their assigned role receive an HTTP `403 Forbidden` abort response.
